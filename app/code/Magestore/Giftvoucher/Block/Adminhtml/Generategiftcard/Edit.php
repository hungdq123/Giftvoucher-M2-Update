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

namespace Magestore\Giftvoucher\Block\Adminhtml\Generategiftcard;

/**
 * Adminhtml Giftvoucher Generategiftcard Edit Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
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
        $this->_controller = 'adminhtml_generategiftcard';

        parent::_construct();



        $this->buttonList->update('save', 'label', __('Save Pattern'));
        $this->buttonList->update('delete', 'label', __('Delete Pattern'));
        $this->buttonList->add(
            'saveandcontinue',
            array(
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ),
            -100
        );

        if ($this->getTemplateGenerate()->getIsGenerated()) {
            $this->buttonList->remove('save');
            $this->buttonList->remove('reset');
            $this->buttonList->remove('saveandcontinue');
            $this->buttonList->add('duplicate', array(
                'label' => __('Duplicate'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => [
                                'back' => 'edit' ,
                                'duplicate' => 'now',
                                'id' => $this->getRequest()->getParam('id')]
                            ]],
                        ],
                    ],
                ]
            ), -100);
        } else {
            $this->buttonList->add(
                'generate',
                array(
                    'label' => __('Save And Generate'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['back'=>'edit' ,'generate' => 'now']]],
                            ],
                        ],
                    ]
                ),
                -100
            );
        }


        $this->_formScripts[] = "        
           window.onload = function() {
                $('list_images').up('div').up('div').hide();
            };
            var image_current;
            var gift_template_id;
            function loadImageTemplate(template_id,image,generated){
                    gift_template_id=template_id;
                    current_image=image;
                    new Ajax.Request('"
                . $this->getUrl('*/*/giftimages', array('_current' => true))
                . "', {
                            parameters: {
                                         form_key: FORM_KEY,
                                         gift_template_id: gift_template_id,
                                         current_image:current_image,
                                         },
                            evalScripts: true,
                            onSuccess: function(transport) {
                                if(transport.responseText){
                                     $('list_images').up('div').up('div').show();
                                    $('list_images').update(transport.responseText);
                                if($$('#gift-image-carosel img').length>=4)
                                carousel = new Carousel('carousel-wrapper', $$('#gift-image-carosel img'), 
                                    $$('#gift-image-carosel a'), {
                                duration: 0.5,
                                transition: 'sinoidal',
                                visibleSlides: 4,
                                circular: false
                            });
                                changeSelectImages(-1);
                                if(generated && generated != 0){
                                    $('list_images').setStyle({
                                        opacity: '0.4',
                                        pointerEvents: 'none'
                                     });
                                    $('page_conditions_fieldset').setStyle({                                       
                                        pointerEvents: 'none'
                                     });
                                    $('page_conditions_fieldset').down('div').setStyle({
                                        opacity: '0.4'
                                     });
                                    }
                                }
                                else{
                                $('list_images').update(transport.responseText);
                                $('list_images').up('div').up('div').hide();
                                }
                            }
                        });
            }
            
            function changeSelectImages(id,image){
                if(id == -1){
                       image_current=$('div-image-for-'+gift_template_id+'-'+selected_image.value);
                       image_current.addClassName('gift-active');
                       image_current.down('.egcSwatch-arrow').show(); 
                       $('giftcard_template_image').value=$('current_image').value;
                }
                else
                {
                image_current.removeClassName('gift-active');
                image_current.down('.egcSwatch-arrow').hide();
                image_current=$('div-image-for-'+gift_template_id+'-'+id);
                image_current.addClassName('gift-active');
                image_current.down('.egcSwatch-arrow').show();
                $('giftcard_template_image').value=image;
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
        if ($this->_coreRegistry->registry('generategiftcard_data')
            && $this->_coreRegistry->registry('generategiftcard_data')->getId()) {
            return __(
                "Edit Gift Code '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('generategiftcard_data')->getTitle())
            );
        } else {
            return __('New Gift Code');
        }
    }

    public function getTemplateGenerate()
    {
        if ($this->_coreRegistry->registry('generategiftcard_data')) {
            return $this->_coreRegistry->registry('generategiftcard_data');
        }
        return $this->_objectManager->get('Magestore\Giftvoucher\Model\Generategiftcard');
    }
}
