<?php

class Ap_Browser_BrowserCurl implements Ap_Browser_BrowserAdapter {

    protected
        $response = '',
        $options = array(),
        $curl = null,
        $headers = array(),
        $cookies = array();
    protected $responseText = '';
    protected $responseHeaders = null;
    protected $responseCookies = null;
    protected $responseStatusCode = 0;
    protected $cookie_file;
    private $contentSize = 0;
    private $maxContentSize = null;
    protected $curl_errors = array();
    
    protected $log_path;
    protected $cookie_path;

    CONST MAXCONTENTSIZE = 500000;
    CONST TIMEOUT = 30;

    /**
     * clear object var
     */
    public function clearVar() {
        $this->response = null;
        $this->responseText = null;
        $this->responseHeaders = null;
        $this->responseCookies = null;
        $this->responseStatusCode = 0;
        $this->contentSize = 0;
        $this->curl_errors = array();
    }

    protected function setTimeOut($sec = self::TIMEOUT) {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $sec);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $sec);
    }

    /**
     * __construct
     * @param array $options
     */
    public function __construct($options = array()) {
        $this->cookie_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . 
            DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'cookies.txt';
        $this->log_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . 
            DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'sfCurlAdapter_verbose.log';
        $this->init($options);
    }

    protected function init($options) {
        $this->options = $options;
        $this->enable_size_limitation = isset($options['enable_size_limitation']) && $options['enable_size_limitation'] ? true : false;
        unset($options['enable_size_limitation']);

        if (!extension_loaded('curl')) {
            throw new Exception('[BrowserAdapterCurl::__construct] Curl extension not loaded');
        }

        $curl_options = $options;

        $this->curl = curl_init();
        $this->setTimeOut();

        // cookies
        if (isset($curl_options['cookies'])) {
            if (isset($curl_options['cookies_file'])) {
                $this->cookie_file = $curl_options['cookies_file'];
                unset($curl_options['cookies_file']);
            } else {
                $this->cookie_file = $this->cookie_path;
            }
            if (isset($curl_options['cookies_dir'])) {
                $cookie_dir = $curl_options['cookies_dir'];
                unset($curl_options['cookies_dir']);
            } else {
                $path_parts = pathinfo($this->cookie_path);
                $cookie_dir = $path_parts['dirname'];
            }
            if (!is_dir($cookie_dir)) {
                if (!mkdir($cookie_dir, 0777, true)) {
                    throw new Exception(sprintf('Could not create directory "%s"', $cookie_dir));
                }
            }

            curl_setopt($this->curl, CURLOPT_COOKIESESSION, false);
            curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_file);
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie_file);
            unset($curl_options['cookies']);
        }

        // default settings
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, true);

        $followlocation = false;
        if (isset($this->options['followlocation']))
            $followlocation = (bool) $this->options['followlocation'];
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, $followlocation);

        // activate ssl certificate verification?

        if (isset($this->options['ssl_verify_host'])) {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, (bool) $this->options['ssl_verify_host']);
        }
        if (isset($curl_options['ssl_verify'])) {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, (bool) $this->options['ssl_verify']);
            unset($curl_options['ssl_verify']);
        }
        // verbose execution?
        if (isset($curl_options['verbose'])) {
            curl_setopt($this->curl, CURLOPT_NOPROGRESS, false);
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
            unset($curl_options['verbose']);
        }
        if (isset($curl_options['verbose_log'])) {
            $log_file = $this->log_path;
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
            $this->fh = fopen($log_file, 'a+b');
            curl_setopt($this->curl, CURLOPT_STDERR, $this->fh);
            unset($curl_options['verbose_log']);
        }

        // Additional options
        foreach ($curl_options as $key => $value) {
            $const = constant('CURLOPT_' . strtoupper($key));
            if (!is_null($const)) {
                if (!is_array($value)) {
                    curl_setopt($this->curl, $const, $value);
                }
            }
        }

        // response header storage - uses callback function
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'read_header'));

        if ($this->enable_size_limitation) {
            $this->maxContentSize = self::MAXCONTENTSIZE;
            curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, array($this, 'read_body'));
        }
    }

    /**
     *
     * @return string cookies file path
     */
    public function getCookieFile() {
        return $this->cookie_file;
    }

    /**
     * setup http auth
     * @param string $auth "username:password"
     */
    public function setAut($auth) {
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->curl, CURLOPT_USERPWD, $auth);
    }

    /**
     * Read content until content size is under limit
     * @param curl $curl
     * @param string $body
     * @return int
     */
    protected function read_body($curl, $body) {
        $length = mb_strlen($body);

        $this->contentSize += $length;

        // Check for size more than max content size
        if ($this->contentSize > $this->maxContentSize)
        // Cut body
            $body = mb_substr($body, 0, $this->maxContentSize);

        $this->response .= $body;

        return $length;
    }

    /**
     * Transforms an associative array of header names => header values to its HTTP equivalent.
     *
     * @param    array     $headers
     * @return   string
     */
    private function prepareHeaders($headers = array()) {
        $prepared_headers = array();
        foreach ((array) $headers as $name => $value) {
            $prepared_headers[] = sprintf("%s: %s", ucfirst($name), $value);
        }

        return $prepared_headers;
    }

    /**
     * Send curl query
     * @param string          $url
     * @param array|null      $get_params
     * @param array|null      $post_params
     * @param array           $headers
     * @param GET|POST        $method
     * @return boolean        Is operation success
     */
    public function call($url, $get_params = array(), $post_params = array(), $headers = array(), $method = 'GET', $cookies = array()) {
        $this->contentSize = 0;
        $this->response = '';

        if (!empty($get_params)) {
            if (is_array($get_params))
                $url .= '?' . http_build_query($get_params, '', '&');
            else
                $url .= '?' . $get_params;
        }
        // uri
        if ('POST' != $method) {
            curl_setopt($this->curl, CURLOPT_POST, false);
        }
        curl_setopt($this->curl, CURLOPT_URL, $url);

        // request headers
        $request_headers = $this->prepareHeaders($headers);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $request_headers);

        // encoding support
        if (isset($headers['Accept-Encoding'])) {
            curl_setopt($this->curl, CURLOPT_ENCODING, $headers['Accept-Encoding']);
        }

        // timeout support
        if (isset($this->options['Timeout'])) {
            curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->options['Timeout']);
        }

        if (!empty($post_params)) {
            if (!is_array($post_params)) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_params);
            } else {
                // multipart posts (file upload support)
                $has_files = false;
                foreach ($post_params as $name => $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    if (is_file($value)) {
                        $has_files = true;
                        $post_params[$name] = '@' . realpath($value);
                    }
                }
                if ($has_files) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_params);
                } else {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($post_params, '', '&'));
                }
            }
        }

        // handle any request method
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($this->curl);

        if (curl_errno($this->curl)) {
            $this->curl_errors = array(curl_errno($this->curl));
            print_r(curl_getinfo($this->curl));
            print_r(curl_error($this->curl));
            return false;
        }

        $requestInfo = curl_getinfo($this->curl);

        if ($this->enable_size_limitation) {
            $this->responseText = $this->response;
        } else {
            $this->responseText = $response;
        }
        $this->responseHeaders = $this->headers;
        $this->responseCookies = $this->cookies;
        $this->responseStatusCode = $requestInfo['http_code'];

        // clear response headers
        $this->headers = array();
        $this->cookies = array();

        return true;
    }

    /**
     * Send GET query
     * @param string    $url URL
     * @param array     $params
     * @param array     $headers
     * @return boolean  TRUE if no errors (curl errors, etc)
     */
    public function get($url, $params = array(), $headers = array(), $cookies = array()) {
        return $this->call($url, $params, null, $headers, 'GET');
    }
    
    /**
     * Send HEAD query
     * @param string    $url URL
     * @param array     $params
     * @param array     $headers
     * @return boolean  TRUE if no errors (curl errors, etc)
     */
    public function head($url, $params = array(), $headers = array(), $cookies = array()) {
        return $this->call($url, $params, null, $headers, 'HEAD');
    }

    /**
     * Send POST query
     * @param string    $url URL
     * @param array     $params
     * @param array     $headers
     * @return boolean  TRUE if no errors (curl errors, etc)
     */
    public function post($url, $params = array(), $headers = array(), $cookies = array()) {
        return $this->call($url, null, $params, $headers, 'POST');
    }

    /**
     * Get response text
     * @return string
     */
    public function getResponseText() {
        return $this->responseText;
    }

    /**
     * Get response headers
     * @return string
     */
    public function getResponseHeaders() {
        return $this->responseHeaders;
    }

    /**
     * Get response status code
     * @return string
     */
    public function getStatusCode() {
        return $this->responseStatusCode;
    }

    /**
     * Get response cookies
     * @return string
     */
    public function getResponseCookies() {
        return $this->responseCookies;
    }

    // Errors

    /**
     * Get last request adapter errors (curl, etc)
     * @return array Errors error (empty if not any)
     */
    public function getErrors() {
        return $this->curl_errors;
    }

    private function parseCookie($cookie_header) {
        $header = mb_substr($cookie_header, 12);
        $cookie = explode("; ", $header);
        $name_value = explode("=", $cookie[0]);
        $cookie_name = $name_value[0];

        $cookies[$cookie_name] = array(
            'value' => $name_value[1],
            'expires' => '',
            'path' => '',
            'domain' => '',
            'HttpOnly' => false,
            'secure' => false,
        );

        // unset name and value of cookies
        unset($cookie[0]);
        foreach ($cookie as $c) {
            $c = explode('=', $c);
            if ('httpOnly' == $c[0]) {
                $cookies[$cookie_name]['HttpOnly'] = true;
                continue;
            }

            if ('secure' == $c[0]) {
                $cookies[$cookie_name]['secure'] = true;
                continue;
            }

            if (1 < count($c)) {
                $param_name = $c[0];
                unset($c[0]);
                $cookies[$cookie_name][$param_name] = implode('=', $c);
            }
        }

        $this->cookies[$cookie_name] = $cookies;
    }

    /**
     * Inner function for headers handling
     * @param curl $curl
     * @param array $header
     * @return integer
     */
    protected function read_header($curl, $header_orig) {
        if (0 === mb_strpos($header_orig, "Set-Cookie:")) {
            $this->parseCookie($header_orig);
        } else {
            $header = explode(": ", $header_orig);
            if (count($header) > 1) {
                $name = $header[0];
                unset($header[0]);
                $this->headers[$name] = implode(": ", $header);
            }
        }

        return mb_strlen($header_orig);
    }

    public function __destruct() {
        curl_close($this->curl);
    }

}

