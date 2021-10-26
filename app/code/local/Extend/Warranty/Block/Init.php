<?php

class Extend_Warranty_Block_Init extends Mage_Catalog_Block_Product_View
{
    /**
     * @return false|string
     */
    public function getChildProductsSku()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $result = array();
            foreach ($childProducts as $childProduct) {
                $result[$childProduct->getId()] = $childProduct->getSku();
            }

            return json_encode($result);
        }

        return '';
    }
}
