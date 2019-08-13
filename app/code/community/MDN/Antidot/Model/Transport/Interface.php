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
interface MDN_Antidot_Model_Transport_Interface
{
    /**
     * Send file to Antidot
     *
     * @param string $file Files to send
     * @param MDN_Antidot_Model_Export_Abstract $exportModel the type of data exported
     */
    public function send($files, $exportModel, SAI_CurlInterface $curlConnector=null);
}