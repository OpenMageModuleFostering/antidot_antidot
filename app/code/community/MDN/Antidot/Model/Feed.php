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

class MDN_Antidot_Model_Feed extends Mage_AdminNotification_Model_Feed {
  
  /**
   * Return the rss feed to check for latest notifications
   * @return string RssFeedUrl
   */
  public function getFeedUrl() {
    if (is_null($this->_feedUrl)) {
      $this->_feedUrl = 'http://ref.antidot.net/store/magento/notifications.rss';
    }
    return $this->_feedUrl;
  }
  
  public function observe() {
    $this->checkUpdate();
  }
  
  /**
   * Set the time of the last rss feed check 
   */
  public function setLastUpdate() {
    Mage::app()->saveCache(time(), 'antidot_notifications_lastcheck');
    return $this;
  }
  
  /**
   * Get the time of the last rss feed check 
   * @return int LastCheckTimestamp
   */
  public function getLastUpdate() {
    return Mage::app()->loadCache('antidot_notifications_lastcheck');
  }
}
