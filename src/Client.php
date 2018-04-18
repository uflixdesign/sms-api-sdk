<?php

namespace UFXDSMSAPI;

/**
 * Class Client: Use to send sms through UFLIX DESIGN's SMS Gateway
 * @package UFXDSMSAPI
 */
class Client{

    protected $config, $user, $pass;

    const ROUTE_PROMO    = 'promo';
    const ROUTE_PROMODND = 'promodnd';
    const ROUTE_TRANS    = 'trans';

    public function __construct($user, $pass, $config = []){
        $this->config = array_merge([
            'url' => 'http://api.sms.uflixdesign.com',
            'version' => '1',
        ], $config);

        $this->config['url'] = rtrim( $this->config['url'], '/' );

        $this->user = $user;
        $this->pass = $pass;
    }

    protected function url($resource = ''){
        $url = "{$this->config['url']}/v{$this->config['version']}/";
        if($resource){
            $url = $url . '/' . ltrim($resource, '/');
        }
        return $url;
    }


    /**
     * @param bool $getException
     * @return bool
     * @throws \Exception
     */
    public function APIStatus($getException = false){
        $curl = curl_init($this->url('api-status'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);

        try{
            if(!$result){
                throw new \Exception('Failed to connect ' . $this->url('sms'));
            }
            $result = json_decode($result);
            if($result){
                if(isset($result->error) AND $result->error){
                    throw new \Exception($result->msg);
                }
            }else{
                throw new \Exception('Some error occurred.');
            }
        }catch (\Exception $e){
            if($getException){
                throw new \Exception($e->getMessage());
            }
            return false;
        }

        return true;
    }


    /**
     * Sends a message to one or multiple numbers.
     * @param string|array $numbers
     * @param string $message
     * @param string $route
     * @return string
     * @throws \Exception
     */
    public function send($numbers, $message, $route = self::ROUTE_PROMO){

        $data = [
            'route' => $route,
            'numbers' => $numbers,
            'message' => $message
        ];

        $curl = curl_init($this->url('sms'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "$this->user:$this->pass");

        $result = curl_exec($curl);
        if(!$result){
            throw new \Exception('Failed to connect ' . $this->url('sms'));
        }

        $result = json_decode($result);
        if($result){
            if(isset($result->error) AND $result->error){
                throw new \Exception($result->msg);
            }elseif (is_string($result->response)){
                throw new \Exception($result->response);
            }
        }else{
            throw new \Exception('Some error occurred.');
        }

        return $result->response;
    }

}