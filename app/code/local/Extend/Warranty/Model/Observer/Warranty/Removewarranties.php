<?php

class Extend_Warranty_Model_Observer_Warranty_Removewarranties
{
    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Extend_Warranty_Helper_Data $warrantyHelper */
        $warrantyHelper = Mage::helper('warranty');

        $removeQuoteItem = $observer->getEvent()->getQuoteItem();
        if ($removeQuoteItem->getProductType() !== Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            $sku = $removeQuoteItem->getSku();
            $removeComplexSku = $warrantyHelper->getComplexQuoteItemSku($removeQuoteItem);

            $quote = Mage::getModel('checkout/cart')->getQuote();
            $items = $quote->getAllItems();

            $removeWarranty = true;
            foreach ($items as $item) {

                if ($item->getProductType() !== Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                    continue;
                }

                if ($warrantyHelper->getComplexQuoteItemSku($item) === $removeComplexSku) {
                    $removeWarranty = false;
                    break;
                }

                if ($item->getSku() === $sku) {
                    $removeWarranty = false;
                    break;
                }
            }

            if ($removeWarranty) {
                foreach ($items as $item) {
                    if ($item->getProductType() !== Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                        continue;
                    }

                    $associatedSku = [$item->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue()];

                    if ($item->getOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU)) {
                        $associatedSku[] = $item->getOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU)->getValue();
                    }

                    if ($item->getOptionByCode(Extend_Warranty_Model_Product_Type::RELATED_ITEM_ID)) {
                        $relatedItemId = $item->getOptionByCode(Extend_Warranty_Model_Product_Type::RELATED_ITEM_ID)->getValue();
                        if ($removeQuoteItem->getId() == $relatedItemId) {
                            $quote->removeItem($item->getItemId());
                        }
                    }

                    if (in_array($removeComplexSku, $associatedSku)) {
                        $quote->removeItem($item->getItemId());
                    }
                }
            }
        }
    }
}
