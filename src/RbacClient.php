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
class RbacClient{

    public $client;
    public $url;
    public   $headers = [
        'Authorization' => null,
        'Content-Type' => 'application/vnd.api+json'
    ];

    public $response = [];

    public $error;

    public function __construct(string $url, string $jwtToken)
    {
        $this->url = $url;
        $this->headers['Authorization'] = sprintf('Bearer %s', $jwtToken);


        $this->client = new \GuzzleHttp\Client();
    }

    public function check(string $permission, array $params = []){
        $result = false;
        $resource = new ResourceObject("Authorize");
        $resource ->setAttributes([
            'permission' => $permission,
            'data' => $params,
        ]);
        $guzzleClient = Client::createWithConfig([]);
        // Instantiate the syncronous JSON:API Client
        $this->client = new JsonApiClient ($guzzleClient);
        $request = new Request('', '');
        $requestBuilder = new JsonApiRequestBuilder($request);
        $requestBuilder->setProtocolVersion("2.0")
            ->setMethod("POST")
            ->setUri($this->url)
            ->setHeader("Authorization", $this->headers['Authorization'])
            ->setHeader("Content-Type", $this->headers['Content-Type'])
            ->setJsonApiBody($resource);
        $request = $requestBuilder->getRequest();

        $psr7Response  = $this->client->sendRequest($request);

        $response = new JsonApiResponse($psr7Response);
        $document = $response->document();
        if($response->isSuccessfulDocument()){
            $result = true;
            $this->response =  $document->primaryResource();
        }else{
            $body =  $psr7Response->getBody();
            $this->response = \GuzzleHttp\json_decode($body);
        }

        return $result;
    }
}