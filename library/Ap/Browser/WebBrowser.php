<?php

/**
 * interface for all class which implements browser
 */
interface Ap_Browser_WebBrowser
{
  /**
   * @param $params array масив параметров необходимых конкретной реализации
   */
  public function __construct(array $wbs, BrowserAdapter $adapter = null);

  /**
   * GET запросс
   * @param $url string адрес ресурса
   * @param $params array параметры запроса
   * @param $headers array  Заголовки, отправляемые на сервер
   * return nothing
   */
  public function get($url, $params = array(), $headers = array());

  /**
   * POST запрос
   * @param $url string адресс ресурса
   * @param $params array параметры запроса
   * @param $headers array  Заголовки, отправляемые на сервер
   * @return nothing
   */
  public function post($url, $params = array(), $headers = array());

  /**
   * Установить UserAgent
   * @param $user_agent string имя агента
   * @return nothing
   */
  public function setUserAgent($user_agent = "");
  
  /**
   * Установить response headers
   * @param $headers array хидеров
   * @return nothing
   */
  public function setResponseHeaders($headers = array());

  /**
   * Установить response content
   * @param $content контент
   * @return nothing
   */
  public function setResponseContent($content);

  /**
   * Получить текущий UserAgent
   * @return string имя текущего агента
   */
  public function getUserAgent();

  /**
   * Получить контент последней операции GET, POST
   * @return string контент
   */
  public function getContent();

  /**
   * Получить статус последней операции GET, POST
   * @return int статус код
   */
  public function getStatusCode();

  /**
   * Получить headers последнего ответа от сервера
   * @return array - ассоциативный масив хедеро
   */
  public function getResponseHeaders();

  /**
   * Получить конкретный header от последнего ответа от сервера
   * @param $header                 Имя заголовка который нужно получить
   * @return string                 Значение или null, если такого заголовка не приходило
   */
  public function getResponseHeader($header, $default = null);

  /**
   * Получить headers последнего ответа от сервера
   * @return array - ассоциативный масив хедеро
   */
  public function getResponseCookies();

  /**
   * Получить объект Ap_Browser_Browser_Settings с настройками браузера
   * @return array
   */
  public function &getSettings();

  /**
   * Установить объект настроек браузера
   * @param array $wbs          Объект настроек
   */
  public function setBrowserSettings($wbs);

  /**
   * Получить ошибки
   * @return array Error code and text
   */
  public function getErrors();

  /**
   * Получить charset страницы (из хидера или метатега в документе)
   * @return charset
   */
  public function getCharset();
  
  /**
   * Парсить ли ответ и получать charset при выполнении запроса
   * если false, то парситься будет при выполнении функции getCharset()
   * @param $extract bool (true - парсить, false - нет)
   * @return nothing
   */
  public function isExtractCharsetOnResponse($extract = true);

  /**
   * Reset all fields to init values (like browser was never used before)
   */
  public function reset();
}
