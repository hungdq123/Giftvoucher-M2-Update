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
 * Giftvoucher Actions Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Actions extends \Magento\Framework\DataObject
{

    const ACTIONS_CREATE = 1;
    const ACTIONS_UPDATE = 2;
    const ACTIONS_MASS_UPDATE = 3;
    const ACTIONS_EMAIL = 4;
    const ACTIONS_SPEND_ORDER = 5;
    const ACTIONS_REFUND = 6;
    const ACTIONS_REDEEM = 7;

    /**
     * Get model option as array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return array(
            self::ACTIONS_CREATE => __('Create'),
            self::ACTIONS_UPDATE => __('Update'),
            self::ACTIONS_MASS_UPDATE => __('Mass update'),
            self::ACTIONS_SPEND_ORDER => __('Spent on order'),
            self::ACTIONS_REFUND => __('Refund'),
            self::ACTIONS_REDEEM => __('Redeem'),
        );
    }

    public static function getOptions()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }
}
