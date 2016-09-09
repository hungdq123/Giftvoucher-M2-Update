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
namespace Magestore\Giftvoucher\Observer\Backend;

use Magento\Framework\DataObject;

class ClearAdminCheckoutSessionObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Clear admin checkout session
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_checkoutSession
            ->setUseGiftCard(null)
            ->setGiftCodes(null)
            ->setBaseAmountUsed(null)
            ->setBaseGiftVoucherDiscount(null)
            ->setGiftVoucherDiscount(null)
            ->setCodesBaseDiscount(null)
            ->setCodesDiscount(null)
            ->setGiftMaxUseAmount(null)
            ->setUseGiftCardCredit(null)
            ->setMaxCreditUsed(null)
            ->setBaseUseGiftCreditAmount(null)
            ->setUseGiftCreditAmount(null);
    }
}
