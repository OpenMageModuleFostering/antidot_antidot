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
class MDN_Antidot_Helper_Url extends Mage_Core_Helper_Abstract 
{
    
    /**
     * Urlize
     *
     * @param string $string
     * @return string
     */
    public function url($string, $separator = '-')
    {
        $string = self::removeAccent($string);
        $string = preg_replace('#([^a-zA-Z0-9])#', $separator, $string);
        $string = preg_replace('#\\'.preg_quote($separator).'{2,}#', $separator, $string);
        $string = preg_replace('#(^'.preg_quote($separator).')|('.preg_quote($separator).'$)#', '', $string);

        return strtolower($string);
    }

    /**
     * Check if the string is utf8
     *
     * @see http://fr2.php.net/manual/fr/function.mb-detect-encoding.php#68607
     * @param string $string
     * @return bool
     */
    public function isUtf8($string)
    {
        return preg_match(
            '%(?:
            [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
            |\xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
            |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
            |\xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
            |\xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
            |[\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
            |\xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
            )+%xs',
            $string
        ) === 1;
    }

    /**
     * Remove accents from the string
     *
     * @param string $string
     * @return string
     */
    protected function removeAccent($string)
    {
        if (!self::isUtf8($string)) {
            $string = utf8_encode($string);
        }

        $string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');

        return preg_replace('#&([a-zA-Z])[a-zA-Z]+;#', '$1', $string);
    }
}