<?php

class Extend_Warranty_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $price
     * @return float|int
     */
    public function formatPrice($price)
    {
        if (empty($price)) {
            return 0;
        }

        $floatPrice = (float)$price;
        $formattedPrice = number_format(
            $floatPrice,
            2,
            '',
            ''
        );

        return (float)$formattedPrice;
    }

    /**
     * @param $price
     * @return float
     */
    public function removeFormatPrice($price)
    {
        $price = (string)$price;
        $price = substr_replace(
            $price,
            '.',
            strlen($price) - 2,
            0
        );

        return (float)$price;
    }

    /**
     * @return mixed
     */
    public function getWarrantyProduct()
    {
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', array('eq' => 'warranty'));

        return $productCollection->getFirstItem();
    }


    /**
     * @param $product
     * @return mixed
     */
    public function getComplexProductSku($product)
    {
        $product = clone $product;
        $product->setData('sku_type', 0);
        return $product->getSku();
    }

    /**
     * @param $quoteItem
     * @return mixed
     */
    public function getComplexQuoteItemSku($quoteItem)
    {
        return $this->getComplexProductSku($quoteItem->getProduct());
    }

    /**
     * @param $orderItem
     * @return mixed
     */
    public function getComplexOrderItemSku($orderItem)
    {
        if ($orderItem->getProduct()->getTypeId() != 'bundle') {
            return $orderItem->getSku();
        }

        $product = $orderItem->getProduct();

        $saleableCheckBuffer = Mage::helper('catalog/product')->getSkipSaleableCheck();
        Mage::helper('catalog/product')->setSkipSaleableCheck(true);

        $product->getTypeInstance()->processConfiguration($orderItem->getBuyRequest(), $product);
        $complexSku = $this->getComplexProductSku($product);

        Mage::helper('catalog/product')->setSkipSaleableCheck($saleableCheckBuffer);
        return $complexSku;
    }
}
