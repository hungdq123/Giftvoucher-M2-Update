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
namespace Magestore\Giftvoucher\Model;

/**
 * Giftvoucher View Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Giftcodesetoptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var \Magestore\Giftvoucher\Model\Generategiftcard
     */
    protected $_giftcodeSets;

    /**
     *
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $giftcodeSets
     */
    public function __construct(
        \Magestore\Giftvoucher\Model\Generategiftcard $giftcodeSets
    ) {
        $this->_giftcodeSets = $giftcodeSets;
    }


    /**
     * Get Gift Card available templates
     *
     * @return array
     */
    public function getAvailableGiftcodeSets()
    {
        $giftcodeSets = $this->_giftcodeSets->getCollection()
            ->addFieldToFilter('status', '2');
        $listGiftcodeSets = array();
        foreach ($giftcodeSets as $giftcodeSet) {
            $listGiftcodeSets[] = array('label' => $giftcodeSet->getGiftCode(),
                'value' => $giftcodeSet->getGiftCode());
        }
        return  $listGiftcodeSets;
    }

    /**
     * Get model option as array
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->getAvailableGiftcodeSets();
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value' => '',
                'label' => __('-- Please Select --'),
            ));
        }
        return $options;
    }
}
