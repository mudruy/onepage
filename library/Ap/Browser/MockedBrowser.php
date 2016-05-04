<?php
class Ap_Browser_MockedBrowser implements Ap_Browser_WebBrowser
{
  const URL_NOT_FOUND_STATUS = 404;
  const OK_STATUS = 200;
  const CHARSET = 'utf-8';
  const POST = 'POST';
  const GET = 'GET';

  public $browserSettings = null;

  /**
   * WebBrowser process variables
   */
  protected $statusCode     = 0;
  protected $userAgent      = '';
  protected $content        = '';
  protected $charset        = '';
  protected $headers        = array();
  protected $cookies        = array();
  protected $adapterErrors  = array();



  /**
   * Set expectations for browser. Expectation is array of arrays:
   * array (
   *    ...
   *    # expectation
   *    array (
   *      #expect
   *      'url'=>           (string) Url
   *      'params'=>        (array) array of params
   *      'userAgent'=>     (string) null as default
   *      #returns
   *      'statusCode'=> (int) status code [default 200]
   *      'charset'=> (string) charset [default utf-8]
   *      'content'=> (string) content to return [default '']
   *      'headers'=> (array) array of headers [default ()]
   *      'cookies'=> (array) array of cookies [default ()]
   *    ),
   *    ...
   * )
   * @param array $e          Expectations
   * @return void
   */
  public function __construct(array $wbs, BrowserAdapter $adapter = null)
  {
    $this->browserSettings = $wbs;
  }

  /**
   * Главный метод. Ищет подходящий экспектейшн и имитирует ответ от сервера
   * @param $url
   * @param $params
   * @param $headers array  Заголовки, отправляемые на сервер
   * @return bool
   */
  protected function process($url, $params, $headers, $method)
  {
    $expects = $this->browserSettings;
    $expect = null;

    foreach ($expects as $name=>$e)
    {
      $e['method'] = (isset($e['method']) ? $e['method'] : self::GET);

      
      if($e['url'] == $url)
      {
        if(!isset($e['params']) || $e['params'] == $params)
        {
          if(!isset($e['userAgent']) || $e['userAgent'] == $this->userAgent)
          {
            if($e['method'] == $method)
            {
              if(!isset($e['request_headers']) || $e['request_headers'] == $headers)
              {
                // forming expectation
                $expect = array(
                  'content' => isset($e['content']) ? $e['content'] : '', 
                  'charset' => isset($e['charset']) ? $e['charset'] : self::CHARSET, 
                  'statusCode' => isset($e['statusCode']) ? (int)$e['statusCode'] : self::OK_STATUS, 
                  'headers' => isset($e['headers']) ? (array)$e['headers'] : array(), 
                  'cookies' => isset($e['cookies']) ? (array)$e['cookies'] : array(), 
                  'adapterErrors' => isset($e['adapterErrors']) ? $e['adapterErrors'] : array());
                break;
              }
            }
          }
        }
      }
    }
    if (null==$expect)
    {
      $this->statusCode = self::URL_NOT_FOUND_STATUS;
      $this->content        = '';
      $this->charset        = '';
      $this->headers        = array();
      $this->cookies        = array();
      $this->adapterErrors  = array();
      return false;
    }
    $this->content        = $expect['content'];
    $this->charset        = $expect['charset'];
    $this->headers        = $expect['headers'];
    $this->cookies        = $expect['cookies'];
    $this->statusCode     = $expect['statusCode'];
    $this->adapterErrors  = $expect['adapterErrors'];
    return true;
  }

  /**
   * @see common/web_browser/WebBrowser#get()
   */
  public function get($url, $params = array(), $headers = array())
  {
    return $this->process($url, $params, $headers, self::GET);
  }

  /**
   * @see common/web_browser/WebBrowser#post()
   */
  public function post($url, $params = array(), $headers = array())
  {
     return $this->process($url, $params, $headers, self::POST);
  }

  /**
   * @see common/web_browser/WebBrowser#setUserAgent()
   */
  public function setUserAgent($userAgent = "")
  {
    $this->userAgent = $userAgent;
  }

  /**
   * @see common/web_browser/WebBrowser#setResponseHeaders()
   */
  public function setResponseHeaders($headers = array())
  {
    $this->headers = $headers;
  }

  /**
   * @see common/web_browser/WebBrowser#setResponseText()
   */
  public function setResponseContent($content)
  {
    $this->content = $content;
  }

  /**
   * @see common/web_browser/WebBrowser#getUserAgent()
   */
  public function getUserAgent()
  {
    return $this->userAgent;
  }

  /**
   * @see common/web_browser/WebBrowser#getContent()
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * @see common/web_browser/WebBrowser#getStatusCode()
   */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * @see common/web_browser/WebBrowser#getResponseHeaders()
   */
  public function getResponseHeaders()
  {
    return $this->headers;
  }

  /**
   * @see common/web_browser/WebBrowser#getResponseCookies()
   */
  public function getResponseCookies()
  {
    // #TODO:implement
    return array();
  }
  
  /**
   * @see common/web_browser/WebBrowser#getResponseHeader()
   */
  public function getResponseHeader($header, $default = null)
  {
    return isset($this->headers[$header]) ? $this->headers[$header] : null;
  }

  /**
   * @see 
   */
  public function &getSettings()
  {
    return $this->browserSettings;
  }

  /**
   * 
   */
  public function setBrowserSettings($wbs)
  {
    $this->browserSettings = $wbs;
  }

 /**
   * @see common/web_browser/WebBrowser#getErrors()
   */
  public function getErrors()
  {
    return $this->adapterErrors;
  }
  /**
   * Получить charset страницы (из хидера или метатега в документе)
   * @return charset
   */
  public function getCharset()
  {
    return $this->charset;
  }
  /**
   * Парсить ли ответ и получать charset при выполнении get
   * в мокед броузере походу безсмысленна
   * @param $extract bool (true - парсить, false - нет)
   * @return nothing
   */
  public function isExtractCharsetOnResponse($extract = true)
  {
  }

  /**
   * @return nothing
   */
  public function reset()
  {
  }
}