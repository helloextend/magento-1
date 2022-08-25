<?php

/**
 * Class Extend_Warranty_Model_SyncProcessor_Products
 */
class Extend_Warranty_Model_SyncProcessor_Products extends Extend_Warranty_Model_SyncProcessor
{
    public function getStartMessage()
    {
        return 'Started syncing products from Magento to Extend. Please wait!';
    }

    /**
     * @return \Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $batchSize = $this->getBatchSize();

            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('neq' => 'warranty'))
                ->setPageSize($batchSize);;
            $this->collection = $productCollection;
        }
        return $this->collection;
    }

    /**
     * @return integer
     */
    public function getBatchSize()
    {
        if ($this->batchSize === null) {
            $this->setBatchSize(Mage::helper('warranty/connector')->getBatchSize());
        }
        return $this->batchSize;
    }

    /**
     * @param $batchSize
     * @return $this
     *
     */
    public function setBatchSize($batchSize)
    {
        if ($batchSize > 100 || $batchSize <= 0) {
            $this->getLogger()->alert('Invalid batch size, value must be between 1-100.');
            throw new Exception("Batch size is invalid");
        }

        $this->getLogger()->info('Setting product batch to ' . $batchSize);

        $this->batchSize = $batchSize;
        return $this;
    }


    public function getSyncHandler()
    {
        return Mage::getModel('warranty/api_sync_products_handler');
    }
}