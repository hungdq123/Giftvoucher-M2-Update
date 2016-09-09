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
 * Giftvoucher Checkout Addgift Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Addgift extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        $session = $this->getModel('Magento\Checkout\Model\Session');
        $giftvoucherSession = $this->getModel('Magestore\Giftvoucher\Model\Session');
        $helper = $this->getHelperData();
        $quote = $session->getQuote();
        $result = array();
        if ($quote->getCouponCode() && !$helper->getGeneralConfig('use_with_coupon')
            && (!$session->getUseGiftCreditAmount() || !$session->getGiftVoucherDiscount())) {
            $result['notice'] = __('A coupon code has been used. You cannot apply gift codes with the coupon to get discount.');
        } else {
            $addCodes = array();
            if ($code = trim($this->getRequest()->getParam('code'))) {
                $addCodes[] = $code;
            }
            if ($code = trim($this->getRequest()->getParam('addcode'))) {
                $addCodes[] = $code;
            }
            
            $max = $helper->getGeneralConfig('maximum');
            $codes = $giftvoucherSession->getCodes();
            
            if (!count($addCodes)) {
                $errorMessage = __('Invalid gift code. Please try again. ');
                if ($max) {
                    $errorMessage .= __('You have %1 time(s) remaining to re-enter Gift Card code.', $max - count($codes));
                }
                $result['error'] = $errorMessage;
                return $this->getResponse()->setBody(\Zend_Json::encode($result));
            }
            if (!$helper->isAvailableToAddCode()) {
                $result['html'] = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
                return $this->getResponse()->setBody(\Zend_Json::encode($result));
            }
            
            foreach ($addCodes as $code) {
                $giftVoucher = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->loadByCode($code);

                if (!$giftVoucher->getId()) {
                    $codes[] = $code;
                    $codes = array_unique($codes);
                    $giftvoucherSession->setCodes($codes);
                    if (isset($errorMessage)) {
                        $result['error'] = $errorMessage . '<br/>';
                    } elseif (isset($result['error'])) {
                        $result['error'] .= '<br/>';
                    } else {
                        $result['error'] = '';
                    }
                    $errorMessage = __('Gift card "%1" is invalid.', $code);
                    $maxErrorMessage = '';

                    if ($max) {
                        $maxErrorMessage = __('You have %1 times left to enter gift codes.', $max - count($codes));
                    }
                    $result['error'] .= $errorMessage . ' ' . $maxErrorMessage;
                } elseif ($giftVoucher->getId() && $giftVoucher->getBaseBalance() > 0
                    && $giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                    && $giftVoucher->validate($quote->setQuote($quote))
                ) {
                    if ($helper->canUseCode($giftVoucher)) {
                        $giftVoucher->addToSession($session);
                        $updatepayment = ($quote->getGrandTotal() < 0.001);
                        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                        if ($updatepayment xor ( $quote->getGrandTotal() < 0.001)) {
                            $result['updatepayment'] = 1;
                        } else {
                            $result['success'] = __(
                                'Gift Voucher "%1" was applied to your order.',
                                $helper->getHiddenCode($giftVoucher->getGiftCode())
                            );
                            if ($giftVoucher->getCustomerId() == $this->getCusomterSessionModel()->getCustomerId()
                                && $giftVoucher->getRecipientName() && $giftVoucher->getRecipientEmail()
                                && $giftVoucher->getCustomerId()
                            ) {
                                if (!isset($result['notice'])) {
                                    $result['notice'] = '';
                                } else {
                                    $result['notice'] .= '<br/>';
                                }
                                $result['notice'] .= __('Please note that gift code "%1" has been sent to your friend. When using, both you and your friend will share the same balance in the gift code.', $helper->getHiddenCode($code));
                            }
                            $result['html'] = 1;
                        }
                    } else {
                        if (!isset($result['error'])) {
                            $result['error'] = '';
                        } else {
                            $result['error'] .= '<br/>';
                        }
                        $result['error'] .= __('This gift code limits the number of users', $code);
                    }
                } else {
                    if (isset($errorMessage)) {
                        $result['error'] = $errorMessage . '<br/>';
                    } elseif (isset($result['error'])) {
                        $result['error'] .= '<br/>';
                    } else {
                        $result['error'] = '';
                    }
                    $result['error'] .= __('Gift code "%1" is no longer available to use.', $code);
                }
            }
            $result['html'] = $this->getModel('Magestore\Giftvoucher\Block\Payment\Form')->getAllGiftvoucherData();
        }
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
