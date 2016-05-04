<?php


interface Ap_Browser_BrowserAdapter {

  /**
   * __construct
   * @param array $options
   */
  public function __construct($options = array());

  /**
   * Send curl query
   * @param string          $url
   * @param array|null      $get_params
   * @param array|null      $post_params
   * @param array           $headers
   * @param GET|POST        $method
   * @return boolean        Is operation success
   */
  public function call($url, $get_params=array(), $post_params=array(), $headers=array(), $method='GET', $cookies=array());

  /**
   * Send GET query
   * @param string    $url URL
   * @param array     $params
   * @param array     $headers
   * @return boolean  TRUE if no errors (curl errors, etc)
   */
   public function get($url, $params=array(), $headers=array(), $cookies=array());


  /**
   * Send POST query
   * @param string    $url URL
   * @param array     $params
   * @param array     $headers
   * @return boolean  TRUE if no errors (curl errors, etc)
   */
  public function post($url, $params=array(), $headers=array(), $cookies=array());

  // Get Reponse

  /**
   * Get response text
   * @return string
   */
  public function getResponseText();

  /**
   * Get response headers
   * @return string
   */
  public function getResponseHeaders();

  /**
   * Get response cookies
   * @return string
   */
  public function getResponseCookies();

  /**
   * Get response status code
   * @return string
   */
  public function getStatusCode();

  // Errors

  /**
   * Get last request adapter errors (curl, etc)
   * @return array Errors error (empty if not any)
   */
  public function getErrors();

  /**
   * clear object big vars
   */
  public function clearVar();

  /**
   * get file path
   */
  public function getCookieFile();
}


