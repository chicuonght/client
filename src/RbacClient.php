<?php

namespace yii2rbac\client;

use Http\Adapter\Guzzle6\Client;
use GuzzleHttp\Psr7\Request;
use WoohooLabs\Yang\JsonApi\Client\JsonApiClient;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use WoohooLabs\Yang\JsonApi\Request\ResourceObject;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

/**
 * RBAC Client for Lumen Framework 5.4
 * Created by PhpStorm.
 * User: thanh
 * Date: 2017-06-02
 * Time: 2:36 PM
 */
class RbacClient
{


    /**
     * @var JsonApiClient
     */
    public $client;

    /**
     * @var array
     */
    public $response = [];


    /**
     * @var JsonApiRequestBuilder
     */
    public $requestBuilder;

    /**
     * RbacClient constructor.
     *
     * @param string $url
     * @param string $jwtToken
     */
    public function __construct(string $url, string $jwtToken)
    {
        $guzzleClient = Client::createWithConfig([]);
        // Instantiate the syncronous JSON:API Client
        $this->client = new JsonApiClient ($guzzleClient);

        $request = new Request('', '');
        $this->requestBuilder = new JsonApiRequestBuilder($request);
        $this->requestBuilder->setProtocolVersion("2.0")
            ->setMethod("POST")
            ->setUri($url)
            ->setHeader("Authorization", sprintf('Bearer %s', $jwtToken))
            ->setHeader("Content-Type", 'application/vnd.api+json');
    }

    /**
     * @param string $permission Permission Name
     * @param array $params      Data to check Permission Rule
     *
     * @return bool
     */
    public function checkPermission(string $permission, array $params = []): bool
    {
        $resource = new ResourceObject("Authorize");
        $resource->setAttributes([
            'permission' => $permission,
            'data'       => $params,
        ]);

        $this->requestBuilder->setJsonApiBody($resource);

        return $this->sendRequest();
    }

    /**
     * Send request to Authorize server
     * @return bool
     */
    protected function sendRequest(): bool {
        $request = $this->requestBuilder->getRequest();

        $psr7Response = $this->client->sendRequest($request);

        $response = new JsonApiResponse($psr7Response);
        $document = $response->document();

        if ($response->isSuccessfulDocument()) {
            $this->response = $document->primaryResource();
            return true;
        } else {
            $body = $psr7Response->getBody();
            $this->response = \GuzzleHttp\json_decode($body);
            return false;
        }
    }


}