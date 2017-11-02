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
var_dump($options) ;
        if($method=="POST"){
            $this->http($uri,$options);
        }else{
            $this->http($uri);
        }
        return $this;
    }

    public function http($url, $postdata=null)
    {
        if (self::$CURLOPT_REFERER === null) {
            $uri = parse_url($url);
            self::$CURLOPT_REFERER = $uri['scheme'] . '://' . $uri['host'];
        }
        $ch = curl_init();
        $options = array(
            CURLOPT_URL                     => $url,
            CURLOPT_REFERER                 => self::$CURLOPT_REFERER,
            CURLOPT_USERAGENT               => self::$CURLOPT_USERAGENT,
            CURLOPT_ENCODING                => self::$CURLOPT_ENCODING,
            CURLOPT_TIMEOUT                 => 10, //数据传输的最大允许时间
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

        if ($postdata!==null) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $postdata['body'];
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $this->CURL_BODY  = curl_exec($ch);
        $this->CURL_INFO  = curl_getinfo($ch);
        $this->CURL_ERROR = curl_error($ch);
        $errno = curl_errno($ch);

var_dump($this->CURL_INFO);
var_dump($this->CURL_BODY);
echo "<hr />";
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
    /**
     * Configures the default options for a client.
     *
     * @param array $config
     */
    private function configureDefaults(array $config)
    {
        $defaults = array(
            'allow_redirects' => array(
                'max'             => 5,
                'protocols'       => array('http', 'https'),
                'strict'          => false,
                'referer'         => false,
                'track_redirects' => false,
            ),
            'http_errors'     => true,
            'decode_content'  => true,
            'verify'          => true,
            'cookies'         => false
        );

        // Use the standard Linux HTTP_PROXY and HTTPS_PROXY if set.

        // We can only trust the HTTP_PROXY environment variable in a CLI
        // process due to the fact that PHP has no reliable mechanism to
        // get environment variables that start with "HTTP_".
        if (php_sapi_name() == 'cli' && getenv('HTTP_PROXY')) {
            $defaults['proxy']['http'] = getenv('HTTP_PROXY');
        }

        if ($proxy = getenv('HTTPS_PROXY')) {
            $defaults['proxy']['https'] = $proxy;
        }

        if ($noProxy = getenv('NO_PROXY')) {
            $cleanedNoProxy = str_replace(' ', '', $noProxy);
            $defaults['proxy']['no'] = explode(',', $cleanedNoProxy);
        }

        $this->config = $config + $defaults;

        if (!empty($config['cookies']) && $config['cookies'] === true) {
            // $this->config['cookies'] = new CookieJar();
        }

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
            $defaultAgent = 'GuzzleHttp/' . Client::VERSION;
            if (extension_loaded('curl') && function_exists('curl_version')) {
                $curl_version = curl_version();
                $defaultAgent .= ' curl/' . $curl_version['version'];
            }
            $defaultAgent .= ' PHP/' . PHP_VERSION;
        }

        return $defaultAgent;
    }
}
