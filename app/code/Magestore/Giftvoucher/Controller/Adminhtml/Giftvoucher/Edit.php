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
 * Adminhtml Giftvoucher Edit Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Edit extends \Magento\Backend\App\Action
{
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This gift codes no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }
        if ($model->getData('balance')) {
            $model->setData('balance', number_format($model->getData('balance'), 2, null, ''));
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('giftvoucher_data', $model);
        
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Gift Code Manager'), __('Gift Code Manager'))
            ->addBreadcrumb(__('Gift Code News'), __('Gift Code News'))
            ->setActiveMenu('Magestore_Giftvoucher::giftvoucher');
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Code'));
        if ($model->getId()) {
            $resultPage->getConfig()->getTitle()->prepend($model->getGiftCode());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Gift Code'));
        }

        return $resultPage;

    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
