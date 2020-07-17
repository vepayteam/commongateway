'use strict';
$(document).ready(function() {
    $("#save-data-form input").inputmask();

    $("#save-data-form").submit(function() {
        var isValidate = true;
        $("#save-data-form input").each(function() {
            if(!$(this).inputmask("isComplete")) {
                isValidate = false;
                $(this).parent().removeClass('has-success').addClass('has-error');
            } else {
                $(this).parent().removeClass('has-error').addClass('has-success');
            }
        });

        return isValidate;
    });
});
