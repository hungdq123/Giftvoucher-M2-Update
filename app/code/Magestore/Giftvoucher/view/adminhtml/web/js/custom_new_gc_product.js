/**
* Created by admin on 8/17/16.
*/
require([
    "jquery",
    "mage/backend/validation"
], function($){

    $( document ).ready(function() {
    var type = $("select[name='product[gift_type]']");
        hidesettingSC();
        type.change(function(){
            hidesettingSC();

        });
    });

    function hidesettingSC(){
        console.log('hide setting sc');
        var type = $("select[name='product[gift_type]']");
        console.log($("select[name='product[gift_type]']"));
        if(type.val()==1)
        {
            console.log('type 1');
            $("input[name='product[credit_rate]']").show();

            $("input[name='product[storecredit_value]']").show();
            $("input[name='product[storecredit_value]']").parent('div').parent('div').prev().show();
            $("input[name='product[storecredit_value]']").next().show();
            $("input[name='product[storecredit_value]']").parent('div').show();
            $("input[name='product[storecredit_value]']").parent('div').parent('div').parent('div').show();

            $("input[name='product[storecredit_from]']").hide();
            $("input[name='product[storecredit_from]']").parent('div').parent('div').prev().hide();
            $("input[name='product[storecredit_from]']").next().hide();
            $("input[name='product[storecredit_from]']").parent('div').hide();


            $("input[name='product[storecredit_to]']").parent('div').parent('div').prev().hide();
            $("input[name='product[storecredit_to]']").next().hide();
            $("input[name='product[storecredit_to]']").parent('div').hide();
            $("input[name='product[storecredit_to]']").hide();

            $("input[name='product[storecredit_dropdown]']").parent('div').prev().hide();
            $("input[name='product[storecredit_dropdown]']").next().hide();
            $("input[name='product[storecredit_dropdown]']").parent('div').hide();
            $("input[name='product[storecredit_dropdown]']").hide();

        }else if(type.val()==2){
            console.log('type 2');
            $("input[name='product[credit_rate]']").show();

            $("input[name='product[storecredit_value]']").hide();
            $("input[name='product[storecredit_value]']").parent('div').parent('div').prev().hide();
            $("input[name='product[storecredit_value]']").next().hide();
            $("input[name='product[storecredit_value]']").parent('div').hide();

            $("input[name='product[storecredit_from]']").show();
            $("input[name='product[storecredit_from]']").parent('div').parent('div').prev().show();
            $("input[name='product[storecredit_from]']").next().show();
            $("input[name='product[storecredit_from]']").parent('div').show();
            $("input[name='product[storecredit_from]']").parent('div').parent('div').parent('div').show();

            $("input[name='product[storecredit_to]']").parent('div').parent('div').prev().show();
            $("input[name='product[storecredit_to]']").parent('div').parent('div').parent('div').show();
            $("input[name='product[storecredit_to]']").next().show();
            $("input[name='product[storecredit_to]']").parent('div').show();
            $("input[name='product[storecredit_to]']").show();

            $("input[name='product[storecredit_dropdown]']").parent('div').prev().hide();
            $("input[name='product[storecredit_dropdown]']").next().hide();
            $("input[name='product[storecredit_dropdown]']").parent('div').hide();
            $("input[name='product[storecredit_dropdown]']").hide();

        }else if(type.val()==3){
            console.log('type 3');
            $("input[name='product[credit_rate]']").show();

            $("input[name='product[storecredit_value]']").hide();
            $("input[name='product[storecredit_value]']").parent('div').parent('div').prev().hide();
            $("input[name='product[storecredit_value]']").next().hide();
            $("input[name='product[storecredit_value]']").parent('div').parent('div').parent('div').hide();

            $("input[name='product[storecredit_from]']").hide();
            $("input[name='product[storecredit_from]']").parent('div').parent('div').prev().hide();
            $("input[name='product[storecredit_from]']").next().hide();
            $("input[name='product[storecredit_from]']").parent('div').parent('div').parent('div').hide();

            $("input[name='product[storecredit_to]']").parent('div').parent('div').prev().hide();
            $("input[name='product[storecredit_to]']").next().hide();
            $("input[name='product[storecredit_to]']").parent('div').parent('div').parent('div').hide();
            $("input[name='product[storecredit_to]']").hide();

            $("input[name='product[storecredit_dropdown]']").parent('div').prev().show();
            $("input[name='product[storecredit_dropdown]']").next().show();
            $("input[name='product[storecredit_dropdown]']").parent('div').show();
            $("input[name='product[storecredit_dropdown]']").show();
        }
    }

});
