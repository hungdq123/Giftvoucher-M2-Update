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
 * Adminhtml GiftCard Template Grid Edit Tab Form Block
 *
 * @category Magestore
 * @package  Magestore_Gifttemplate
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Form extends \Magestore\Giftvoucher\Block\Adminhtml\Gifttemplate\Edit\Tab\Abstractgifttemplate implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        
        $isElementDisabled = false;
        $fieldset = $form->addFieldset('gifttemplate_form', array('legend' => __('General Information')));
        if ($this->_coreRegistry->registry('gifttemplate_data')) {
            $model = $this->_coreRegistry->registry('gifttemplate_data');
        } else {
            $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Gifttemplate');
        }
        if ($model->getId()) {
            $fieldset->addField('giftcard_template_id', 'hidden', array('name' => 'giftcard_template_id'));
        }

        $fieldset->addField(
            'template_name',
            'text',
            array(
                'name' => 'template_name',
                'label' => __('Template Name'),
                'title' => __('Template Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label' => __('Status'),
                'name' => 'status',
                'values' => \Magestore\Giftvoucher\Model\Gifttemplate\Status::getOptions()
            )
        );
        $pattern = $model->getData('design_pattern') ? $model->getData('design_pattern') : 1;
        $fieldset->addField(
            'design_pattern',
            'select',
            array(
                'name' => 'design_pattern',
                'label' => __('Template Design'),
                'title' => __('Template Design'),
                'required' => false,
                'onchange' => 'changePattern()',
                'disabled' => $isElementDisabled,
                'values' => \Magestore\Giftvoucher\Model\Gifttemplate\Type::getOptions(),
            )
        );
        
        $fieldset->addField(
            'template_type',
            'note',
            array(
                'name' => 'template_type',
                'text' => '
                    <div id="demo_pattern" style=""><img id="pattern_demo" style="width:100%" src="'
                    . $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'giftvoucher/template/pattern/GC_' . $pattern . '.jpg' . '" /> </div>'
                    . '<script type="text/javascript">
                            function viewdemo() {
                                value=$("design_pattern").value;
                                new Ajax.Request("'
                    . $this->_storeManager->getStore()->getUrl('*/*/viewdemo', array('_current' => true))
                    . '", {
                                    parameters: {
                                                 form_key: FORM_KEY,
                                                 value: value,

                                                 },
                                    evalScripts: true,
                                    onSuccess: function(transport) {
                                        TINY.box.show("");
                                        $("tinycontent").update(transport.responseText);
                                    }
                                });
                            }
                        </script>
                ',
            )
        );

        $fieldset->addField(
            'caption',
            'text',
            array(
                'name' => 'caption',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            )
        );
        $fieldset->addField(
            'style_color',
            'text',
            array(
                'name' => 'style_color',
                'label' => __('Style Color'),
                'title' => __('Style Color'),
                'class' => 'color {required:false, adjust:false, hash:true}',
                'required' => true,
                'value' =>  '#FFFFFF',
                'style' =>  'width: 150px',
                'disabled' => $isElementDisabled,
                'note' => __('Choose color of texts in Gift Cart title, value and gift code fields.'),
            )
        );

        $fieldset->addField(
            'text_color',
            'text',
            array(
                'name' => 'text_color',
                'label' => __('Text Color'),
                'title' => __('Text Color'),
                'required' => true,
                'style' =>  'width: 150px',
                'class' => 'color {required:false, adjust:false, hash:true}',
                'disabled' => $isElementDisabled,
                'note' => __('Choose color of other texts (fields’ title, notes, etc.).')
            )
        );

//        $fieldset->addField(
//            'background_img',
//            'image',
//            array(
//                'name' => 'background_img',
//                'label' => __('Backgroud Image'),
//                'title' => __('Backgroud Image'),
//                'required' => false,
//                'disabled' => $isElementDisabled,
//                'note' => __('Support jpg, jpeg, gif, png files.'),
//            )
//        );

//        $fieldset->addField(
//            'notes',
//            'textarea',
//            array(
//                'name' => 'notes',
//                'label' => __('Notes'),
//                'title' => __('Notes'),
//                'required' => false,
//                'disabled' => $isElementDisabled,
//                'note' => __(
//                    '{store_name}: your store\'s name<br/>
//                    {store_url}: your store\'s url<br/>
//                    {store_address}: your store\'s address'
//                ),
//            )
//        );

        if ($model->getData('background_img')) {
            $dirBackground = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(
                'giftvoucher/template/background/'.$model->getData('background_img')
            );
            if (file_exists($dirBackground)) {
                $type = '';
                switch ($model->getData('design_pattern')) {
                    case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_TOP:
                        $type = 'left/';
                        break;
                    case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_TOP:
                        $type = 'top/';
                        break;
                    case \Magestore\Giftvoucher\Model\Gifttemplate\Type::TYPE_CENTER:
                        $type = '';
                        break;
                }
                $model->setData(
                    'background_img',
                    $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'giftvoucher/template/background/' . $type . $model->getData('background_img')
                );
            }
        }
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
