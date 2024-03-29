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
class MDN_Antidot_Block_System_Config_Fieldset_Notice
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('Antidot');
        $html = '<div class="be2bill-api-notice">';
        $html .= '<ul style="margin:10px;color:red">';
        if (!class_exists('DOMDocument')) {
            $html .= "<li><strong>" . $helper->__("DOMDocument class doesn't exist, you must install php libxml extension, otherwise the xsd validation during export will not run and autocomplete neither") . "</strong></li>";
        }
        if (!class_exists('XSLTProcessor')) {
            $html .= "<li><strong>" . $helper->__("XSLTProcessor class doesn't exist, you must install php xsl extension, otherwise the autocomplete will not run correctly") . "</strong></li>";
        }
        /* MCNX-245 : don't check ZipArchive, it's not blocking for using module, because there's a workaround with shell_exec zip, and the error is logged correctly
         * if (!class_exists('ZipArchive')) {
            $html .= "<li><strong>" . $helper->__("ZipArchive class doesn't exist, you must install php zip extension, otherwise the zip operation during export may not run correctly") . "</strong></li>";
        }*/
        $extensions = get_loaded_extensions();
        if (!in_array('curl', $extensions)) {
            $html .= "<li><strong>" . $helper->__("The curl php extension is not installed, it's required to upload export files") . "</strong></li>";
        } else {
            $curl_version = curl_version();
            if (!isset($curl_version['protocols']) || !in_array("https", $curl_version['protocols'])) {
                $html .= "<li><strong>" . $helper->__("The curl php extension doesn't support https protocol, libcurl must be upgraded on your system, otherwise the export files won't be uploaded") . "</strong></li>";
            }
        }

        $html .= '</ul>';
        $html .= '<ul style="margin-bottom:10px;margin-top:10px;margin-left:5px;"><li>';
        $html .= '<a target="_blank "href="http://doc-store.antidot.net/book#!book;uri=aa77b81f03d5e6f1e553b7e6c6fd0e0c;breadcrumb=a909fa5b2c554e4d940a115a476a5fd5">'.$helper->__("Documentation").'</a>';
        $html .= '</ul></li>';
        $html .= '</div>';
        return $html;
    }

}
