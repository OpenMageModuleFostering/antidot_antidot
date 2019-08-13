<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Model_Transport_Ftp extends MDN_Antidot_Model_Transport_Abstract implements MDN_Antidot_Model_Transport_Interface 
{
    
    /**
     * {@inherit}
     */
    public function send($file) 
    {
        $ftpConfig = Mage::getStoreConfig('antidot/ftp');
        if (!$ftpConfig)
            throw new Exception('No ftp configuration set');

        foreach($ftpConfig as $key =>  $config) {
            if(empty($config) && ($key !== 'port') && ($key !== 'working_directory')) {
                throw new Exception("The ftp $key is required");
            }
        }
        Mage::log('Send file '.$file, null, 'antidot.log');
        
        if(!$fHandle = fopen($file, 'r')) {
            throw new Exception("Can't read file ".$file);
        }
        
        $ftpHost = 'upload.antidot.net';
        if (isset($ftpConfig['host'])) {
        	$ftpHost = $ftpConfig['host'];
        }
        
        $url = 'sftp://'
                .$ftpConfig['login'].':'.$ftpConfig['password']
                .'@'.$ftpHost.'/'.$ftpConfig['directory'].'/'.basename($file);
        Mage::log('Ftp connect with : '.$url, null, 'antidot.log');
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_UPLOAD, 1);
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
        curl_setopt($curl, CURLOPT_INFILE, $fHandle);
        curl_setopt($curl, CURLOPT_INFILESIZE, filesize($file));
        curl_exec($curl);
        
        $errorNo = curl_errno($curl);
        if ($errorNo != 0) {
            throw new Exception("Can't send the file to ftp (error : ".$errorNo.")");
        }
    }
}
