<?php

class Extend_Warranty_Adminhtml_Sync_OrdersController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function syncAction()
    {
        try {
            /** @var Extend_Warranty_Model_SyncProcessor_Orders $orderSyncProcessor */
            $orderSyncProcessor = Mage::getModel('warranty/syncProcessor_orders');
            if ($storeId = $this->getCurrentStore()) {
                $orderSyncProcessor->setStoreId($storeId);
            }
            $orderSyncProcessor->getCollection()->addFieldToFilter('historical.was_sent', ['null' => true]);

            $batchSize = $orderSyncProcessor->getCollection()->getPageSize();
            $orderCount = $orderSyncProcessor->getCollection()->getSize();
            $progressBar = $this->getProgressBar($orderCount / $batchSize);

            if ($orderCount) {
                $orderSyncProcessor->setProgressBar($progressBar);
                $orderSyncProcessor->process();
                $progressBar->update(null, 'Data of ' . $orderCount . ' orders were sent to the Extend.');
            } else {
                $progressBar->update(null, 'Production orders have already been integrated to Extend. The historical import has been canceled');
            }
            $progressBar->finish();
            $this->getResponse()->setHttpResponseCode(200);
            Mage::helper('warranty/connector')->setLastSyncDate();
        } catch (\Exception $e) {
            $progressBar = $this->getProgressBar(1);
            $progressBar->finish();
            Mage::getModel('warranty/logger')->critical(['Exception' => $e->getMessage()], 'Error happens on a sync process');
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    public function getProgressBar($batches, $start = 0)
    {
        return new Zend_ProgressBar(
            new Zend_ProgressBar_Adapter_JsPush(array(
                'updateMethodName' => 'orderSyncUpdate',
                'finishMethodName' => 'orderSyncFinish'
            )),
            $start,
            $batches
        );
    }

    protected function getCurrentStore()
    {
        $store_id = false;
        if (strlen($code = Mage::app()->getRequest()->getParam('store'))) // store level
        {
            $store_id = Mage::getModel('core/store')->load($code)->getId();
        }
        return $store_id;
    }
}
