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
class MDN_Antidot_Model_Export_Article extends MDN_Antidot_Model_Export_Product
{
    
    const TYPE = 'ARTICLE';
    const FILE_PATH_CONF = 'articles';
    const FILENAME_XML   = 'articles-%s-%s-%s.xml';
    const FILENAME_ZIP   = '%s_full_%s_articles.zip';
    const XSD   = 'http://ref.antidot.net/store/latest/articles.xsd';
    
    const imagePrefix = 'media/catalog/article';
    
    /**
     * {@inherit}
     */
    public function getPafName() {
        return "Articles";
    }

    /**
     * Write the xml file
     * 
     * @param array $context
     * @param string $filename
     * @param string $type Incremantal or full
     */
    public function writeXml($context, $filename, $type)
    {

        if (count($context->getStoreIds()) == 0) {
            return 0;
        }

        Mage::log('Starting article XML export, filename = '.$filename, null, 'antidot.log');
        $articlesExported = 0;

        $pageResource = Mage::getResourceModel('cms/page');
        $articles = Mage::getModel('cms/page')
            ->getCollection()
            ->addStoreFilter($context->getStoreIds())
            ->addFieldToFilter('identifier', array('nin' => array('home', 'no-route')))
            ->addFieldToFilter('is_active', 1)
            ->addFieldToSelect('*')
            ;

        $chunkSize = 100;
        $articles->setPageSize($chunkSize);

        $articlesCount = $articles->getSize();
        Mage::log('Articles to export : '.$articlesCount, null, 'antidot.log');
        $chunkCount = $articles->getLastPageNumber();

        if ($articlesCount > 0) {

            $this->initXml();
            $this->initFields('article');
            $this->setFilename($filename);

            $this->xml->push('articles', array('xmlns' => "http://ref.antidot.net/store/afs#"));
            $this->writeHeader($context);
            $this->writePart($this->xml->flush());

            $lastExecutionTime = time();
            Mage::log(
                'Process chunk # 0 / '.$chunkCount.' - memory usage = '.memory_get_usage(),
                null,
                'antidot.log'
            );
            for ($chunkId = 1; $chunkId <= $chunkCount; $chunkId++) {
                $articles->setCurPage($chunkId);


                foreach ($articles as $article) {

                    //Get stores affected on the page :
                    $storeIds = $pageResource->lookupStoreIds($article->getId());
                    $websites = array();
                    //get the corresponding websites
                    foreach ($storeIds as $storeId) {
                        if ($storeId == 0) {
                            $websites = $context->getWebsites();
                        } else {
                            $website = $context->getWebSiteByStore($storeId);
                            if ($website) { //ad dthe website only if the store of the page is in the export context
                                $websites[$website->getId()] = $website;
                            }
                        }
                    }

                    //Write 1 article by website
                    foreach ($websites as $website) {

                        $this->xml->push(
                            'article',
                            array('id' => $article->getId().'_'.$website->getId(), 'xml:lang' => $context->getLang())
                        );

                        //write websites
                        $this->xml->push('websites');
                        $this->xml->element(
                            'website',
                            $website->getName(),
                            array('id' => $website->getId())
                        );
                        $this->xml->pop();

                        //$this->xml->element('created_at', $article->getCreationTime());
                        //$this->xml->element('last_updated_at', $article->getUpdateTime());
                        $this->xml->push('identifiers');
                        $this->xml->element(
                            'identifier',
                            $this->xml->encloseCData($this->getField($article, 'identifier')),
                            array('type' => 'identifier')
                        );
                        $this->xml->pop();

                        $this->xml->element('title', $this->xml->encloseCData($this->getField($article, 'title')));
                        $this->xml->element(
                            'subtitle',
                            $this->xml->encloseCData($this->getField($article, 'content_heading'))
                        );

                        //remove html tags, and script tags, can cause xml validation failure
                        $content = $this->getField($article, 'content');
                        $content = html_entity_decode(strip_tags( str_replace( '<', ' <',$content )));
                        $this->xml->element('text', $this->xml->encloseCData($content));

                        $urlStoreId = $website->getDefaultStore()->getId();
                        if (!in_array($urlStoreId, $storeIds) && count($storeIds) && $storeIds[0]!=0) {
                            $urlStoreId=$storeIds[0];
                        }
                        $this->writeUrl($article, $urlStoreId);

                        $articlesExported++;

                        $this->xml->pop();
                    }
                }
                $articles->clear();

                Mage::log(
                    'Process chunk # '.$chunkId.' / '.$chunkCount.' - memory usage = '.memory_get_usage(
                    ).' - took '.(time() - $lastExecutionTime).' sec',
                    null,
                    'antidot.log'
                );
                $lastExecutionTime = time();


                $this->writePart($this->xml->flush());
            }

            $this->xml->pop();

            $this->writePart($this->xml->flush(), true);

        }

        return $articlesExported;
    }
    
    /**
     * Write the product urls
     *
     * @param Mage_Cms_Model_Page $article
     * @param int $storeId
     */
    protected function writeUrl($article, $storeId)
    {
        $this->xml->element('url', $this->xml->encloseCData(Mage::getUrl($article->getIdentifier(), array(
            '_store' => $storeId,
            '_nosid' => true
        ))));
    }

}
