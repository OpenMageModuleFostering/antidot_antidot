<?php 

class MDN_Antidot_Test_Model_Feed extends EcomDev_PHPUnit_Test_Case
{
  
  
  public function testFeedNotification() {
    $notificationText = 'Antidot Test Item';
    
    $stub = $this->getMockBuilder('MDN_Antidot_Model_Feed')->setMethods(array('getFeedData', 'getLastUpdate'))
    ->getMock();
    $stub->method('getLastUpdate')->willReturn(0);
    $stub->method('getFeedData')->willReturn($this->_getFeed($notificationText));
    
    $res = $stub->checkUpdate();
    $notifications = Mage::getModel('adminnotification/inbox')->getCollection()->getItems();
    $notificationTitles = array_map(function($elem) {
      return $elem->title;
    }, $notifications);
    
    $this->assertContains($notificationText, $notificationTitles);
  }
  
  private function _getFeed($notification) {
    $data = '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link
            href="http://ref.antidot.net/store/magento/notifications.rss"
            rel="self" type="application/rss+xml"/>
        <title>MagentoCommerce</title>
        <link>http://www.magentocommerce.com/</link>
        <description>MagentoCommerce</description>
        <copyright>Copyright (c) 2015 Antidot</copyright>
        <webMaster>magento@antidot.net (Magento support)</webMaster>
        <language>en</language>
        <lastBuildDate>Thu, 22 Jan 2015 17:47:27 UTC</lastBuildDate>
        <ttl>300</ttl>
        <item>
            <title><![CDATA['.$notification.']]></title>
            <link><![CDATA['.$notification.']]></link>
            <severity>4</severity>
            <description><![CDATA['.$notification.']]></description>
            <pubDate>Thu, 22 Jan 2015 17:47:27 UTC</pubDate>
        </item>
    </channel>
</rss>';
    $xml  = new SimpleXMLElement($data);
    return $xml;  
  }
}
