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
class MDN_Antidot_Block_System_Config_Button_ShowXml extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * {@inherit}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        
        $suggestXml = '';
        $configFile = dirname(__FILE__).'/../../../../etc/config.xml';
        if($sxe = @simplexml_load_file($configFile)) {
            if($suggestXml = $sxe->xpath('//suggest_xml')) {
                $suggestXml = str_replace('"', '\"', trim(htmlentities((string)$suggestXml[0])));
                $suggestXml = str_replace("\n", '\n', $suggestXml);
                $suggestXml = str_replace("    ", '&nbsp;&nbsp;&nbsp;&nbsp;', $suggestXml);
            }
        }
        
        return $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('Antidot')->__('Display XML'))
                    ->setOnClick(
                        "var w = window.open('', '', 'width=400,height=400,resizeable,scrollbars');"
                       ."w.document.write('".$suggestXml."');"
                       ."return false;"
                    )
                    ->toHtml();
    }
}