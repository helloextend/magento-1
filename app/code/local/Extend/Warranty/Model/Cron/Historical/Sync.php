<?php

class Extend_Warranty_Model_Cron_Historical_Sync
{
    public function execute()
    {
        if (!Mage::helper('warranty/connector')->getHistoricalOrdersSyncEnabled()) {
            return;
        }

        /** @var Extend_Warranty_Model_SyncProcessor_Orders $orderSyncProcessor */
        $orderSyncProcessor = Mage::getModel('warranty/syncProcessor_orders');
        $orderSyncProcessor->process();
    }

}