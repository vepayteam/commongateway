"use strict";

var tplUsl = {

    form: function (type) {
        if (type === 'two') {
            return '<form action="#" method="POST" id="fielsForm" class="col-xs-6 nopadding"><ul class="list-group" id="listgroupsortable" ondrop="addUsl.dropElem(event)" ondragover="addUsl.makeDroppable(event)">' +
                '</ul></form>' +
                '<form action="#" method="POST" id="fielsForm2" class="col-xs-6 nopadding"><ul class="list-group" id="listgroupsortable2" ondrop="addUsl.dropElem(event)" ondragover="addUsl.makeDroppable(event)">' +
                '</ul></form>';
        } else {
            return '<form action="#" method="POST" id="fielsForm">' +
                '<ul class="list-group" id="listgroupsortable" ' +
                'ondrop="addUsl.dropElem(event)" ondragover="addUsl.makeDroppable(event)"></ul>' +
                '</form>';
        }
    },
    field : function (data) {
        return ' <li class="list-group-item">' +
            '<a href="#" class="close" onclick="addUsl.delField(this)">×</a>' +
            data +
            '</li>';
    },
    input : function (label, coment, type) {

        return '<div class="form-group">\
         <label>'+label+'</label>\
         <input type="hidden" name="inp[]" value="'+encodeURIComponent($.toJSON({type: type, label: label, coment: coment}))+'"> \
         <input type="text" class="form-control">\
         <span class="help-block">'+coment+'</span>\
        </div>\
        ';
    },
    datetime : function (label, coment) {
        return '<div class="form-group">\
            <label>'+label+'</label>\
            <input type="hidden" name="inp[]" value="'+encodeURIComponent($.toJSON({type: "datetime", label: label, coment: coment}))+'"> \
            <div class="input-group date datetimepicker">\
            <input type="hidden" value=""> \
            <input type="text" class="form-control" />\
            <span class="input-group-addon">\
            <span class="glyphicon glyphicon-calendar"></span>\
            </span>\
            </div>\
            <span class="help-block">'+coment+'</span>\
            </div>\
            ';
    },
    summ : function (label) {
        var summ = parseFloat(label).toFixed(2);
        return '<div class="form-group">\
         <label class="summfield">Сумма: '+summ+' руб.</label>\
            <input type="hidden" name="inp[]" value="'+encodeURIComponent($.toJSON({type: "summ", fixed: summ}))+'"> \
        </div>\
        ';
    },

    statictext : function (label) {
        return '<div class="form-group">\
         <p>'+label+'</p>\
            <input type="hidden" name="inp[]" value="'+encodeURIComponent($.toJSON({type: "statictext", label: label}))+'"> \
        </div>\
        ';
    },

    staticimage : function (label, vallist, valsRFl) {
        var val = vallist[0];
        var valUmgs = '';
        if (valsRFl !== undefined) {
            var item = valsRFl[0];
            tplUsl.readFileSelect(item.files[0], val);
            valUmgs = URL.createObjectURL(item.files[0]);
        } else {
            var partPath = '/shopdata/'+$('[name="IdPartner"]').val()+'/';
            valUmgs = partPath+val+".png";
        }

        return '<div class="form-group">'+
                '<input type="hidden" name="valFile['+val+']" id="valFile_'+val+'" value="">' +
                '<img class="media-object" style="max-width: 240px" src="'+valUmgs+'">'+
            '</div>' +
            '<input type="hidden" name="inp[]" ' +
        'value="'+encodeURIComponent($.toJSON({type: "staticimage", label: label, vals: [val]}))+'">';
    },

    selects : function (label, coment, vallist) {
        var ret = '<div class="form-group">\
        <label class="labelselect">'+label+'</label>\
        <select class="form-control" data-type="selects">';
        $.each(vallist, function(i, item) {
            ret += '<option value="'+i+'">'+item+'</option>';
        });
        ret += '</select><span class="help-block">'+coment+'</span></div>';
        ret += '<input type="hidden" name="inp[]" ' +
            'value="'+encodeURIComponent($.toJSON({type: "selects", label: label, coment: coment, vals: vallist}))+'">';
        return ret;
    },

    radios : function (label, coment, vallist) {
        var ret = '<div class="form-group">\
        <label class="labelselect">'+label+'</label>\
        <input type="hidden" value="radiogroup" data-type="radios">';
        $.each(vallist, function(i, item) {
            ret += '<div class="radio">' +
                '  <label>' +
                '    <input type="radio" value="'+i+'">' +
                    item +
                '  </label>' +
                '</div>';
        });
        ret += '<span class="help-block">'+coment+'</span></div>';
        ret += '<input type="hidden" name="inp[]" ' +
            'value="'+encodeURIComponent($.toJSON({type: "radios", label: label, coment: coment, vals: vallist}))+'">';
        return ret;
    },

    checks : function (label, coment, vallist) {
        var ret = '<div class="form-group">\
        <label class="labelselect">'+label+'</label>\
        <input type="hidden" value="checkgroup" data-type="checks">';
        $.each(vallist, function(i, item) {
            ret += '<div class="checkbox">' +
                '  <label>' +
                '    <input type="checkbox" value="'+i+'">' +
                        item +
                '  </label>' +
                '</div>';
        });
        ret += '<span class="help-block">'+coment+'</span></div>';
        ret += '<input type="hidden" name="inp[]" ' +
            'value="'+encodeURIComponent($.toJSON({type: "checks", label: label, coment: coment, vals: vallist}))+'">';
        return ret;
    },

    readFileSelect: function (file, ident) {

        // Only process image files.
        if (!file || !file.type.match('image.*')) {
            return;
        }

        var reader = new FileReader();
        // Closure to capture the file information.
        reader.onload = (function(theFile) {
            return function(e) {
                // Render thumbnail.
                //через колбэк возврат
                $('#valFile_'+ident).val(e.target.result);
            };
        })(file, ident);

        // Read in the image file as a data URL.
        reader.readAsDataURL(file);

    },

    generateUUID: function() {
        var d = new Date().getTime();
        if (typeof performance !== 'undefined' && typeof performance.now === 'function'){
            d += performance.now(); //use high-precision timer if available
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (d + Math.random() * 16) % 16 | 0;
            d = Math.floor(d / 16);
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    },

    radioimgs : function (label, vallist, valsRFl) {
        var valUmgs = [];
        if (valsRFl !== undefined) {
            $.each(valsRFl, function(i, item) {
                if (item.files[0]) {
                    tplUsl.readFileSelect(item.files[0], vallist[i].file);
                    valUmgs[i] = URL.createObjectURL(item.files[0]);
                } else {
                    valUmgs[i] = undefined;
                }
            });
        } else {
            var partPath = '/shopdata/'+$('[name="IdPartner"]').val()+'/';
            $.each(vallist, function(i, val) {
                valUmgs[i] = partPath + val.file + ".png";
            });
        }

        var ret = '<div class="form-group">\
        <label class="labelselect">'+label+'</label>\
        <input type="hidden" value="radiogroup" data-type="radioimgs">';
        $.each(vallist, function(i, item) {
            ret +=
               '<input type="hidden" name="valFile['+item.file+']" id="valFile_'+item.file+'" value="">' +
               '<div class="radio">' +
               '  <label>' +
               '    <input type="radio" value="'+i+'" style="margin-top: 17px;">' +
               '<div class="media">' +
               '  <div class="media-left">';
            if (valUmgs[i] !== undefined) {
                ret += '<img class="media-object" src="'+valUmgs[i]+'">';
            } else {
                //ret += '<img class="media-object" src="/shopdata/noimg.png">'
            }
            ret +=
               '  </div>' +
               '  <div class="media-body">' +
               '    <h4 class="media-heading">' + item.val + '</h4>' +
                     item.comment +
               '  </div>' +
                '  </label>' +
                '</div>' +
               '</div>';
        });

        ret += '<input type="hidden" name="inp[]" ' +
            'value="'+encodeURIComponent($.toJSON({type: "radioimgs", label: label, vals: vallist}))+'">';

        return ret;
    },

    paramInput:  function () {
        return '' +
            '<div class="form-group">' +
                '<label for="fieldLabel">Название поля</label><label id="fieldLabelLen" class="maxleninfo pull-right">70/70</label>' +
                '<input type="text" class="form-control" name="fieldLabel" id="fieldLabel" maxlength="70">' +
            '</div>' +
            '<div class="form-group">' +
                '<label for="fieldComent">Комментарий</label><label id="fieldComentLen" class="maxleninfo pull-right">70/70</label>' +
                '<input type="text" class="form-control" name="fieldComent" id="fieldComent" maxlength="70">' +
            '</div>';
    },

    paramSumm:  function () {
        return '' +
            '<div class="form-group">' +
                '<label for="fieldLabel">Сумма <small>(от 1 до 150 000 руб.)</small></label>' +
                '<input type="text" class="form-control summ" name="fieldLabel" id="fieldLabel" maxlength="10">' +
            '</div>';
    },

    paramStaticText:  function () {
        return '' +
            '<div class="form-group">' +
            '<label for="fieldLabel">Текст</label><label id="fieldLabelLen" class="maxleninfo pull-right">250/250</label>' +
            '<textarea class="form-control" name="fieldLabel" id="fieldLabel" maxlength="250"></textarea>' +
            '</div>';
    },

    paramStaticImage:  function () {
        return '' +
            '<div class="form-group">' +
            '<label>Изображение<small> (до 240x240 px)</small></label>' +
            '<input type="file" name="valFile[]" accept="image/*">' +
            '</div>';
    },

    paramSelect:  function () {
        return '' +
            '<div class="form-group">' +
            '<label for="fieldLabel">Название элемента</label><label id="fieldLabelLen" class="maxleninfo pull-right">70/70</label>' +
            '<input type="text" class="form-control" name="fieldLabel" id="fieldLabel" maxlength="70">' +
            '</div>'+
            '<div class="form-group">' +
            '<label>Значения:</label>' +
            tplUsl.paramSelectFld() +
            tplUsl.paramSelectFld() +
            '<div><a onclick="tplUsl.addFieldSelect(this)"><i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить</a></div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label for="fieldComent">Комментарий</label><label id="fieldComentLen" class="maxleninfo pull-right">70/70</label>' +
            '<input type="text" class="form-control" name="fieldComent" id="fieldComent" maxlength="70">' +
            '</div>';
    },

    paramSelectFld: function () {
        return '<div class="form-group form-inline">' +
            '<input type="text" class="form-control" value="" name="selval[]" style="margin-left: 15px; width: 80%;" maxlength="50">' +
            ' <a href="#" onclick="tplUsl.delFieldSelect(this)">' +
            '<i class="fa fa-times-circle" aria-hidden="true"></i>' +
            '</a></div>';
    },

    delFieldSelect: function (elem) {
        $(elem).parent().remove();
    },

    addFieldSelect: function (elem) {
        var cnt = $(elem).parent().parent().find('.form-group').length;
        if (cnt < 10) {
            $(tplUsl.paramSelectFld()).insertBefore($(elem).parent());
        } else {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1000
            };
            toastr.error("Доступно до 10 вариантов", "Ошибка");
        }
    },

    paramSelectImgs:  function () {
        return '<div class="form-group">' +
            '<label for="fieldLabel">Название элемента</label><label id="fieldLabelLen" class="maxleninfo pull-right">70/70</label>' +
            '<input type="text" class="form-control" name="fieldLabel" id="fieldLabel" maxlength="70">' +
            '</div>'+
            '<div class="form-group">' +
            '<label>Значения:</label>' +
            tplUsl.paramSelectFldImgs() +
            tplUsl.paramSelectFldImgs() +
            '<div><a onclick="tplUsl.addFieldSelectImg(this)">' +
            '<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить</a>' +
            '</div>' +
            '</div>';
    },

    paramSelectFldImgs: function () {
        return '<div class="row" style="margin-left: 15px;"><div class="col-sm-10">' +
            '<div class="form-group">' +
            '<label>Заголовок</label><label class="maxleninfo pull-right">50/50</label>' +
            '<input type="text" class="form-control" value="" name="selval[]" maxlength="50">' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Описание</label><label class="maxleninfo pull-right">70/70</label>' +
            '<input type="text" class="form-control" value="" name="valComent[]" maxlength="70">' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Изображение<small> (до 80x80 px)</small></label>' +
            '<input type="file" name="valFile[]" accept="image/*">' +
            '</div>' +
            '</div>' +
            '<div  class="col-sm-2" style="margin-top: 31px;"><a href="#" onclick="tplUsl.delFieldSelectImg(this)">' +
            '<i class="fa fa-times-circle" aria-hidden="true"></i>' +
            '</a></div></div>'
        ;
    },

    addFieldSelectImg: function (elem) {
        var cnt = $(elem).parent().parent().find('.row').length;
        if (cnt < 10) {
            $(tplUsl.paramSelectFldImgs()).insertBefore($(elem).parent());
        } else {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1000
            };
            toastr.error("Доступно до 10 вариантов", "Ошибка");
        }
    },

    delFieldSelectImg: function (elem) {
        $(elem).parent().parent().remove();
    }

};