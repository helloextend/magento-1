<?php

/**
 *
 */
class Extend_Warranty_Model_ProductSyncProcessor
{
    /**
     * @var
     */
    protected $progressBar;

    protected $batchSize;

    protected $logger;

    protected $productCollection;

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Extend_Warranty_Model_Logger
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = Mage::getModel('warranty/logger');
        }
        return $this->logger;
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

    /**
     * @param $progressBar
     */
    public function setProgressBar($progressBar)
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @return mixed
     */
    public function getProgressBar()
    {
        return $this->progressBar;
    }

    /**
     * @return \Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if ($this->productCollection === null) {
            $batchSize = $this->getBatchSize();

            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('neq' => 'warranty'))
                ->setPageSize($batchSize);;
            $this->productCollection = $productCollection;
        }
        return $this->productCollection;
    }

    /**
     *
     */
    public function process()
    {
        try {
            $this->getLogger()->debug('== Script execution started ==');
            $this->getLogger()->info("Started syncing products from Magento to Extend. Please wait!");
            $productCollection = $this->getProductCollection();
            $pages = $productCollection->getLastPageNumber();
            $this->getLogger()->info('Total batches: ' . $pages);
            $currentPage = 1;
            do {
                $productCollection->setCurPage($currentPage);
                $productCollection->load();
                Mage::getModel('warranty/api_sync_products_handler')->sync($productCollection, $currentPage);
                $productCollection->clear();
                if ($this->getProgressBar()) {
                    $message = "Updated batch " . $currentPage;
                    $this->getProgressBar()->update($currentPage, $message);
                }
                $currentPage++;
            } while ($currentPage <= $pages);
            $this->getLogger()->info("Syncing was finished");
            Mage::helper('warranty/connector')->setLastSyncDate();
        } catch (Exception $e) {
            $this->getLogger()->crit($e->getMessage());
            $this->getLogger()->crit('Syncing has been stopped!');
        }
    }

}