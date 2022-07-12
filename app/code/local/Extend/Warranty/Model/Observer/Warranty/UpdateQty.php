<?php

class Extend_Warranty_Model_Observer_Warranty_UpdateQty
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Item $item */
        $item = $observer->getItem();
        if ($item->getProductType() == Extend_Warranty_Model_Product_Type::TYPE_CODE
            && $item->getId()
            && $item->getBuyRequest()->getLeadQty()
            && $item->getBuyRequest()->getLeadQty() < $item->getData('qty')
            && $item->getOptionByCode('lead_token')
            && $item->getOptionByCode('lead_token')->getValue()
        ) {
            $item->setUseOldQty(true);
            $item->setHasError(true);
            $item->addErrorInfo(
                'warranty',
                Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                Mage::helper('warranty')->__('This warranty qty can\'t be more then warrantable product qty: %s', $item->getBuyRequest()->getLeadQty())
            );
        }
    }
}
