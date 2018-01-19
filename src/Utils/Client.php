<?php
namespace Payment\Utils;

class Client
{
    private $config;
    private $CURL_INFO;
    private $CURL_BODY;
    private $CURL_ERROR;
    const VERSION = '6.0';

    public static $CURLOPT_ENCODING       = '';
    public static $CURLOPT_REFERER        = null;
    public static $CURLOPT_TIMEOUT        = 10; //数据传输的最大允许时间
    public static $CURLOPT_CONNECTTIMEOUT = 3; //连接超时时间
    public static $CURLOPT_USERAGENT      = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 Safari/537.36';
    public static $CURLOPT_COOKIEFILE     = null;
    public static $CURLOPT_COOKIEJAR      = null;
    public static $CURLOPT_HTTPHEADER     = null;

    public function __construct($config = array())
    {
        $this->configureDefaults($config);
    }
    public function request($method, $uri = '', array $options = array())
    {
        if($method=="POST"){
            $this->http($uri,$options);
        }else{
            $this->http($uri,$options);
        }
        return $this;
    }

    public function http($url=null, $postdata=null)
    {
        if(empty($url)){
            $url = $this->config['base_uri'];
        }
        if (self::$CURLOPT_REFERER === null) {
            $uri = parse_url($url);
            self::$CURLOPT_REFERER = $uri['scheme'] . '://' . $uri['host'];
        }
        if(isset($postdata['query'])){
            $url = self::make($postdata['query'],$url);
            $postdata = null;
        }
        $options = array(
            CURLOPT_URL                     => $url,
            CURLOPT_REFERER                 => self::$CURLOPT_REFERER,
            CURLOPT_USERAGENT               => $this->config['headers']['User-Agent'],
            CURLOPT_ENCODING                => self::$CURLOPT_ENCODING,
            CURLOPT_TIMEOUT                 => $this->config['timeout'], //数据传输的最大允许时间
            CURLOPT_CONNECTTIMEOUT          => 10, //连接超时时间
            CURLOPT_RETURNTRANSFER          => 1,
            CURLOPT_FAILONERROR             => 0,
            CURLOPT_HEADER                  => 0,
            CURLOPT_NOSIGNAL                => true,
            // CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            // CURLOPT_DNS_CACHE_TIMEOUT    => 86400,
            CURLOPT_SSL_VERIFYPEER          => false,
            CURLOPT_SSL_VERIFYHOST          => false,
            // CURLOPT_FOLLOWLOCATION       => 1,// 使用自动跳转
            // CURLOPT_MAXREDIRS            => 7,//查找次数，防止查找太深
        );
        if(is_file($postdata['cert'])&&is_file($postdata['ssl_key'])){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            $options[CURLOPT_SSLCERTTYPE] = 'PEM';
            $options[CURLOPT_SSLCERT]     = $postdata['cert'];
            $options[CURLOPT_SSLKEYTYPE]  = 'PEM';
            $options[CURLOPT_SSLKEY]      = $postdata['ssl_key'];
        }
        if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        if ($postdata!==null) {
            isset($postdata['form_params']) && $postdata['body'] = $postdata['form_params'];
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $postdata['body'];
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $this->CURL_BODY  = curl_exec($ch);
        $this->CURL_INFO  = curl_getinfo($ch);
        $this->CURL_ERROR = curl_error($ch);
        $errno = curl_errno($ch);

        // self::$debug && var_dump($response);
        curl_close ($ch);


        if(empty($response)){
            // return '-100000';
        }
        // return json_decode($response);
    }
    public function getContents()
    {
        return $this->CURL_BODY;
    }
    public function getBody()
    {
        return $this;
    }
    public function getReasonPhrase()
    {
        return var_export($this->CURL_INFO,true);
    }
    public function getStatusCode()
    {
        return $this->CURL_INFO['http_code'];
    }
    public function getConfig($option = null)
    {
        return $option === null
            ? $this->config
            : (isset($this->config[$option]) ? $this->config[$option] : null);
    }
    public static function make($QS=null,$url=null) {
        $url OR $url = $_SERVER["REQUEST_URI"];

        $parse  = parse_url($url);
        parse_str($parse['query'], $query);

        $output = (array)$QS;
        is_array($QS) OR parse_str($QS, $output);
        foreach ($output as $key => $value) {
            //这个null是字符
            if($value==='null'||$value===null){
                unset($output[$key]);
                unset($query[$key]);
            }
        }
        $query = array_merge((array)$query,(array)$output);
        $parse['query'] = http_build_query($query);
        $nurl = self::glue($parse);
        return $nurl?$nurl:$url;
    }
    public static function glue($parsed) {
        if (!is_array($parsed)) return false;

        $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
        $uri.= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
        $parsed['host']    && $uri.= $parsed['host'];
        $parsed['port']    && $uri.= ':'.$parsed['port'];
        $parsed['path']    && $uri.= $parsed['path'];
        $parsed['query']   && $uri.= '?'.$parsed['query'];
        $parsed['fragment']&& $uri.= '#'.$parsed['fragment'];
        return $uri;
    }
    /**
     * Configures the default options for a client.
     *
     * @param array $config
     */
    private function configureDefaults(array $config)
    {

        $this->config = $config;

        // Add the default user-agent header.
        if (!isset($this->config['headers'])) {
            $this->config['headers'] = array(
                'User-Agent' => $this->default_user_agent()
            );
        } else {
            // Add the User-Agent header if one was not already set.
            foreach (array_keys($this->config['headers']) as $name) {
                if (strtolower($name) === 'user-agent') {
                    return;
                }
            }
            $this->config['headers']['User-Agent'] = $this->default_user_agent();
        }
    }
    /**
     * Get the default User-Agent string to use with Guzzle
     *
     * @return string
     */
    private function default_user_agent()
    {
        static $defaultAgent = '';

        if (!$defaultAgent) {
            $defaultAgent = 'PaymentHttp/' . Client::VERSION;
            if (extension_loaded('curl') && function_exists('curl_version')) {
                $curl_version = curl_version();
                $defaultAgent .= ' curl/' . $curl_version['version'];
            }
            $defaultAgent .= ' PHP/' . PHP_VERSION;
        }

        return $defaultAgent;
    }
}
