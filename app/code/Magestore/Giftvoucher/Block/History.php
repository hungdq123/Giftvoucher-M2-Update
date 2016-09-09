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
namespace Magestore\Giftvoucher\Block;

/**
 * Giftvoucher History block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class History extends \Magestore\Giftvoucher\Block\Account
{
    
    protected $_loadCollection;
    
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'history_pager'
        )->setCollection($this->getCreditcardHistoryCollection());
        $this->setChild('history_pager', $pager);
        $grid = $this->getLayout()->createBlock('Magestore\Giftvoucher\Block\Grid', 'history_grid');
        // prepare column

        $grid->addColumn('action', array(
            'header' => __('Action'),
            'index' => 'action',
            'format' => \IntlDateFormatter::MEDIUM,
            'align' => 'left',
            'width' => '120px',
            'type' => 'options',
            'options' => $this->getSingleton('Magestore\Giftvoucher\Model\Creditaction')->getOptionArray(),
            'searchable' => true,
        ));

        $grid->addColumn('balance_change', array(
            'header' => __('Balance Change'),
            'align' => 'left',
            'type' => 'baseprice',
            'index' => 'balance_change',
            'width' => '120px',
            //'render' => 'getBalanceChangeFormat',
            'searchable' => true,
        ));

        $grid->addColumn('giftcard_code', array(
            'header' => __('Gift Card Code'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'giftcard_code',
            'searchable' => true,
        ));

        $grid->addColumn('order_number', array(
            'header' => __('Order'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'order_number',
            'width' => '80px',
            'render' => 'getOrder',
            'searchable' => true,
        ));

        $grid->addColumn('currency_balance', array(
            'header' => __('Current Balance'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'baseprice',
            'index' => 'currency_balance',
            //'render' => 'getBalanceFormat',
        ));

        $grid->addColumn('created_date', array(
            'header' => __('Changed Time'),
            'index' => 'created_date',
            'type' => 'date',
            'format' => \IntlDateFormatter::MEDIUM,
            'align' => 'left',
            'width' => '120px',
            'searchable' => true,
        ));


        $this->setChild('history_grid', $grid);
        return $this;
    }
    
    /**
     * Returns the formatted blance
     *
     * @param mixed $row
     * @return string
     */
    public function getBalanceFormat($row)
    {
        $currency = $this->getModel('Magento\Directory\Model\Currency')->load($row->getCurrency());
        return $currency->format($row->getCurrencyBalance());
    }
    
    public function getCreditcardHistoryCollection()
    {
        if (!$this->_loadCollection) {
            $customerId = $this->getSingleton('Magento\Customer\Model\Session')->getCustomer()->getId();
            $collection = $this->getModel('Magestore\Giftvoucher\Model\Credithistory')->getCollection()
                ->addFieldToFilter('main_table.customer_id', $customerId);
            $collection->setOrder('history_id', 'DESC');
            $this->_loadCollection = $collection;
        }
        return $this->_loadCollection;
    }

    /**
     * Render an order link
     *
     * @param mixed $row
     * @return string
     */
    public function getOrder($row)
    {
        if ($this->_getOrderId($row)) {
            $render = '<a href="' . $this->getUrl('sales/order/view', array('order_id' => $this->_getOrderId($row)))
                . '">' . $row->getOrderNumber() . '</a>';
            return $render;
        }
        return 'N/A';
    }
    
    protected function _getOrderId($row)
    {
        if (!$row->getOrderId()) {
            $order = $this->objectManager->create('Magento\Sales\Model\Order')
                    ->load($row->getOrderNumber(), 'increment_id');
            return $order->getEntityId();
        }
        return $row->getOrderId();
    }

    /**
     * Returns the formatted blance change
     *
     * @param mixed $row
     * @return string
     */
    public function getBalanceChangeFormat($row)
    {
        $currency = $this->getModel('Magento\Directory\Model\Currency')->load($row->getCurrency());
        return $currency->format($row->getBalanceChange());
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('history_pager');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('history_grid');
    }

    protected function _toHtml()
    {
        $this->getChildBlock('history_grid')->setCollection($this->getCreditcardHistoryCollection());
        return parent::_toHtml();
    }

    public function getBalanceAccount()
    {
        $credit = $this->objectManager->create('Magestore\Giftvoucher\Model\Credit')->getCreditAccountLogin();
        return $this->formatCurrency($credit->getBalance(), $credit->getCurrency());
    }
}
