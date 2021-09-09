<?php

class Extend_Warranty_Model_Logger
{
    const INFO_LOG_FILE = 'extend_warranty-info.log';
    const ERR_LOG_FILE = 'extend_warranty-error.log';
    const CRIT_LOG_FILE = 'extend_warranty-critical.log';

    /**
     * @param $data
     * @param $message
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function info($data, $message = null, $prefix = null)
    {
        $this->addAdditionalInfo(Zend_Log::INFO, self::INFO_LOG_FILE);
        if ($message) {
            Mage::log('----- ' . $message . ' -----', Zend_Log::INFO, self::INFO_LOG_FILE, true);
        }
        if ($prefix && $data) {
            Mage::log(
                $prefix . ' ' . print_r($data, true),
                Zend_Log::INFO,
                self::INFO_LOG_FILE,
                true
            );
        } elseif (!$prefix && $data) {
            Mage::log($data, Zend_Log::INFO, self::INFO_LOG_FILE, true);
        }
    }

    /**
     * @param $data
     * @param $message
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function error($data, $message = null, $prefix = null)
    {
        $this->addAdditionalInfo(Zend_Log::ERR, self::ERR_LOG_FILE);
        if ($message) {
            Mage::log('----- ' . $message . ' -----', Zend_Log::ERR, self::ERR_LOG_FILE, true);
        }
        if ($prefix && $data) {
            Mage::log(
                $prefix . ' ' . print_r($data, true),
                Zend_Log::ERR,
                self::ERR_LOG_FILE,
                true);
        } elseif (!$prefix && $data) {
            Mage::log($data, Zend_Log::ERR, self::ERR_LOG_FILE, true);
        }
    }

    /**
     * @param $data
     * @param $message
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function critical($data, $message = null, $prefix = null)
    {
        $this->addAdditionalInfo(Zend_Log::CRIT, self::CRIT_LOG_FILE);
        if ($message) {
            Mage::log('----- ' . $message . ' -----', Zend_Log::CRIT, self::CRIT_LOG_FILE, true);
        }
        if ($prefix && $data) {
            Mage::log(
                $prefix . print_r($data, true),
                Zend_Log::CRIT,
                self::CRIT_LOG_FILE,
                true
            );
        } elseif (!$prefix && $data) {
            Mage::log($data, Zend_Log::CRIT, self:: CRIT_LOG_FILE, true);
        }
    }

    /**
     * @param $level
     * @param $file
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function addAdditionalInfo($level, $file)
    {
        Mage::log('-------------------------------------------------', $level, $file, true);
        Mage::log(
            sprintf("Selected Time Zone %s, Time in a selected Time Zone: %s",
                    Mage::app()->getStore()->getConfig('general/locale/timezone'),
                    Mage::getModel('core/date')->date('Y-m-d H:i:s')
            ),
            $level,
            $file,
            true
        );
        Mage::log('API Auth Mode: ' . $this->getApiAuthMode(), $level, $file, true);
    }

    /**
     * @return string
     */
    protected function getApiAuthMode()
    {
        return Mage::helper('warranty/connector')->isLiveMode() ? 'Live' : 'Sandbox';
    }
}
