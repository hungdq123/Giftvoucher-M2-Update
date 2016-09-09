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
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Giftvoucher\Controller\Adminhtml\Gifttemplate;

/**
 * Adminhtml Gifttemplate Previewimage Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Previewimage extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultLayoutFactory;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\DataObject $dataObject
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_objectManager = $objectManager;
        $this->_dataObject = $dataObject;
    }


    public function execute()
    {
        $imageName = $this->getRequest()->getParam('value');
        $data = $this->getRequest()->getParam('form_data');
        $template = $this->_dataObject->setData(json_decode($data, true));
        $template->setImages($imageName);
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $registryObject->register('gifttemplate_data', $template);

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('gifttemplate_previewimage');
        return $resultLayout;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
