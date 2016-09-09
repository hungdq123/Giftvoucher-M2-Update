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


namespace Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Edit\Tab;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Adminhtml GiftCard Template Grid Edit Tab Image Block
 *
 * @category Magestore
 * @package  Magestore_Gifttemplate
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Images extends \Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Edit\Tab\Abstractgifttemplate implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        if ($this->_coreRegistry->registry('gifttemplate_data')) {
            $model = $this->_coreRegistry->registry('gifttemplate_data');
        } else {
            $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate');
        }
        $data = $model->getData();
        $data['number_image'] = 0;
        if (isset($data['images']) && $data['images']) {
            $images = $data['images'];
            $str = '';
            
            if ($images) {
                $str.='<div class=\"carousel\" id=\"gift-image-carosel\">
                            <div class=\"gift-middle\" id=\"carousel-wrapper\">
                                <div class=\"inner\" style=\"width: auto;height: 150px\">
                  ';
                $type = '';
                switch ($data['design_pattern']) {
                    case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_LEFT:
                        $type = 'left/';
                        break;
                    case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_TOP:
                        $type = 'top/';
                        break;
                    case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_CENTER:
                        $type = '';
                        break;
                }
                $images = explode(',', $images);
                $i = 0;
                foreach ($images as $image) {
                    $i++;
                    $str.='<div id=\"' . $image
                        . '\" style=\"position:relative; float: left;border: 2px solid white;\">';
                    $str.='<img src=\"' . $this->_storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                        . 'giftvoucher/template/images/'  . $image . '\" alt=\"\" style=\"width:100px;height:100px\">';
                    $str.='<div style=\"position: absolute;top: 105px;text-align: center;width: 100%\">';
                    $str.='<a class=\"preview-img\" href=\"javascript:previewImage(\'' . $image . '\')\">'
                        . __("Preview") . '</a><br/><a class=\"remove-img\" href=\"javascript:removeImage(\'' . $image
                        . '\')\">' . __("Remove") . '</a>';
                    $str.='</div>';
                    $str.='</div>';
                    if ($i%8 == 0) {
                        $str .= '</div><div class=\"inner\" style=\"width: auto;height: 150px\">';
                    }
                }
                $str.='</div>
                </div>
               </div>';
            }
            
        }
        $fieldset = $form->addFieldset('images_fieldset', array('legend' => __('Upload Images')));
        $fieldset->addField('number_image', 'hidden', array(
            'name' => 'number_image', //declare this as array. Otherwise only one image will be uploaded
        ));
        if (isset($str) && $str != null) {
            $listImage = str_replace(PHP_EOL, '', $str);
            $listImage = preg_replace(array('/\r/', '/\n/'), '', $str);
        } else {
            $listImage = '';
        }
        $fieldset->addField('upload_images', 'hidden', array('name' => 'upload_images',
            'label' => __('Images'),
            'title' => __('Images'),
            'style' =>  'width: 100%',
            'after_element_html' => '<div> 
                <span style="font-family:Arial"><span style="">' . __("Click to add file(s)") . '</span></span>
                <button id="addMore" title="Add more image" type="button" class="scalable add"'
            . ' onclick="AddFileUpload()"><span>' . __("Add") . '</span></button>
                <span>&nbsp;&nbsp;' . __('Recommended size: ') . '<span id="giftcard-notes-top" style="display: none">'
            . '600x190</span><span id="giftcard-notes-center" style="display: none">600x365</span><span id="giftcard-notes-left" style="display: none">250x365</span>' . __('&nbsp;&nbsp;Support gif, jpg, png files.') . '</span>
                <br /><br />
                  <div id="FileUploadContainer">
                  <!--FileUpload Controls will be added here -->               </div> 
                 <br /><br />
                </td>
                </div>
            '
        ));
        $fieldset->addField('hr', 'note', array('name' => 'hr',
            'label' => __(''),
            'title' => __(''),
            'style' => 'height: 10px',
            'text'  => '<hr/>'
        ));
        $fieldset->addField('list_image', 'hidden', array(
            'name' => 'list_image',
            'label' => __('Images'),
            'title' => __('Images'),
            'after_element_html' => '<div id="fileuploaded" class="">
                <span style="color: #303030;font-size: 1.7rem;font-weight: 600;padding: 7px 0 10px;display: inline-block;">' . __("Uploaded images") . '</span>
                <div style="padding-top: 10px;border-top: 1px solid #cac3b4;"></div>           
            </div>' . '<script>
            window.onload = function(){
            changePattern();
            list_image="' . $listImage . '";
            if(!list_image){
                $("fileuploaded").up("label").hide();
            }
            else { 
                if(typeof($("fileuploaded")) != "undefined"){
                    $("fileuploaded").up("label").show();
                    $("fileuploaded").down("div").update(list_image);
                    /*if($$("#gift-image-carosel img").length>=4){
                     carousel = new Carousel("carousel-wrapper", $$("#gift-image-carosel img"), 
                        $$("#gift-image-carosel .carousel-control"), {
                            duration: 0.5,
                            transition: "sinoidal",
                            visibleSlides: 4,
                            circular: false
                        });
                    }*/
                }
            }
            }
        </script>'
        ));
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Gift Card Template Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Gift Card Template Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
