<?php

class Extend_Warranty_Model_Observer_Warranty_Normalize
{
    /**
     * @inheritDoc
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if(!$this->helper->isBalancedCart()){
            return;
        }

        $_cart = $observer->getEvent()->getCart();

        /* Normalize on quote/cart update */
        if (empty($_cart)) {
            $this->_normalize($this->checkoutSession->getQuote());
        } else {
            $this->normalizer->normalize($_cart);
        }
    }

    private function _normalize($quote) {

        //split cart items from products and warranties
        $warranties = [];
        $products = [];

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() === 'warranty') {
                $warranties[$item->getItemId()] = $item;
            } else {
                $products[] = $item;
            }
        }

        //Loop products to see if their qty is different from the warranty qty and adjust both to max
        foreach ($products as $item) {
            $sku = $item->getSku();

            foreach ($warranties as $warrantyitem) {
                if ($warrantyitem->getOptionByCode('associated_product')->getValue() == $sku &&
                    ($item->getProductType() == 'configurable' || is_null($item->getOptionByCode('parent_product_id')))) {
                    if ($warrantyitem->getQty() <> $item->getQty()) {
                        if ($item->getQty() > 0) {
                            //Update Warranty QTY
                            $warrantyitem->setQty($item->getQty());
                            $warrantyitem->calcRowTotal();
                            $warrantyitem->save();
                        }
                    }
                }
            }
        }
        $quote->setTriggerRecollect(1);
        $quote->collectTotals()->save();
    }
}
