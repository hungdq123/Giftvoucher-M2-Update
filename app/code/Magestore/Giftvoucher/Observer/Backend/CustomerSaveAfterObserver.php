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

class CustomerSaveAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Update the Gift Card credit to customer's account
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId()) {
            return;
        }
        $balance = $this->_request->getParam('change_balance');
        if (!is_numeric($balance) || $balance == 0) {
            return;
        }
        $credit = $this->_objectManager->get('Magestore\Giftvoucher\Model\Credit')
                    ->getCreditByCustomerId($customer->getId());
        if (!$credit->getCurrency()) {
            $currency = $this->_storeManager->getStore()->getDefaultCurrencyCode();
            $credit->setCurrency($currency);
            $credit->setCustomerId($customer->getId());
        }
        $credit->setBalance($credit->getBalance() + $balance);

        $credithistory = $this->_objectManager->get('Magestore\Giftvoucher\Model\Credithistory')
                ->setCustomerId($customer->getId())
                ->setAction('Adminupdate')
                ->setCurrencyBalance($credit->getBalance())
                ->setBalanceChange($balance)
                ->setCurrency($credit->getCurrency())
                ->setCreatedDate(date('Y-m-d'));
        try {
            $credit->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        try {
            $credithistory->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $credit->setBalance($credit->getBalance() - $balance)->save();
            $this->messageManager->addError(__($e->getMessage()));
        }
        return;
    }
}
