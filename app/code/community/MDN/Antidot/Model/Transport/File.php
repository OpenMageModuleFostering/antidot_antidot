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
 * @copyright  Copyright (c) 2015 Antidot (http://www.antidot.net)
 * @author : Antidot devmagento@antidot.net
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Model_Transport_File extends MDN_Antidot_Model_Transport_Abstract implements MDN_Antidot_Model_Transport_Interface 
{
    
    const DIRECTORY = '/home/bmsoliv2/www/magento/';
    
    /**
     * {@inherit}
     */
    public function send($file, $exportModel, SAI_CurlInterface $curlConnector=null)
    {
        return rename($file, self::DIRECTORY.'/'.basename($file));
    }
}
