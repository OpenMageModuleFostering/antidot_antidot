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
class MDN_Antidot_Model_Search_Suggest extends MDN_Antidot_Model_Search_Abstract 
{

    const URI    = 'http://%s/acp?afs:service=%s&afs:status=%s&afs:output=xml&afs:feed=%s&afs:feedOrder=%s&afs:replies=%s&afs:query=%s&afs:sessionId=%s';

    const DEFAULT_REPLIES_NUMBER = 10;
    /**
     * List feeds to use for the query sprintf($feed, website_id, lang)
     * 
     * @var array
     */
    private $feed = array(
        'products' => array(
            'tpl'    => 'featured_products_%d_%s',
            'number' => self::DEFAULT_REPLIES_NUMBER,
            'order'  => 1,    
        ),
        'categories' => array(
            'tpl'    => 'categories_%d_%s',
            'number' => self::DEFAULT_REPLIES_NUMBER,
            'order'  => 2,
        ),
        'brands' => array(
            'tpl'    => 'brands_%d_%s',
            'number' => self::DEFAULT_REPLIES_NUMBER,
            'order'  => 3,
        ),
        'articles' => array(
            'tpl'    => 'articles_%d_%s',
            'number' => self::DEFAULT_REPLIES_NUMBER,
            'order'  => 4,
        ),
        'stores' => array(
            'tpl'    => 'stores_%d_%s',
            'number' => self::DEFAULT_REPLIES_NUMBER,
            'order'  => 5,
        ),
    );
    
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
                if(isset($this->feed[$value])) {
                    $this->feed[$value]['order'] = $order;
                }
            }
        }
        
        $this->loadFacetAutocomplete();
        $this->loadAdditionalFeeds();
        
        list($lang) = explode('_', Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId()));
        foreach($this->feed as $key => $feed) {
        	//take the storeId for product feed, website for others
        	$id = ($key == 'products') ? Mage::app()->getStore()->getWebsiteId() : Mage::app()->getStore()->getId();
        	$this->feed[$key]['name'] = sprintf($feed['tpl'], $id, $lang);
        }
        
    }

    /**
     * Add the facets configured in the back-office as used in autocomplete
     * 
     * @return array
     */
    protected function loadFacetAutocomplete()
    {
        $facets = @unserialize(Mage::getStoreConfig('antidot/fields_product/properties'));
        foreach($facets as $facet) {
            if($facet['autocomplete'] === '1') {
                $this->feed['property_'.$facet['value']] = array(
                    'tpl'    => 'property_'.$facet['value'].'_%d_%s',
                    'number' => self::DEFAULT_REPLIES_NUMBER,
                    'order' => (count($this->feed)+1),
                );
            }
        }
    }

    /**
     * Add the additional feeds configured in the BO
     * @return array
     */
    protected function loadAdditionalFeeds()
    {
    	$additionalFeeds = @unserialize(Mage::getStoreConfig('antidot/suggest/additionnal_feed'));
    	foreach($additionalFeeds as $feed) {
    		$addFeed = $feed['value'];
    	    $this->feed[$addFeed] = array(
   					'tpl'    => $addFeed,
   					'number' => self::DEFAULT_REPLIES_NUMBER,
    	            'order'  => (count($this->feed)+1),
   			);
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

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($url);
        if ($xml === false) {
			 Mage::log("Erreur lecture flux xml ". $url, null, 'antidot.log');           
        	 Mage::log(print_r(libxml_get_errors(), true), null, 'antidot.log');         
			 return "";
        }
        
		$xml = $this->postProcessXml($xml);
        
        if($format === 'xml') {
            return $this->displayXml($xml);
        }
        
        return $this->transformToXml($xml);
    }

    /**
     * Post Process xml : limit the number of result in
     * each feed according to backend configuration
     *
     * @param SimpleXmlElement $xml
     * @return SimpleXmlElement 
     */
    private function postProcessXml(&$xml)
    {
        
    	$ns = $xml->getNamespaces(true);
    	foreach ($xml->children($ns['afs'])->replySet as $replySet) {

    		$type = (string)$replySet->attributes()->name;
    		$feed = $this->getFeed($type);

    		$nbLimit = $feed['number'];
    		$nbItems = (int)$replySet->meta->attributes()->totalItems;
    		if ($nbLimit<$nbItems) {
	    		$replySet->meta->attributes()->totalItems = $nbLimit;
	    		for ($i=($nbItems-1);  $i >=  ($nbLimit); $i--) {
	    			unset($replySet->reply[$i]);
	    		}
    		}
		}
        
     	return $xml;

     }
    
    
    /**
     * Display xml
     *
     * @param SimpleXmlElement $xml
     */
    private function displayXml($xml)
    {
        header ("Content-Type:text/xml");
        echo $xml->asXML();
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
                $this->getFeedOrder(),
            	$this->getReplies(),
                urlencode($query),
                $this->getSession());
        return $url;
    }
    
    /**
     * Build the feed param
     * 
     * @return string
     */
    protected function getFeeds() 
    {
        $feeds = '';
        foreach($this->feed as $feed) {
            $feeds.= empty($feeds) ? '' : '&afs:feed=';     //for AFS engine v7.7
            $feeds.= $feed['name'];
        }

        return $feeds;
    }

    /**
     * Build the feedOrder param
     *
     * @return string
     */
    protected function getFeedOrder()
    {
    
    	$feedOrder = array();
    	foreach($this->feed as $feed) {
    		$feedOrder[$feed['order']]=$feed['name'];
    	}
    	ksort($feedOrder);
    	$feedOrderParam = implode(',',$feedOrder);
    	return $feedOrderParam;
    }
    
    /**
     * Build the replies param
     *
     * Specify the max number of replies to AFSStore (is not specified, AFStore take 10),
     * this parameter is common to all feed then with take the higher specified
     *
     * @return string
     */
    protected function getReplies()
    {
        $maxReplies = 0;
        foreach($this->feed as $feed) {
            $maxReplies = ($feed['number']>$maxReplies)?$feed['number']:$maxReplies;
        }
        return $maxReplies;
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
            if($type == $feed['name']) {
                return $feed;
            }
        }
    }

    /**
     * Format the response to html format
     * 
     * @param SimpleXmlElement $xml Response from AFS formated
     * @return string
     */
    protected function transformToXml($xml) 
    {
    	if (!$xml) {
    		return '';
    	}
    	
        libxml_use_internal_errors(true);
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
