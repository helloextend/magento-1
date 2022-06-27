<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'abstract.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app/Mage.php';

class Extend_Orders_Sync extends Local_Shell_Abstract
{
    public function run()
    {
        $batchSize = $this->getArg('batch-size');
        $logger = $this->initLog();
        /** @var Extend_Warranty_Model_SyncProcessor_Orders $ordersSyncProcessor */
        $ordersSyncProcessor = Mage::getModel(
            'warranty/syncProcessor_orders'
        );

        $ordersSyncProcessor->setLogger($logger);

        if ($batchSize) {
            $ordersSyncProcessor->setBatchSize($batchSize);
        }

        $pagesCount = $ordersSyncProcessor->getCollection()->getLastPageNumber();

        $bar = $this->progressBar($pagesCount);

        $ordersSyncProcessor->setProgressBar($bar);
        $ordersSyncProcessor->process();
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f orders_sync.php -- [options]
            php -f orders_sync.php -- --batch-size 100 

    help    This help 
    
USAGE;
    }

}

$shell = new Extend_Orders_Sync();
$shell->run();
