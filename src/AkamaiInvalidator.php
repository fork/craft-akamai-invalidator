<?php

namespace fork\akamaiinvalidator;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\helpers\ElementHelper;
use craft\utilities\ClearCaches;
use craft\web\View;
use fork\akamaiinvalidator\models\Settings;
use fork\akamaiinvalidator\services\CacheTags;
use fork\akamaiinvalidator\services\FastPurgeApi;
use fork\akamaiinvalidator\services\LastModified;
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
 * @property-read LastModified $lastModified
 */
class AkamaiInvalidator extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                'cacheTags' => CacheTags::class,
                'fastPurgeApi' => FastPurgeApi::class,
                'lastModified' => LastModified::class,
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
                        AkamaiInvalidator::getInstance()->fastPurgeApi->invalidateTags(['all']);
                    },
                ];
            }
        );

        /**
         * Invalidates individual entries after they are saved
         */
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
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

                $tags = ['entry-' . $entry->id];
                AkamaiInvalidator::getInstance()->fastPurgeApi->invalidateTags($tags);
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

                    // Add Last-Modified header to the response to support invalidation
                    $lastModified = AkamaiInvalidator::getInstance()->lastModified->getLastModifiedHeader($entry);
                    $app->response->headers->add('Last-Modified', $lastModified);
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
