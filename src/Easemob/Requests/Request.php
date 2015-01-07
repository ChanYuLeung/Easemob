<?php

namespace Easemob\Requests;

use Curl\Curl;

/**
 * @Synopsis  请求类
 */
class Request {


    private static $debug = False;

    /**
     * @Synopsis  开启调试模式
     *
     * @Returns   
     */
    public static function enableDebug(){
        self::$debug = True;
    }

    /**
     * @Synopsis  关闭调试模式
     *
     * @Returns   
     */
    public static function disableDebug(){
        self::$debug = False;
    }

    /**
     * 向环信请求
     * @param $url
     * @param string $params
     * @param string $type POST|GET
     * @return mixed
     * @throws \ErrorException
     */
    public static function contact($url, $postData= '', $type = 'POST' ,$headers= array()) {
        $curl = new Curl();
        $curl->setUserAgent('curl/7.35.0');
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        foreach ($headers as $header) {
            $curl->setHeader($header['key'], $header['value']);
        }

        switch ($type) {
        case 'POST': {
            $curl->post($url, $postData);
            break;
        }
        case 'GET': {
            $curl->get($url);
            break;
        }
        case 'PUT': {
            $curl->put($url,$postData);
            break;
        }
        case 'DELETE': {
            $curl->delete($url);
            break;
        }
        }
        $curl->close();

        if (self::$debug) {
            //echo "URL: {$url}\n Header: {self::$bearer} \nBody: \"{$postData}\"\n";
        }
        if ($curl->error) {
            throw new \ErrorException('CURL Error: ' . $curl->error_message, $curl->error_code);
        }
        if (self::$debug) {
            echo "return: {$curl->raw_response} \n";
        }
        return $curl->raw_response;
    }
}
?>
