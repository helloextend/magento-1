<?php

class Extend_Warranty_Helper_Contract extends Mage_Core_Helper_Abstract
{

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getOrderItemContractIds($orderItem)
    {
        $contractIdsJson = $orderItem->getContractId();
        $contractIds = $contractIdsJson ? json_decode($contractIdsJson, 1) : [];
        return $contractIds;
    }
}
