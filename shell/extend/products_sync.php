<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'abstract.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app/Mage.php';

class Extend_Products_Sync extends Local_Shell_Abstract
{
    public function run()
    {
        $batchSize = $this->getArg('batch-size');
        $logger = $this->initLog();
        /** @var Extend_Warranty_Model_SyncProcessor_Products $productSyncProcessor */
        $productSyncProcessor = Mage::getModel(
            'warranty/syncProcessor_products'
        );

        $productSyncProcessor->setLogger($logger);

        if ($batchSize) {
            $productSyncProcessor->setBatchSize($batchSize);
        }

        $pagesCount = $productSyncProcessor->getCollection()->getLastPageNumber();

        $bar = $this->progressBar($pagesCount);

        $productSyncProcessor->setProgressBar($bar);
        $productSyncProcessor->process();
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
