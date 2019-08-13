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
class MDN_Antidot_Model_Export_Article extends MDN_Antidot_Model_Export_Product 
{
    
    const TYPE = 'ARTICLE';
    const FILENAME_XML   = 'articles-mdn-fr.xml';
    const FILENAME_ZIP   = '%s_full_mdn_articles.zip';
    const XSD   = 'http://ref.antidot.net/store/latest/articles.xsd';
    
    const imagePrefix = 'media/catalog/article';
    
    const ARTICLE_LIMIT  = 1000;
    
    /**
     * Write the xml file
     * 
     * @param array $context
     * @param string $filename
     */
    public function writeXml($context, $filename) 
    {
        $this->initXml();
        $this->initFields('article');
        $this->setFilename($filename);
        
        
        
        $this->xml->push('articles', array('xmlns' => "http://ref.antidot.net/store/afs#"));
        $this->writeHeader($context);
        $this->writePart($this->xml->flush());
        
        foreach($context['store_id'] as $storeId) {
            $store = Mage::getModel('core/store')->load($storeId);
            $page = 1;
            while($articles = $this->getProducts($store, $page, self::ARTICLE_LIMIT)) {
                foreach($articles as $article) {
                    $this->xml->push('article', array('id' => $article->getId(), 'xml:lang' => $context['lang']));

                    $this->xml->push('websites');
                    $this->xml->element('website', $store->getWebsite()->getName(), array('id' => $store->getWebsite()->getId()));
                    $this->xml->pop();

                    $this->xml->element('created_at', $article->getCreated_at());
                    $this->xml->element('last_updated_at', $article->getUpdated_at());
                    //$this->xml->element('published_at', $article->getPublished_at());

                    $this->xml->element('title', $this->xml->encloseCData($this->getField($article, 'title')));
                    $this->xml->element('subtitle', $this->xml->encloseCData($this->getField($article, 'subtitle')));
                    $this->xml->element('type', $this->xml->encloseCData($this->getField($article, 'type')));
                    $this->xml->element('text', $this->xml->encloseCData($this->getField($article, 'text')));

                    $this->writeDescriptions($article);
                    $this->writeIdentifiers($article);
                    $this->writeClassification($article);
                    //$this->writeBrands($article);

                    $this->writeUrl($article, false);
                    $this->writeMisc($article);

                    $this->xml->pop();
                }
                $page++;

                $this->writePart($this->xml->flush());
            }
        }
        $this->xml->pop();
        
        $this->writePart($this->xml->flush(), true);
    }
    
    /**
     * Write the xml header
     * 
     * @param array $context
     */
    protected function writeHeader($context)
    {
        $this->xml->push('header');
        $this->xml->element('owner', $context['owner']);
        $this->xml->element('feed', 'article');
        $this->xml->element('generated_at', date('c', Mage::getModel('core/date')->timestamp(time())));
        $this->xml->pop();
    }
    
    /**
     * Write the article identifiers
     * 
     * @param Product $article
     */
    protected function writeBrands($article)
    {
        if ($manufacturer = $this->getField($article, 'manufacturer')) {
            $this->xml->push('brands');
            $brandUrl = Mage::getModel('Antidot/Export_Brand')->getUrl($article->getAttributeText('manufacturer'));
            $this->xml->element('brand', $this->xml->encloseCData($article->getAttributeText('manufacturer')), array('id' => $manufacturer, 'url' => $brandUrl));
            $this->xml->pop();
        }
    }
    
    /**
     * Get articles to generate
     * 
     * @param int $store
     * @param int $page
     * @param int $limit
     * @return array
     */
    protected function getArticles($store, $page, $limit) 
    {
        return Mage::getModel('cms/page')
            ->getCollection()
            ->addStoreFilter($store->getId())
            ->addAttributeToSelect('*')
            ->setPage($page, $limit)
        ;
    }
}
