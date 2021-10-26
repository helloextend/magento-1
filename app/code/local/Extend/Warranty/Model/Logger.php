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
    public function info($message = null, $data = [], $prefix = null)
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
     * @param null $message
     * @param array $data
     * @param null $prefix
     */
    public function debug($message = null, $data = [], $prefix = null)
    {
        $this->_log($message, $data, $prefix, Zend_Log::DEBUG);
    }

    /**
     * @param $message
     * @param $data
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function error($message = null, $data = array(), $prefix = null)
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
     * alias to self::critical
     * @param null $message
     * @param array $data
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function crit($message = null, $data = array(), $prefix = null)
    {
        $this->critical($message, $data, $prefix);
    }

    /**
     * @param $message
     * @param $data
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function alert($message = null, $data = array(), $prefix = null)
    {
        $this->_log($message, $data, $prefix, Zend_Log::ALERT);
    }

    /**
     * @param $message
     * @param $data
     * @param null $prefix
     * @throws Mage_Core_Model_Store_Exception
     */
    public function critical($message = null, $data = array(), $prefix = null)
    {
        $this->_log($message, $data, $prefix, Zend_Log::CRIT);
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
     * @param $message
     * @param $data
     * @param $prefix
     * @param $level
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _log($message, $data, $prefix, $level)
    {
        $file = $this->_getLogFile($level);
        $this->addAdditionalInfo($level, $file);
        if ($message) {
            Mage::log('----- ' . $message . ' -----', $level, $file, true);
        }
        if ($prefix && $data) {
            Mage::log(
                $prefix . print_r($data, true),
                $level,
                $file,
                true
            );
        } elseif (!$prefix && $data) {
            Mage::log($data, $level, $file, true);
        }
    }

    /**
     * @param $level
     * @return string
     */
    protected function _getLogFile($level)
    {
        switch ($level) {
            case Zend_Log::CRIT:
                $filename = self::CRIT_LOG_FILE;
                break;
            case Zend_Log::ERR:
                $filename = self::ERR_LOG_FILE;
                break;
            case Zend_Log::DEBUG:
            case Zend_Log::INFO:
            default:
                $filename = self::INFO_LOG_FILE;
                break;
        }

        return $filename;
    }

    /**
     * @return string
     */
    protected function getApiAuthMode()
    {
        return Mage::helper('warranty/connector')->isLiveMode() ? 'Live' : 'Sandbox';
    }
}
