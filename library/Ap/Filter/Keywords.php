<?php
class Ap_Filter_Keywords implements Zend_Filter_Interface
{
    function filter($str, $limit = 100, $ignore = ''){
	
    	$ignore_arr = explode(" ", $ignore);
    
    	$str = trim($str);
    	$str = preg_replace("#[&].{2,7}[;]#sim", " ", $str);
    	
    	$str = preg_replace("#[()Â°^!\"Â§\$%&/{(\[)\]=}?Â´`,;.:\-_\#'~+*—]#u", " ", $str);
    	$str = preg_replace("#\s+#sim", " ", $str);
    	$str = mb_strtolower($str, 'UTF-8');
    	$arraw = explode(" ", $str);
    	$arr = array();
    	foreach($arraw as $v){
    		$v = (string)trim($v);
    		if(preg_match('/@/',$v)){
    		    continue;
    		}
    	    if(preg_match('/^\d*$/',$v)){
    		    continue;
    		}
    	    if(preg_match('/^[&\'\#\[]/',$v)){
    		    continue;
    		}
    		if(mb_strlen($v, 'UTF-8')<2 || in_array($v, $ignore_arr)) continue;
    		if(!isset($arr[$v])){
    		    $arr[$v] = 0;
    		}
    		$arr[$v]++;
    	}
    	arsort($arr);
    	$array_out = array();
    	$n = 0;
    	foreach($arr AS $word=>$count){
    	    $n++;
    	    if($n==$limit)
    	        break;
    	    $array_out[] = array(
    	        'word' => $word,
    	        'count' => $count
    	    );
    	}
    	
    	return $array_out;
    }
    
    public static function prepare_keyword_like_exe( $keyword ) {
        $keyword = preg_replace('/\(.*?\)/', '', $keyword); 
        $keyword = preg_replace('/\[.*?\]/', '', $keyword);
        return str_replace('+','_',urlencode(trim($keyword)));
    }
}
