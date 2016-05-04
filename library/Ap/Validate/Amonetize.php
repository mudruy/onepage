<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Amonetize
 *
 * @author mudruy
 */
class Ap_Validate_Amonetize {
    
    protected $_files;
    protected $_date_from, $_date_to;
    protected $_bundles = array(
        '1546' => 'yontoo',
        '1875' => 'iminent',
        '1837' => 'wsbing',
        '1926' => 'gamestoolbar',
        '2233' => 'yontoo',
        '2232' => 'iminent',
        '2230' => 'wsbing',
        '2231' => 'gamestoolbar',
        '2258' => 'zdownloadterms',
        '2259' => 'zfastfreeconverter',
    );

    CONST DELIM = '_-_';


    public function __construct($files) {
        $this->_files = $files;
        if(count($files) == 0) {
            throw new Exception('Empty directory');
        }
        $this->_bundles = Zend_Registry::get('Config')->amonetize->bundles->toArray();
    }


    public function execute( $country_code, $user_id ) {
        $files_for_bundle  = array();
        $current_date_from = false;
        $current_date_to   = false;
        foreach ($this->_files as $key => $file) {
            $data = $this->_initVars($key);
            $files_for_bundle[$data[0]][] = $file;
            if($current_date_from == false) {
                $current_date_from = $data[1];
            } else {
                if( $current_date_from != $data[1]) {
                    throw new Exception("Date error in filenames $current_date_from {$data[1]}");
                }
            }
            if($current_date_to == false) {
                $current_date_to = $data[2];
            } else {
                if( $current_date_to != $data[2]) {
                    throw new Exception("Date error in filenames $current_date_to {$data[2]}");
                }
            }
            
        }
        $this->_date_from = $current_date_from;
        $this->_date_to   = $current_date_to;
        
        foreach ($files_for_bundle as $key => $ffb) {
            $this->checkBundle($key, $ffb, $country_code, $user_id);
        }
    }
    
    /**
     * init data to parse file fro filename
     * @param int $offset number of file
     * @return array of result
     * @throws Exception 
     */
    protected function _initVars($offset) {
        $first_file = $this->_files[$offset];
        $chunk = explode(self::DELIM, $first_file);
        if(count($chunk) != 3) {
            throw new Exception('Unexpected filename mask');
        }
        $bunle_id = explode('-', $chunk[0]);
        $bunle_id = array_pop($bunle_id);
        if(!array_key_exists($bunle_id, $this->_bundles)) {
            throw new Exception('New bundle id in files to check');
        }
        $bandle_name = $this->_bundles[$bunle_id];
        
        $date_from = date('Y-m-d H:i:s', strtotime($chunk[1]));
        
        $date_to = explode('.', $chunk[2]);
        $date_to = array_shift($date_to);
        $date_to = date('Y-m-d H:i:s', strtotime($date_to)+86399);
        return array($bandle_name, $date_from, $date_to);
    }
    
    
    public function checkBundle($bundlename, $files, $country_code, $user_id) {
        
        
        $patterns = array();
        $patterns[0] = '/Macedonia\, The Former Yugoslav Republic of/';
        $patterns[1] = '/Korea\, Republic of/';
        $patterns[2] = '/Palestinian Territory\, Occupied/';
        $patterns[3] = '/Taiwan\, Province Of China/';
        $patterns[4] = '/Congo\, The Democratic Republic of the/';
        $patterns[5] = '/Tanzania\, United Republic of/';
        $patterns[6] = '/Moldova\, Republic of/';
        $patterns[7] = '/Iran\, Islamic Republic of/';
        $patterns[8] = '/Korea\, Democratic People\'s Republic of/';
        $patterns[9] = '/Micronesia\, Federated States of/';
        $patterns[10] = '/Virgin Islands\, British/';
        $patterns[11] = '/Virgin Islands\, U.S./';
        
        $replacements = array();
        $replacements[0] = 'Macedonia The Former Yugoslav Republic of';
        $replacements[1] = 'Korea Republic of';
        $replacements[2] = 'Palestinian Territory Occupied';
        $replacements[3] = 'Taiwan Province Of China';
        $replacements[4] = 'Congo The Democratic Republic of the';
        $replacements[5] = 'Tanzania United Republic of';
        $replacements[6] = 'Moldova Republic of';
        $replacements[7] = 'Iran Islamic Republic of';
        $replacements[8] = 'Korea Democratic Peoples Republic of';
        $replacements[9] = 'Micronesia Federated States of';
        $replacements[10] = 'Virgin Islands British';
        $replacements[11] = 'Virgin Islands U.S.';
        

        $separated = ",";
        $data = array();
        foreach ($files as $file) {
            $text = file_get_contents($file);
            $text = preg_replace($patterns, $replacements, $text);
            foreach(explode("\n", $text) AS $row) {
                $data[] = array_map('trim', explode($separated, $row));
            }
        }
        
        $ips = array();
        $ips_clear = array();
        foreach ($data as $transact) {
            if(!array_key_exists(4, $transact))
                continue;
            if(preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $transact[4]))
                $ips_clear[] = trim($transact[4], '"');
            $ips[] = trim($transact[4], '"');
        }
        $res = array_diff($ips, $ips_clear);
        
        if( count($res) != count($files) ) {
            throw new Exception("New countries whith coma presented. add country in patterns.dif " . print_r($res, 1));
        }
        
        $ip_from_db = Ap_Db_Core::getTable('Transact')->getTransactionIp($bundlename, 
            $this->_date_from, $this->_date_to, $country_code, $user_id);
        $ip_from_db_clear = array();
        foreach ($ip_from_db as $key => $ip_record_array) {
            $ip_from_db_clear[$key] = $ip_record_array['ip'];
        }
        
        $not_registered_amonetize_transaction = array_diff($ip_from_db_clear, $ips_clear);
        $revers = array_diff($ips_clear, $ip_from_db_clear);
        
        $info = array();
        if(!$user_id) {
            foreach ($not_registered_amonetize_transaction as $key => $ip) {
                if(isset($info[$ip_from_db[$key]['user_id']])) {
                    $info[$ip_from_db[$key]['user_id']]++;
                } else {
                    $info[$ip_from_db[$key]['user_id']] = 1;
                }
            }
            arsort($info);
            foreach ($info as $user_id => $value) {
                $ip_from_db = Ap_Db_Core::getTable('Transact')->getTransactionIp($bundlename, 
                    $this->_date_from, $this->_date_to, $country_code, $user_id);
                $all_user_transaction = count($ip_from_db);
                $info[$user_id] = $value . '/' . $all_user_transaction . 
                ' ' . number_format(($value / $all_user_transaction * 100), 2, '.', ' ') . '%';
            }
            
            
        } else {
            foreach ($not_registered_amonetize_transaction as $key => $ip) {
                $u_id = $ip_from_db[$key]['user_id'];
                if($u_id != $user_id)
                    continue;
                $info[$u_id][] = $ip_from_db[$key];
            }
            if(array_key_exists($user_id, $info)) {
                $all_user_transaction = count($ip_from_db);
                $not_registered_user_transaction = count($info[$user_id]);
                $info[$user_id]['stat'] = $not_registered_user_transaction . '/' . $all_user_transaction . 
                    ' ' . number_format(($not_registered_user_transaction / $all_user_transaction * 100), 2, '.', ' ') . '%';
            }
            
        }
        echo $bundlename ."\n";
        echo count($not_registered_amonetize_transaction) ."\n";
        print_r($info);
    }
    
}

?>
