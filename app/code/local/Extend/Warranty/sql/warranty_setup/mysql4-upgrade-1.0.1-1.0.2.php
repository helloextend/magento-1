<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'admin/assert'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('extend_historical_orders'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => false,
        'unsigned' => true,
        'nullable' => false,
        'primary' => false,
    ), 'Order ID')
    ->addColumn('was_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => true,
        'default' => null,
    ), 'Order sent status')
    ->addForeignKey(
        'EXTEND_HISTORICAL_ORDERS_SALES_ORDER_ENTITY_ID',
        'entity_id',
        'sales_flat_order',
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Extend Order History Table');

$installer->getConnection()->createTable($table);