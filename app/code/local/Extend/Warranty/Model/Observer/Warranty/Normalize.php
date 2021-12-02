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
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            Mage::getModel('warranty/normalizer')->normalize($quote);
            $quote->setTriggerRecollect(1);
            $quote->collectTotals()->save();
        } else {
            Mage::getModel('warranty/normalizer')->normalize($_cart);
        }
    }
}
