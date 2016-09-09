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
 * Giftvoucher Index SendEmail Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class SendEmail extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $data['giftcard_id'];
            $giftCard = $this->getModel('Magestore\Giftvoucher\Model\Giftvoucher')->load($id);
            
            $customer = $this->getSingleton('Magento\Customer\Model\Session')->getCustomer();
            if (!$customer || ($giftCard->getCustomerId() != $customer->getId()
                && $giftCard->getCustomerEmail() != $customer->getEmail())
            ) {
                $this->messageManager->addError(__('The Gift Card email has been failed to send.'));
                return $this->_redirect('*/*/');
            }
            
            $giftCard->setNotResave(true);
            foreach ($data as $key => $value) {
                if ($value) {
                    $giftCard->setData($key, $value);
                }
            }
            
            try {
                $giftCard->save();
            } catch (\Exception $ex) {
                $this->messageManager->addError($ex->getMessage());
            }
            if ($giftCard->sendEmailToRecipient()) {
                $this->messageManager->addSuccess(__('The Gift Card email has been sent successfully.'));
            } else {
                $this->messageManager->addError(__('The Gift Card email cannot be sent to your friend!'));
            }
            //$translate->setTranslateInline(true);
        }
        $this->_redirect('*/*/');
    }
}
