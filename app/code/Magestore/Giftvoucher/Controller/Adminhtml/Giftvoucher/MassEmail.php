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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher;

/**
 * Adminhtml Giftvoucher MassEmail Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class MassEmail extends \Magento\Backend\App\Action
{
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select Gift Code(s)'));
        } else {
            $giftvoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher');
            try {
                $totalEmailSent = 0;
                foreach ($ids as $id) {
                    $giftvoucher->load($id);
                    $giftvoucher->setMassEmail(true);
                    $emailSent = (int) $giftvoucher->sendEmail()->getEmailSent();
                    if ($emailSent) {
                        $totalEmailSent += $emailSent;
                    }
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 Gift Code with %2 email(s) were successfully sent.', count($ids), $totalEmailSent)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
