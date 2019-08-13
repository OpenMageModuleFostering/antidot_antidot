<?php


class MDN_Antidot_Test_Model_Export_Model_Product extends EcomDev_PHPUnit_Test_Case
{

    /**
     * MCNX-264
     * in fixture config cataloginventory/item_options/manage_stock : 0  meens  the product is available
     * even if is_in_stock data from database is false
     *
     * @test
     * @loadFixture
     */
    public function testGetIsInStock() {

        $product = Mage::getModel('Antidot/export_model_product');
        $product->setData('is_in_stock', 0);

        $this->assertTrue($product->getIsInStock());
    }
}
