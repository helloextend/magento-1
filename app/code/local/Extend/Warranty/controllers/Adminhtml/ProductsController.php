<?php

class Extend_Warranty_Adminhtml_ProductsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function syncAction()
    {
        $data = array();
        try {
            $batchSize = Mage::helper('warranty/connector')->getBatchSize();
            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('neq' => 'warranty'));
            $collectionSize = $productCollection->getSize();
            $productCollection->setPageSize($batchSize);
            $pages = $productCollection->getLastPageNumber();
            $currentPage = 1;
            do {
                $productCollection->setCurPage($currentPage);
                $productCollection->load();
                Mage::getModel('warranty/api_sync_products_handler')->sync($productCollection, $currentPage);
                $currentPage++;
                $productCollection->clear();
            } while ($currentPage <= $pages);
            $data['status'] = 'SUCCESS';
            $data['message'] = 'Data of ' . $collectionSize . ' products were sent to the Extend.';
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
