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

//with modman we use symlinks and we need realpath
$basepath = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
require_once $basepath.'abstract.php';

class MDN_Shell_AntidotExportProduct extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $start = time();

        Mage::getModel('Antidot/Observer')->catalogFullExport('cli');

        return true;
    }
}

$shell = new MDN_Shell_AntidotExportProduct();
$shell->run();
