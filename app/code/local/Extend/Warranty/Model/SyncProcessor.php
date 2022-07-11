<?php

/**
 * Class Extend_Warranty_Model_SyncProcessor
 */
abstract class Extend_Warranty_Model_SyncProcessor
{
    protected $progressBar;

    protected $batchSize;

    protected $logger;

    protected $collection;

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

    abstract public function getCollection();

    abstract function getSyncHandler();

    abstract function getStartMessage();

    protected $hasError = false;
    protected $errorMessages = [];

    /**
     *
     */
    public function process()
    {
        try {
            $this->getLogger()->debug('== Script execution started ==');
            $this->getLogger()->info($this->getStartMessage());
            $collection = $this->getCollection();
            $pages = $collection->getLastPageNumber();
            $this->getLogger()->info('Total batches: ' . $pages);
            $currentPage = 1;
            do {
                $collection->setCurPage($currentPage);
                $collection->load();
                $syncHandler = $this->getSyncHandler();
                $syncHandler->sync($collection, $currentPage);
                $collection->clear();
                if ($this->getProgressBar()) {
                    $message = "Updated batch " . $currentPage;
                    $this->getProgressBar()->next(1, $message);
                }
                $currentPage++;
            } while ($currentPage <= $pages);
            $this->getLogger()->info("Syncing was finished");
            Mage::helper('warranty/connector')->setLastSyncDate();
        } catch (Exception $e) {
            if ($this->getProgressBar()) {
                $this->getProgressBar()->finish();
            }
            $this->hasError = true;
            $this->errorMessages[] = $e->getMessage();
            $this->getLogger()->crit($e->getMessage());
            $this->getLogger()->crit('Syncing has been stopped!');
        }
    }

}