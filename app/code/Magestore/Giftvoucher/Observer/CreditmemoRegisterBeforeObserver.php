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
namespace Magestore\Giftvoucher\Observer;

use Magento\Framework\DataObject;

class CreditmemoRegisterBeforeObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Calculate the Gift Card refund amount
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\CreditmemoRegisterBeforeObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $this->_request;
        $input = $this->_request->getParam('creditmemo');
        $action = $observer->getEvent()->getControllerAction();

        $creditmemo = $observer['creditmemo'];
        $order = $creditmemo->getOrder();

        if (($order->getGiftVoucherDiscount() > 0 || $order->getUseGiftCreditAmount() > 0)
            && $this->_priceCurrency->round($creditmemo->getGrandTotal()) <= 0) {
            $creditmemo->setAllowZeroGrandTotal(true);
        }

        if (isset($input['giftcard_refund'])) {
            $refund = $input['giftcard_refund'];
            if ($refund < 0) {
                return;
            }

            $creditmemo = $observer->getEvent()->getCreditmemo();
            $maxAmount = 0;
            if ($creditmemo->getUseGiftCreditAmount()
                && $this->_helperData->getGeneralConfig('enablecredit', $creditmemo->getStoreId())) {
                $maxAmount += floatval($creditmemo->getUseGiftCreditAmount());
            }
            if ($creditmemo->getGiftVoucherDiscount()) {
                $maxAmount += floatval($creditmemo->getGiftVoucherDiscount());
            }

            if ($action == 'adminhtml_sales_order_creditmemo_updateQty') {
                $creditmemo->setGiftcardRefundAmount($maxAmount);
            } else {
                $creditmemo->setGiftcardRefundAmount(min(floatval($refund), $maxAmount));
            }
        }
    }
}
