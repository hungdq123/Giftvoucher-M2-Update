/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require([
    "jquery",
    "jquery/ui",
    "prototype",
    "mage/translate"
], function(){
    //var templates;
    var customer_name;
    var recipient_name;
    var recipient_email;
    var recipient_ship;
    var message;
    var day_to_send;
    var email_sender;
    var current_image;
    hideTemplateImages = function() {
        if ($('select-gift')[0].selected == true) {
            $('gift-image-carosel').hide();
        } else {
            $('gift-image-carosel').show();
        }
    }
    //if (customer_name && customer_name.value) {
    //    $('customer_name').value = customer_name.value;
    //}
    //if (recipient_name && recipient_name.value) {
    //    $('recipient_name').value = recipient_name.value;
    //}
    //if (recipient_email && recipient_email.value) {
    //    $('recipient_email').value = recipient_email.value;
    //}
    //if (recipient_ship && recipient_ship.value) {
    //    $('recipient_ship').checked = true;
    //}
    //if (message && message.value) {
    //    $('message').value = message.value;
    //}
    //if (day_to_send && day_to_send.value) {
    //    $('recipient_email').value = recipient_email.value;
    //}
    //if (email_sender && email_sender.value) {
    //    $('email_sender').checked = true;
    //}
    loadGiftCard = function(templates) {
        if ($('select-gift') && $('select-gift').value)
            changeTemplate($('select-gift'), templates);
    }
    sendFriend = function(el) {
        if (!el)
            return;
        var receiver = $('giftvoucher-receiver');
        if (el.checked) {
            if (receiver) {
                receiver.show();
                if ($('recipient_name'))
                    $('recipient_name').addClassName('required-entry');
                if ($('recipient_email')) {
                    $('recipient_email').addClassName('required-entry');
                    $('recipient_email').addClassName('validate-email');
                    $('recipient_email').addClassName('validate-same-email');
                }
                if ($('day_to_send')) {
                    $('day_to_send').addClassName('required-entry');
                    $('day_to_send').addClassName('validate-date');
                    $('day_to_send').addClassName('validate-date-giftcard');
                }
            }
        } else {
            if (receiver)
            {
                if ($('recipient_email')) {
                    $('recipient_email').removeClassName('required-entry');
                    $('recipient_email').removeClassName('validate-email');
                    $('recipient_email').removeClassName('validate-same-email');
                }
                receiver.hide();
                if ($('recipient_name'))
                    $('recipient_name').removeClassName('required-entry');

                if ($('day_to_send')) {
                    $('day_to_send').removeClassName('required-entry');
                    $('day_to_send').removeClassName('validate-date');
                    $('day_to_send').removeClassName('validate-date-giftcard');
                }
            }
        }
    }

    var image_old;
    var image_count;
    var template_show_id;
    var template_id;
    var giftcard_prev = 0;
    var giftcard_next = 4;
    //var image_form_data;
    changeTemplate = function(el) {
        template_id = getTemplateById(el.value, templates);
        if (typeof image_for_old !== 'undefined')
            $(image_for_old).hide();
        if (typeof image_form_data === 'undefined') {
            image_for_old = 'div-bound-' + templates[template_id].giftcard_template_id + '-0';
            if ($(image_for_old))
                $(image_for_old).show();
            giftcard_prev = 0;
            giftcard_next = 4;
        } else
            image_for_old = 'div-bound-' + templates[template_id].giftcard_template_id + '-' + (image_form_data - image_form_data % 4);
        if ($(image_for_old))
            $(image_for_old).show();
        if (templates[template_id].images)
            count_next_fix = templates[template_id].images.split(',').length;
        else
            count_next_fix = 0;

        if (giftcard_next >= count_next_fix)
            $('giftcard-template-next').hide();
        else
            $('giftcard-template-next').show();
        if (giftcard_prev <= 0)
            $('giftcard-template-prev').hide();
        else
            $('giftcard-template-prev').show();

        if (typeof image_form_data !== 'undefined') {
            changeSelectImages(image_form_data);
            delete image_form_data;
        } else {
            changeSelectImages(0);
        }
    }
    getTemplateById = function(id, templates) {
        for (i = 0; i < templates.length; i++) {
            if (templates[i].giftcard_template_id === id)
                return i;
        }
        return 0;
    }
    changeSelectImages = function(image_id) {
        if (typeof image_old != 'undefined') {
            $('div-' + image_old).removeClassName('gift-active');
            $('div-' + image_old).down('.egcSwatch-arrow').hide();
        }
        if ($('image-for-' + templates[template_id].giftcard_template_id + '-' + image_id)) {
            image_old = 'image-for-' + templates[template_id].giftcard_template_id + '-' + image_id;
            $('div-' + image_old).addClassName('gift-active');

            $('div-image-for-' + templates[template_id].giftcard_template_id + '-' + image_id).down('.egcSwatch-arrow').show();
            image = $(image_old).src;

            images_tmp = templates[template_id].images;
            if (images_tmp != null) {
                images_tmp = images_tmp.split(',');
                $('giftcard-template-images').value = images_tmp[image_id];
            }
        }
    }
    giftcardPrevImage = function() {
        if (giftcard_prev === 0)
            return;
        if (typeof image_for_old !== 'undefined')
            $(image_for_old).hide();
        giftcard_prev = giftcard_prev - 4;
        giftcard_next = giftcard_next - 4;
        image_for_old = 'div-bound-' + templates[template_id].giftcard_template_id + '-' + giftcard_prev;
        $(image_for_old).show();
        if (giftcard_prev === 0)
            $('giftcard-template-prev').hide();
        if (giftcard_next < templates[template_id].images.split(',').length)
            $('giftcard-template-next').show();
    }
    giftcardNextImage = function() {
        if (giftcard_next >= templates[template_id].images.split(',').length)
            return;
        if (typeof image_for_old !== 'undefined')
            $(image_for_old).hide();
        giftcard_next = giftcard_next + 4;
        giftcard_prev = giftcard_prev + 4;
        image_for_old = 'div-bound-' + templates[template_id].giftcard_template_id + '-' + giftcard_prev;
        $(image_for_old).show();
        if (giftcard_next >= templates[template_id].images.split(',').length)
            $('giftcard-template-next').hide();
        if (giftcard_prev > 0)
            $('giftcard-template-prev').show();
    }
    changeRemaining = function(el, remaining_max) {
        if (el.value.length > remaining_max) {
            el.value = el.value.substring(0, remaining_max);
        }
        $('giftvoucher_char_remaining').innerHTML = remaining_max - el.value.length;
    }
    day_to_send_error = 'We cannot send a Gift Card on a date in the past. Please choose the sending date again.';
    Validation.add('validate-date-giftcard', day_to_send_error, function(v) {
        if (Validation.get('validate-date').test(v)) {
            var test = new Date(v);
            var today = getTodayDate();
            if (test < today)
                return false;
        }
        return true;
    });
//    function getTodayDate() {
//        todayDate = new Date();
//        todayDate.setDate(todayDate.getDate() -1);
//        todayDate.setHours(0);
//        todayDate.setMinutes(0);
//        todayDate.setSeconds(0);
//        todayDate.setMilliseconds(0);
//        return todayDate;
//    }
    shipToFriend = function(el, check) {
        if (el != null && el.checked) {
            if ($('recipient_email'))
                $('recipient_email').removeClassName('required-entry');
            if ($('recipient_ship_desc'))
                $('recipient_ship_desc').show();
        } else {

            if ($('recipient_ship_desc'))
                $('recipient_ship_desc').hide();
        }
    }
    validateInputRange = function(el, from, to, priceFormat) {
        var result = [];
        price = priceFormat.match('1.000.00')[0];
        result['decimalSymbol'] = price.charAt(5);
        result['groupSymbol'] = price.charAt(1);

        var gift_amount_min = from;
        var gift_amount_max = to;

        validateValue = el.value.replace(/\s/g, '');
        if (validateValue.search(result.groupSymbol) != -1)
            validateValue = validateValue.replace(result.groupSymbol, '');
        el.value = validateValue.replace(result.decimalSymbol, '.');
        $('amount_range').value = el.value;

        if (el.value < gift_amount_min)
            el.value = gift_amount_min;
        if (el.value > gift_amount_max)
            el.value = gift_amount_max;
    }
    setAmountRange = function(amount) {
        if (!$('amount_range').value)
            $('amount_range').value = amount;
    }
})
