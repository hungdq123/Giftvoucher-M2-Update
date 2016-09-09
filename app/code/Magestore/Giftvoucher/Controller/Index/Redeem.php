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

namespace Magestore\Giftvoucher\Controller\Index;

use Magento\Customer\Model\Session;

/**
 * Giftvoucher Index Redeem Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Redeem extends \Magestore\Giftvoucher\Controller\Action
{
    
    public function execute()
    {
        if (!$this->customerLoggedIn()) {
            $this->_redirect("customer/account/login");
            return;
        }
        if (!$this->getHelper()->getGeneralConfig('enablecredit')) {
            $this->_redirect("giftvoucher/index/index");
            return;
        }
        $code = $this->getRequest()->getParam('giftvouchercode');

        $max = $this->getHelper()->getGeneralConfig('maximum');
        $giftCardSession = $this->_objectManager->create('Magestore\Giftvoucher\Model\Session');

        if ($code) {
            $giftVoucher = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->loadByCode($code);
            
            $codes = $giftCardSession->getCodes();
            if (!$this->getHelper()->isAvailableToAddCode()) {
                $this->messageManager->addError(__('The maximum number of times to enter gift codes is %1!', $max));
                $this->_redirect("giftvoucher/index/index");
                return;
            }
            if (!$giftVoucher->getId()) {
                $codes[] = $code;
                $codes = array_unique($codes);
                $giftCardSession->setCodes($codes);
                $errorMessage = __('Gift card "%1" is invalid.', $code);
                if ($max) {
                    $errorMessage .= __('You have %1 time(s) remaining to re-enter Gift Card code.', $max - count($codes));
                }
                $this->messageManager->addError($errorMessage);
                $this->_redirect("giftvoucher/index/addredeem");
                return;
            } else {
                //Hai.Tran
                $conditions = $giftVoucher->getConditionsSerialized();
                if (!empty($conditions)) {
                    $conditions = unserialize($conditions);
                    if (is_array($conditions) && !empty($conditions)) {
                        if (!$this->getHelper()->getGeneralConfig('credit_condition')
                            && isset($conditions['conditions']) && $conditions['conditions']) {
                            $this->messageManager->addError(__('Gift code "%1" has usage conditions, you cannot redeem it to Gift Card credit', $code));
                            $this->_redirect("giftvoucher/index/addredeem");
                            return;
                        }
                    }
                }
                $actions = $giftVoucher->getActionsSerialized();
                if (!empty($actions)) {
                    $actions = unserialize($actions);
                    if (is_array($actions) && !empty($actions)) {
                        if (!$this->getHelper()->getGeneralConfig('credit_condition')
                            && isset($actions['conditions']) && $actions['conditions']) {
                            $this->messageManager->addError(__('Gift code "%1" has usage conditions, you cannot redeem it to Gift Card credit', $code));
                            $this->_redirect("giftvoucher/index/addredeem");
                            return;
                        }
                    }
                }
               
                //End Hai.Tran
                if (!$this->getHelper()->canUseCode($giftVoucher)) {
                    $this->messageManager->addError(__('The gift code usage has exceeded the number of users allowed.'));
                    return $this->_redirect("giftvoucher/index/index");
                }
                $customer = $this->getModel('Magento\Customer\Model\Session')->getCustomer();
                if ($giftVoucher->getBalance() == 0) {
                    $this->messageManager->addError(__('%1 - The current balance of this gift code is 0.', $code));
                    $this->_redirect("giftvoucher/index/addredeem");
                    return;
                }
                if ($giftVoucher->getStatus() != 2 && $giftVoucher->getStatus() != 4) {
                    $this->messageManager->addError(__('Gift code "%1" is not avaliable', $code));
                    $this->_redirect("giftvoucher/index/addredeem");
                    return;
                } else {
                    $balance = $giftVoucher->getBalance();

                    $credit = $this->getModel('Magestore\Giftvoucher\Model\Credit')->getCreditAccountLogin();
                    $creditCurrencyCode = $credit->getCurrency();
                    $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
                    if (!$creditCurrencyCode) {
                        $creditCurrencyCode = $baseCurrencyCode;
                        $credit->setCurrency($creditCurrencyCode);
                        $credit->setCustomerId($customer->getId());
                    }

                    $voucherCurrency = $this->getModel('Magento\Directory\Model\Currency')
                        ->load($giftVoucher->getCurrency());
                    $baseCurrency = $this->getModel('Magento\Directory\Model\Currency')->load($baseCurrencyCode);
                    $creditCurrency = $this->getModel('Magento\Directory\Model\Currency')->load($creditCurrencyCode);
                    
                    if ($creditCurrencyCode != $giftVoucher->getCurrency()) {
                        //$amount_temp = $balance * $balance / $baseCurrency->convert($balance, $voucherCurrency);
                        //$amount = $baseCurrency->convert($amount_temp, $creditCurrency);
                        $rate = $this->getHelper()->getRateToCurrentCurrency($voucherCurrency, $creditCurrency);
                        $amount = $balance * $rate;
                    } else {
                        $amount =$balance;
                    }
                    $credit->setBalance($credit->getBalance() + $amount);
                    $nowTime = date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                    $credithistory = $this->getModel('Magestore\Giftvoucher\Model\Credithistory')
                            ->setCustomerId($customer->getId())
                            ->setAction('Redeem')
                            ->setCurrencyBalance($credit->getBalance())
                            ->setGiftcardCode($giftVoucher->getGiftCode())
                            ->setBalanceChange($amount)
                            ->setCurrency($credit->getCurrency())
                            ->setCreatedDate($nowTime);
                    $history = $this->getModel('Magestore\Giftvoucher\Model\History')->setData(array(
                        'order_increment_id' => '',
                        'giftvoucher_id' => $giftVoucher->getId(),
                        'created_at' => $nowTime,
                        'action' => \Magestore\Giftvoucher\Model\Actions::ACTIONS_REDEEM,
                        'amount' => $balance,
                        'balance' => 0.0,
                        'currency' => $giftVoucher->getCurrency(),
                        'status' => $giftVoucher->getStatus(),
                        'order_amount' => '',
                        'comments' => __('Redeem to Gift Card credit balance'),
                        'extra_content' => __('Redeemed by %1', $customer->getName()),
                        'customer_id' => $customer->getId(),
                        'customer_email' => $customer->getEmail(),
                    ));

                    try {
                        $giftVoucher->setBalance(0)
                            ->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_USED)
                            ->save();
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        $this->_redirect("giftvoucher/index/addredeem");
                        return;
                    }

                    try {
                        $credit->save();
                    } catch (\Exception $e) {
                        $giftVoucher->setBalance($balance)
                            ->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE)
                            ->save();
                        $this->messageManager->addError($e->getMessage());
                        $this->_redirect("giftvoucher/index/addredeem");
                        return;
                    }
                    try {
                        $history->save();
                        $credithistory->save();
                        $this->messageManager->addSuccess(__('Gift card "%1" was successfully redeemed', $code));
                        $this->_redirect("giftvoucher/index/index");
                        return;
                    } catch (\Exception $e) {
                        $giftVoucher->setBalance($balance)
                            ->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE)
                            ->save();
                        $credit->setBalance($credit->getBalance() - $amount)->save();
                        $this->messageManager->addError($e->getMessage());
                        $this->_redirect("giftvoucher/index/addredeem");
                        return;
                    }
                }
            }
        }

        $this->_redirect("giftvoucher/index/index");
    }
}
