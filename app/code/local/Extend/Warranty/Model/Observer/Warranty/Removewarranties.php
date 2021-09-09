<?php

class Extend_Warranty_Model_Observer_Warranty_Removewarranties
{
    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        if ($item->getProductType() !== Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            $sku = $item->getSku();

            $quote = Mage::getModel('checkout/cart')->getQuote();
            $items = $quote->getAllItems();

            $removeWarranty = true;
            foreach ($items as $item) {
                if ($item->getSku() === $sku) {
                    $removeWarranty = false;
                    break;
                }
            }

            if ($removeWarranty) {
                foreach ($items as $item) {
                    if ($item->getProductType() === Extend_Warranty_Model_Product_Type::TYPE_CODE
                        && $item->getOptionByCode('associated_product')->getValue() === $sku
                    ) {
                        $quote->removeItem($item->getItemId());
                    }
                }
            }
        }
    }
}
