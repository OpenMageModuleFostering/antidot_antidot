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
class MDN_Antidot_Helper_LogExport extends Mage_Core_Helper_Abstract 
{
    
    /**
     * Add a row to antidot_export
     * 
     * @param string $uid Reference to 
     * @param string $type FULL|INC
     * @param string $element CATALOG|CATEGORY
     * @param string $begin
     * @param string $end
     * @param int $items
     */
    public function add($reference, $type, $element, $begin, $end, $items, $status, $error = '')
    {
        $error = str_replace("'", "", $error);
        if (strlen($error) > 254)
            $error = substr ($error, 0, 254);

        $query = "INSERT INTO antidot_export(reference, type, element, begin_at, end_at, items_processed, status, error) "
               . "VALUES('".$reference."', '".$type."', '".$element."', '".date('Y-m-d H:i:s', $begin)."', '".date('Y-m-d H:i:s', $end)."', ".(int)$items.", '".$status."', '".$error."')";

        Mage::getSingleton('core/resource')->getConnection('core_write')->query($query);
    }
    
    /**
     * Return the last export
     * 
     * @param string $element
     * @return array
     */
    public function getLastGeneration($element)
    {
        $query = "SELECT begin_at "
                . "FROM antidot_export "
                . "WHERE element = '".$element."' "
                . "ORDER BY begin_at DESC "
                . "LIMIT 1";

        return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($query);
    }
    
    /**
     * Return the last export
     * 
     * @param int Since x hours
     * @return array
     */
    public function getAllLastGeneration($sinceHour = 24)
    {
        $since = date('Y-m-d H:i:s', time()-(int)$sinceHour*60*60);
        $query = "SELECT reference, type, element, begin_at, items_processed, status, error "
                . "FROM antidot_export "
                . "WHERE begin_at > '".$since."' "
                . "ORDER BY begin_at DESC";

        return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
    }
}
