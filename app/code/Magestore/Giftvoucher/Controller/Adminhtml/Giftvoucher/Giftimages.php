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
 * Adminhtml Giftvoucher Giftimages Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftimages extends \Magento\Backend\App\Action
{
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Giftvoucher\Model\Gifttemplate $gifttemplate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Giftvoucher\Model\Gifttemplate $gifttemplate
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $templateId = $this->getRequest()->getParam('gift_template_id');
        $currentImage = $this->getRequest()->getParam('current_image');
        $customerUploadImage = $this->getRequest()->getParam('custom_image');
        
        if (!$templateId && $customerUploadImage == 'false') {
            echo '';
            return;
        }
        $template = $gifttemplate->load($templateId);
        $images = $template->getImages();
        if ($customerUploadImage == 'true') {
            $images = $currentImage;
        }
        $str = '';
        if ($images) {
            $str.='<div class="carousel" id="gift-image-carosel">
                        <a href="javascript:" class="carousel-control next" rel="next">›</a>
                        <a href="javascript:" class="carousel-control prev" rel="prev">‹</a>
                        <div class="gift-middle" id="carousel-wrapper">
                            <div class="inner" style="width: 3000px;">
                  ';
            $type = '';
            switch ($template->getDesignPattern()) {
                case \Magestore\Giftvoucher\Model\Designpattern::PATTERN_LEFT:
                    $type = 'left/';
                    break;
                case \Magestore\Giftvoucher\Model\Designpattern::PATTERN_TOP:
                    $type = 'top/';
                    break;
                case \Magestore\Giftvoucher\Model\Designpattern::PATTERN_CENTER:
                    $type = '';
                    break;
            }
            $images = explode(',', $images);
            $count = 0;
            $selectImage = 0;
            foreach ($images as $image) {
                $str.='<div id="div-image-for-' . $templateId . '-' . $count
                    . '" style="position:relative; float: left;border: 2px solid white;">';
                $str.='<img src="'
                    . $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'giftvoucher/template/images/' . $type . $image . '" alt="" style="width:80px;height:80px"
                    onclick="changeSelectImages(' . $count . ',\'' . $image . '\')">';
                $str.= '<div class="egcSwatch-arrow" style="display:none"></div>';
                $str.='</div>';
                if ($image == $currentImage) {
                    $selectImage = $count;
                }
                $count++;
            }
            if ($currentImage) {
                $str.='<input type="hidden" id="current_image" value=' . $currentImage . '>';

                $str.='<input type="hidden" id="selected_image" value=' . $selectImage . '>';
            } else {
                $str.='<input type="hidden" id="current_image" value=' . $images[0] . '>';

                $str.='<input type="hidden" id="selected_image" value="0">';
            }
            $str.='</div>
                </div>
                </div>
               ';
        }
        $this->getResponse()->setBody($str);
    }
    
    public function execute()
    {
        
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
