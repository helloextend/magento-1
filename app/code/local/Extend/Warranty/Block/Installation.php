<?php

class Extend_Warranty_Block_Installation extends Mage_Core_Block_Template
{

    public function getIntegrationJsonConfig()
    {
        /** @var Extend_Warranty_Helper_Connector $helper */
        $helper = Mage::helper('warranty/connector');
        $isLiveMode = $helper->isLiveMode();

        $integrationConfig = array(
            'auth' => array(
                'mode' => $isLiveMode ? \Extend_Warranty_Helper_Connector::LIVE : \Extend_Warranty_Helper_Connector::DEMO,
                'id' => $isLiveMode ? $helper->getApiStoreId() : '',
                'sandboxId' => $isLiveMode ? '' : $helper->getApiStoreId(),
                'extendStoreName' => $helper->getApiStoreName()
            ),
            'general' => array(
                'enableExtend' => $helper->isExtendEnabled(),
                'balancedCart' => $helper->isBalancedCart(),
                'enableRefunds' => $helper->isRefundEnabled()
            ),
            'syncProducts' => array(
                'batchSize' => $helper->getBatchSize(),
                'lastSyncDate' => $helper->getLastSyncDate()
            ),
            'orders' => array(
                'ordersAPI' => $helper->isOrdersApiEnabled()
            ),
            'offers' => array(
                'displayCartOffers' => $helper->isDisplayOffersEnabled(),
                'postPurchaseLeadsModal' => $helper->isOffersLeadModalEnabled(),
                'orderInformationOffers' => $helper->isOffersOrderOffersEnabled()
            ),
            'syncHistoricalOrders' => array(
                'batchSize' => $helper->getHistoricalOrdersBatchSize(),
                'lastSendDate' => $helper->getHistoricalOrdersSyncPeriod(),
                'enableCronSync' => $helper->getHistoricalOrdersSyncEnabled()
            )
        );


        return json_encode($integrationConfig);
    }
}