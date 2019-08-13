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
class MDN_Antidot_Helper_XmlWriter extends Mage_Core_Helper_Abstract 
{

    protected $xml;
    protected $indent = "";
    protected $cr = "";
    protected $stack = array();

    /**
     * Init the xml string
     * 
     * @param string $indent
     */
    public function init($debug = false) 
    {
    	if ($debug) {
    		$this->indent= "  ";
    		$this->cr= "\n";
    	}

        $this->xml = '<?xml version="1.0" encoding="utf-8"?>' . $this->cr;
    }

    /**
     * Added an indent to xml
     */
    protected function indent() 
    {
        for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
            $this->xml.= $this->indent;
        }
    }

    /**
     * Open a new element
     * 
     * @param string $element
     * @param array $attributes
     */
    public function push($element, $attributes = array()) 
    {
        $this->indent();
        $this->xml.= '<' . $element;
        foreach ($this->cleanAttributes($attributes) as $key => $value) {
            if ($value !== '') {
                $this->xml.= ' ' . $key . '="' . $value . '"';
            }
        }
        $this->xml.= ">".$this->cr;
        $this->stack[] = $element;
    }

    /**
     * Add a new element
     * 
     * @param string $element
     * @param string $content
     * @param array $attributes
     */
    public function element($element, $content, $attributes = array()) 
    {
        $this->indent();
        $this->xml.= '<' . $element;
        foreach ($this->cleanAttributes($attributes) as $key => $value) {
            if ($value !== '') {
                $this->xml.= ' ' . $key . '="' . $value . '"';
            }
        }
        
        $content = Mage::helper('Antidot/url')->isUtf8($content) === false ? mb_convert_encoding($content, "UTF-8") : $content;
        $this->xml.= '>' . ($content) . '</' . $element . '>' . $this->cr;
    }

    /**
     * Add a new empty element
     * 
     * @param string $element
     * @param array $attributes
     */
    public function emptyelement($element, $attributes = array()) 
    {
        $this->indent();
        $this->xml.= '<' . $element;
        foreach ($this->cleanAttributes($attributes) as $key => $value) {
            if ($value !== '') {
                $this->xml.= ' ' . $key . '="' . $value . '"';
            }
        }
        $this->xml.= " />".$this->cr;
    }
    
    /**
     * Clean attributes
     * 
     * @param array $attributes
     * @return array
     */
    protected function cleanAttributes($attributes) 
    {
        foreach ($attributes as &$value) {
            $value = htmlspecialchars($value);
        }
        
        return $attributes;
    }

    /**
     * Close an element
     */
    public function pop() 
    {
        $element = array_pop($this->stack);
        $this->indent();
        $this->xml.= "</$element>".$this->cr;
    }

    /**
     * Return the xml
     * 
     * @return string
     */
    public function getXml() 
    {
        return $this->xml;
    }
    
    /**
     * Return the xml and set to empty
     * 
     * @return string
     */
    public function flush() 
    {
        $content = $this->xml;
        $this->xml = '';
        
        return $content;
    }

    /**
     * Add an enclose CData
     * 
     * @param string $value
     * @return string
     */
    public function encloseCData($value)
    {
        return '<![CDATA['.$value.']]>';
    }
    
    /**
     * Return last errors generated
     * 
     * @return array
     */
    public function getErrors() 
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = $this->getError($error);
        }
        libxml_clear_errors();
        
        return $errors;
    }

    /**
     * Return an error
     * 
     * @param XmlError $error
     * @return string
     */
    protected function getError($error)
    {
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return = "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return = "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return = "Fatal Error $error->code: ";
                break;
        }
        
        $return.= trim($error->message);
        if ($error->file) {
            $return.= " in $error->file";
        }
        $return.= " on line <b>$error->line";

        return $return;
    }
}
