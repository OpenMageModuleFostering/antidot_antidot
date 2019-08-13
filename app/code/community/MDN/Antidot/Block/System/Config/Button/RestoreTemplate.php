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
class MDN_Antidot_Block_System_Config_Button_RestoreTemplate extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * {@inherit}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        
        $xslt = '';
        $configFile = dirname(__FILE__).'/../../../../etc/config.xml';
        if($sxe = @simplexml_load_file($configFile)) {
            if($template = $sxe->xpath('//template')) {
                $search  = array('"', "'", "\n");
                $replace = array('\"', "\'", '\n');
                $xslt = str_replace($search, $replace, trim(htmlentities((string)$template[0])));
            }
        }
        
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel(Mage::helper('Antidot')->__('Restore the default template'))
                    ->setOnClick("$('antidot_suggest_template').setValue('".$xslt."'); return false;")
                    ->toHtml();

        return $html;
    }
}