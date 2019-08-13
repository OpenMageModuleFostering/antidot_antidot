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
abstract class MDN_Antidot_Model_Export_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Instance of XmlWriter
     *
     * @var XmlWriter
     */
    protected $xml;
    
    /**
     * The fields to load
     *
     * @var array
     */
    protected $fields = array();
    
    protected $fieldsSerialized = array(
        'properties',
        'misc',
        'identifier',
        'description'
    );

    /**
     * @var boolean isIncremental
     */
    protected $isIncremental = false;

    /**
     * The name of the PAF export
     *
     * @return string
     */
    abstract public function getPafName();

    /**
     * Is an incremental export
     *
     * @return string
     */
    public function getIsIncremental() {
        return $this->isIncremental;
    }

    public function setIsIncremental($bool) {
        $this->isIncremental = $bool;
    }
    /**
     * Init the xml writer
     */
    protected function initXml()
    {
        if($this->xml === null) {
            $this->xml = Mage::helper('Antidot/xmlWriter');
            $debug = Mage::getStoreConfig('antidot/export/debug_xml_enabled');
            $this->xml->init($debug);
        }
    }
    
    /**
     * Extract the exact uri from an url
     * (When executed from cron script magento can generate url whith the script name instead of index.php)
     * (MCNX-253)
     * Remove also SID param
     *
     * @param string $url
     * $param boolean $onlyPath (remove http://domain.com )
     * @return string
     */
    protected function getExactUrl($url, $onlyPath = true)
    {
        $urls = parse_url($url);
        $url = '';
        if (!$onlyPath) {
            //add scheme, host
            $url.=$urls['scheme'].'://';
            $url.=$urls['host'];
        }

        $path = $urls['path'];
        //replace all antidotExport*.php script by index.php in uri (in case of cron export) :
        $path =  preg_replace('#\/antidotExport(.*)\.php#', '/index.php', $path);
        $url .= $path;

        if (isset($urls['query'])) {
            //construct query string without SID
            $query = array();
            parse_str($urls['query'], $query);
            $queryStr = '';
            foreach ($query as $key => $value) {
                if ($key!='SID'){
                    $queryStr .= $key . '=' . $value;
                }
            }
            if ($queryStr) {
                $url .= '?' . $queryStr;
            }
        }
        return $url;
    }
    
    /**
     * Init the fields
     * 
     * @param string $section The section to load
     */
    protected function initFields($section)
    {
        $this->fields = array();
        $values = Mage::getStoreConfig('antidot/fields_'.$section);
        if ($values) {
            foreach ($values as $key => $value) {
                if (in_array($key, $this->fieldsSerialized) && $value = @unserialize($value)) {
                    $values = array_values($value);
                    foreach ($values as $value) {
                        if ($key !== 'properties') {
                            $this->fields[$key][] = $value['value'];
                        } else {
                            $this->fields[$key][] = $value;
                        }
                    }
                    continue;
                }
                $this->fields[$key] = $value;
            }
        }
    }
    
    /**
     * Rertrieve a data from an entity
     * 
     * @param Entity $entity
     * @param string $field
     * @return string
     */
    protected function getField($entity, $field) 
    {
        $field = isset($this->fields[$field]) && !is_array($this->fields[$field]) ? $this->fields[$field] : $field;
        if(empty($field)) {
            return false;
        }
        
        $method = 'get'.ucfirst(strtolower($field));
        
        return $entity->$method();
    }

    /**
     * Write the xml header
     * @param MDN_Antidot_Model_Export_Context $context
     */
    protected function writeHeader($context)
    {
        $this->xml->push('header');
        $this->xml->element('owner', $this->getOwner());
        $this->xml->element('feed', $this->getFeed($context));
        $this->xml->element('generated_at', date('c', Mage::getModel('core/date')->timestamp(time())));
        $this->xml->pop();
    }

    /**
     * Get the value to insert in the owner tag
     * @return string
     */
    public function getOwner() {
        $owner      = 'AFS@Store for Magento v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version;
        if (Mage::getStoreConfig('antidot/general/owner', Mage_Core_Model_App::ADMIN_STORE_ID)) {
            $owner = Mage::getStoreConfig('antidot/general/owner', Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        return $owner;
    }

    /**
     * Get the value to insert in the feed tag
     * @param $type (product, category, article)
     * @param MDN_Antidot_Model_Export_Context $context
     * @return string
     */
    public function getFeed($context) {
        return strtolower($this::TYPE) . ' ' . $context->getRunType() . ' v' . Mage::getConfig()->getNode()->modules->MDN_Antidot->version;
    }

    /**
     *  Free some memory if the memory used is higher than 80% of the
     *  memory limit, and if garbage collection if configured .
     *
     * @return boolean (true if garbage collection has been done, for tests)
     */
    protected function garbageCollection() {

        $gc_enabled = Mage::getStoreConfig('antidot/export/gc_enabled');
        $gc_percentage_limit = Mage::getStoreConfig('antidot/export/gc_percentage_limit');
        if ($gc_enabled) {
            $memoryUsed = memory_get_usage(true);

            $memoryLimit = Mage::helper('Antidot')->returnBytes(ini_get('memory_limit'));
            if ($memoryUsed > ($gc_percentage_limit * $memoryLimit / 100)) {
                gc_collect_cycles();
                Mage::log("Garbage Collection : memory used : $memoryUsed, memory limit : $memoryLimit, memory after gc_collect_cylces : ".memory_get_usage(true), null, 'antidot.log');
                return true;
            }
        }
       return false;
    }

    /**
     *  Log profiler informations :
     *
     * SQL : number of queries, time consumed, average, top slower queries
     *
     * Varien_Profiler most time consuming items
     *
     */
    protected function profile() {

        $profiler = Mage::getStoreConfig('antidot/export/profiler_enable');
        if ($profiler) {

            Mage::log('################### ', null, 'antidot.log');
            Mage::log('#### STATS DB ##### ', null, 'antidot.log');
            Mage::log('################### ', null, 'antidot.log');
            $_profiler = Mage::getSingleton('core/resource')->getConnection('core_read')->getProfiler();
            $_queries = $_profiler->getQueryProfiles();
            uasort($_queries, array('self', 'compareElapsedSecs'));


            Mage::log(
                sprintf(
                    'Executed: %s queries in %s seconds',
                    $_profiler->getTotalNumQueries(),
                    $_profiler->getTotalElapsedSecs()
                ),
                null,
                'antidot.log'
            );
            Mage::log(
                sprintf(
                    'Average query length: %s seconds',
                    $_profiler->getTotalNumQueries() && $_profiler->getTotalElapsedSecs(
                    ) ? $_profiler->getTotalElapsedSecs() / $_profiler->getTotalNumQueries() : 0
                ),
                null,
                'antidot.log'
            );
            Mage::log(
                sprintf(
                    'Queries per second: %s ',
                    ($_profiler->getTotalNumQueries() && $_profiler->getTotalElapsedSecs(
                    ) ? $_profiler->getTotalNumQueries() / $_profiler->getTotalElapsedSecs() : 0)
                ),
                null,
                'antidot.log'
            );

            $profiler_nb_queries = Mage::getStoreConfig('antidot/export/profiler_nb_lowest_queries');
            Mage::log("TOP $profiler_nb_queries lowest queries : ", null, 'antidot.log');
            $i=0;
            foreach ($_queries as $_query) {
                if ($i<$profiler_nb_queries) {
                    Mage::log($_query->getElapsedSecs().' '.$_query->getQuery(), null, 'antidot.log');
                    $i++;
                }
            }

            Mage::log('################### ', null, 'antidot.log');
            Mage::log('#### PROFILER ##### ', null, 'antidot.log');
            Mage::log('###########################################', null, 'antidot.log');
            Mage::log("#    durée      #   nbs  #     emalloc    #", null, 'antidot.log');
            Mage::log('###########################################', null, 'antidot.log');

            $timers = Varien_Profiler::getTimers();
            $totalTime = 0;
            foreach ($timers as $key => $value) {
                $timers[$key]['sum'] = Varien_Profiler::fetch($key,'sum');
                $totalTime += $timers[$key]['sum'];
            }
            uasort($timers, array('self', 'compareTimers'));

            $profiler_varien_quota = Mage::getStoreConfig('antidot/export/profiler_varien_quota');
            foreach ($timers as $timerName => $timerData) {
                if ($timerData['sum'] > $totalTime * $profiler_varien_quota) {
                    Mage::log("# "
                        . str_pad(number_format($timerData['sum'], 6), 12, ' ', STR_PAD_LEFT ) . 's # '
                        . str_pad($timerData['count'], 6, ' ', STR_PAD_LEFT ) . ' # '
                        . str_pad(number_format($timerData['emalloc']), 14, ' ', STR_PAD_LEFT ) . ' # '
                        . $timerName,
                        null,
                        'antidot.log'
                    );
                }
            }


        }

    }

    static public function compareTimers(array $timerA, array $timerB)
    {
        return $timerA['sum'] < $timerB['sum'];
    }

    static public function compareElapsedSecs(array $queryA, array $queryB)
    {
        return $queryA->getElapsedSecs() < $queryB->getElapsedSecs();
    }

}