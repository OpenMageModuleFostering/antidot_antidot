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
class MDN_Antidot_Model_Resource_Engine_Antidot extends MDN_Antidot_Model_Resource_Engine_Abstract
{
    /**
     * @var booleen Check if the note is already added
     */
    protected $addedNote = false;

    /**
     * Initializes search engine.
     *
     * @see MDN_Antidot_Model_Resource_Engine_Antidotclient
     */
    public function __construct()
    {
        $this->client = Mage::getModel('Antidot/search_search');
    }

    /**
     * reset addedNote (unit tests)
     */
    public function init() {
        $this->addedNote = false;
    }

    /**
     * Returns search helper.
     *
     * @return MDN_Antidot_Helper_Antidot
     */
    protected function _getHelper()
    {
        return Mage::helper('Antidot/antidot');
    }

    /**
     * Prepares facets conditions.
     *
     * @param array $facetsFields
     * @return array
     */
    protected function prepareFacetsConditions($facetsFields)
    {
        $result = array();
        if (is_array($facetsFields)) {
            foreach ($facetsFields as $facetField => $facetFieldConditions) {
                if (empty($facetFieldConditions)) {
                    $result[] = $facetField;
                } else {
                    foreach ($facetFieldConditions as $facetCondition) {
                        $result['queries'][] = array($facetField => $facetCondition);
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * Prepares facets query response.
     *
     * @param mixed $response
     * @return array
     */
    protected function prepareFacetsQueryResponse($response)
    {
        $result = array();
        foreach ($response as $facet) {
            foreach($facet->get_elements() as $element) {
                if ($facet->get_layout() === 'TREE') {
                    if(!isset($result[$facet->get_id()])) {
                        $result[$facet->get_id()] = array();
                    }
                    if(Mage::helper('Antidot')->hasFacetMultiple($facet->get_id())) {
                        $result[$facet->get_id()] = array_merge($result[$facet->get_id()], $this->getListFacet($element));
                    } else {
                        $result[$facet->get_id()] = array_merge($result[$facet->get_id()], $this->getTreeFacet($element));
                    }
                } elseif ($facet->get_layout() === 'INTERVAL') {
                    $key = str_replace(array('[', ']'), array('', ''), $element->key);
                    list($from, $to) = explode(' .. ', $key);

                    $tplPrice = Mage::getStoreConfig('antidot/engine/price_facet');
                    $label = str_replace(array('{min}', '{max}'), array($from, $to), $tplPrice);
                    $result[$facet->get_id()][$from.'-'.$to] = array('count' => $element->count, 'label' => $label);
                }
            }
        }

        $maxOptions = (int)Mage::getStoreConfig('antidot/engine/facet_options');
        foreach($result as &$facets) {
            $facets = array_slice($facets, 0, $maxOptions);
        }

        return $result;
    }

    /**
     * Retrieve elements from tree facet
     *
     * @param StdClass $element
     * @param array     $sortElements
     * @return array
     */
    protected function getListFacet($element)
    {
        $result[$element->key] = array('count' => $element->count, 'label' => $element->label);
        if(!empty($element->values)) {
            foreach($element->values as $childElement) {
                $result = array_merge($result, $this->getListFacet($childElement));
            }
        }

        return $result;
    }

    /**
     * Retrieve elements from tree facet
     *
     * @param StdClass $element
     * @param array     $sortElements
     * @return array
     */
    protected function getTreeFacet($element)
    {
        $result[$element->key] = array('count' => $element->count, 'label' => $element->label);
        if(!empty($element->values)) {
            $result[$element->key]['child'] = array();
            foreach($element->values as $childElement) {
                $result[$element->key]['child'] = array_merge($result[$element->key]['child'], $this->getTreeFacet($childElement));
            }
        }

        return $result;
    }

    /**
     * Prepares filters.
     *
     * @param array $filters
     * @return array
     */
    protected function prepareFilters($filters)
    {

        $result = array();
        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    if (substr($field, 0, 5) === 'price') {
                        list($from, $to) = explode('-', $value[0]);
                        $fieldCondition = array($field => "[$from .. $to]");
                    } else {
                        $fieldCondition = array();
                        foreach ($value as $part) {
                            //explode values if facet is multi select
                            $isMultiSelect = $this->isMultiSelect($field);
                            if ($isMultiSelect) {
                                $fieldCondition = array($field => $this->extractMultiSelectValues($part));
                            } else {
                                $fieldCondition = array($field => $part);
                            }
                            break;
                        }
                    }
                } else {
                    $fieldCondition = array($field => $value);
                }
                
                $result[] = $fieldCondition;
            }
        }

        return $result;
    }

    /**
     * Extract the multi select values from his raw value
     *
     * @param $rawValue
     * @return array
     */
    public function extractMultiSelectValues($rawValue)
    {
        /* MCNX-171 : String facet with coma were sent as two facet
         * Correction : we use str_getcsv because fields are separated by , and enclosed by ", then we re-add the enclosing "
         */
        $partArray = str_getcsv($rawValue);
        array_walk($partArray, function(&$value, $key) {
            $value='"' . $value . '"';
        });
        return $partArray;
    }

    /**
     * Return true if a facet allows multi select
     *
     * @param $field
     */
    public function isMultiSelect($field)
    {
        $facets = Mage::helper('Antidot')->getFacetsFilter();

        if (isset($facets[$field]))
            return $facets[$field]['multiple'];

        return false;
    }

    /**
     * Prepares query response.
     *
     * @param StdClass $response
     * @return array
     */
    protected function prepareQueryResponse($response, $type = 'Catalog')
    {
        $result = array();
        if ($response) {
            if ($type === 'Categories') {
                foreach ($response->get_replies() as $reply) {
                    $reply = $this->getDataFromReply($reply);
                    $result[] = $reply['id'];
                }

                return $result;
            }

            if ($type === 'Catalog') {
                $this->_lastNumFound = (int)$response->get_meta()->get_total_replies();
                foreach ($response->get_replies() as $reply) {
                    $result[] = $this->_objectToArray($this->getDataFromReply($reply));
                }
            }

            if ($type === 'Articles' || $type === 'Stores') {
                foreach ($response->get_replies() as $reply) {
                    $data = $this->getDataFromReply($reply);
                    $data['title'] = $reply->get_title();
                    $data['abstract'] = $reply->get_abstract();
                    $result[] = $data;
                }
            }

        }

        return $result;

    }
    
    /**
     * Retrieve client data
     * 
     * @param ReplyHelper $reply
     * @return array
     */
    protected function getDataFromReply($reply)
    {
        $data = array();
        $sxe = simplexml_load_string(str_replace('&', '&amp;', $reply->get_clientdata()->get_value()));

        $data['id'] = (string)$sxe['id'];
        foreach($sxe->children() as $field) {
            if($field->children()) {
                foreach($field->children() as $child) {
                    $data[$field->getName()][] = (string)$child;
                }
            } else {
                $data[$field->getName()] = (string)$field;
            }
        }
        return $data;
    }

    /**
     * Prepares sort fields.
     *
     * @param array $sortBy
     * @return array
     */
    protected function prepareSortFields($sortBy)
    {
        $result = array();
        foreach ($sortBy as $sort) {
            $_sort = each($sort);
            $sortField = $_sort['key'];
            $sortType = $_sort['value'];
            
            $result[] = $sortField.','.trim(strtoupper($sortType));
        }

        return $result;
    }

    /**
     * Performs search and facetting.
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    protected function _search($query, $params = array())
    {
        $_params = $this->_defaultQueryParams;
        if (is_array($params) && !empty($params)) {
            $_params = array_merge($_params, $params);
        }
        
        $searchParams = array();
        $searchParams['page'] = isset($_params['p']) ? (int) $_params['p'] : 1;
        $searchParams['limit'] = (int)$_params['limit'];

        if (!is_array($_params['params'])) {
            $_params['params'] = array($_params['params']);
        }

        /* we applied the tuned defaut sorting if the relevance sorting is asked in the request */
        if(!empty($_params['sort_by']) && $_params['sort_by']!='afs:relevance') {
            $searchParams['sort'] = $this->prepareSortFields($_params['sort_by']);
        } elseif($configSort = Mage::getStoreConfig('antidot/engine/default_sort')) {
            $listDefaultSort = unserialize($configSort);
            foreach($listDefaultSort as $defaultSort) {
                list($defaultSort['field']) = explode('|', $defaultSort['field']);
                $_params['sort_by'][] = array($defaultSort['field'] => $defaultSort['dir']);
            }

            $searchParams['sort'] = $this->prepareSortFields($_params['sort_by']);
        }
        
        if (isset($params['facets']) && !empty($params['facets'])) {
            $searchParams['facets'] = $this->prepareFacetsConditions($params['facets']);
        }
        
        if (!empty($_params['params'])) {
            foreach ($_params['params'] as $name => $value) {
                $searchParams[$name] = $value;
            }
        }

        $searchParams['filters'] = $this->prepareFilters($_params['filters']);

        Varien_Profiler::start('ANTIDOT_SEARCH');
        $resultAntidot = $this->client->search($query, $searchParams);
        Varien_Profiler::stop('ANTIDOT_SEARCH');
        
        return $this->formatResult($resultAntidot);
    }
    
    /**
     * Format the response from antidot
     * 
     * @param StdClass $resultAntidot
     * @return array
     */
    protected function formatResult($resultAntidot)
    {
        $result = array('ids' => array(), 'total_count' => 0);
        if(isset($resultAntidot->replyset) && $resultAntidot->replyset !== null) {
            $result = array(
                'ids' => $this->prepareQueryResponse($resultAntidot->replyset),
                'total_count' => $resultAntidot->replyset->get_meta()->get_total_replies()
            );
        }

        if (isset($resultAntidot->replyset) && $resultAntidot->replyset !== null && $resultAntidot->replyset->has_facet()) {
            $result['facets'] = $this->prepareFacetsQueryResponse($resultAntidot->replyset->get_facets());
        }
        
        if(isset($resultAntidot->replysetCategories) && $resultAntidot->replysetCategories !== null) {
            $result['category_ids'] = $this->prepareQueryResponse($resultAntidot->replysetCategories, 'Categories');
        }

        if (isset($resultAntidot->additionalReplyset) && is_array($resultAntidot->additionalReplyset)) {
            foreach ($resultAntidot->additionalReplyset as $additionalFeed => $additionalReplyset) {
                $result[$additionalFeed] = $this->prepareQueryResponse(
                    $additionalReplyset,
                    $additionalFeed
                );
            }
        }

        if(isset($resultAntidot->promote) && $resultAntidot->promote && $replies = $resultAntidot->promote->get_replies()) {
            if((Mage::getStoreConfig('antidot/promote/redirect') === 'no_result' && $result['total_count'] == 0) || Mage::getStoreConfig('antidot/promote/redirect') === 'always') {
                $promote = current($replies);
                $redirectUrl = '';
                if ($promote->get_type() == 'redirect') {
                    $redirectUrl = $promote->get_url();
                } elseif ($promote->get_type() == 'default') {
                    $redirectUrl = $promote->get_uri();
                }
                if($redirectUrl !== Mage::helper('core/url')->getCurrentUrl()) {
                    $result['redirect'] = $redirectUrl;
                }
            }
            $result['banners'] = array();
            foreach ($replies as $promote) {
                if ($promote->get_type() == 'banner') {
                    $bannerObject = new Varien_Object();
                    $bannerObject->setData('url', $promote->get_url());
                    $bannerObject->setData('image', $promote->get_image_url());
                    $result['banners'][] = $bannerObject;
                }
            }
        }

        $result['spellcheck'] = false;
        if (!$this->addedNote) {
            if (isset($resultAntidot->spellcheck) && $resultAntidot->spellcheck) {
                $redirect = Mage::getStoreConfig('antidot/engine/redirect_product');
                if ($result['total_count'] == 0 ||
                    ($result['total_count'] == 1 && $redirect == MDN_Antidot_Model_System_Config_Source_Redirect::UNLESS_SPELLCHECK) ) {
                    $spellcheck = $resultAntidot->spellcheck;
                    $message =  Mage::helper('Antidot')->__(Mage::getStoreConfig('antidot/engine/spellcheck'));
                    $link = '<a href="'.Mage::helper('catalogsearch')->getResultUrl(
                            $spellcheck
                        ).'">'.$spellcheck.'</a>';
                    $message = str_replace('{spellcheck}', $link, $message);

                    Mage::helper('catalogsearch')->addNoteMessage($message);
                    $this->addedNote = true;
                    $result['spellcheck'] = true;
                }
            }
        }

        /**
         * MCNX-64 query orchestration : add a message if the query has been re-executed by AFSStore based on spellcheck
         */
        if (!$this->addedNote && isset($resultAntidot->isOrchestrated) && isset($resultAntidot->spellcheck)) {
            if(($spellcheck = $resultAntidot->spellcheck) && $resultAntidot->isOrchestrated) {
                if ($message = Mage::getStoreConfig('antidot/engine/spellcheck_query')) {
                    $message = Mage::helper('Antidot')->__($message);
                    $message = str_replace('{spellcheck}', $spellcheck, $message);
                    $message = str_replace('{originalQuery}', $resultAntidot->originalQuery, $message);

                    Mage::helper('catalogsearch')->addNoteMessage($message);
                    $this->addedNote = true;
                }
            }
        }

        return $result;
    }
    
    /**
     * Default method call by Magento
     */
    public function cleanIndex()
    {
        return $this;
    }
    
    /**
     * Default method call by Magento
     */
    public function prepareEntityIndex()
    {
        return $this;
    }

    /**
     * Default method call by Magento
     */
    public function saveEntityIndexes()
    {
        return $this;
    }
    
    /**
     * Define if current search engine supports advanced index
     *
     * (compatibiliy Magento Enterprise)
     * 
     * @return bool
     */
    public function allowAdvancedIndex()
    {
    	return false;
    }
    
    /**
     * Checks search engine availability.
     *	- Antidot search is disabled on the magento advanced search
     *  - TODO : see Jira MCNX-19 : Extension should be able to detect bad web service configuration,
     *       set antidotServiceAvailable to false if the webservice isn't available
     * @return bool
     */
    public function test()
    {
        $notInAdvancedSearch = (Mage::app()->getRequest()->getControllerName() != 'advanced');
        $antidotServiceAvailable = true;
        return $antidotServiceAvailable && $notInAdvancedSearch;
    }
}
