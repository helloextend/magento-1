<?php

class Extend_Warranty_Helper_Product_Configuration extends Mage_Catalog_Helper_Product_Configuration
{
    /**
     * Retrieves product configuration options
     *
     * @param Mage_Catalog_Model_Product_Configuration_Item_Interface $item
     * @return array
     */
    public function getCustomOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId === Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            $attributes = $product->getTypeInstance()->getWarrantyInfo($product);
            return array_merge($attributes, parent::getCustomOptions($item));
        }

        return parent::getCustomOptions($item);
    }
}
