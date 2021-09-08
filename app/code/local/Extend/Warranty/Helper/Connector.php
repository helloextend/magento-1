<?php

class Extend_Warranty_Helper_Connector extends Mage_Core_Helper_Abstract
{
    const SANDBOX_URL = 'https://api-demo.helloextend.com/';
    const LIVE_URL = 'https://api.helloextend.com/';
    const XML_PATH_PRODUCTS_BATCH_SIZE = 'warranty/products/batch_size';
    const XML_PATH_LAST_SYNC_PATH = 'warranty/products/last_sync';
    const XML_PATH_AUTH_MODE = 'warranty/authentication/auth_mode';
    const XML_PATH_LIVE_API_KEY = 'warranty/authentication/api_key';
    const XML_PATH_LIVE_STORE_ID = 'warranty/authentication/store_id';
    const XML_PATH_SANDBOX_API_KEY = 'warranty/authentication/sandbox_api_key';
    const XML_PATH_SANDBOX_STORE_ID = 'warranty/authentication/sandbox_store_id';
    const XML_PATH_ENABLE_EXTEND = 'warranty/enableExtend/enable';

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var bool
     */
    protected $liveMode = '';

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getEndpointUri($endpoint)
    {
        $baseUrl = $this->isLiveMode() ? self::LIVE_URL : self::SANDBOX_URL;
        $storeId = $this->getApiStoreId();

        return rtrim($baseUrl, DS) . DS . 'stores' . DS . $storeId . DS . ltrim($endpoint, DS);
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isLiveMode()
    {
        if (!$this->liveMode) {
            $store = $this->getStore();
            $this->liveMode = Mage::getStoreConfig(self::XML_PATH_AUTH_MODE, $store);
        }

        return $this->liveMode;
    }

    /**
     * Retrieve Store object
     *
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = Mage::app()->getStore();
        }

        return $this->_store;
    }

    /**
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getApiStoreId()
    {
        $store = $this->getStore();
        if ($this->isLiveMode()) {
            return Mage::getStoreConfig(self::XML_PATH_LIVE_STORE_ID, $store);
        }

        return Mage::getStoreConfig(self::XML_PATH_SANDBOX_STORE_ID, $store);
    }

    /**
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getApiKey()
    {
        $store = $this->getStore();
        if ($this->isLiveMode()) {
            return Mage::getStoreConfig(self::XML_PATH_LIVE_API_KEY, $store);
        }

        return Mage::getStoreConfig(self::XML_PATH_SANDBOX_API_KEY, $store);
    }

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getBatchSize()
    {
        $store = $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_PRODUCTS_BATCH_SIZE, $store);
    }

    /**
     * return void
     */
    public function setLastSyncDate()
    {
        Mage::getModel('core/config')->saveConfig(
            self::XML_PATH_LAST_SYNC_PATH,
            Mage::getModel('core/date')->date('Y-m-d H:i:s')
        );
    }

    /**
     * @return string
     */
    public function getLastSyncDate()
    {
        return Mage::getStoreConfig(self::XML_PATH_LAST_SYNC_PATH);
    }

    /**
     * @return bool
     */
    public function isExtendEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_EXTEND);
    }
}
