<?php

 /**
 * 
 *
/**
 * This class rewrite the Enterprise search model observer if the Enterprise Search module exist
 * Otherwise it rewrite an empty class and isn't used at all
 */
if ((string)Mage::getConfig()->getModuleConfig('Enterprise_Search')->active != 'true') {
	class Enterprise_Search_Helper_Data extends Mage_Core_Helper_Abstract {}	
}
class MDN_Antidot_Helper_Enterprise_Search_Data extends Enterprise_Search_Helper_Data
{
 
	/**
	 * This function is called in Enterprise_Search_Model_Observer#resetCurrentSearchLayer
	 * wich reset the layer engine to Enterprise_Search instead of Antidot one
	 * 
	 * (non-PHPdoc)
	 * @see Enterprise_Search_Helper_Data::getIsEngineAvailableForNavigation()
	 */
    public function getIsEngineAvailableForNavigation($isCatalog = true)
    {
        if (Mage::helper('Antidot')->isActiveEngine()){
    	    return false;
    	} else {
    	    return parent::getIsEngineAvailableForNavigation($isCatalog);
    	}
    }
}
