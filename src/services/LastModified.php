<?php

namespace fork\akamaiinvalidator\services;

use craft\elements\Entry;
use DateTime;
use DateTimeZone;
use yii\base\Component;

/**
 * Last Modified service
 */
class LastModified extends Component
{
    /**
     * Get the Last-Modified header value for an Entry
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified
     * @param Entry $entry The entry
     * @return string Timestamp at which $entry was last modified, formatted for the Last-Modified header
     */
    public function getLastModifiedHeader(Entry $entry): string
    {
        /** @var DateTime */
        $lastModified = $entry->dateUpdated;
        $lastModified->setTimezone(new DateTimeZone('GMT'));

        /** @var string */
        $lastModified = $lastModified->format('D, d M Y H:i:s') . ' GMT';

        return $lastModified;
    }
}
