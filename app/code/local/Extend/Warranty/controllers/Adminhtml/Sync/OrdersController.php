<?php

class Extend_Warranty_Adminhtml_Sync_OrdersController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function syncAction()
    {
        $data = array();
        try {
            $batchSize = Mage::helper('warranty/connector')->getBatchSize();


            /** @var Extend_Warranty_Model_SyncProcessor_Orders $orderSyncProcessor */
            $orderSyncProcessor = Mage::getModel('warranty/sync_processor_orders');

            $orderSyncProcessor->process();
            $data['status'] = 'SUCCESS';

            $this->getResponse()->setHttpResponseCode(200);
            Mage::helper('warranty/connector')->setLastSyncDate();
        } catch (\Exception $e) {
            Mage::getModel('warranty/logger')->critical(['Exception' => $e->getMessage()], 'Error happens on a sync process');
            $data['status'] = 'FAIL';
            $data['message'] = $e->getMessage();
            $this->getResponse()->setHttpResponseCode(500);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }
}
