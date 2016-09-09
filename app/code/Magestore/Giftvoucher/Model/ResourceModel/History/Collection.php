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
namespace Magestore\Giftvoucher\Model\ResourceModel\History;

/**
 * Giftvoucher history resource collection
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_isGroupSql = false;
    
    protected function _construct()
    {
        $this->_init('Magestore\Giftvoucher\Model\History', 'Magestore\Giftvoucher\Model\ResourceModel\History');
    }
    
    public function joinGiftVoucher()
    {
        if ($this->hasFlag('join_giftvoucher') && $this->getFlag('join_giftvoucher')) {
            return $this;
        }
        $this->setFlag('join_giftvoucher', true);
        $this->getSelect()->joinLeft(
            array('giftvoucher' => $this->getTable('giftvoucher')),
            'main_table.giftvoucher_id = giftvoucher.giftvoucher_id',
            array(
                'gift_code'
            )
        )->where('main_table.action = ?', \Magestore\Giftvoucher\Model\Actions::ACTIONS_SPEND_ORDER);
        return $this;
    }

    public function joinSalesOrder()
    {
        $this->getSelect()->joinLeft(
            array('o' => $this->getTable('sales_order')),
            'main_table.order_increment_id = o.increment_id',
            array('order_customer_id' => 'customer_id')
        )->group('o.customer_id');

        return $this;
    }

    public function getHistory()
    {
        $this->getSelect()->order('main_table.created_at DESC');
        $this->getSelect()
            ->joinLeft(
                array('o' => $this->getTable('sales_order')),
                'main_table.order_increment_id = o.increment_id',
                array('order_id' => 'entity_id')
            );

        return $this;
    }
}
