"use strict";

$(document).ready(function () {
    $('body').on('keyup input', '.error-field', function () {
        $(this).removeClass('error-field');
        $(this).prev().removeClass('error-field');
        $(this).tooltip('destroy');
    }).on('dp.change', '.datetimepicker', function () {
        $(this).find('input').removeClass('error-field');
        $(this).find('input').prev().removeClass('error-field');
        $(this).tooltip('destroy');
    });
});

var CustomValid = {
    checkReuired: function (elem, wasError) {
        if (elem !== undefined && elem.val() !== undefined) {
            if (elem.val().length === 0) {
                this.showErrorValid(elem, wasError);
                return true;
            }
        }
        return false;
    },

    checkEmail: function (elem, wasError) {
        if (elem !== undefined && elem.val() !== undefined) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (!re.test(elem.val())) {
                this.showErrorValid(elem, wasError);
                return true;
            }
        }
        return false;
    },

    showErrorValid: function (elem, wasError) {
        elem.addClass('error-field');
        elem.prev().addClass('error-field');
        if (!wasError) {
            elem.tooltip('show');
            elem.trigger('focus');
        }

    }
};