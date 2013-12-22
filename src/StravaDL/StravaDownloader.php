<?php

namespace StravaDL;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use StravaDL\Exception\UnauthorizedException;

class StravaDownloader
{
    private $client;
    private $client_id;
    private $client_secret;

    /**
     * @param $client_secret
     * @param $client_id
     */
    public function __construct($client_secret, $client_id)
    {
        $this->client = new Client('https://www.strava.com/api/v3');
        $this->client_secret = $client_secret;
        $this->client_id = $client_id;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function auth($code)
    {
        $request = $this->client->post('https://www.strava.com/oauth/token', null, array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
        ));

        $response = $request->send();

        return $response->json()['access_token'];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function deauth($key)
    {
        $request = $this->client->post('https://www.strava.com/oauth/deauthorize', null, array(
            'access_token' => $key
        ));

        $response = $request->send();

        return $response->json()['access_token'];
    }

    /**
     * @param $key
     * @return array|bool|float|int|string
     * @throws Exception\UnauthorizedException
     * @throws \Exception
     */
    public function getAthlete($key)
    {
        try{
            $request = $this->client->get('athlete', null, array(
                'query' => array(
                    'access_token' => $key,
                ),
            ));
            $response = $request->send();

            return $response->json();
        }
        catch(ClientErrorResponseException $e){
            if($e->getResponse()->getStatusCode() == 401)
                throw new UnauthorizedException();
            else{
                throw new \Exception();
            }
        }

    }

    /**
     * @param $key
     */
    public function getActivities($key)
    {
        $this->client->get('v3/athlete/activities', array(), array(
            'access_token' => $this->key,
    ));
}

}