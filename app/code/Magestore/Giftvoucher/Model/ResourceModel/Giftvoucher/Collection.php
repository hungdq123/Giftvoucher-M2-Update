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
namespace Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher;

/**
 * Giftvoucher resource collection
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_isGroupSql = false;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Zend_Db_Adapter_Abstract $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $connection = null
    ) {
        $this->_date = $date;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }
    
    protected function _construct()
    {
        $this->_init('Magestore\Giftvoucher\Model\Giftvoucher', 'Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher');
    }
    
    public function getAvailable()
    {
        $this->addFieldToFilter(
            'main_table.status',
            array('neq' => \Magestore\Giftvoucher\Model\Status::STATUS_DELETED)
        );
        return $this;
    }
    
    public function joinHistory()
    {
        if ($this->hasFlag('join_history') && $this->getFlag('join_history')) {
            return $this;
        }
        $this->setFlag('join_history', true);
        $this->_isGroupSql = true;
        $this->getSelect()->group('main_table.giftvoucher_id')->joinLeft(
            array('history' => $this->getTable('giftvoucher_history')),
            'main_table.giftvoucher_id = history.giftvoucher_id',
            array(
                'history_amount' => 'amount',
                'history_currency' => 'currency',
                'created_at',
                'extra_content',
                'order_increment_id'
            )
        )->where('history.action = ?', \Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE);
        return $this;
    }
    
    public function getSelectCountSql()
    {
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset('order');
            $countSelect->reset('limitcount');
            $countSelect->reset('limitoffset');
            $countSelect->reset('columns');
            if (count($this->getSelect()->getPart('group')) > 0) {
                $countSelect->reset('group');
                $countSelect->distinct(true);
                $group = $this->getSelect()->getPart('group');
                $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
            } else {
                $countSelect->columns('COUNT(*)');
            }
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
    
    public function addItemFilter($quoteId)
    {
        if ($this->hasFlag('add_item_filer') && $this->getFlag('add_item_filer')) {
            return $this;
        }
        $this->setFlag('add_item_filer', true);

        $this->getSelect()->joinLeft(
            array('history' => $this->getTable('giftvoucher_history')),
            'main_table.giftvoucher_id = history.giftvoucher_id',
            array('quote_item_id')
        )->where('history.quote_item_id = ?', $quoteId)
        ->where('history.action = ?', \Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE);

        return $this;
    }
    
    public function addExpireAfterDaysFilter($dayBefore)
    {
        $date = $this->_date->gmtDate();
        $zendDate = new \Zend_Date($date);
        $dayAfter = $zendDate->addDay($dayBefore)->toString('YYYY-MM-dd');
        $this->getSelect()->where('date(expired_at) = ?', $dayAfter);
        return $this;
    }
}
