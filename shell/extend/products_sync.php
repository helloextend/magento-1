<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'abstract.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app/Mage.php';

class Extend_Products_Sync extends Local_Shell_Abstract
{
    public function run()
    {
        try {
            $batchSize = $this->getArg('batch-size');
            if (!$batchSize) {
                $batchSize = Mage::helper('warranty/connector')->getBatchSize();
            }
            if ($batchSize > 100 || $batchSize <= 0) {
                $this->log->alert('Invalid batch size, value must be between 1-100.');
                exit;
            }
            $this->log->info('Setting product batch to ' . $batchSize);
            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('neq' => 'warranty'));
            $this->log->info("Started syncing products from Magento to Extend. Please wait!");
            $productCollection->setPageSize($batchSize);
            $pages = $productCollection->getLastPageNumber();
            $this->log->info('Total batches: ' . $pages);
            $currentPage = 1;
            $bar = $this->progressBar($pages);
            do {
                $productCollection->setCurPage($currentPage);
                $productCollection->load();
                Mage::getModel('warranty/api_sync_products_handler')->sync($productCollection, $currentPage);
                $message = "Updated batch " . $currentPage;
                $bar->update($currentPage, $message);
                $currentPage++;
                $productCollection->clear();
            } while ($currentPage <= $pages);
            $this->log->info("Syncing was finished");
            Mage::helper('warranty/connector')->setLastSyncDate();
        } catch (Exception $e) {
            $this->log->crit($e->getMessage());
            $this->log->crit('Syncing has been stopped!');
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f products_sync.php -- [options]
            php -f products_sync.php -- --batch-size 100 

    help    This help 
    
USAGE;
    }

}

$shell = new Extend_Products_Sync();
$shell->run();
