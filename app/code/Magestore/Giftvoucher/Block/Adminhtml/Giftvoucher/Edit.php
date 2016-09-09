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
namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher;

/**
 * Adminhtml Giftvoucher Edit Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magestore_Giftvoucher';
        $this->_controller = 'adminhtml_giftvoucher';
        
        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->remove('delete');
        
        if ($this->_coreRegistry->registry('giftvoucher_data')
            && $this->_coreRegistry->registry('giftvoucher_data')->getId()) {
            $this->buttonList->add(
                'delete-giftvoucher',
                array(
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to do this?') . '\', \''
                    . $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id'))) . '\')',
                ),
                -100
            );
        }

        $this->buttonList->add(
            'sendemail',
            array(
                'label' => __('Save And Send Email'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['back'=>'edit' ,'sendemail' => 'now']]],
                        ],
                    ],
                ]
            ),
            -100
        );
        
        $this->buttonList->add(
            'saveandcontinue',
            array(
                'label' => __('Save And Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ),
            -100
        );
        
        if ($this->_coreRegistry->registry('giftvoucher_data')
            && $this->_coreRegistry->registry('giftvoucher_data')->getId()) {
            $this->buttonList->add(
                'print',
                array(
                    'label' => __('Print'),
                    'class' => 'save',
                    'onclick' => "window.open('"
                    . $this->getUrl('*/*/print', array('id' => $this->getRequest()->getParam('id')))
                    . "', 'newWindow', 'width=1000,height=700,resizable=yes,scrollbars=yes')",
                ),
                -100
            );
        }
        
        $this->_formScripts[] = "
           
            require([
                'prototype'
            ], function () {
                document.observe('dom:loaded',function(){
                    $('recipient_email').setAttribute('type', 'email');
                    $('customer_email').setAttribute('type', 'email');
                    $('list_images').up('div').up('div').hide();
                });
            });
            
            var image_current;
            var gift_template_id;
            
            function loadImageTemplate(template_id,image,custom_image){
                gift_template_id=template_id;
                current_image=image;
                custom_image=custom_image;

                new Ajax.Request('". $this->getUrl('*/*/giftimages', array('_current' => true)). "', 
                {
                    parameters: {
                        form_key: FORM_KEY,
                        gift_template_id: gift_template_id,
                        current_image:current_image,
                        custom_image:custom_image,
                    },
                    evalScripts: true,
                    onSuccess: function(transport) {
                        if (transport.responseText) {
                            $('list_images').up('div').up('div').show();
                            $('list_images').update(transport.responseText);
                            if ($$('#gift-image-carosel img').length >= 4)
                                carousel = new Carousel('carousel-wrapper', $$('#gift-image-carosel img'), 
                                    $$('#gift-image-carosel a'), {
                                    duration: 0.5,
                                    transition: 'sinoidal',
                                    visibleSlides: 4,
                                    circular: false
                                });
                            changeSelectImages(-1);
                        } else {
                            $('list_images').update(transport.responseText);
                            $('list_images').up('div').up('div').hide();
                        }
                    }
                });
            }
            function changeSelectImages(id, image) {
                if (id == -1) {
                    image_current = $('div-image-for-' + gift_template_id + '-' + selected_image.value);
                    image_current.addClassName('gift-active');
                    image_current.down('.egcSwatch-arrow').show();
                    $('giftcard_template_image').value = $('current_image').value;
                } else
                {
                    if(typeof image_current == 'undefined'){
                        image_current = $('div-image-for-' + gift_template_id + '-' + selected_image.value);
                    }
                    image_current.removeClassName('gift-active');
                    image_current.down('.egcSwatch-arrow').hide();
                    image_current = $('div-image-for-' + gift_template_id + '-' + id);
                    image_current.addClassName('gift-active');
                    image_current.down('.egcSwatch-arrow').show();
                    $('giftcard_template_image').value = image;
                }
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('giftvoucher_data')
            && $this->_coreRegistry->registry('giftvoucher_data')->getId()) {
            return __("Edit Gift Code '%s'", $this->escapeHtml($this->_coreRegistry->registry('giftvoucher_data')->getTitle()));
        } else {
            return __('New Gift Code');
        }
    }
}
