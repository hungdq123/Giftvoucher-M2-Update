<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Giftvoucher\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Model\Entity\Type;

class InstallSchema implements InstallSchemaInterface
{
    
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityTypeModel;
    
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_catalogAttribute;
    
    /**
     * @var \Magento\Eav\Setup\EavSetupe
     */
    protected $_eavSetup;

    /**
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param \Magento\Eav\Model\Entity\Type $entityType
     * @param \Magento\Eav\Model\Entity\Attribute $catalogAttribute
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Eav\Model\Entity\Type $entityType,
        \Magento\Eav\Model\Entity\Attribute $catalogAttribute
    ) {
        $this->_eavSetup = $eavSetup;
        $this->_entityTypeModel = $entityType;
        $this->_catalogAttribute = $catalogAttribute;
    }
    
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $entityTypeModel = $this->_entityTypeModel;
        $catalogAttributeModel = $this->_catalogAttribute;
        
        $installer =  $this->_eavSetup;
        $setup->startSetup();
        
        $tax = $catalogAttributeModel->loadByCode('catalog_product', 'tax_class_id');
        $applyTo = explode(',', $tax->getData('apply_to'));
        $applyTo[] = 'giftvoucher';
        $taxApplyTo = implode(',', $applyTo);
        $tax->addData(array('apply_to' => $taxApplyTo))->save();
        
        $weight = $catalogAttributeModel->loadByCode('catalog_product', 'weight');
        $applyTo = explode(',', $weight->getData('apply_to'));
        $applyTo[] = 'giftvoucher';
        $weightApplyTo = implode(',', $applyTo);
        $weight->addData(array('apply_to' => $weightApplyTo))->save();
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'base_giftvoucher_discount_for_shipping',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftvoucher_discount_for_shipping',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'base_giftcredit_discount_for_shipping',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftcredit_discount_for_shipping',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'base_gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'base_use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice_item'),
            'base_gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice_item'),
            'gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice_item'),
            'base_use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice_item'),
            'use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'base_gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'base_use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'giftcard_refund_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'base_gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'base_use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'giftcard_refund_amount',
            'decimal(12,4) NULL'
        );
        
        
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher_history'));
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher'));
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher_credit'));
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher_customer_voucher'));
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher_credit_history'));
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher_template'));
        $setup->getConnection()->dropTable($setup->getTable('giftvoucher_product'));
        $setup->getConnection()->dropTable($setup->getTable('giftcard_template'));
        
  
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher')
        )->addColumn(
            'giftvoucher_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Giftvoucher Id'
        )->addColumn(
            'gift_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['nullable' => false, 'default' => ''],
            'Gift Code'
        )->addColumn(
            'balance',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Balance'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['default' => ''],
            'Currency'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['nullable' => false, 'default' => '0'],
            'Status'
        )->addColumn(
            'expired_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Expired At'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'default' => '0'],
            'Customer Id'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['nullable' => false, 'default' => ''],
            'Customer Name'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['nullable' => false, 'default' => ''],
            'Customer Email'
        )->addColumn(
            'recipient_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['nullable' => false, 'default' => ''],
            'Recipient Name'
        )->addColumn(
            'recipient_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['nullable' => false, 'default' => ''],
            'Recipient Email'
        )->addColumn(
            'recipient_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => ''],
            'Recipient Address'
        )->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => ''],
            'Message'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Conditions Serialized'
        )->addColumn(
            'day_to_send',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            [],
            'Day To Send'
        )->addColumn(
            'is_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['default' => '0'],
            'Is Sent'
        )->addColumn(
            'shipped_to_customer',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => '0'],
            'Shipped To Customer'
        )->addColumn(
            'created_form',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            45,
            [],
            'Created Form'
        )->addColumn(
            'template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Template Id'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Description'
        )->addColumn(
            'giftvoucher_comments',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Giftvoucher Comments'
        )->addColumn(
            'email_sender',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['default' => '0'],
            'Email Sender'
        )->addColumn(
            'notify_success',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            '1',
            ['default' => '0'],
            'Notify Success'
        )->addColumn(
            'giftcard_custom_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['default' => '0'],
            'Giftcard Custom Image'
        )->addColumn(
            'giftcard_template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['default' => '0'],
            'Giftcard Template Id'
        )->addColumn(
            'giftcard_template_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => '0'],
            'Giftcard Template Image'
        )->addColumn(
            'actions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Actions Serialized'
        )->addColumn(
            'timezone_to_send',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Timezone To Send'
        )->addColumn(
            'day_store',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Day Store'
        )->addIndex(
            $setup->getIdxName(
                'giftvoucher',
                ['giftvoucher_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['giftvoucher_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher_history')
        )->addColumn(
            'history_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'History Id'
        )->addColumn(
            'giftvoucher_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Giftvoucher id'
        )->addColumn(
            'action',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['nullable' => false, 'default' => '0'],
            'Action'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Create At'
        )->addColumn(
            'amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Amount'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['default' => ''],
            'Currency'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['nullable' => false, 'default' => '0'],
            'Status'
        )->addColumn(
            'comments',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => ''],
            'Comments'
        )->addColumn(
            'order_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['default' => ''],
            'Order Increment Id'
        )->addColumn(
            'quote_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, ],
            'Quote Item Id'
        )->addColumn(
            'order_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Order Amount'
        )->addColumn(
            'extra_content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => ''],
            'Extra Content'
        )->addColumn(
            'balance',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Balance'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Customer Id'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            [],
            'Customer Email'
        )->addIndex(
            $setup->getIdxName(
                'giftvoucher_history',
                ['history_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['history_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName('giftvoucher_history', ['created_at']),
            ['created_at']
        )->addForeignKey(
            $setup->getFkName('giftvoucher_history', 'giftvoucher_id', 'giftvoucher', 'giftvoucher_id'),
            'giftvoucher_id',
            $setup->getTable('giftvoucher'),
            'giftvoucher_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher_credit')
        )->addColumn(
            'credit_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Credit Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Customer id'
        )->addColumn(
            'balance',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Balance'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            45,
            ['default' => ''],
            'Currency'
        )->addIndex(
            $setup->getIdxName('giftvoucher_credit', ['customer_id']),
            ['customer_id']
        )->addForeignKey(
            $setup->getFkName('giftvoucher_credit', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher_credit_history')
        )->addColumn(
            'history_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'History Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Customer id'
        )->addColumn(
            'action',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            45,
            ['default' => ''],
            'Action'
        )->addColumn(
            'currency_balance',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Currency Balance'
        )->addColumn(
            'giftcard_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Giftcard Code'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [],
            'Order Id'
        )->addColumn(
            'order_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Order Number'
        )->addColumn(
            'balance_change',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Balance Change'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            45,
            ['default' => ''],
            'Currency'
        )->addColumn(
            'base_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Base Amount'
        )->addColumn(
            'amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['default' => '0'],
            'Amount'
        )->addColumn(
            'created_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Created Date'
        )->addIndex(
            $setup->getIdxName('giftvoucher_credit_history', ['customer_id']),
            ['customer_id']
        )->addForeignKey(
            $setup->getFkName('giftvoucher_credit_history', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher_customer_voucher')
        )->addColumn(
            'customer_voucher_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Voucher Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Customer id'
        )->addColumn(
            'voucher_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true],
            'Voucher Id'
        )->addColumn(
            'added_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Added Date'
        )->addIndex(
            $setup->getIdxName('giftvoucher_customer_voucher', ['customer_id']),
            ['customer_id']
        )->addIndex(
            $setup->getIdxName('giftvoucher_customer_voucher', ['voucher_id']),
            ['voucher_id']
        )->addForeignKey(
            $setup->getFkName('giftvoucher_customer_voucher', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('giftvoucher_customer_voucher', 'voucher_id', 'giftvoucher', 'giftvoucher_id'),
            'voucher_id',
            $setup->getTable('giftvoucher'),
            'giftvoucher_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher_template')
        )->addColumn(
            'template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Template Id'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            45,
            ['default' => ''],
            'Type'
        )->addColumn(
            'template_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => ''],
            'Template Name'
        )->addColumn(
            'pattern',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => ''],
            'Pattern'
        )->addColumn(
            'balance',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            ['default' => '0'],
            'Balance'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            45,
            [],
            'Currency'
        )->addColumn(
            'expired_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Expired At'
        )->addColumn(
            'amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['default' => '0'],
            'Amount'
        )->addColumn(
            'day_to_send',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Day To Send'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => ''],
            'Conditions Serialized'
        )->addColumn(
            'is_generated',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Generated'
        )->addColumn(
            'giftcard_template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => '0'],
            'Giftcard Template Id'
        )->addColumn(
            'giftcard_template_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Giftcard Template Image'
        )->addIndex(
            $setup->getIdxName('giftvoucher_template', ['template_id']),
            ['template_id']
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftvoucher_product')
        )->addColumn(
            'giftcard_product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Giftcard Product Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Conditions Serialized'
        )->addColumn(
            'giftcard_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            [],
            'Giftcard Description'
        )->addColumn(
            'actions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Actions Serialized'
        );
        $setup->getConnection()->createTable($table);
        
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('giftcard_template')
        )->addColumn(
            'giftcard_template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Giftcard Template Id'
        )->addColumn(
            'template_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Template Name'
        )->addColumn(
            'style_color',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Style Clor'
        )->addColumn(
            'text_color',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => '0'],
            'Text Color'
        )->addColumn(
            'caption',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => '0'],
            'Caption'
        )->addColumn(
            'notes',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            [],
            'Notes'
        )->addColumn(
            'images',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['default' => ''],
            'Images'
        )->addColumn(
            'design_pattern',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            [],
            'Design Pattern'
        )->addColumn(
            'background_img',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Background Img'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Status'
        )->addIndex(
            $setup->getIdxName('giftcard_template', ['giftcard_template_id']),
            ['giftcard_template_id']
        );
        $setup->getConnection()->createTable($table);
        
        
        $data = array();
        $data[0]['template_name'] = __('Default Template 1');
        $data[0]['style_color'] = '#DC8C71';
        $data[0]['text_color'] = '#949392';
        $data[0]['caption'] = __('Gift Card');
        $data[0]['notes'] = '';
        $data[0]['images'] = 'default.png';
        $data[0]['background_img'] = 'default.png';
        $data[0]['design_pattern'] = \Magestore\Giftvoucher\Model\Designpattern::PATTERN_LEFT;
        
        $data[1]['template_name'] = __('Default Template 2');
        $data[1]['style_color'] = '#FFFFFF';
        $data[1]['text_color'] = '#636363';
        $data[1]['caption'] = __('Gift Card');
        $data[1]['notes'] = '';
        $data[1]['images'] = 'default.png';
        $data[1]['background_img'] = 'default.png';
        $data[1]['design_pattern'] = \Magestore\Giftvoucher\Model\Designpattern::PATTERN_TOP;
        
        $data[2]['template_name'] = __('Default Template 3');
        $data[2]['style_color'] = '#FFFFFF';
        $data[2]['text_color'] = '#A9A7A7';
        $data[2]['caption'] = __('Gift Card');
        $data[2]['notes'] = '';
        $data[2]['images'] = 'default.png';
        $data[2]['background_img'] = 'default.png';
        $data[2]['design_pattern'] = \Magestore\Giftvoucher\Model\Designpattern::PATTERN_CENTER;
        
        $setup->getConnection()->insertMultiple($setup->getTable('giftcard_template'), $data);
        
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_type'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_value'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_from'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_to'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_dropdown'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_price_type'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_price'
        );
        $installer->removeAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_template_ids'
        );
        
        $data = array(
            'group' => 'General',
            'type' => 'varchar',
            'input' => 'multiselect',
            'default' => 1,
            'label' => 'Select Gift Card templates ',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'frontend' => '',
            'source' => 'Magestore\Giftvoucher\Model\Templateoptions',
            'visible' => 1,
            'required' => 1,
            'user_defined' => 1,
            'used_for_price_rules' => 1,
            'position' => 2,
            'unique' => 0,
            'default' => '',
            'sort_order' => 100,
            'apply_to' => 'giftvoucher',
            'is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'is_required' => 1,
            'is_configurable' => 1,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 1,
            'is_used_for_promo_rules' => 1,
            'is_html_allowed_on_front' => 0,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 1,
            'used_for_sort_by' => 0,
        );
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_template_ids',
            $data
        );
        $giftTemplateIds = $catalogAttributeModel->loadByCode('catalog_product', 'gift_template_ids');
        $giftTemplateIds->addData($data)->save();
        

        $data['group'] = 'Prices';
        $data['type'] = 'int';
        $data['input'] = 'select';
        $data['label'] = 'Type of Gift Card value';
        $data['backend'] = '';
        $data['required'] = 1;
        $data['source'] = 'Magestore\Giftvoucher\Model\Gifttype';
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_type',
            $data
        );
        $giftType = $catalogAttributeModel->loadByCode('catalog_product', 'gift_type');
        $giftType->addData($data)->save();
        
        
        $data['type'] = 'decimal';
        $data['input'] = 'price';
        $data['class'] = 'validate-number';
        $data['label'] = 'Gift Card value';
        $data['position'] = 4;
        $data['sort_order'] = 103;
        $data['source'] = '';
        $data['required'] = 0;
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_value',
            $data
        );
        $giftValue = $catalogAttributeModel->loadByCode('catalog_product', 'gift_value');
        $giftValue->addData($data)->save();
        
        
        $data['type'] = 'text';
        $data['input'] = 'text';
        $data['class'] = '';
        $data['label'] = 'Gift Card price';
        $data['position'] = 13;
        $data['sort_order'] = 110;
        $data['note'] = __('Notes: ');
        $data['required'] = 0;
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_price',
            $data
        );
        $giftPrice = $catalogAttributeModel->loadByCode('catalog_product', 'gift_price');
        $giftPrice->addData($data)->save();
        
        
        $data['type'] = 'decimal';
        $data['input'] = 'price';
        $data['label'] = 'Minimum Gift Card value';
        $data['class'] = 'validate-number';
        $data['position'] = 10;
        $data['sort_order'] = 107;
        $data['note'] = '';
        $data['required'] = 0;
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_from',
            $data
        );
        $giftFrom = $catalogAttributeModel->loadByCode('catalog_product', 'gift_from');
        $giftFrom->addData($data)->save();
        
        
        $data['label'] = 'Maximum Gift Card value';
        $data['class'] = 'validate-number';
        $data['position'] = 11;
        $data['sort_order'] = 108;
        $data['required'] = 0;
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_to',
            $data
        );
        $giftTo = $catalogAttributeModel->loadByCode('catalog_product', 'gift_to');
        $giftTo->addData($data)->save();
        
        
        $data['type'] = 'varchar';
        $data['input'] = 'text';
        $data['label'] = 'Gift Card values';
        $data['class'] = '';
        $data['position'] = 12;
        $data['sort_order'] = 109;
        $data['backend_type'] = 'text';
        $data['note'] = __('Seperated by comma, e.g. 10,20,30');
        $data['required'] = 0;
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_dropdown',
            $data
        );
        $giftDropdown = $catalogAttributeModel->loadByCode('catalog_product', 'gift_dropdown');
        $giftDropdown->addData($data)->save();
        
        
        $data['type'] = 'int';
        $data['input'] = 'select';
        $data['label'] = 'Type of Gift Card price';
        $data['class'] = '';
        $data['required'] = 1;
        $data['position'] = 12;
        $data['sort_order'] = 109;
        $data['backend_type'] = 'text';
        $data['note'] = __('Gift Card price is the same as Gift Card value by default.');
        $data['source'] = 'Magestore\Giftvoucher\Model\Giftpricetype';
        $installer->addAttribute(
            $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
            'gift_price_type',
            $data
        );
        $giftPriceType = $catalogAttributeModel->loadByCode('catalog_product', 'gift_price_type');
        $giftPriceType->addData($data)->save();
        
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'base_gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'base_use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftvoucher_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftvoucher_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftcredit_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftcredit_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftvoucher_base_shipping_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftvoucher_shipping_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftcredit_base_shipping_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'giftcredit_shipping_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'giftvoucher_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'giftvoucher_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'giftcredit_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'giftcredit_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'giftvoucher_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'giftvoucher_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'giftcredit_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'giftcredit_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'base_gift_voucher_discount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'base_use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'use_gift_credit_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'giftvoucher_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'giftvoucher_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'giftcredit_base_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'giftcredit_hidden_tax_amount',
            'decimal(12,4) NULL'
        );
        
        $setup->endSetup();
    }
}
