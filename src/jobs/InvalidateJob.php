<?php

namespace fork\akamaiinvalidator\jobs;

use Craft;
use craft\queue\BaseJob;
use fork\akamaiinvalidator\AkamaiInvalidator;

class InvalidateJob extends BaseJob
{
    /** @var array */
    public $tags = [];

    public function execute($queue): void
    {
        AkamaiInvalidator::getInstance()->fastPurgeApi->invalidateTags($this->tags);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('akamai-invalidator', 'Invalidate Akamai cache tags');
    }
}
