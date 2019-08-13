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
class MDN_Antidot_Block_System_Config_Html_ShowXml extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * {@inherit}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel(Mage::helper('Antidot')->__('Display XML'))
            ->setOnClick(
                 "var term = document.getElementById('suggest_term').value;"
                ."var url  = document.getElementById('suggest_url').value;"
                ."var w = window.open(url+'/Antidot/Front_Search/Suggest/?q='+term+'&format=xml', '', 'width=1000,height=750,resizeable,scrollbars');"
                ."return false;"
            )
            ->toHtml();

        $storeOptions = '';
        foreach (Mage::app()->getStores() as $store) {
            $url =  Mage::app()->getStore($store->getId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
            $storeOptions.= '<option value="'.$url.'">'.$store->getName().'</option>';
        }

        return '<div class="grid">'
                    .'<table class="border" cellspacing="0" cellpadding="0">'
                        .'<thead>'
                            .'<tr>'
                                .'<td>'.Mage::helper('Antidot')->__('Store').'</td>'
                                .'<td>'.Mage::helper('Antidot')->__('Term').'</td>'
                                .'<td></td>'
                            .'</tr>'
                        .'</thead>'
                        .'<tbody>'
                            .'<tr>'
                                .'<td><select name="suggest_url" id="suggest_url">'.$storeOptions.'</select></td>'
                                .'<td><input type="text" name="suggest_term" id="suggest_term" value="" /></td>'
                                .'<td>'.$button.'</td>'
                            .'</tr>'
                        .'</tbody>'
                    .'</table>'
                .'</div>'
        ;
    }
}