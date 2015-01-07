<?php
namespace Easemob;

use Curl\Curl;
use Easemob\Requests\Request;

class Easemob {
    const URL = 'https://a1.easemob.com';

    private $client_id;
    private $client_secret;
    private $org_name;
    private $app_name;
    private $data_path;     // 存储环信的文件夹地址,如token,调试信息等
    private $url;           // 环信常用的请求地址 URL/{org_name}/{app_name}
    private $debug = false; // 是否开启调试
    private $token = '';    // 环信授权码
    private $expire = '';   // 授权码过去时间

    /**
     * @Synopsis 构造函数 
     *
     * @Param $options ['client_id']
     * @Param $options ['client_secret']
     * @Param $options ['org_name']
     * @Param $options ['app_name']
     * @Param $options ['data_path']
     *
     * @Returns   
     */
    public function __construct($options, $debug = false) {
        $paramsMap = array(
            'client_id',
            'client_secret',
            'org_name',
            'app_name',
            'data_path'
        );
        foreach ($paramsMap as $paramsName) {
            if (!isset($options[$paramsName])) {
                throw new \InvalidArgumentException("初始化未设置[{$paramsName}]");
            } else {
                $this->$paramsName = $options[$paramsName];
            }
        }
        $this->url = self::URL . '/' . $this->org_name . '/' . $this->app_name;
    }

    /**
     * @Synopsis  获取Token
     *
     * @Returns   
     */
    public function getToken() {
        //从本类对象获取
        if(time() < $this->expire && !empty($this->token)){
            return $this->token;
        }
        //从文件获取
        $authInfo = $this->getTokenFromCache();
        if($authInfo == False){
            //从Api获取
            $authInfo = $this->getAuthInfoFromApi();
            $authInfo['expire'] = time() + $authInfo['expires_in'];
            unset($authInfo['expires_in']);
            unset($authInfo['application']);
            $this->cacheAuth($authInfo);
        }
        $this->token = $authInfo['access_token'];
        $this->expire = $authInfo['expire'];
        return $this->token;
    }

    /**
     * @Synopsis  封装调用Api协议
     *
     * @Param $type 类型,GET POST, PUT, DELETE
     * @Param $path 路径
     * @Param $data 数据
     * @Param $hasBearer 是否需要token
     *
     * @Returns   
     */
    private function ApiRequest($type,$path,$data='',$hasBearer= True){
        $token = $hasBearer ? $this->getToken() : '';
        $url = $this->url.$path;
        $headers = array(
            array('key' => 'Content-Type', 'value'=>'application/json'),
        );
        if($token){
            $headers[] = array('key' => 'Authorization', 'value'=>'Bearer '.$token);
        }

        $response = Request::contact($url, json_encode($data),$type,$headers);

        return  json_decode($response,True);
    }

    /**
     * @Synopsis  上传图片等上传操作
     *
     * @Param $path
     * @Param $filepath
     * @Param $hasBearer
     *
     * @Returns   
     */
    public function ApiUpload($path,$filepath,$hasBearer = True){
        $token = $hasBearer ? $this->getToken() : '';
        if(is_file($filepath)){
            $file = '@'.$filepath;
        }else{
            throw new \Exception('filepath is not a file');
        }
        $headers = array(
            array('key' => 'Content-Type', 'value'=>'multipart/form-data'),
            array('key' => 'restrict-access', 'value'=>'true'),
            array('key' => 'Authorization', 'value'=>'Bearer '.$token)
        );
        $url = $this->url.$path;
        $response = Request::contact($url, array( 'file'=>$file),'POST',$headers);
        return $response;
    }

    /**
     * @Synopsis  下载图片等下载操作
     *
     * @Param $path
     * @Param $shareSecret
     * @Param $hasBearer
     * @Param $isThumbnail
     *
     * @Returns   
     */
    public function ApiDownload($path,$shareSecret,$hasBearer = True,$isThumbnail = False){
        $headers = array(
            array('key' => 'Accept', 'value'=>'application/octet-stream'),
            array('key' => 'share-secret', 'value'=>$shareSecret),
            array('key' => 'Authorization', 'value'=>'Bearer '.$token)
        );
        if($isThumbnail){
            $headers[] = array('key' => 'thumbnail', 'value'=>'true');
        }
        $url = $this->url.$path;
        $response = Request::contact($url,'','GET',$headers);
        return $response;

    }

    /**
     * @Synopsis  使用POST方法调用Api
     *
     * @Param $path
     * @Param spostData
     * @Param $hasBearer
     *
     * @Returns   
     */
    public function ApiPost($path,$postData,$hasBearer = True){
        return $this->ApiRequest('POST',$path,$postData,$hasBearer);
    }

    /**
     * @Synopsis  使用PUT方法调用Api
     *
     * @Param $path
     * @Param $putData
     * @Param $hasBearer
     *
     * @Returns   
     */
    public function ApiPut($path,$putData,$hasBearer = True){
        return $this->ApiRequest('PUT',$path,$putData,$hasBearer);
    }

    /**
     * @Synopsis  使用GET方法调用Api
     *
     * @Param $path
     * @Param $hasBearer
     *
     * @Returns   
     */
    public function ApiGet($path,$hasBearer = True){
        return $this->ApiRequest('GET',$path,$hasBearer);
    }

    /**
     * @Synopsis  使用DELETE方法调用Api
     * 
     *
     * @Param $path
     * @Param $hasBearer
     *
     * @Returns   
     */
    public function ApiDelete($path,$hasBearer = True){
        return $this->ApiRequest('DELETE',$path,$hasBearer);
    }

    /**
     * @Synopsis  从接口中获取认证信息
     *
     * @Returns   
     */
    private function getAuthInfoFromApi(){
        $options['grant_type']    = "client_credentials";
        $options['client_id']     = $this->client_id;
        $options['client_secret'] = $this->client_secret;
        return $this->ApiPost('/token',$options,False);
    }

    /**
     * @Synopsis  缓存认证信息
     *
     * @Param $authInfo 认证信息
     * @Param $dataPath 存储文件夹路径
     *
     * @Returns   
     */
    private function cacheAuth($authInfo,$dataPath = ''){
        $cacheFile = $this->getCacheFile($dataPath);
        $fp = fopen($cacheFile, 'w');
        fwrite($fp, serialize($authInfo));
        fclose($fp);
        return true;
    }

    /**
     * @Synopsis  从本地缓存中读取认证信息
     *
     * @Param $dataPath
     *
     * @Returns   
     */
    protected function getTokenFromCache($dataPath=''){
        $cacheFile = $this->getCacheFile($dataPath);
        try{
            $fp = fopen($cacheFile, 'r');
            $content = fread($fp, filesize($cacheFile));
            fclose($fp);
            $authInfo = unserialize($content);
            return time() > $authInfo['expire'] ? False: $authInfo;
        }catch(Exception $e){
            return False;
        }
    } 

    /**
     * @Synopsis  获取缓存文件地址
     *
     * @Param $dataPath
     *
     * @Returns   
     */
    private function getCacheFile($dataPath = ''){
        $cachePath = empty($dataPath) ? $this->data_path : $dataPath;
        if(!is_dir($cachePath)){
            mkdir($cachePath,0766,true);
        }
        return $cachePath . '/token';
    }

    /**
     * @Synopsis  使Json数据以友好方式显示
     * 在PHP 5.4以上,可以使用 $json_string = json_encode($data, JSON_PRETTY_PRINT)
     *
     * @Param $data
     *
     * @Returns   
     */
    function prettyPrint($data) {
        if(version_compare(PHP_VERSION,'5.4.0','>')) {
            return json_encode($data,JSON_PRETTY_PRINT);
        }
        $json = json_encode($data);
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } else if( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            } else if ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
        }

        return $result;
    }

    /**
     * @Synopsis  开启调试模式
     *
     * @Returns   
     */
    public function enableDebug(){
        $this->debug = true;
    }

    /**
     * @Synopsis  关闭调试模式
     *
     * @Returns   
     */
    public function disableDebug(){
        $this->debug = false;
    }
}
