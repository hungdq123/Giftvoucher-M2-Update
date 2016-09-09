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
namespace Magestore\Giftvoucher\Observer\Frontend;

use Magento\Framework\DataObject;

class CartIndexActionObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Show Gift Card notification in Cart page
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        if ($session->getQuote()->getCouponCode() && !$this->_helperData->getGeneralConfig('use_with_coupon')
            && ($session->getUseGiftCreditAmount() > 0 || $session->getGiftVoucherDiscount() > 0)) {
            $this->messageManager->addNotice(__('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.'));
            $session->setMessageApplyGiftcardWithCouponCode(false);
        }
        $session->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
    }
}
