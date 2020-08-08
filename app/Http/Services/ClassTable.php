<?php
namespace App\Http\Services;

class ClassTable
{

    public function post($body,$apiStr)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://192.168.31.XX:xxx/api/']);
        $res = $client->request('POST', $apiStr,
            ['json' => $body,
                'headers' => [
                    'Content-type'=> 'application/json',
//    'Cookie'=> 'XDEBUG_SESSION=PHPSTORM',
                    "Accept"=>"application/json"]
            ]);
        $data = $res->getBody()->getContents();

        return $data;
    }

    public function get($apiStr,$header)
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://192.168.31.XX:xxx/api/']);
        $res = $client->request('GET', $apiStr,['headers' => $header]);
        $statusCode= $res->getStatusCode();

        $header= $res->getHeader('content-type');

        $data = $res->getBody();

        return $data;
    }
}
