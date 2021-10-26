<?php

class Extend_Warranty_Model_Normalizer
{
    /**
     * @param Mage_Checkout_Model_Cart $cart
     */
    public function normalize(Mage_Checkout_Model_Cart $cart)
    {
        //split cart items from products and warranties
        $warranties = [];
        $products = [];
        foreach ($cart->getItems() as $item) {
            if ($item->getProductType() === Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                $warranties[$item->getItemId()] = $item;
            } else {
                $products[] = $item;
            }
        }

        //Loop products to see if their qty is different from the warranty qty and adjust both to max
        foreach ($products as $item) {
            $sku = $item->getSku();
            $itemQty = $item->getQty();
            foreach ($warranties as $warrantyitem) {
                if (
                    $warrantyitem->getQty() !== $itemQty
                    && $warrantyitem->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue() === $sku
                    && ($item->getProductType() == 'configurable' || is_null($item->getOptionByCode('parent_product_id')))
                ) {
                    if ($itemQty > 0) {
                        //Update Warranty QTY
                        $warrantyitem->setQty($itemQty);
                    } else {
                        //Remove both product and warranty
                        $cart->removeItem($warrantyitem->getItemId());
                        $cart->removeItem($item->getItemId());
                    }
                }
            }
        }
    }
}
