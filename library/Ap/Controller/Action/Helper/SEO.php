<?php

class Ap_Controller_Action_Helper_SEO extends Zend_Controller_Action_Helper_Abstract
{

    public function __construct()
    {
        return $this;
    }

    public function isWebBot($agent)
    {
        $bots = array('yandex', 'google', 'msnbot', 'bing', 'yahoo',
                      'semrushbot', 'sogou', 'exabot', 'ahrefsbot', 'baiduspider', 'teoma');
        if (!empty($agent)) {
            foreach ($bots as $bot) {
                if (preg_match('/'.$bot.'/ui', $agent)) {
                    return $bot;
                }
            }
        }
        return false;
    }

    public function getSerpEngine($referer)
    {
        if(empty($referer))
            return false;

        $bots = array('google', 'msn', 'live', 'altavista', 'ask', 'yahoo', 'aol', 'bing', 'seznam');
        $purl = @parse_url($referer);
        $se = '';
        if ($purl) {
            $host = str_replace('www.', '', strtolower($purl['host']));
            $parts = explode('.', $host);
            foreach ($parts as $part) {
                if (in_array($part, $bots)) {
                    $se = $part;
                    break;
                }
            }
        }
        return $se;
    }
}