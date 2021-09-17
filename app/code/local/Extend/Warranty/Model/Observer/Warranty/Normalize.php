<?php

class Extend_Warranty_Model_Observer_Warranty_Normalize
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $connectorHelper = Mage::helper('warranty/connector');
        if (!$connectorHelper->isBalancedCart()) {
            return;
        }

        $_cart = $observer->getEvent()->getCart();

        /* Normalize on quote/cart update */
        if (empty($_cart)) {
            $this->_normalize(Mage::getSingleton('checkout/session')->getQuote());
        } else {
            Mage::getModel('warranty/normalizer')->normalize($_cart);
        }
    }

    /**
     * @param $quote
     */
    private function _normalize($quote)
    {
        //split cart items from products and warranties
        $warranties = [];
        $products = [];

        foreach ($quote->getAllItems() as $item) {
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
                    $itemQty > 0
                    && ($warrantyitem->getQty() !== $itemQty)
                    && $warrantyitem->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue() === $sku
                    && ($item->getProductType() == 'configurable' || is_null($item->getOptionByCode('parent_product_id')))
                ) {
                    //Update Warranty QTY
                    $warrantyitem->setQty($item->getQty());
                    $warrantyitem->calcRowTotal();
                    $warrantyitem->save();
                }
            }
        }

        $quote->setTriggerRecollect(1);
        $quote->collectTotals()->save();
    }
}
