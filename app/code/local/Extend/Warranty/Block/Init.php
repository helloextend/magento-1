<?php

class Extend_Warranty_Block_Init extends Mage_Catalog_Block_Product_View
{
    /**
     * Returns configurable children as json
     *
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

    public function getBundleConfig()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() != 'bundle') {
            return '';
        }

        $jsonConfig = [];
        $optionsIds = $product->getTypeInstance()->getOptionsIds();
        $selections = $product->getTypeInstance()->getSelectionsCollection($optionsIds);
        foreach ($selections as $selection) {
            $jsonConfig[$selection->getSelectionId()] = $selection->getSku();
        }
        return json_encode($jsonConfig);
    }
}
