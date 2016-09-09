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

class ApplyWithCouponObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Show the Gift Card notification when creating order in the back-end
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\Backend\ApplyWithCouponObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $session = $this->_checkoutSession;
        $adminSession = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
        $params = $action->getRequest()->getPost();
        if (isset($params['order']['coupon']['code']) && $params['order']['coupon']['code'] != null) {
            $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                                ->loadByCode($params['order']['coupon']['code']);
            $quote = $adminSession->getQuote();
            if ($giftVoucher->getId() && $giftVoucher->getBaseBalance() > 0
                && $giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
            ) {
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
                    unset($params->order['coupon']);
                    $action->getRequest()->setPost($params);
                } else {
                    $giftVoucher->addToSession($session);
                    $session->setUseGiftCard(1);
                    if ($giftVoucher->validate($quote->setQuote($quote))) {
                        $this->messageManager->addSuccess(__('Gift code "%1" was applied successfully.', $giftVoucher->getGiftCode()));
                    } else {
                        $this->messageManager->addNotice(__('You can’t use this gift code since its conditions haven’t been met.'));
                    }
                    unset($params->order['coupon']);
                }
            } else {
                if (!$this->_helperData->getGeneralConfig('use_with_coupon')
                    && ($session->getUseGiftCreditAmount() > 0 || $session->getGiftVoucherDiscount() > 0)) {
                    if ($session->getUseGiftCardCredit() && $session->getUseGiftCard()) {
                        $this->messageManager->addNotice(__('You cannot apply a coupon code with either gift codes or Gift Card credit at once to get discount.'));
                    } elseif ($session->getUseGiftCard()) {
                        $this->messageManager->addNotice(__('The gift code(s) has been used. You cannot apply a coupon code with gift codes to get discount.'));
                    } elseif ($session->getUseGiftCardCredit()) {
                        $this->messageManager->addNotice(__('An amount in your Gift Card credit has been used. You cannot apply a coupon code with Gift Card credit to get discount.'));
                    }
                    unset($params->order['coupon']);
                    $action->getRequest()->setPost($params);
                }
            }
        }
        return;
    }
}
