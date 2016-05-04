<?php

class mockedBrowserTest extends PHPUnit_Framework_TestCase
{
  const USER_AGENT_WRONG = 'wrong agent';
  const USER_AGENT_RIGHT = 'correct_useragent';

  public $browser;

  public function setUp()
  {
    $this->browser = new Ap_Browser_MockedBrowser($test_mock_browser_urls);
  }

  function testSomeUrl()
  {
    $b = $this->browser;
    $b->get('http://some.url/', array('a'=>'not 1'));
    $this->assertEquals(MockedBrowser::URL_NOT_FOUND_STATUS, $b->getStatusCode(),
        "Status code must be 404 because of params.");
    $b->get('http://some.url/', array('a'=>1));
    $this->assertEquals(MockedBrowser::OK_STATUS, $b->getStatusCode(), "Everything must be OK.");
  }


  function testUserAgent() {
    $b = $this->browser;
    $b->setUserAgent(self::USER_AGENT_WRONG);
    $b->post('http://freemail.ukr.net/');
    $this->assertEquals(MockedBrowser::URL_NOT_FOUND_STATUS, $b->getStatusCode(),
            "User agent must fail.");
    $b->setUserAgent(self::USER_AGENT_RIGHT);
    $b->get('http://freemail.ukr.net/');
    $this->assertEquals(MockedBrowser::OK_STATUS, $b->getStatusCode(),
            "User agent must be " . self::USER_AGENT_RIGHT . " .");
  }

  function testHeaders() {
    $b = $this->browser;
    $b->get('http://headers.url/');
    $this->assertEquals(array('header'=>'value'), $b->getResponseHeaders());
  }

  function testStatusCode() {
    $b = $this->browser;
    $this->browser->getSettings()->set('/mock3/statusCode', 123);
    $b->get('http://statuscodecheck.com/');
    $this->assertEquals(123, $b->getStatusCode());
  }

  function testCharset() {
    $b = $this->browser;

    // from config
    $b->get('http://statuscodecheck.com/');
    $this->assertEquals('utf-8', $b->getCharset());

    // change config on fly
    $b->getSettings()->set('/mock3/charset', 'cp1251');
    $b->get('http://statuscodecheck.com/');
    $this->assertEquals('cp1251', $b->getCharset());
  }

  function testMethod() {
    $b = $this->browser;
    $b->get('http://method.check.com/');
    $this->assertEquals(404, $b->getStatusCode(), "GET must fail.");
    $b->post('http://method.check.com/');
    $this->assertEquals(200, $b->getStatusCode(), "POST must be OK.");
  }

  public function tearDown()
  {

  }

}