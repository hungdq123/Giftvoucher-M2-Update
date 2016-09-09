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
 * Adminhtml Giftvoucher Delete Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
    ) {
        parent::__construct($context);
        $this->_giftvoucher = $giftvoucher;
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $this->_giftvoucher->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $this->messageManager->addSuccess(__('Gift Code was successfully deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
