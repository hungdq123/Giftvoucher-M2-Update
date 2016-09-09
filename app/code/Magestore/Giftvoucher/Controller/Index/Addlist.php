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
 * Giftvoucher Index Addlist Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Addlist extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        if (!$this->customerLoggedIn()) {
            $this->_redirect("customer/account/login");
            return;
        }
        $code = $this->getRequest()->getParam('giftvouchercode');

        $max = $this->getHelper()->getGeneralConfig('maximum');
        $nowTime = date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        if ($code) {
            $giftVoucher = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->loadByCode($code);
            $codes = $this->getSingleton('Magestore\Giftvoucher\Model\Session')->getCodes();
            if (!$this->getHelper()->isAvailableToAddCode()) {
                $this->messageManager->addError(__('The maximum number of times to enter gift codes is %d!', $max));
                $this->_redirect("giftvoucher/index/index");
                return;
            }
            if (!$giftVoucher->getId()) {
                $codes[] = $code;
                $codes = array_unique($codes);
                $this->getSingleton('Magestore\Giftvoucher\Model\Session')->setCodes($codes);
                $errorMessage = __('Gift code "%1" is invalid.', $code);
                if ($max) {
                    $errorMessage .= __('You have %1 time(s) remaining to re-enter Gift Card code.', $max - count($codes));
                }
                $this->messageManager->addError($errorMessage);
                $this->_redirect("giftvoucher/index/addredeem");
                return;
            } else {
                if (!$this->getHelper()->canUseCode($giftVoucher)) {
                    $this->messageManager->addError(__('The gift code usage has exceeded the number of users allowed.'));
                    return $this->_redirect("giftvoucher/index/index");
                }
                $customer = $this->getSingleton('Magento\Customer\Model\Session')->getCustomer();
                $collection = $this->getModel('Magestore\Giftvoucher\Model\Customervoucher')->getCollection();
                $collection->addFieldToFilter('customer_id', $customer->getId())
                        ->addFieldToFilter('voucher_id', $giftVoucher->getId());
                if ($collection->getSize()) {
                    $this->messageManager->addError(__('This gift code has already existed in your list.'));
                    $this->_redirect("giftvoucher/index/addredeem");
                    return;
                } elseif ($giftVoucher->getStatus() != 1 && $giftVoucher->getStatus() != 2
                    && $giftVoucher->getStatus() != 4) {
                    $this->messageManager->addError(__('Gift code "%s" is not avaliable', $code));
                    $this->_redirect("giftvoucher/index/addredeem");
                    return;
                } else {
                    $model = $this->getModel('Magestore\Giftvoucher\Model\Customervoucher')
                            ->setCustomerId($customer->getId())
                            ->setVoucherId($giftVoucher->getId())
                            ->setAddedDate($nowTime);
                    try {
                        $model->save();
                        $this->messageManager->addSuccess(__('The gift code has been added to your list successfully.'));
                        $this->_redirect("giftvoucher/index/index");
                        return;
                    } catch (\Exception $e) {
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
