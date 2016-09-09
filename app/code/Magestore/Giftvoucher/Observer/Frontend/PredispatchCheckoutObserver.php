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

class PredispatchCheckoutObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Disable Gift Card multishipping
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\Frontend\PredispatchCheckoutObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Session');

        $result = $this->_objectManager->create('Magento\Framework\DataObject');
        
        $items = $cart->getQuote()->getAllItems();
        foreach ($items as $item) {
            $code = 'recipient_ship';
            $codeSendFriend = 'send_friend';
            $option = $item->getOptionByCode($code);
            $option2 = $item->getOptionByCode($codeSendFriend);
            if ($option && $option2) {
                $data = $option->getData();
            }

            if (isset($data['value']) && $data['value'] != null) {
                $result->setData(
                    'error_messages',
                    __('You need to add your friend\'s address as the shipping address. We will send this gift card to that address.')
                );
                return $this->resultJsonFactory->create()->setData($result->getData());
            }
        }

        if ($cart->getQuote()->getCouponCode() && !$this->_helperData->getGeneralConfig('use_with_coupon')
            && ($cart->getUseGiftCreditAmount() > 0 || $cart->getGiftVoucherDiscount() > 0)) {
            $this->_sessionManager->setMessageApplyGiftcardWithCouponCode(false);
            $this->messageManager->addNotice(__('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.'));
        }
        return;
    }
}
