<?php

  interface GuestDeterminant
  {
    public static function isUnic();   
   }

  class Ap_Validate_CookieGuestDeterminant implements GuestDeterminant
  {
    
    const CookieName = 'cookie';
    const CookieValue = 'cvalue';
    /**
     * Определяет по наличию нашей куки, уникален посетитель или нет
     * @return boolean 
     */
    public static function isUnic()
    {
      $ExpPeriod = Zend_Registry::get('Config')->cookie->timeout;
      // выбираем таймаукт куки из конфигурации
        
      if (isset($_COOKIE[self::CookieName])) 
          return false; // посетитель не уникален
      else {
        if ( APPLICATION_ENV != 'testing' ){
            setcookie(self::CookieName, self::CookieValue, time() + $ExpPeriod,'/');
        }
        $_COOKIE[self::CookieName] = self::CookieValue;
      }
      
      return true;
    }
    
  }


