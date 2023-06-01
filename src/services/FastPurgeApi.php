<?php

namespace fork\akamaiinvalidator\services;

use fork\akamaiinvalidator\AkamaiInvalidator;
use Psr\Http\Message\ResponseInterface;
use yii\base\Component;

/**
 * Fast Purge API service
 */
class FastPurgeApi extends Component
{
    /**
     * Invalidates cache tags using the Akamai Fast Purge API
     *
     * @see https://techdocs.akamai.com/purge-cache/reference/invalidate-tag
     * @see https://techdocs.akamai.com/developer/docs/authenticate-with-edgegrid
     * @see https://github.com/akamai/AkamaiOPEN-edgegrid-php-client
     * @param string[] $tags Array of tags to be invalidated
     * @return ResponseInterface The API response
     */
    public function invalidateTags(array $tags): ResponseInterface
    {
        $client = \Akamai\Open\EdgeGrid\Client::createFromEdgeRcFile();

        $network = AkamaiInvalidator::getInstance()->getSettings()->network;

        $url = '/ccu/v3/invalidate/tag/' . $network;

        $response = $client->request('POST', $url, [
            'json' => [
                'objects' => $tags,
            ],
            'headers' => [
                'accept' => 'application/json',
            ],
        ]);

        return $response;
    }
}
