<?php

namespace yii2rbac\client;

/**
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

    public function __construct(string $url, string $jwtToken)
    {
        $this->url = $url;
        $this->headers['Authorization'] = sprintf('Bearer %s', $jwtToken);
        $this->client = new \GuzzleHttp\Client();
    }

    public function check(string $permission, array $params = []){
        $method = 'POST';
        $body = [
            'data' => [
                "type" => "Authorize",
                "attributes" => [
                    'permission' => $permission,
                    'data' => $params,
                ],
            ],
        ];

        $options  = [
            'headers' => $this->headers,
            'body' => json_encode($body)
        ];

        $response  = $this->client->request($method, $this->url, $options);
        return $response->getStatusCode();
        if($response->getStatusCode()  == 200){
            return $response->getBody();

        }
        return false;
    }
}