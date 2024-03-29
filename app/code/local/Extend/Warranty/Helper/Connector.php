<?php

class Extend_Warranty_Helper_Connector extends Mage_Core_Helper_Abstract
{
    const SANDBOX_URL = 'https://api-demo.helloextend.com/';
    const LIVE_URL = 'https://api.helloextend.com/';
    const XML_PATH_PRODUCTS_BATCH_SIZE = 'warranty/products/batch_size';
    const XML_PATH_HISTORICAL_ORDERS_BATCH_SIZE = 'warranty/historical_orders/batch_size';
    const XML_PATH_HISTORICAL_ORDERS_SYNC_PERIOD = 'warranty/historical_orders/historical_orders_sync';
    const XML_PATH_HISTORICAL_ORDERS_CRON_SYNC_ENABLED = 'warranty/historical_orders/cron_sync_enabled';
    const XML_PATH_LAST_SYNC_PATH = 'warranty/products/last_sync';
    const XML_PATH_LAST_ORDERS_SYNC_DATE_PATH = 'warranty/products/last_orders_sync_date';
    const XML_PATH_AUTH_MODE = 'warranty/authentication/auth_mode';
    const XML_PATH_LIVE_API_KEY = 'warranty/authentication/api_key';
    const XML_PATH_LIVE_STORE_ID = 'warranty/authentication/store_id';
    const XML_PATH_SANDBOX_API_KEY = 'warranty/authentication/sandbox_api_key';
    const XML_PATH_SANDBOX_STORE_ID = 'warranty/authentication/sandbox_store_id';
    const XML_PATH_STORE_NAME = 'warranty/authentication/store_name';
    const XML_PATH_ENABLE_EXTEND = 'warranty/enableExtend/enable';
    const XML_PATH_ENABLE_BALANCE = 'warranty/enableExtend/enableBalance';
    const XML_PATH_ENABLE_CARTOFFERS = 'warranty/enableExtend/enableCartOffers';
    const XML_PATH_ENABLE_REFUNDS = 'warranty/enableExtend/enableRefunds';
    const DEMO = 'demo';
    const LIVE = 'live';

    /**
     * Orders API settings
     */
    const XML_PATH_WARRANTY_ENABLE_ORDERS_API = 'warranty/orders/enable';

    /**
     * Orders API settings
     */
    const XML_PATH_WARRANTY_ORDERS_API_CREATE_MODE = 'warranty/orders/order_create';


    /**
     * Offers Leads Model Settings
     */
    const XML_PATH_WARRANTY_OFFERS_LEADS_MODEL_ENABLED = 'warranty/offers/leads_modal_enabled';

    /**
     * Offers Order Information Settings
     */
    const XML_PATH_WARRANTY_OFFERS_ORDER_OFFERS_ENABLED = 'warranty/offers/order_offers_enabled';

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

        if (stripos($endpoint, 'offers') !== false
            || stripos($endpoint, 'orders') !== false
            || stripos($endpoint, 'refunds') !== false
        ) {
            $endpointUrl = $baseUrl . $endpoint;
        } else {
            $endpointUrl = rtrim($baseUrl, DS) . DS . 'stores' . DS . $storeId . DS . ltrim($endpoint, DS);
        }

        return $endpointUrl;
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


    public function getApiStoreName($store = null)
    {
        $store = $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_STORE_NAME, $store);
    }

    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
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
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getHistoricalOrdersBatchSize()
    {
        $store = $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_HISTORICAL_ORDERS_BATCH_SIZE, $store);
    }

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getHistoricalOrdersSyncPeriod($store = null)
    {
        $store = is_null($store) ? $this->getStore() :( $store);
        return Mage::getStoreConfig(self::XML_PATH_HISTORICAL_ORDERS_SYNC_PERIOD, $store);
    }

    /**
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getHistoricalOrdersSyncEnabled($store = null)
    {
        $store = is_null($store) ? $this->getStore() :( $store);
        return Mage::getStoreConfig(self::XML_PATH_HISTORICAL_ORDERS_CRON_SYNC_ENABLED, $store);
    }

    public function setHistoricalOrdersSyncPeriod($value, $scope = 'default', $scopeId = 0)
    {
        Mage::getModel('core/config')->saveConfig(
            self::XML_PATH_HISTORICAL_ORDERS_SYNC_PERIOD,
            $value,
            $scope,
            $scopeId);
        Mage::getConfig()->reinit();
        return $this;
    }

    /**
     * return void
     */
    public function setLastSyncDate()
    {
        Mage::getModel('core/config')->saveConfig(
            self::XML_PATH_LAST_SYNC_PATH,
            Mage::getModel('core/date')->date('Y-m-d H:i:s') . ' Selected Timezone: ' . Mage::getStoreConfig('general/locale/timezone')
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
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isExtendEnabled($storeId = null)
    {
        $store = $storeId ? $storeId : $this->getStore();
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_EXTEND, $store);
    }

    /**
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isBalancedCart()
    {
        $store = $this->getStore();
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_BALANCE, $store);
    }

    /**
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isDisplayOffersEnabled()
    {
        $store = $this->getStore();
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_CARTOFFERS, $store);
    }

    /**
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isRefundEnabled()
    {
        $store = $this->getStore();
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_REFUNDS, $store);
    }

    /**
     * @return false|string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getJsonConfig()
    {
        $data = [
            'storeId' => $this->getApiStoreId(),
            'environment' => $this->isLiveMode() ? self::LIVE : self::DEMO,
        ];

        if($this->isAdmin()){
            $data['region'] = 'US';
        }

        return json_encode($data);
    }

    /**
     * @return boolean
     */
    protected function isAdmin(){
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }
    /**
     * @param $sku
     * @param false $fromAdmin
     * @return bool
     */
    public function hasWarranty($sku, $fromAdmin = false)
    {
        $quote = $this->getCurrentQuote($fromAdmin);
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() !== Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                continue;
            }
            $associatedSku = [$item->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue()];

            if ($item->getOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU)) {
                $associatedSku[] = $item->getOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU)->getValue();
            }

            if (in_array($sku, $associatedSku)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $product
     * @param false $fromAdmin
     */
    public function productHasWarranty($product, $fromAdmin = false)
    {
        $warrantyHelper = Mage::helper('warranty');
        return $this->hasWarranty($warrantyHelper->getComplexProductSku($product), $fromAdmin);
    }

    /**
     * @param false $fromAdmin
     * @return mixed
     */
    protected function getCurrentQuote($fromAdmin = false)
    {
        if ($fromAdmin) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @param $store
     * @return mixed
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isOrdersApiEnabled($store = null)
    {
        $store = $store ?: $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_WARRANTY_ENABLE_ORDERS_API, $store);
    }

    /**
     * @param $store
     * @return boolean
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isOffersLeadModalEnabled($store = null)
    {
        $store = $store ?: $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_WARRANTY_OFFERS_LEADS_MODEL_ENABLED, $store);
    }

    /**
     * @param $store
     * @return boolean
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isOffersOrderOffersEnabled($store = null)
    {
        $store = $store ?: $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_WARRANTY_OFFERS_ORDER_OFFERS_ENABLED, $store);
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getOrdersApiCreateMode($store = null)
    {
        $store = $store ?: $this->getStore();
        return Mage::getStoreConfig(self::XML_PATH_WARRANTY_ORDERS_API_CREATE_MODE, $store);
    }

    /**
     * Generates unique request key
     * @return string
     */
    public function getUuid4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
