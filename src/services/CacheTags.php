<?php

namespace fork\akamaiinvalidator\services;

use yii\base\Component;

/**
 * Cache Tags service
 */
class CacheTags extends Component
{
    /** @var string[] Cache tags for the current request */
    private $cacheTags = [];

    /**
     * Add a cache tag to the current response
     *
     * @param string $tag The tag to be added
     */
    public function addCacheTag(string $tag): void
    {
        if (!in_array($tag, $this->cacheTags)) {
            array_push($this->cacheTags, $tag);
        }
    }

    /**
     * Get and format cache tags for the Edge-Cache-Tag header
     *
     * @return string The cache tags, delimited by commas
     */
    public function getCacheTagHeader(): string
    {
        return collect($this->cacheTags)->join(', ');
    }
}
