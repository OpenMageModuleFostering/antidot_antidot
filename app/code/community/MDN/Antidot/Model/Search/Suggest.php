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
class MDN_Antidot_Model_Search_Suggest extends MDN_Antidot_Model_Search_Abstract 
{

    const URI    = 'http://%s/acp?afs:service=%s&afs:status=%s&afs:feed=%s&afs:query=%s&afs:sessionId=%s';
    
    /**
     * List feeds to use for the query sprintf($feed, website_id, lang)
     * 
     * @var array
     */
    private $feed = array(
        'products' => array(
            'prefix' => 'featured_products_',
            'tpl'    => 'featured_products_%d_%s',
            'number' => 5,
        ),
        'categories' => array(
            'prefix' => 'categories_',
            'tpl'    => 'categories_%d_%s',
            'number' => 5,
        ),
        'brands' => array(
            'prefix' => 'brands_',
            'tpl'    => 'brands_%d_%s',
            'number' => 5,
        ),
    );
    
    /**
     * @var array Types sorted
     */
    protected $typeOrder = array();
    
    /**
     * Xslt Template
     * 
     * @var string 
     */
    protected $template;
    
    /**
     * {@inherit}
     */
    public function _construct()
    {
        parent::_construct();
        
        $config = Mage::getStoreConfig('antidot/suggest');
        $this->template = trim($config['template']);
        foreach($config as $field => $value) {
            if(isset($this->feed[$field]) && $value === '0') {
                unset($this->feed[$field]);
            } elseif(preg_match('/([a-z]+)_displayed/', $field, $matches)) {
                $field = $matches[1];
                if(isset($this->feed[$field])) {
                    $this->feed[$field]['number'] = (int)$value;
                }
            } elseif(preg_match('/order_([0-4])/', $field, $matches)) {
                $order = $matches[1];
                $this->typeOrder[$value] = $order;
            }
        }
        $this->loadFacetAutocomplete();
    }

    /**
     * @return array
     */
    protected function loadFacetAutocomplete()
    {
        $facets = @unserialize(Mage::getStoreConfig('antidot/fields_product/properties'));
        foreach($facets as $facet) {
            if($facet['autocomplete'] === '1') {
                $this->feed['property_'.$facet['value']] = array(
                    'prefix' => 'property_'.$facet['value'].'_',
                    'tpl'    => 'property_'.$facet['value'].'_%d_%s',
                    'number' => 5,
                );
            }
        }
    }
    
    /**
     * Get the suggest list
     * 
     * @param string $query
     * @param string $format
     */
    public function get($query, $format = 'html')
    {
        $url = $this->buildUrl($query);
        Mage::log($url, null, 'antidot.log');
        if(!$content = file_get_contents($url)) {
            $response = array();
        } elseif(!$response = json_decode($content, true)) {
            $response = array();
        }

        $xml = $this->getXmlSuggest($response, $query);
        if($format === 'xml') {
            $this->displayXml($xml);
        }
        
        return $this->transformToXml($xml);
    }

    /**
     * Display xml
     *
     * @param string $xml
     */
    private function displayXml($xml)
    {
        header ("Content-Type:text/xml");
        echo $xml;
        exit(0);
    }
    
    /**
     * Build url to request AFS
     * 
     * @param string $query
     * @return string
     */
    protected function buildUrl($query) 
    {
        $url = sprintf(
                static::URI, 
                $this->afsHost, 
                $this->afsService, 
                $this->afsStatus, 
                $this->getFeeds(), 
                urlencode($query),
                $this->getSession());
        return $url;
    }
    
    /**
     * Extract the items from response
     * 
     * @param array $response
     * @return array
     */
    protected function getXmlSuggest($response, $query)
    {
        $xml = Mage::helper('Antidot/XmlWriter');
        $xml->init();

        $ns = array(
            'xmlns:afs' => 'http://ref.antidot.net/v7/afs#',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation' => 'http://ref.antidot.net/v7/afs# http://ref.antidot.net/v7.7/acp-reply.xsd'
        );
        $xml->push('afs:replies', $ns);

            $xml->push('afs:header');
                $xml->emptyElement('afs:query', array('textQuery' => ($query)));    //AFM98 : do NOT htmlentities query
            $xml->pop();

            if(!is_numeric(key($response))) {
                $response = $this->setOrders($response);
                foreach($response as &$responseOrder) {
                    $type = key($responseOrder);
                    $data = current($responseOrder);
                    $feed = $this->getFeed($type);

                    $currentItems = 0;
                    $xml->push('afs:replySet', array('name' => $type));
                    $xml->emptyElement('afs:meta', array('uri' => $type, 'producer' => 'acp', 'totalItems' => count($data[2])));
                    foreach($data[2] as $key => &$item) {
                        $xml->push('afs:reply', array('label' => $data[1][$key]));
                        $this->writeOptions($xml, $item);
                        $xml->pop();
                        if(++$currentItems >= $feed['number']) {
                            break;
                        }
                    }
                    $xml->pop();
                }
            }
        $xml->pop();
        
        return $xml->getXml();
    }
    
    /**
     * Write suggest options
     * 
     * @param XmlWriter $xml
     * @param array $items
     */
    protected function writeOptions($xml, $item)
    {
        foreach($item as $field => $value) {
            if(is_array($value)) {
                $this->writeOptions($xml, $value);
                continue;
            }

            $attributes = array(
                'key'   => $field,
                'value' => html_entity_decode($value),
            );
            $xml->emptyelement('afs:option', $attributes);
        }
    }
    
    /**
     * Set response order
     * 
     * @param array $response
     * @return array
     */
    protected function setOrders($response)
    {
        // set orders
        $types = array();
        foreach($response as $type => $data) {
            $feed = $this->getFeedKey($type);
            if(array_key_exists($feed, $this->typeOrder)) {
                $types[$this->typeOrder[$feed]][$type] = $data;
                unset($response[$type]);
            }
        }
        
        foreach($response as $type => $data) {
            $types[][$type] = $data;
        }
        
        ksort($types);
        return $types;
    }
    
    
    /**
     * Build the feed param
     * 
     * @return string
     */
    protected function getFeeds() 
    {
        list($lang) = explode('_', Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId()));
        
        $feeds = '';
        foreach($this->feed as $feed) {
            $id = substr($feed['prefix'], 0, 18) !== 'featured_products_' ? Mage::app()->getStore()->getWebsiteId() : Mage::app()->getStore()->getId();
            $feeds.= empty($feeds) ? '' : '&afs:feed=';     //for AFS engine v7.7
            $feeds.= sprintf($feed['tpl'], $id, $lang);
        }
        
        return $feeds;
    }
    
    /**
     * Get feed by type
     * 
     * @param string $type
     * @return array
     */
    protected function getFeed($type) 
    {
        foreach($this->feed as $feed) {
            if(strpos($type, $feed['prefix']) !== false) {
                return $feed;
            }
        }
    }
    
    /**
     * Get the key feed
     * 
     * @param string $type
     * @return string
     */
    protected function getFeedKey($type) 
    {
        foreach($this->feed as $key => $feed) {
            if(strpos($type, $feed['prefix']) !== false) {
                return $key;
            }
        }
    }
    
    /**
     * Format the response to html format
     * 
     * @param string $suggestXml Response from AFS formated
     * @return string
     */
    protected function transformToXml($suggestXml) 
    {

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($suggestXml);
        if ($xml === false) {
        	Mage::log('Error loading xml (suggest) : ', null, 'antidot.log');
        	Mage::log(print_r(libxml_get_errors(), true), null, 'antidot.log');
        	return '';
        }
        $xsl = simplexml_load_string($this->template);
        if ($xsl === false) {
        	Mage::log('Error loading xsl template (suggest) : ', null, 'antidot.log');
        	Mage::log(print_r(libxml_get_errors(), true), null, 'antidot.log');
        	return '';
        }
        
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);
        
        $xml = $xslt->transformToXml($xml);
        if ($xml === false) {
        	Mage::log('Error during xslt transformation (suggest) : ', null, 'antidot.log');
        	Mage::log(print_r(libxml_get_errors(), true), null, 'antidot.log');
            return '';
        }

        return str_replace('<?xml version="1.0"?>', '', $xml);
    }
}
