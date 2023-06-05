<?php

namespace fork\akamaiinvalidator\services;

use craft\base\ElementInterface;
use DateTime;
use DateTimeZone;
use yii\base\Component;

/**
 * Last Modified service
 */
class LastModified extends Component
{
    /**
     * Get the Last-Modified header value for an Element
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified
     * @param ElementInterface $entry The entry
     * @return string Timestamp at which $entry was last modified, formatted for the Last-Modified header
     */
    public function getLastModifiedHeader(ElementInterface $entry): string
    {
        /** @var DateTime */
        $lastModified = $entry->dateUpdated;
        $lastModified->setTimezone(new DateTimeZone('GMT'));

        /** @var string */
        $lastModified = $lastModified->format('D, d M Y H:i:s') . ' GMT';

        return $lastModified;
    }
}
