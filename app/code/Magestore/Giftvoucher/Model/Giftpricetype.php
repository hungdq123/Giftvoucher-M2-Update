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
 * Giftvoucher Gifttemplate Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Giftpricetype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const GIFT_PRICE_TYPE_DEFAULT = 1;
    const GIFT_PRICE_TYPE_FIX = 2;
    const GIFT_PRICE_TYPE_PERCENT = 3;

    /**
     * Get model option as array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => __('Same as Gift Card Value'),
                    'value' => self::GIFT_PRICE_TYPE_DEFAULT
                ),
                array(
                    'label' => __('Fixed Price'),
                    'value' => self::GIFT_PRICE_TYPE_FIX
                ),
                array(
                    'label' => __('Percent of Gift Card value'),
                    'value' => self::GIFT_PRICE_TYPE_PERCENT
                ),
            );
        }
        return $this->_options;
    }
}
