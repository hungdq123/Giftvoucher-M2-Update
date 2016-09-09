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
 * Giftvoucher Index Remove Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Remove extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        if (!$this->getSingleton('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->_redirect("customer/account/login");
            return;
        }
        $customerVoucherId = $this->getRequest()->getParam('id');
        $voucher = $this->getModel('Magestore\Giftvoucher\Model\Customervoucher')->load($customerVoucherId);
        if ($voucher->getCustomerId() == $this->getSingleton('Magento\Customer\Model\Session')->getCustomer()->getId()
        ) {
            try {
                $voucher->delete();
                $this->messageManager->addSuccess(__('Gift card was successfully removed'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect("giftvoucher/index/index");
    }
}
