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
 * Giftvoucher Creditaction Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Creditaction extends \Magento\Framework\DataObject
{
    const ACTIONS_REDEEM = 'Redeem';
    const ACTIONS_APIREDEEM = 'Api_re';
    const ACTIONS_APIUPDATE = 'Apiupdate';
    const ACTIONS_ADMINUPDATE = 'Adminupdate';
    const ACTIONS_SPEND = 'Spend';
    const ACTIONS_REFUND = 'Refund';

    public static function getOptionArray()
    {
        return array(
            self::ACTIONS_REDEEM => __('Customer Redemption'),
            self::ACTIONS_APIREDEEM => __('API User Redemption'),
            self::ACTIONS_APIUPDATE => __('API User Update'),
            self::ACTIONS_ADMINUPDATE => __('Admin Update'),
            self::ACTIONS_SPEND => __('Customer Spend'),
            self::ACTIONS_REFUND => __('Admin Refund'),
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
