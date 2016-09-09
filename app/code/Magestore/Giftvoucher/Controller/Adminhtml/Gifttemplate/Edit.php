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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Gifttemplate;

/**
 * Adminhtml Gifttemplate Edit Action
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
    protected $resultPageFactory;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }


    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError('Gift Card Template is not exist.');
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $data = $this->_objectManager->create('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        $registryObject->register('gifttemplate_data', $model);
        $resultPage = $this->resultPageFactory->create();
        if ($model->getId()) {
            $resultPage->getConfig()->getTitle()->prepend($model->getTemplateName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Gift Card Template'));
        }
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
