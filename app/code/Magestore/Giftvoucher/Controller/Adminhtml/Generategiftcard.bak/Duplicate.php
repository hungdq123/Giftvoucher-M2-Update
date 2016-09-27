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
namespace Magestore\Giftvoucher\Controller\Adminhtml\Generategiftcard;

/**
 * Adminhtml Generategiftcard Duplicate Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Duplicate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magestore\Giftvoucher\Model\Generategiftcard
     */
    protected $_generategiftcardModel;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcardModel
    ) {
        parent::__construct($context);
        $this->_generategiftcardModel = $generategiftcardModel;
    }

    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParam('id')) {
            $model = $this->_generategiftcardModel;

            $data = $model->load($this->getRequest()->getParam('id'))->getData();
            $data['is_generated'] = 0;
            unset($data['template_id']);

            $model->setData($data);

            try {
                $model->save();

                $this->messageManager->addSuccess(__('The pattern has been duplicated successfully.'));
                $this->_getSession()->setFormData(false);

                return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId()));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->messageManager->addError(__('Unable to find Template to duplicate'));
        return $resultRedirect->setPath('*/*/');
    }
}
