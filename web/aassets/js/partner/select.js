(function ($) {

    "use strict";

    let multiselect = {
        start: function (selector, placeholder = "Все") {
            $(selector).multiselect({
                    columns: 1,
                    search: true,
                    selectAll: true,
                    optionAttributes: [
                        'data-partner'
                    ],
                    maxHeight: 225,
                    minHeight: 20,
                    texts: {
                        placeholder: placeholder,
                        search: "Быстрый поиск",
                        selectedOptions: " выбрано",
                        selectAll      : 'Выбрать все',     // select all text
                        unselectAll    : 'Снять выбор со всех',   // unselect all text
                        noneSelected   : 'Ничего не выбрано'   // None selected text
                    }
                }
            );
        },

        changeMerchant: function (merchantSelector, multiselectSelector, attrOptionMultiSelect) {
            let nodeMultiSelect = $(multiselectSelector).next();
            let val = $(merchantSelector).find("option:selected").attr('data-ismfo');
            let optionsMultiselect = $(nodeMultiSelect).find('input');
            $(optionsMultiselect).each(function () {
                let selAttr = $(this).attr(attrOptionMultiSelect);
                if (selAttr != '-1' && selAttr !== val && val !== "-1") {
                    $(this).parent().parent('li').remove();
                }
            });
        },

        otch: function () {
            this.start('.multiselect-field');
            $(document).on('change', '.form-control[name=IdPart]', function () {
                $('.multiselect-field').multiselect('reset');
                multiselect.changeMerchant(
                    '.form-control[name=IdPart]',
                    '.multiselect-field',
                    'data-partner'
                )
            });
        },

        statList: function () {
            this.start('.multiselect-field');
            this.start('.multiselect-status', 'Все');
            $(document).on('change', '.form-control[name=IdPart]', function () {
                $('.multiselect-field').multiselect('reset');
                multiselect.changeMerchant(
                    '.form-control[name=IdPart]',
                    '.multiselect-field',
                    'data-partner'
                );
            });
        },

        statSale: function () {
            this.start('.multiselect-sale');
        },

        statRecurrentpaysdata: function () {
            this.start('.multiselect-recurrentpaysdata')
        },

        orderIndex: function () {
            this.start('.multiselect-order');
        },

        statMerchantusluga: function () {
            this.start('.multiselect-field');
            this.start('.multiselect-status', 'Все');
            $(document).on('change', '.form-control[name=IdPart]', function () {
                $('.multiselect-field').multiselect('reset');
                multiselect.changeMerchant(
                    '.form-control[name=IdPart]',
                    '.multiselect-field',
                    'data-partner'
                );
            });
        },
    };

    window.multiselect = multiselect;
}(jQuery || $));