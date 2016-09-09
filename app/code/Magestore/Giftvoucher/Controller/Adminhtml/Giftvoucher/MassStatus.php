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
 * Adminhtml Giftvoucher MassStatus Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class MassStatus extends \Magento\Backend\App\Action
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
            $authSession = $this->_objectManager->create('Magento\Backend\Model\Auth\Session');
            try {
                $cnt = 0;
                foreach ($ids as $id) {
                    $giftvoucher->load($id);
                    if ($giftvoucher->getStatus() < \Magestore\Giftvoucher\Model\Status::STATUS_EXPIRED) {
                        $giftvoucher->setStatus($this->getRequest()->getParam('status'));
                        $giftvoucher->setIsMassupdate(true)
                                ->setAction(\Magestore\Giftvoucher\Model\Actions::ACTIONS_MASS_UPDATE)
                                ->setExtraContent(__('Mass status updated by %1', $authSession->getUser()->getUsername()))
                                ->setIncludeHistory(true)
                                ->save();
                        $cnt++;
                    }
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', $cnt)
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
