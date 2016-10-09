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

namespace Magestore\Giftvoucher\Block\Account;

/**
 * Giftvoucher Account View block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class View extends \Magestore\Giftvoucher\Block\Account
{

    protected $_loadCollection;

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'giftvoucher.index.index'
            )->setCollection($this->getCollection());
            $this->setChild('giftvoucher_pager', $pager);
        }

        $grid = $this->getLayout()->createBlock('Magestore\Giftvoucher\Block\Grid', 'giftvoucher_grid');
        // prepare column
        $grid->addColumn('gift_code', array(
            'header' => __('Gift Card Code'),
            'index' => 'gift_code',
            'format' => 'medium',
            'align' => 'left',
            'width' => '80px',
            'render' => 'getCodeTxt',
            'searchable' => true,
        ));

        $grid->addColumn('balance', array(
            'header' => __('Balance'),
            'align' => 'right',
            'type' => 'price',
            'index' => 'balance',
            //'render' => 'getBalanceFormat',
            'searchable' => true,
        ));
        $statuses = \Magestore\Giftvoucher\Model\Status::getOptionArray();
        $grid->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => $statuses,
            'width' => '120px',
            'searchable' => true,
        ));

        $grid->addColumn('added_date', array(
            'header' => __('Added Date'),
            'index' => 'added_date',
            'type' => 'date',
            'format' => \IntlDateFormatter::MEDIUM,
            'align' => 'left',
            'searchable' => true,
        ));
        $grid->addColumn('expired_at', array(
            'header' => __('Expired Date'),
            'index' => 'expired_at',
            'type' => 'date',
            'format' => \IntlDateFormatter::MEDIUM,
            'align' => 'left',
            'searchable' => true,
        ));

        $grid->addColumn('action', array(
            'header' => __('Action'),
            'align' => 'left',
            'type' => 'action',
            'width' => '300px',
            'render' => 'getActions',
        ));

        $this->setChild('giftvoucher_grid', $grid); //('giftvoucher_grid', $grid, );
        return $this;
    }

    public function getCollection()
    {
        if (!$this->_loadCollection) {
            $customerId = $this->getCustomer()->getId();
            $timezone = $this->datetime->getGmtOffset('hours');
            $collection = $this->getModel('Magestore\Giftvoucher\Model\Customervoucher')->getCollection()
                ->joinCustomer($customerId, $timezone);
            $collection->setOrder('customer_voucher_id', 'DESC');
            $this->_loadCollection = $collection;
        }
        return $this->_loadCollection;
    }

    /**
     * Returns the formatted blance
     *
     * @return mixed
     */
    public function getBalanceAccount()
    {
        $credit = $this->objectManager->get('Magestore\Giftvoucher\Model\Credit')->getCreditAccountLogin();
        $balanceCurrency = $this->getModel('Magento\Directory\Model\Currency')->load($credit->getCurrency());
        return $this->getHelper()->getCurrencyFormat($credit->getBalance(), $balanceCurrency);
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('giftvoucher_grid');
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('giftvoucher_pager');
    }

    protected function _toHtml()
    {
        $this->getChildBlock('giftvoucher_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

    /**
     * Returns the HTML codes of the action's column
     *
     * @param mixed $row
     * @return string
     */
    public function getActions($row)
    {
        $confirmText = __('Are you sure?');
        $removeurl = $this->getUrl('giftvoucher/index/remove', array('id' => $row->getId()));
        $redeemurl = $this->getUrl('giftvoucher/index/redeem', array('giftvouchercode' => $row->getGiftCode()));
        $type = $this->getHelper()->getSetIdOfCode($row->getGiftCode());

        $action = '<a href="' . $this->getUrl('*/*/view', array('id' => $row->getId())) . '">' . __('View') . '</a>';
        // can print gift voucher when status is not used
        $template = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->load($row->getVoucherId())->getGiftcardTemplateId();
        if ($row->getStatus() < \Magestore\Giftvoucher\Model\Status::STATUS_DISABLED && $template) {
            $action .= ' | <a href="javascript:void(0);" onclick="window.open(\''
                . $this->getUrl('*/*/print', array('id' => $row->getId()))
                . '\',\'newWindow\', \'width=1000,height=700,resizable=yes,scrollbars=yes\')" >' . __('Print') . '</a>';
            if ($row->getRecipientName() && $row->getRecipientEmail()
                && ($row->getCustomerId() == $this->getCustomer()->getId()
                    || $row->getCustomerEmail() == $this->getCustomer()->getEmail())
            ) {
                $action .= ' | <a href="' . $this->getUrl('*/*/email', array('id' => $row->getId())) . '">'
                    . __('Email') . '</a>';
            }
        }

        $avaiable = $this->getHelper()
            ->canUseCode($this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->load($row->getVoucherId()));
        if ($this->getHelper()->getGeneralConfig('enablecredit') && $avaiable) {
            if ($row->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE && !$type
                || ($row->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_USED && $row->getBalance() > 0 && !$type)
            ) {
                $action .= ' | <a href="javascript:void(0);" onclick="redeem' . $row->getId() . '()">' . __('Redeem')
                    . '</a>';
                $action .= '<script type="text/javascript">
                    //<![CDATA[
                        function redeem' . $row->getId() . '(){
                            if (confirm(\'' . $confirmText . '\')){
                                setLocation(\'' . $redeemurl . '\');
                            }
                        }
                    //]]>
                </script>';
            }
        }
        $action .= ' | <a href="javascript:void(0);" onclick="remove' . $row->getId() . '()">' . __('Remove') . '</a>';
        $action .= '<script type="text/javascript">
                    //<![CDATA[
                        function remove' . $row->getId() . '(){
                            if (confirm(\'' . $confirmText . '\')){
                                setLocation(\'' . $removeurl . '\');
                            }
                        }
                    //]]>
                </script>';
        return $action;
    }

    /**
     * Returns the HTML codes of the gift code's column
     *
     * @param mixed $row
     * @return string
     */
    public function getCodeTxt($row)
    {
        $type = $this->getHelper()->getSetIdOfCode($row->getGiftCode());
        $input = '<input style="width:auto;" id="input-gift-code' . $row->getId()
            . '" readonly type="text" class="input-text" value="' . $row->getGiftCode()
            . '" onblur="hiddencode' . $row->getId() . '(this);">';
        if($type){
            $aelement = '<a href="javascript:void(0);" onclick="">'
                . $this->getHelper()->getHiddenCode($row->getGiftCode()) . '</a>';
        }else{
            $aelement = '<a href="javascript:void(0);" onclick="viewgiftcode' . $row->getId() . '()">'
                . $this->getHelper()->getHiddenCode($row->getGiftCode()) . '</a>';
        }



        $html = '<div id="inputboxgiftvoucher' . $row->getId() . '" >' . $aelement . '</div>
                <script type="text/javascript">
                    //<![CDATA[
                    require([
                        "jquery",
                        "prototype"
                    ], function(jQuery){
                        viewgiftcode' . $row->getId() . ' = function(){
                            $(\'inputboxgiftvoucher' . $row->getId() . '\').innerHTML=\'' . $input . '\';
                            $(\'input-gift-code' . $row->getId() . '\').focus();
                        }
                        hiddencode' . $row->getId() . ' = function(el) {
                            $(\'inputboxgiftvoucher' . $row->getId() . '\').innerHTML=\'' . $aelement . '\';
                        }
                    });
                    //]]>
                </script>';
        return $html;
    }
}
