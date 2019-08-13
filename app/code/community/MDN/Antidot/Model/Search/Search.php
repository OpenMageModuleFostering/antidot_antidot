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
class MDN_Antidot_Model_Search_Search extends MDN_Antidot_Model_Search_Abstract
{
    public static $lastSearchTranslations = array();

    /**
     * @var string Language used
     */
    protected $lang;

    /**
     * @var array list feed
     */
    protected $feeds = array('Catalog', 'Promote');

    /**
     * @var AfsSearch
     */
    protected $afsSearch;

    /**
     * @var array facets
     */
    protected $facets;

    /**
     * @var array additionalFeeds;
     */
    protected $additionalFeeds = array();

    /**
     * @var AfsIntrospection
     */
    protected $introspector;

    /**
     * {@inherit}
     */
    public function _construct()
    {
        parent::_construct();

        list($lang) = explode('_', Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId()));
        $this->lang = $lang;

        foreach (Mage::getStoreConfig('antidot/engine') as $field => $value) {
            //If category search is enabled, add it to feeds
            if (substr($field, 0, 4) === 'feed' && $value === '1') {
                $this->feeds[] = ucfirst(substr($field, 5));
            }
        }

        //Add Articles and Stores feeds if corresponding tabs are activated in BO
        foreach (Mage::helper('Antidot')->getActiveResultTabs() as $tab) {
            if ($tab['tab'] !== 'products') { //articles or stores
                $this->additionalFeeds[] = ucfirst($tab['tab']);
            }
        }

        $this->feeds = array_unique(array_merge($this->feeds, $this->additionalFeeds));

        if ($this->isConfigured) {
            $this->afsSearch = new AfsSearch($this->afsHost, $this->afsService, $this->afsStatus);
        } else {
            //FOR UNIT TEST only : pass the afsSearch mock objetc through Mage::registry
            if (Mage::registry('test_afsSearch')) {
                $this->afsSearch = Mage::registry('test_afsSearch');
            }
        }
    }

    public function getLastSearchTranslations() {
        return self::$lastSearchTranslations;
    }

    /**
     * Get the suggest list
     *
     * @param string $query
     */
    public function search($search = null, $params = array(), $facetOnly = false)
    {
        if (!$this->isConfigured) {
            return;
        }

        if (!$facetOnly) {
            $params['filters'][] = array(
                'store'   => '"' . Mage::app()->getStore()->getId() . '"',
                'website' => '"' . Mage::app()->getStore()->getWebsiteId() . '"',
            );

            if (!isset($params['lang'])) {
                $params['lang'] = $this->lang;
            }
        }


        $this->afsSearch->set_query($this->getQuery($search, $params));
        $results = $this->afsSearch->execute(AfsHelperFormat::HELPERS);
        Mage::log(urldecode($this->afsSearch->get_generated_url()), null, 'antidot.log');

        $resultAntidot = new stdClass();
        if ($results->in_error()) {
            return $resultAntidot;
        }

        $resultAntidot->spellcheck         = $this->getSpellcheckFromResult($results);
        $resultAntidot->originalQuery      = Mage::helper('catalogsearch')->getEscapedQueryText();
        $resultAntidot->promote            = $this->getPromoteFromResult($results);
        $resultAntidot->replyset           = $this->getReplySetFromResult($results);
        $resultAntidot->replysetCategories = $this->getReplySetFromResult($results, 'Categories');
        $resultAntidot->additionalReplyset = array();
        foreach ($this->additionalFeeds as $additionalFeed) {
            $resultAntidot->additionalReplyset[$additionalFeed] = $this->getReplySetFromResult($results, $additionalFeed);
        }

        $resultAntidot->isOrchestrated     = $this->getOrchestratedFromResult($results);

        //save translations
        if ($resultAntidot->replyset) {
	        foreach($resultAntidot->replyset->facets as $item)
    	    {
        	    self::$lastSearchTranslations[$item->id] = $item->label;
        	}
        }

        return $resultAntidot;
    }

    /**
     * Get spellcheck from result
     *
     * @param StdClass $results
     * @return string
     */
    protected function getSpellcheckFromResult($results)
    {
        $spellcheck = null;
        try {
            $spellcheck = $results->get_spellchecks();
            if($results->has_spellcheck() && !empty($spellcheck['Catalog'][0])) {
                $spellcheck = $spellcheck['Catalog'][0]->get_raw_text();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'antidot.log');
        }

        return $spellcheck;
    }

    /**
     * Get promote from result
     *
     * @param StdClass $results
     * @return string
     */
    protected function getPromoteFromResult($results)
    {
        $promote = null;
        try {
            $promote = $results->get_promote();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'antidot.log');
        }

        return $promote;
    }

    /**
     * Get Replyset from results
     *
     * @param StdClass $results
     * @param string $type Catalog|Product
     * @return ReplySetHelper|null
     */
    protected function getReplySetFromResult($results, $type = 'Catalog')
    {
        try {
            $replyset = $results->get_replyset($type);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'antidot.log');
            $replyset = null;
        }

        return $replyset;
    }

    /**
     * Get Orchestraed boolean from results
     *
     * @param StdClass $results
     * @return boolean
     */
    protected function getOrchestratedFromResult($results)
    {
        $orchestrated = false;
        try {
            $orchestrated = $results->is_orchestrated();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'antidot.log');
        }

        return $orchestrated;
    }

    /**
     * Return facets list
     *
     * @return array
     */
    public function getFacets()
    {
        if (!$this->facets) {
            $this->facets = array();

            $introspector = $this->getAfsIntrospector();
            $metadata = $introspector->get_all_metadata();

            if (isset($metadata['Catalog']) ) {
                foreach ($metadata['Catalog']->get_facets_and_filters_info() as $facet) {
                    //exclude some facets from AFSStore
                    $excludedFacets = array('afs:PaFId', 'afs:uri', 'afs:validity', 'magento_type', 'name', 'product', 'product_id', 'price', 'price_from', 'price_off');
                    if (!in_array($facet->get_id() , $excludedFacets)) {
                        $this->facets[$facet->get_id()] = $facet;
                    }
                }
            }

        }

        return $this->facets;
    }

    /**
     * Prepare the Antidot query
     *
     * @param string $search
     * @param array $params
     * @return AfsQuery
     */
    protected function getQuery($search, $params)
    {
        $query = new AfsQuery();
        $query = $query->set_query($search);
        $query = $query->set_session_id($this->getSession());
        $query = $query->set_user_id($this->getUserId());

        foreach ($this->feeds as $feed) {
            $query = $query->add_feed($feed);
        }

        if (isset($params['lang'])) {
            $query = $query->set_lang($params['lang']);
        }

        if (isset($params['filters']) && is_array($params['filters'])) {
            foreach ($params['filters'] as $filter) {
                if (is_array($filter)) {
                    foreach ($filter as $key => $values) {
                        $query = $query->add_filter_on_feed($key, $values, 'Catalog');
                        if ($key == "website") {
                            $query = $query->add_filter_on_feed($key, $values, 'Articles');
                        }
                    }
                } else {
                    //$query = $query->add_filter($key, $value);
                }
            }
        }

        if (isset($params['sort']) && is_array($params['sort'])) {
            foreach($params['sort'] as $sort) {
                list($field, $dir) = explode(',', $sort);
                $dir = $dir === 'ASC' && $field !== 'afs:relevance' ? AfsSortOrder::ASC : AfsSortOrder::DESC;
                $query = $query->add_sort($field, $dir);
            }
        }

        if (isset($params['limit']) && is_numeric($params['limit']) && $params['limit']>0) {
            $query = $query->set_replies((int)$params['limit']);
        } else {
            $query = $query->set_replies(100); 
            //when limit=all or limit=0, set limit to 100 in order to have enought results and avoid 
            //performances issues on very big numbers of results (instead AFStore defaut of 20 is used)
        }

        if (isset($params['page']) && is_numeric($params['page'])) {
            $query = $query->set_page((int)$params['page']);
        }

        $query = $query->add_log('AFS@Store for Magento v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version);

        $mageEdition = Mage::helper('Antidot')-> getMagentoEdition();
        $query = $query->add_log('Magento '.$mageEdition.' '.Mage::getVersion());

        $query = $this->setSelectionFacets($query);

        $query = $query->set_facets_values_sort_order(AfsFacetValuesSortMode::ITEMS, AfsSortOrder::DESC);

        return $query;
    }

    /**
     * Set selection facets
     *
     * @param AFSQuery $query
     * return AFSQuery
     */
    protected function setSelectionFacets($query)
    {
        $facets = Mage::helper('Antidot')->getFacetsFilter();

        $multiSelectionFacets = array();
        $monoSelectionFacets  = array();
        foreach($facets as $facetId => $facet) {
            if (!$facetId)
                continue;
            if($facet['multiple'] === '1') {
                $multiSelectionFacets[] = $facetId;
            } else {
                $monoSelectionFacets[] = $facetId;
            }
        }

        if(!empty($multiSelectionFacets)) {
            $query = call_user_func_array(array($query, 'set_multi_selection_facets'), $multiSelectionFacets);
        }

        if(!empty($monoSelectionFacets)) {
            $query = call_user_func_array(array($query, 'set_mono_selection_facets'), $monoSelectionFacets);
        }

        return $query;
    }

    /**
     * @return AfsIntrospection
     */
    private function getAfsIntrospector()
    {
        if (!$this->introspector && $this->afsSearch) {
            $this->introspector = (new AfsIntrospection($this->afsSearch));
        }
        return $this->introspector;
    }

    /**
     * @return boolean
     */
    public function isInstantSearch() {

        if (Mage::app()->getRequest()->getActionName()=='save') {
            //no need to call introspection on save
            return false;
        }
        if (!$this->getAfsIntrospector()) {
            Mage::getSingleton('adminhtml/session')->addWarning(
                Mage::helper('Antidot')->__('You need to configure your AFS@Store credentials and your webservices settings')
            );
            //if the webservice host is not yet configured, we consider as instant search and hide engine config
            return true;
        }
        if ($this->getAfsIntrospector()->in_error()) {
            Mage::getSingleton('adminhtml/session')->addWarning(
                Mage::helper('Antidot')->__('Your AFS@Store credentials or webservices settings are incorrects')
            );
            //if the webservice is not well configured, we consider as instant search and hide engine config
            return true;
        }
        return  ("instantSearch" === $this->getAfsIntrospector()->get_query_parameter("afs:storeContract"));
    }
}
