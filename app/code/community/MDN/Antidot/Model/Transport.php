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
class MDN_Antidot_Model_Transport extends Mage_Core_Model_Abstract 
{
    
    const TRANS_FILE = 'file';
    const TRANS_FTP  = 'ftp';
    const TRANS_HTTP = 'http';
    
    /**
     * Send files to Antidot
     * 
     * @param string $file File to send
     * @param string $type The transport type used to send the file
     */
    public function send($file, $type = self::TRANS_FILE) 
    {
        if($transport = Mage::getModel('Antidot/Transport_'.ucfirst($type))) {
            return $transport->send($file);
        }
        
        throw new Exception('The type transport "'.$type.'" does not exist');
    }
}