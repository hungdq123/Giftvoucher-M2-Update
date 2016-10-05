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

namespace Magestore\Giftvoucher\Controller\Checkout;

use Magento\Customer\Model\Session;

/**
 * Giftvoucher Checkout GiftcardPost Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class GiftcardPost extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $helper = $this->getHelperData();
        $session = $helper->getCheckoutSession();
        $customerSession = $this->getCusomterSessionModel();
        $customerId = $customerSession->getCustomerId();
        $credit = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credit')->load($customerId, 'customer_id');
        $session->setGiftcreditBalance(floatval($credit->getBalance()));
        if ($session->getQuote()->getCouponCode() && !$helper->getGeneralConfig('use_with_coupon')) {
            $this->messageManager->addNotice(__('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.'));
        } else {
            $request = $this->getRequest();
            if ($request->isPost()) {
                if ($request->getParam('giftvoucher_credit') && $helper->getGeneralConfig('enablecredit')) {
                    $session->setUseGiftCardCredit(1);
                    $session->setMaxCreditUsed(
                        floatval($request->getParam('credit_amount') / $this->priceCurrency->convert(1, false, false))
                    );
                    $this->messageManager->addSuccess(__('Your Gift Credit has been applied successfully.'));
                } else {
                    if ($session->getUseGiftCardCredit()) {
                        $this->messageManager->addSuccess(__('Your Gift Credit information has been removed successfully.'));
                    }
                    $session->setUseGiftCardCredit(0);
                    $session->setMaxCreditUsed(null);
                }
                if ($request->getParam('giftvoucher')) {
                    $session->setUseGiftCard(1);
                    $giftcodesAmount = $request->getParam('giftcodes');
                    if (count($giftcodesAmount)) {
                        $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
                        if (!is_array($giftMaxUseAmount)) {
                            $giftMaxUseAmount = array();
                        }
                        $giftMaxUseAmount = array_merge($giftMaxUseAmount, $giftcodesAmount);
                        $session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
                    }
                    $addcodes = array();
                    if ($request->getParam('existed_giftvoucher_code')) {
                        $addcodes[] = trim($request->getParam('existed_giftvoucher_code'));
                    }
                    if ($request->getParam('giftvoucher_code')) {
                        $addcodes[] = trim($request->getParam('giftvoucher_code'));
                    }
                    if (count($addcodes)) {
                        $max = $helper->getGeneralConfig('maximum');
                        if ($helper->isAvailableToAddCode()) {
                            foreach ($addcodes as $code) {
                                $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                                                ->loadByCode($code);
                                $quote = $session->getQuote();
                                if (!$giftVoucher->getId() || $giftVoucher->getSetId()) {
                                    $codes = $this->_objectManager->get('Magestore\Giftvoucher\Model\Session')
                                                ->getCodes();
                                    $codes[] = $code;
                                    $this->_objectManager->get('Magestore\Giftvoucher\Model\Session')
                                            ->setCodes(array_unique($codes));
                                    if($giftVoucher->getSetId()){
                                        $this->messageManager->addError(__('Gift card is invalid.'));
                                    }else{
                                        $this->messageManager->addError(__('Gift card "%1" is invalid.', $code));
                                    }

                                    if ($max - count($codes)) {
                                        $this->messageManager->addError(__('You have %1 time(s) remaining to re-enter your Gift Card code.', $max - count($codes)));
                                    }
                                } elseif ($giftVoucher->getBaseBalance() > 0
                                    && $giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                                ) {
                                    if ($helper->canUseCode($giftVoucher)) {
                                        $flag = false;
                                        foreach ($quote->getAllItems() as $item) {
                                            if ($giftVoucher->getActions()->validate($item)) {
                                                $flag = true;
                                            }
                                        }
                                        $giftVoucher->addToSession($session);
                                        if ($giftVoucher->getCustomerId() && $giftVoucher->getRecipientEmail()
                                            && $giftVoucher->getCustomerId() == $customerId
                                            && $giftVoucher->getRecipientName()
                                        ) {
                                            $this->messageManager->addNotice(__('Please note that gift code "%1" has been sent to your friend. When using, both you and your friend will share the same balance in the gift code.', $helper->getHiddenCode($code)));
                                        }
                                        if ($flag && $giftVoucher->validate($quote->setQuote($quote))) {
                                            $isGiftVoucher = true;
                                            foreach ($quote->getAllItems() as $item) {
                                                if ($item->getProductType() != 'giftvoucher') {
                                                    $isGiftVoucher = false;
                                                    break;
                                                }
                                            }
                                            if (!$isGiftVoucher) {
                                                $this->messageManager->addSuccess(__('Gift code "%1" has been applied successfully.', $helper->getHiddenCode($code)));
                                            } else {
                                                $this->messageManager->addNotice(__('Please remove your Gift Card information since you cannot use either gift codes or Gift Card credit balance to purchase other Gift Card products.'));
                                            }
                                        } else {
                                            $this->messageManager->addError(__('You can’t use this gift code since its conditions haven’t been met. <p>Please check these conditions by entering your gift code <a href="' . $this->_url->getUrl('giftvoucher/index/check') . '">here</a>.'));
                                        }
                                    } else {
                                        $this->messageManager->addError(__('This gift code limits the number of users', $helper->getHiddenCode($code)));
                                    }
                                } else {
                                    $this->messageManager->addError(__('Gift code "%1" is no longer available to use.', $code));
                                }
                            }
                        } else {
                            $this->messageManager->addError(__('The maximum number of times to enter gift codes is %1!', $max));
                        }
                    } else {
                        $this->messageManager->addSuccess(__('Your Gift Card(s) has been applied successfully.'));
                    }
                } elseif ($session->getUseGiftCard()) {
                    $session->setUseGiftCard(null);
                    $this->messageManager->addSuccess(__('Your Gift Card information has been removed successfully.'));
                }
            }
        }
        $session->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $session->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
        $this->_objectManager->get('Magento\Quote\Model\QuoteRepository')->save($session->getQuote());
        $this->_redirect('checkout/cart');
    }
}
