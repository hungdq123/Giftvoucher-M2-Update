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

class CouponPostActionObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Apply gift codes to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\Frontend\CouponPostActionObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $quote = $session->getQuote();
        $code = trim($action->getRequest()->getParam('coupon_code'));

        if (!$code) {
            return;
        }

        if (!$this->_helperData->isAvailableToAddCode()) {
            return;
        }
        $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->loadByCode($code);

        if ($giftVoucher->getId() && !$giftVoucher->getSetId() && $giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
            && $giftVoucher->getBaseBalance() > 0 && $giftVoucher->validate($quote->setQuote($quote))
        ) {
            if ($quote->getCouponCode() && !$this->_helperData->getGeneralConfig('use_with_coupon')) {
                $this->messageManager->addNotice(__('A coupon code has been used. You cannot apply gift codes with the coupon to get discount.'));
            } else {
                $count = 0;
                $items = $quote->getAllItems();
                foreach ($items as $item) {
                    $data = $item->getData();
                    if ($data['product_type'] == 'giftvoucher') {
                        $count++;
                    }
                }
                if ($count == count($items)) {
                    $this->messageManager->addNotice(__('Gift Cards cannot be used to purchase Gift Card products'));
                } else {
                    $giftVoucher->addToSession($session);
                    $session->setUseGiftCard(1);
                    $this->messageManager->addSuccess(
                        __('Gift code "%1" was applied successfully.', $this->_helperData->getHiddenCode($giftVoucher->getGiftCode()))
                    );
                }
            }
            $action->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setRedirect($this->_urlBuilder->getUrl('checkout/cart'));
        } else {
            if (!$this->_helperData->getGeneralConfig('use_with_coupon') && ($session->getUseGiftCreditAmount() > 0
                || $session->getGiftVoucherDiscount() > 0)) {
                if ($session->getUseGiftCardCredit() && $session->getUseGiftCard()) {
                    $this->messageManager->addNotice(__('You cannot apply a coupon code with either gift codes or Gift Card credit at once to get discount.'));
                } elseif ($session->getUseGiftCard()) {
                    $this->messageManager->addNotice(__('The gift code(s) has been used. You cannot apply a coupon code with gift codes to get discount.'));
                } elseif ($session->getUseGiftCardCredit()) {
                    $this->messageManager->addNotice(__('An amount in your Gift Card credit has been used. You cannot apply a coupon code with Gift Card credit to get discount.'));
                }
                $action->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $action->getResponse()->setRedirect($this->_urlBuilder->getUrl('checkout/cart'));
            }
        }
        return;
    }
}
