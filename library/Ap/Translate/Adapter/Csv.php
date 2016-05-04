<?php

class Ap_Translate_Adapter_Csv extends Zend_Translate_Adapter_Csv
{
    public function translate($messageId, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->_options['locale'];
        }

        $plural = null;
        if (is_array($messageId)) {
            if (count($messageId) > 2) {
                $number = array_pop($messageId);
                if (!is_numeric($number)) {
                    $plocale = $number;
                    $number  = array_pop($messageId);
                } else {
                    $plocale = 'en';
                }

                $plural    = $messageId;
                $messageId = $messageId[0];
            } else {
                $messageId = $messageId[0];
            }
        }

        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                $this->_log($messageId, $locale);
                if (!empty($this->_options['route'])) {
                    if (array_key_exists($locale, $this->_options['route']) &&
                        !array_key_exists($locale, $this->_routed)) {
                        $this->_routed[$locale] = true;
                        return $this->translate($messageId, $this->_options['route'][$locale]);
                    }
                }

                $this->_routed = array();
                if ($plural === null) {
                    return $messageId;
                }

                $rule = Zend_Translate_Plural::getPlural($number, $plocale);
                if (!isset($plural[$rule])) {
                    $rule = 0;
                }

                return $plural[$rule];
            }

            $locale = new Zend_Locale($locale);
        }

        $locale = (string) $locale;
        if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
            // return original translation
            if ($plural === null) {
                $this->_routed = array();
                return $this->_translate[$locale][$messageId];
            }

            $rule = Zend_Translate_Plural::getPlural($number, $locale);
            if (isset($this->_translate[$locale][$plural[0]][$rule])) {
                $this->_routed = array();
                return $this->_translate[$locale][$plural[0]][$rule];
            }
        } else if (strlen($locale) != 2) {
            // faster than creating a new locale and separate the leading part
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

            if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
                // return regionless translation (en_US -> en)
                if ($plural === null) {
                    $this->_routed = array();
                    return $this->_translate[$locale][$messageId];
                }

                $rule = Zend_Translate_Plural::getPlural($number, $locale);
                if (isset($this->_translate[$locale][$plural[0]][$rule])) {
                    $this->_routed = array();
                    return $this->_translate[$locale][$plural[0]][$rule];
                }
            }
        }

        $this->_log($messageId, $locale);
        // use rerouting when enabled
        if (!empty($this->_options['route'])) {
            if (array_key_exists($locale, $this->_options['route']) &&
                !array_key_exists($locale, $this->_routed)) {
                $this->_routed[$locale] = true;
                return $this->translate($messageId, $this->_options['route'][$locale]);
            }
        }

        $this->_routed = array();
        if ($plural === null) {
            $this->addMessage($messageId, $locale);
            return $messageId;
        }

        $rule = Zend_Translate_Plural::getPlural($number, $plocale);
        if (!isset($plural[$rule])) {
            $rule = 0;
        }

        return $plural[$rule];
    }
    
    public function addMessage($messageId, $locale = null)
    {
            if ($locale === null) {
                $locale = $this->_options['locale'];
            }
            if($this->_options['addMessages']){
                $options = $this->getOptions();
                $dir = $options['content'].$locale;
                if(!is_dir($dir)){
                    mkdir($dir, 0777);
                }

                if(preg_match('/^([\w\_]*)\:\:(.*)$/', $messageId, $match)) {
                    $message = $match[2];
                    $filename = $options['content'].$locale.'/'.$match[1].'.csv';
                    $file = fopen($filename, 'a+');
                    $translateMessage = $message;//$this->_translate($message, $locale);
                    $str = $this->_options['enclosure'].$messageId.$this->_options['enclosure'].$this->_options['delimiter'].$this->_options['enclosure'].$translateMessage.$this->_options['enclosure']."\n";
                    fwrite($file, $str);  
                    fclose($file);  
                    $this->_translate[$locale][$messageId] = $translateMessage;
                }
            }
    }
    
    public function _translate($message, $locale)
    {
        if($locale!='en'){
            $client = new Zend_Http_Client('http://translate.google.ru/translate_a/t?', array(
                'maxredirects' => 0,
                'charset'   =>  'utf8',
                'timeout'      => 30));
            $client->setParameterGet(array(
                'client'=>  't',
                'text'  =>  $message,
                'sl'    =>  'en',
                'hl'    =>  'en',
                'tl'    =>  $locale,
                'ie'=>'UTF-8',
                'oe'=>'UTF-8'
            ));

            $response = $client->request();
            $data = $response->getBody();
            if(preg_match('/\[\[\["([^\"]*)/ui', $data, $match)){
                return $match[1];
            }else return $message;
        }
        return $message;
    }
}