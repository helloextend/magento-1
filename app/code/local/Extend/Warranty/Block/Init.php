<?php

class Extend_Warranty_Block_Init extends Mage_Catalog_Block_Product_View
{
    const DEMO = 'demo';
    const LIVE = 'live';

    /**
     * @return false|string
     */
    public function getJsonConfig()
    {
        $data = [
            'storeId'     => Mage::helper('warranty/connector')->getApiStoreId(),
            'environment' => Mage::helper('warranty/connector')->isLiveMode() ? self::LIVE : self::DEMO,
        ];

        return json_encode($data);
    }

    public function getChildProductsSku()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $result = array();
            foreach ($childProducts as $childProduct) {
                $result[$childProduct->getId()] = $childProduct->getSku();
            }

            return json_encode($result);
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function isExtendEnabled()
    {
        return Mage::helper('warranty/connector')->isExtendEnabled();
    }
}
