<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'abstract.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app/Mage.php';


class Extend_Orders_Sync extends Local_Shell_Abstract
{
    public function run()
    {
        $logger = $this->initLog();
        /** @var Extend_Warranty_Model_SyncProcessor_Orders $ordersSyncProcessor */
        $ordersSyncProcessor = Mage::getModel(
            'warranty/syncProcessor_orders'
        );

        $ordersSyncProcessor->setLogger($logger);
        $size = $ordersSyncProcessor->getCollection()->getSize();
        if ($size) {
            $pagesCount = $ordersSyncProcessor->getCollection()->getLastPageNumber();
            $bar = $this->progressBar($pagesCount);
            $ordersSyncProcessor->setProgressBar($bar);
            $ordersSyncProcessor->process();
        } else {
            $logger->info("Production orders have already been integrated to Extend. The historical import has been canceled");
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f orders_sync.php
    help    This help 
    
USAGE;
    }

}

$shell = new Extend_Orders_Sync();
$shell->run();
