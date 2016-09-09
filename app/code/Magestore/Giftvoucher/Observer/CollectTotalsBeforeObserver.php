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

class CollectTotalsBeforeObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Set Quote information about Gift Card discount
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $quote = $observer->getEvent()->getQuote();
        if ($quote->getCouponCode() && !$this->_helperData->getGeneralConfig('use_with_coupon')
            && ($session->getUseGiftCreditAmount() > 0 || $session->getGiftVoucherDiscount() > 0)) {
            if ($session->getUseGiftCard()) {
                $session->setUseGiftCard(null)
                        ->setGiftCodes(null)
                        ->setBaseAmountUsed(null)
                        ->setBaseGiftVoucherDiscount(null)
                        ->setGiftVoucherDiscount(null)
                        ->setCodesBaseDiscount(null)
                        ->setCodesDiscount(null)
                        ->setGiftMaxUseAmount(null);
            }
            if ($session->getUseGiftCardCredit()) {
                $session->setUseGiftCardCredit(null)
                        ->setMaxCreditUsed(null)
                        ->setBaseUseGiftCreditAmount(null)
                        ->setUseGiftCreditAmount(null);
            }

            $session->setMessageApplyGiftcardWithCouponCode(true);
        }

        if ($codes = $session->getGiftCodes()) {
            $codesArray = array_unique(explode(',', $codes));
            foreach ($codesArray as $key => $value) {
                $codesArray[$key] = 0;
            }
            $session->setBaseAmountUsed(implode(',', $codesArray));
        } else {
            $session->setBaseAmountUsed(null);
        }
        $session->setBaseGiftVoucherDiscount(0);
        $session->setGiftVoucherDiscount(0);
        $session->setUseGiftCreditAmount(0);

        foreach ($quote->getAllAddresses() as $address) {
            $address->setGiftcardCreditAmount(0);
            $address->setBaseUseGiftCreditAmount(0);
            $address->setUseGiftCreditAmount(0);

            $address->setBaseGiftVoucherDiscount(0);
            $address->setGiftVoucherDiscount(0);

            $address->setGiftvoucherBaseHiddenTaxAmount(0);
            $address->setGiftvoucherHiddenTaxAmount(0);

            $address->setMagestoreBaseDiscount(0);
            $address->setMagestoreBaseDiscountForShipping(0);

            foreach ($address->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $child->setBaseGiftVoucherDiscount(0)
                                ->setGiftVoucherDiscount(0)
                                ->setBaseUseGiftCreditAmount(0)
                                ->setMagestoreBaseDiscount(0)
                                ->setUseGiftCreditAmount(0);
                    }
                } elseif ($item->getProduct()) {
                    $item->setBaseGiftVoucherDiscount(0)
                            ->setGiftVoucherDiscount(0)
                            ->setBaseUseGiftCreditAmount(0)
                            ->setMagestoreBaseDiscount(0)
                            ->setUseGiftCreditAmount(0);
                }
            }
        }
    }
}
