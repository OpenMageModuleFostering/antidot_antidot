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
class MDN_Antidot_Model_Observer extends Mage_Core_Model_Abstract
{
    const GENERATE_FULL = 'FULL';
    const GENERATE_INC = 'INC';
    const MINIMUM_MEMORY_LIMIT = '2048M';

    /**
     * @var string @tmpDirectory
     */
    private $tmpDirectory;
    private $tmpErrorDirectory;
    
    /**
     * @var string Uniq request Id
     */
    private $request;
    
    /**
     * @var int Begin timestamp
     */
    private $begin;
    
    /**
     * @var string Current generation type
     */
    private $type;
    
    /**
     * Init the controller
     */
    protected function _construct()
    {
        $this->request      = uniqid();
        $this->begin        = microtime(true);
        $this->initTmpDirectory();

        /**
         * If the default memory limit is below 2048M set it to 2048M
         * else if it is above 2048M let it as it is
         *
         * @var $antidotHelper MDN_Antidot_Helper_Data
         */
        $antidotHelper = Mage::helper('Antidot');
        $memoryLimit = Mage::getStoreConfig('antidot/export/memory_limit');
        if (!$memoryLimit) {
            $memoryLimit = self::MINIMUM_MEMORY_LIMIT;
        }
	    if ($antidotHelper->returnBytes(ini_get('memory_limit')) < $antidotHelper->returnBytes($memoryLimit)) {
			ini_set('memory_limit', $memoryLimit);
		}

    }

    /**
     * Init the tmp directory
     */
    protected function initTmpDirectory()
    {
        $baseDirectory = sys_get_temp_dir().DS;

        if (Mage::getStoreConfig('antidot/ftp/working_directory'))
            $baseDirectory = Mage::getStoreConfig('antidot/ftp/working_directory').DS;

        $this->tmpDirectory = $baseDirectory.'antidot'.DS;
        Mage::log('Working directory is '.$this->tmpDirectory, null, 'antidot.log');

        $this->tmpErrorDirectory = $this->tmpDirectory.'error'.DS;
        Mage::log('Error directory is '.$this->tmpErrorDirectory, null, 'antidot.log');

        if(!is_dir($this->tmpDirectory)) {
            $r1 = mkdir($this->tmpDirectory, 0775, true);
            $r2 = mkdir($this->tmpErrorDirectory, 0775, true);
            Mage::log('Directories do not exist, create them : '.(($r1 && $r2) ? ' OK' : ' NOK '), null, 'antidot.log');
        }
    }
    
    /**
     * Generate the full catalog file
     */
    public function catalogFullExport($runContext)
    {
        $this->log('FULL EXPORT');
        return $this->generate(Mage::getModel('Antidot/export_product'), self::GENERATE_FULL, $runContext);
    }
    
    /**
     * Generate the  inc catalog file
     */
    public function catalogIncExport($runContext)
    {
        return $this->generate(Mage::getModel('Antidot/export_product'), self::GENERATE_INC, $runContext);
    }
    
    /**
     * Generate the category file
     */
    public function categoriesFullExport($runContext)
    {
        return $this->generate(Mage::getModel('Antidot/export_category'), self::GENERATE_FULL, $runContext);
    }
    
    /**
     * Generate files
     * 
     * @param Antidot/Export_* $exportModel
     * @param string $type
     * @throws Exception
     */
    protected function generate($exportModel, $type, $runContext)
    {
        if ($runContext instanceof Mage_Cron_Model_Schedule) {
            $runContext = 'cron';
        }

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->type = $exportModel::TYPE;
        $this->log('start');
        $log['begin'] = time();
        $log['items'] = 0;
        $log['error'] = array();
        $log['reference'] = '';
        
        try
        {
            $owner = Mage::getStoreConfig('antidot/general/owner', Mage_Core_Model_App::ADMIN_STORE_ID);
            $ownerForFilename = $this->getOwnerForFilename($owner);
        	$files = array();
            foreach($this->getDefaultContext($runContext) as $context) {

                $this->log('generate '.$exportModel::TYPE.' '.$exportModel->getOwner());

                $filename = $this->tmpDirectory.sprintf($exportModel::FILENAME_XML, $ownerForFilename, $type, $context->getLang());
                $items    = $exportModel->writeXml($context, $filename, $type);
                if($items === 0) {
                    $this->log('No items to export');
                    continue;
                }

                $log['items']+= $items;
                $validationErrors = $this->schemaValidate($filename, $exportModel::XSD);
                if (count($validationErrors)==0) {
                    $files[] = $filename;
                } else {

                    $this->fileError($filename);

                    foreach ($validationErrors as $error) {
                        $log['error'][] = $error;
                    }
                    continue;

                }
            }

            if($log['items'] === 0) {
                $this->log('No items available, cancel export');
                return 0;
            }

            $log['reference'] = 'unknown';
            if(!empty($files)) {
                $this->log('Compress files');
                $filenameZip = $type === self::GENERATE_INC ? $exportModel::FILENAME_ZIP_INC : $exportModel::FILENAME_ZIP;
                $filenameZip = sprintf($filenameZip, date('YmdHis'), $ownerForFilename);
                $filename = $this->compress($files, $filenameZip);
                $log['reference'] = md5($filename);
                $this->send($filename, $exportModel);

                $log['status'] = 'SUCCESS';
            } else {
                $log['status'] = 'FAILED';
                $lastError = current(Mage::helper('Antidot/xmlWriter')->getErrors());
                if ($lastError) {
                    $log['error'][] = $lastError;
                } else {
                    $log['error'][] = 'No file to export';
                }
            }

            if(file_exists($filename)) {
                $this->log('Unlink file');
                unlink($filename);
            }
        }
        catch(Exception $ex)
        {
            Mage::log($ex->__toString(), Zend_Log::ERR, 'antidot.log');
            $log['error'][] = $ex->getMessage();
            $log['status'] = 'FAILED';
        }
        
        $log['end'] = time();
        $this->log('generate '.$exportModel::TYPE.' '.$exportModel->getOwner());
        $this->log('end');

        Mage::helper('Antidot/logExport')->add($log['reference'], $type, $exportModel::TYPE, $log['begin'], $log['end'], $log['items'], $log['status'], implode(',', $log['error']));

        if ( count($log['error']) ) {
            //send error alert mail
            Mage::helper('Antidot')->sendMail('Export failed', print_r($log['error'], true));
            //throw Exception in order to dispay error message in UI
            Mage::throwException(implode(',', $log['error']));
        }

        return $log['items'];
    }

    /**
     * Move file with error to another directory $tmp/antidot/error
     *
     * @param string $file
     */
    protected function fileError($file)
    {
        $files = array();
        if ($handle = opendir($this->tmpErrorDirectory)) {
            while ($fileError = readdir($handle)) {
                if ($fileError != "." && $fileError != "..") {
                    $files[] = $fileError;
                }
            }
            closedir($handle);

            if(count($files) >= 5) {
                sort($files);
                unlink($this->tmpErrorDirectory.current($files));
            }

            $fileError  = $this->tmpErrorDirectory.time().'-'.basename($file);
            rename($file, $fileError);
        }
    }
   
   /**
    * Compress xml file
    * 
    * @param array $files
    * @param string $compressFile filename
    * @return path to file compressed
    */
   protected function compress($files, $compressFile)
    {
        $this->log('compress the file');
       
        $compressFile = dirname(current($files)).'/'.$compressFile;
        Mage::helper('Antidot/compress')->zip($files, $compressFile);
       
        return $compressFile;
   }
   
   /**
    * Send the file to antidot
    * 
    * @param string $filename
    * @return boolean
    */
   protected function send($filename, $exportModel)
    {
       $this->log('send the file');
       
       $transport = Mage::getModel('Antidot/transport');
       
       return $transport->send($filename, Mage::getStoreConfig('antidot/ftp/upload_type'), $exportModel);
   }
   
   /**
    * Check if the xml file is valid
    * Returns array of error message, empty if schema is valid
    * 
    * @param string $filename xml file
    * @param string $xsd xsd file
    * @return array
    */
   protected function schemaValidate($filename, $xsd)
    {
        $errors = array();

        //disable schema validation
        if (Mage::getStoreConfig('antidot/export/xsd_validation_disable') == 1) {
            $this->log('schema validation is DISABLED due to configuration');
        } elseif (!class_exists('DOMDocument')) {
            $this->log('schema validation is DISABLED due to lack of libxml DOMDocument Class');
        } else {

            libxml_use_internal_errors(true);
            $this->log('schema validate');

            $xml = new DOMDocument();
            $xml->load($filename);

            try {

                if ($xml->schemaValidate($xsd)) {
                    $this->log('Schema validated');
                } else {

                    $errors = Mage::helper('Antidot/xmlWriter')->getErrors();

                    $match = array();
                    if (preg_match('#Warning 1549: failed to load external entity "(.*)\.xsd"#', $errors[0], $match)) {
                        $errors = array();
                        //In case XSD is not reacheable, disable the schema validation
                        $this->log('failed to load external entity '.$match[1].'.xsd, schema validation is DISABLED');
                        $this->log('Schema validated');
                    }
                }

            } catch (Exception $e) {
                $match = array();
                if (preg_match(
                    "#Warning: DOMDocument::schemaValidate\(http://(.*)\.xsd\): failed to open stream:#",
                    $e->getMessage(),
                    $match
                )) {
                    //In case xsd is not reacheable, disable the schema validation
                    $this->log('http://'.$match[1].'.xsd is not reacheable, schema validation is DISABLED');
                } else {
                    throw $e;
                }
            }
        }
        return $errors;

   }
   
   /**
    * Return the context default values
    *
    * @return array
    */
    private function getDefaultContext($runContext)
    {

        $listContext = array();
        foreach (Mage::app()->getStores() as $store) {
            if ($store->getIsActive()) {
	            list($lang) = explode('_', Mage::getStoreConfig('general/locale/code', $store->getId()));
                /* @var $context \MDN_Antidot_Model_Export_Context */
                if (isset($listContext[$lang])) {
                    $context = $listContext[$lang];
                } else {
                    $context = Mage::getModel('Antidot/export_context', array($lang, $runContext));
                    $listContext[$lang] = $context;
                }
                $context->addStore($store);
            }
        }
        foreach ($listContext as $context) {
            $context->initCategoryTree();
        }

        return $listContext;

   }
   
    /**
     * Write message to log
     * 
     * @param string $actiong
     */
    private function log($action)
    {
        $message = '[antidot] ['.$this->type.'] ['.$this->request.'] '
                 .  memory_get_usage(true).' '
                 . $action.' ('.round(microtime(true)-$this->begin, 2)."sec)";

        Mage::log($message, null, 'antidot.log');
    }
    
    /**
     * MCNX-27 Return the Owner defined in the AFSStore Config Area
     * formated in order tu be used in the export filename
     * 
     * @return string
     */
    private function getOwnerForFilename($owner = null) {
    	 
    	$filename = 'magento';
    	if ($owner) {
    
    		/// convert non-alphanumeric characters
    		// - see the rules below on the page
    		$owner = Mage::helper('catalog/product_url')->format($owner);
    
    		// replace remaining non-alphanumeric characters
    		// with dashes
    		$owner = preg_replace('#[^0-9a-z]+#i', '_', $owner);
    
    		// make it lowercase
    		$owner = strtolower($owner);
    
    		// trim dashes on the left and right
    		$owner = trim($owner, '_');

    		$filename .= '_'.$owner;
    	}

    	return $filename;
    }
}
