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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Gift Card module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
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
     * {@inheritdoc}
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
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $entityTypeModel = $this->_entityTypeModel;
        $catalogAttributeModel = $this->_catalogAttribute;

        $installer =  $this->_eavSetup;

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $setup->getConnection()->dropTable($setup->getTable('giftvoucher_sets'));
            $setup->getConnection()->addColumn(
                $setup->getTable('giftvoucher'),
                'used',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT
            );

            $table = $setup->getConnection()->newTable(
                $setup->getTable('giftvoucher_sets')
            )->addColumn(
                'set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Set Id'
            )->addColumn(
                'set_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                45,
                ['default' => ''],
                'Set Name'
            )->addColumn(
                'sets_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => '0'],
                'Set Qty'
            )->addIndex(
                $setup->getIdxName('giftvoucher_sets', ['set_id']),
                ['set_id']
            );
             $setup->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $setup->getTable('giftvoucher'),
                'set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            );

            $data = array(
                'group' => 'General',
                'type' => 'varchar',
                'input' => 'select',
                'default' => 1,
                'label' => 'Select Gift Card Templates ',
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

                $data['label'] = 'Sellect The Gift Code Sets';
                $data['source'] ='Magestore\Giftvoucher\Model\Giftcodesetsoptions';
                $data['sort_order'] = 110;
                $data['is_required'] = 0;

                $installer->addAttribute(
                    $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
                    'gift_code_sets',
                    $data
                );
                $giftCodeSets = $catalogAttributeModel->loadByCode('catalog_product', 'gift_code_sets');
                $giftCodeSets->addData($data)->save();


                $data['label'] = 'Sellect Gift Card Type';
                $data['source'] = 'Magestore\Giftvoucher\Model\Giftcardtypeoptions';
                $data['sort_order'] = 14;
                $data['is_required'] = 1;

                $installer->addAttribute(
                $entityTypeModel->loadByCode('catalog_product')->getData('entity_type_id'),
                'gift_card_type',
                $data
                );
                $giftCardType = $catalogAttributeModel->loadByCode('catalog_product', 'gift_card_type');
                $giftCardType->addData($data)->save();


            $setup->endSetup();

        }
    }
}
