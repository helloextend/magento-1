<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_item'),
    'lead_token',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length'   => '2M',
        'comment'  => 'Extend Lead Token'
    )
);

$installer->endSetup();
