<?php
/**
 * This class rewrite the ManaFilter Helper if the mana filter module exist
 * Otherwise it rewrite an empty class and isn't used at all
 */
if (!Mage::helper('core')->isModuleEnabled('Mana_Filters')) {
	class Mana_Filters_Helper_Data extends Mage_Core_Helper_Abstract {}	
}
class MDN_Antidot_Helper_ManaFilters_Data extends Mana_Filters_Helper_Data {

    /**
     * override method : return MDN_Antidot_Model_Catalogsearch_Layer if
     * Antidot AFStore engine is enabled
     * 
     * @return Mage_Catalog_Model_Layer
     * @throws Exception
     */
    public function getLayer ($mode = null) {
    	if (!$mode) {
    		$mode = $this->getMode();
    	}
    	switch ($mode) {
    		case 'category':
    			return Mage::getSingleton($this->useSolrForNavigation()
    			? 'enterprise_search/catalog_layer'
    					: 'catalog/layer'
    							);
    		case 'search':
    			return Mage::getSingleton($this->useAntidotForSearch()
                ? 'Antidot/catalogsearch_layer' : ($this->useSolrForSearch()
    			? 'enterprise_search/search_layer' : 'catalogsearch/layer')
    									);
    		default:
    			throw new Exception('Not implemented');
    	}
    }
    
    /**
     * return if Antidot AFStore engine is enabled
     * @return boolean
     */
    public function useAntidotForSearch() {
    	if (!Mage::helper('core')->isModuleEnabled('MDN_Antidot')) {
    		return false;
    	}
    	/* @var $helper MDN_Antidot_Helper_Data */
    	$helper = Mage::helper('Antidot');
    
    	return $helper->isActiveEngine();
    }
}