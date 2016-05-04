<?php
class Ap_View_Formatter
{    
    protected $_view_helper;
    
    public function __construct() {
        $this->_view_helper = new Zend_View_Helper_Url();
    }
    
    public function getFormatter(){
        return $this->_view_helper;
    }
    
    public function search($term, $params = array())
    {
        $term = $this->makeHRUrl($term);
        return $this->_view_helper->url(
                array_merge(array('controller'=>'search', 'q' => $term), $params)
                , 'search', true, true);
    }
    
    public function searchImage($term, $params = array())
    {
        $term = $this->makeHRUrl($term);
        return $this->_view_helper->url(
                array_merge(array('controller'=>'search', 'q' => $term), $params)
                , 'search_image', true, true);
    }
    

    public function searchVideo($term, $params = array())
    {
        $term = $this->makeHRUrl($term);
        return $this->_view_helper->url(
            array_merge(array('controller'=>'search', 'q' => $term), $params)
            , 'search_video', true, true);
    }
    
    public function makeHRUrl($term)
    {
        $term = str_replace('-', '---', $term);
        $term = str_replace(' ', '-', $term);
        $term = urlencode($term);
        return $term;
    }
    
    public static function makeReverseVar($var)
    {
        $var = urldecode($var);
        if(strpos($var, ' ') !== false){
           return false; 
        }
        $var = str_replace('---', '-', $var);
        $var = str_replace('-', ' ', $var);
        return $var;
    }
        

    public function truncate($string, $start = 0, $length = 100, $prefix = '...', $postfix = '...')
    {
        $truncated = trim($string);
        $start = (int) $start;
        $length = (int) $length;
        // Return original string if max length is 0
        if ($length < 1) return $truncated;

        $full_length = iconv_strlen($truncated);

        // Truncate if necessary
        if ($full_length > $length) {
            // Right-clipped
            if ($length + $start > $full_length) {
                $start = $full_length - $length;
                $postfix = '';
            }
            // Left-clipped
            if ($start == 0) $prefix = '';
            // Do truncate!
            $truncated = $prefix . trim(substr($truncated, $start, $length)) . $postfix;
        }
        return $truncated;
    }

    public function truncateName($str, $length = 100)
    {
        $strLength = mb_strwidth($str);
        if ($strLength <= $length)
            return $str;
        $str = mb_substr($str, 0, $length);
        $rpos = mb_strrpos($str, ' ');
        if ($rpos > 0)
            $str = mb_substr($str, 0, $rpos);
        return $str;
    }
}