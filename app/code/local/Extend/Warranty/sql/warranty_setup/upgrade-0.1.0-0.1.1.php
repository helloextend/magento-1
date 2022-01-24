<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'),
    'extend_order_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length'   => '2M',
        'comment'  => 'Extend Order ID'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_item'),
    'extend_line_item_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length'   => '2M',
        'comment'  => 'Extend Line Item ID'
    )
);

$installer->endSetup();
