<?php

namespace fork\akamaiinvalidator;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\helpers\ElementHelper;
use craft\helpers\Queue;
use craft\utilities\ClearCaches;
use craft\web\View;
use fork\akamaiinvalidator\jobs\InvalidateJob;
use fork\akamaiinvalidator\models\Settings;
use fork\akamaiinvalidator\services\CacheTags;
use fork\akamaiinvalidator\services\FastPurgeApi;
use yii\base\Event;

/**
 * akamai-invalidator plugin
 *
 * @method static AkamaiInvalidator getInstance()
 * @author Fork Unstable Media GmbH <obj@fork.de>
 * @copyright Fork Unstable Media GmbH
 * @license MIT
 * @property-read CacheTags $cacheTags
 * @property-read FastPurgeApi $fastPurgeApi
 */
class AkamaiInvalidator extends Plugin
{
    /**
     * Whether cache tags get invalidated on entry save
     *
     * @var bool
     */
    public bool $invalidateOnSave = true;

    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                'cacheTags' => ['class' => CacheTags::class],
                'fastPurgeApi' => ['class' => FastPurgeApi::class],
            ],
        ];
    }

    public function init()
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });
    }

    private function attachEventHandlers(): void
    {
        /** @var \fork\akamaiinvalidator\models\Settings */
        $settings = AkamaiInvalidator::getInstance()->getSettings();

        if ($settings->getEnableInvalidateAll()) {
            /**
             * Adds Craft cache option that invalidates all pages
             */
            Event::on(
                ClearCaches::class,
                ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
                function(RegisterCacheOptionsEvent $event) {
                    $event->options[] = [
                        'key' => 'akamai-invalidator',
                        'label' => Craft::t('akamai-invalidator', 'Akamai Cache'),
                        'action' => function() {
                            Queue::push(new InvalidateJob(['tags' => ['all']]));
                        },
                    ];
                }
            );
        }

        /**
         * Invalidates individual entries after they are saved
         */
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
                /** @var \fork\akamaiinvalidator\models\Settings */
                $settings = AkamaiInvalidator::getInstance()->getSettings();

                if (!$settings->getInvalidateOnSave()) {
                    // Don't do anything when invalidation is disabled
                    return;
                }

                /* @var Entry $entry */
                $entry = $event->sender;

                if (ElementHelper::isDraftOrRevision($entry)) {
                    // don't do anything with drafts or revisions
                    return;
                }

                if ($entry->url == null) {
                    // Ignore entries without URL
                    return;
                }

                Queue::push(new InvalidateJob(['tags' => ['entry-' . $entry->id]]));
            }
        );

        /**
         * Tasks that are run after the page finished rendering
         */
        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
            function() {
                /** @var \craft\web\Application */
                $app = Craft::$app;

                // Attach `all` cache tag that is used to invalidate all pages at once
                AkamaiInvalidator::getInstance()->cacheTags->addCacheTag('all');

                $entry = $app->getUrlManager()->getMatchedElement();
                if ($entry) {
                    // Attach an entry-specific cache tag that is used to invalidate pages including specific entries
                    AkamaiInvalidator::getInstance()->cacheTags->addCacheTag('entry-' . $entry->id);
                }

                // Collect and add all cache tags to the response
                $cacheTagsHeader = AkamaiInvalidator::getInstance()->cacheTags->getCacheTagHeader();
                $app->response->headers->add('Edge-Cache-Tag', $cacheTagsHeader);
            }
        );
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }
}
