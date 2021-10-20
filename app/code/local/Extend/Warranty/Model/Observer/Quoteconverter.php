<?php

class Extend_Warranty_Model_Observer_Quoteconverter
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getItem();
        $product = $quoteItem->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId === Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            $attributes = $product->getTypeInstance()->getWarrantyInfo($product);
            $orderItem = $observer->getOrderItem();
            $options = $orderItem->getProductOptions();
            $options['additional_options'] = $attributes;
            if($orderItem->getProduct()->getCustomOption('info_buyRequest')){
                $options['info_buyRequest'] = unserialize($orderItem->getProduct()->getCustomOption('info_buyRequest')->getValue());
            }
            $orderItem->setProductOptions($options);
        }
    }
}
