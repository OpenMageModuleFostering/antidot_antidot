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
class MDN_Antidot_Block_System_Config_Button_AfsStore extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /*
     * The AfsStore Back-Office can be translated in theses locales
     */
    protected $afsStoreLocales = array('en', 'fr');

    /**
     * {@inherit}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
 		$this->setElement($element);
        $url = 'https://bo-store.afs-antidot.net/';

        /*
         * we get the magento back-office session language to determine
         * the locale of the AfsStore Back-office link
         */
        $codeLocale = Mage::getSingleton('adminhtml/session')->getLocale();
        $urlLocale = Mage::helper('Antidot')->getLanguageFromCodeLocale($codeLocale);
        if (in_array($urlLocale, $this->afsStoreLocales)) {
            $url .= $urlLocale;
        } else {
            $url .= 'en';
        }

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('Antidot')->__('Analytics, Synonyms, Promote'))
                    ->setOnClick("window.open('$url')")
                    ->toHtml();

        return $html;
    }
}