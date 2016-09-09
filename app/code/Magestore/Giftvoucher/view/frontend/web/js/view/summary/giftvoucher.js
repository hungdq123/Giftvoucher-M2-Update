/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magestore_Giftvoucher/js/model/giftvoucher'
    ],
    function (Component, giftvoucher) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Giftvoucher/summary/giftvoucher'
            },
            giftvouchers: giftvoucher.getData(),
            isDisplayed: function() {
                return this.getPureValue() > 0;
            },
            
            getPureValue: function() {
                var price = 0;
                if(this.giftvouchers() && this.giftvouchers().useGiftcreditAmount)
                    price = parseFloat(this.giftvouchers().useGiftcreditAmount);
                return price;
            },
            getValue: function() {
                return this.getFormattedPrice(-this.getPureValue());
            },
            
            isDisplayedCard: function() {
                return this.getPureValueCard() > 0;
            },
            
            getPureValueCard: function() {
                var price = 0;
                if(this.giftvouchers() && this.giftvouchers().useGiftVoucher && this.giftvouchers().giftVoucherDiscount.length >0){
                    for(var index = 0; index < this.giftvouchers().giftVoucherDiscount.length; index++){
                        price += parseFloat(this.giftvouchers().giftVoucherDiscount[index].value);
                    }
                }
                return price;
            },
            getValueCard: function() {
                return this.getFormattedPrice(-this.getPureValueCard());
            },
        });
    }
);
