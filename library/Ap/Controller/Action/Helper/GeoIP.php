<?php

class Ap_Controller_Action_Helper_GeoIP extends Zend_Controller_Action_Helper_Abstract
{
    protected $_country_code, $_ip,$_city_name;
    public function __construct()
    {
        return $this;
    }

    public function getCountryCode()
    {
        if($this->_country_code === null){
            if(isset($_SERVER["GEO_IP_CC"])){
                $this->_country_code = $_SERVER["GEO_IP_CC"];
            } else {
                try{
                    $this->_country_code = geoip_country_code_by_name($this->getIp());
                } catch (Exception $e){
                    $this->_country_code = '--';
                }
            }
        }
        return $this->_country_code;
    }

    public function getCityName()
    {
        if($this->_city_name === null){
            if(isset($_SERVER["GEO_IP_CN"])){
                $this->_city_name = $_SERVER["GEO_IP_CN"];
            } else {
                try{
                    $info = geoip_record_by_name($this->getIp());
                    $this->_city_name = $info['city'];
                } catch (Exception $e){
                    $this->_city_name = '--';
                }
            }
        }
        return $this->_city_name;
    }

    public function getIp()
    {
        if($this->_ip === null) {
            if ( getenv('REMOTE_ADDR') ) $this->_ip = getenv('REMOTE_ADDR');
            elseif ( getenv('X-FORWARDED-FOR') ) $this->_ip = getenv('X-FORWARDED-FOR');
            elseif ( getenv('HTTP_FORWARDED_FOR') ) $this->_ip = getenv('HTTP_FORWARDED_FOR');
            elseif ( getenv('HTTP_X_FORWARDED_FOR') ) $this->_ip = getenv('HTTP_X_FORWARDED_FOR');
            elseif ( getenv('HTTP_X_COMING_FROM') ) $this->_ip = getenv('HTTP_X_COMING_FROM');
            elseif ( getenv('HTTP_VIA') ) $this->_ip = getenv('HTTP_VIA');
            elseif ( getenv('HTTP_XROXY_CONNECTION') ) $this->_ip = getenv('HTTP_XROXY_CONNECTION');
            elseif ( getenv('HTTP_CLIENT_IP') ) $this->_ip = getenv('HTTP_CLIENT_IP');
            // $server_ips = Zend_Registry::get('Config')->server->ips->toArray();
            // if(in_array($this->_ip, $server_ips)) {
                // if (isset($_GET['REMOTE_ADDR']) && !empty($_GET['REMOTE_ADDR'])) {
                    // $this->_ip = $_GET['REMOTE_ADDR'];
                // }
            // }
        }
        return $this->_ip;
    }
}