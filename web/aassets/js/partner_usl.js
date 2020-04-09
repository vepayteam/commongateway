
function initAdd() {

    //$('a[data-click="field"]').on('click', function () {
    //addUsl.idField = $(this).attr('data-id');
    //$('#addFeildModal').modal('show');
    //});

    onLoadForm();

    toastr.options = {
        closeButton: true,
        progressBar: true,
        showMethod: 'slideDown',
        timeOut: 1000
    };

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var proc = $(this).attr('data-progress');
        $('.progress-bar').css('width', proc+'%');
    });

    $('#addFeildModal').on('show.bs.modal', function () {

        $('.modal-dialog', this).css('width', 700);
        if ($('[name="TypeTemplate"]').val() == 'two') {
            $('.modal-dialog', this).css('width', 400);
        }

        $('.modal-body').off('keypress').off('input')
            .on("keypress, input", "input, textarea", function(event) {
                var max = $(this).attr('maxlength');
                if ($(this).prev('.maxleninfo')) {
                    $(this).prev('.maxleninfo').html((max - $(this).val().length) + '/' + max);
                }
            });

        switch (addUsl.idField) {
            case "text":
            case "dig":
            case "datetime":
                $('#addFeildModalLabel').html('Добавить элемент поля');
                $(this).find('.modal-body').html(tplUsl.paramInput);
                break;
            case "summ":
                $('#addFeildModalLabel').html('Добавить элемент суммы');
                $(this).find('.modal-body').html(tplUsl.paramSumm);
                $('.modal-body').find('input.summ').off("keypress").off("input").off("paste")
                    .on("keypress input paste", function (event) {
                        //фильтр для ввода суммы
                        var val = $(this).val();
                        return (event.charCode >= 48 && event.charCode <= 57) ||
                            (event.charCode === 46 && val.indexOf('.') === -1);
                    });
                break;
            case "statictext":
                $('#addFeildModalLabel').html('Добавить элемент контента');
                $(this).find('.modal-body').html(tplUsl.paramStaticText);
                break;
            case "staticimage":
                $('#addFeildModalLabel').html('Добавить элемент контента');
                $(this).find('.modal-body').html(tplUsl.paramStaticImage);
                break;
            case "selects":
            case "radios":
            case "checks":
                $('#addFeildModalLabel').html('Добавить элемент списка');
                $(this).find('.modal-body').html(tplUsl.paramSelect);
                break;
            case "radioimgs":
                $('#addFeildModalLabel').html('Добавить элемент списка');
                $(this).find('.modal-body').html(tplUsl.paramSelectImgs);
                break;
        }
    });

    $('.modal-dialog').draggable();

    $('#addFeildModalOk').on('click', function () {

        if (!validateAddField()) return false;

        var label = $('#addFeildModalBody').find('[name="fieldLabel"]').val();
        var coment = $('#addFeildModalBody').find('[name="fieldComent"]').val();
        if (addUsl.typesFlt.indexOf(addUsl.idField) !== -1) {

            var valCmt = [];
            $.each($('#addFeildModalBody').find('[name="valComent[]"]'), function (i, item) {
                valCmt[i] = item;
            });
            var valsRFl = $('#addFeildModalBody').find('[name="valFile[]"]');
            var uidFile = [];
            $.each(valsRFl, function (i, item) {
                uidFile[i] = tplUsl.generateUUID();
            });
            var vallist = [];
            $.each($('#addFeildModalBody').find('[name="selval[]"]'), function (i, item) {
                if (addUsl.idField === 'radioimgs') {
                    vallist[i] = {val: $(item).val(), comment: $(valCmt[i]).val(), file: uidFile[i]};
                } else {
                    vallist[i] = $(item).val();
                }
            });
            if (addUsl.idField === 'staticimage') {
                vallist[0] = uidFile[0];
            }
            var fld = addUsl.addField(addUsl.idField, label, coment, vallist, valsRFl);
            if (fld !== '') {
                var items = $('#' + addUsl.dropFrom).find('.list-group-item');
                var isAdd = 0;
                if (items.length > 0) {
                    $.each(items, function (i, item) {
                        var posY = $(item).offset().top - $(document).scrollTop() + $(item).height();
                        if (posY > addUsl.dropPosY) {
                            $(fld).insertBefore(item);
                            isAdd = 1;
                            return false;
                        }
                    });
                }
                if (!isAdd) {
                    $('#' + addUsl.dropFrom).append(fld);
                }

                if (addUsl.idField === "datetime") {
                    $('.datetimepicker').datetimepicker();
                }

                //$('#listgroupsortable').append(fld);
            }
        }
        $('#addFeildModal').modal('hide');
        return false;
    });

    function validateAddField() {

        var hasZnach;
        switch (addUsl.idField) {
            case "text":
            case "dig":
            case "datetime":
                break;
            case "summ":
                hasZnach = $('#addFeildModalBody').find('[name="fieldLabel"]').val();
                if (parseFloat(hasZnach) <= 0 || parseFloat(hasZnach) > 150000) {
                    toastr.error("Неверная сумма", "Ошибка");
                    return false;
                }
                break;
            case "statictext":
                hasZnach = $('#addFeildModalBody').find('[name="fieldLabel"]').val();
                if (hasZnach == '') {
                    toastr.error("Введите текст", "Ошибка");
                    return false;
                }
                break;
            case "staticimage":
                hasZnach = $('#addFeildModalBody').find('[name^=valFile]').val();
                if (hasZnach == '') {
                    toastr.error("Выберите изображение", "Ошибка");
                    return false;
                }
                break;
            case "selects":
            case "radios":
            case "radioimgs":
                hasZnach = true;
                $.each($('#addFeildModalBody').find('[name^=selval]'), function(i, item) {
                    if (!$(item).val().length) {
                        hasZnach = false;
                    }
                });
                if (hasZnach) {
                    hasZnach = $('#addFeildModalBody').find('[name^=selval]').length;
                    if (hasZnach < 2 || hasZnach > 10) {
                        hasZnach = false;
                    }
                }

                if (!hasZnach) {
                    toastr.error("Необходимо добавить от 2 до 10 значениий", "Ошибка");
                    return false;
                }
                break;

            case "checks":
                hasZnach = true;
                $.each($('#addFeildModalBody').find('[name^=selval]'), function(i, item) {
                    if (!$(item).val().length) {
                        hasZnach = false;
                    }
                });
                if (hasZnach) {
                    hasZnach = $('#addFeildModalBody').find('[name^=selval]').length;
                    if (!hasZnach || hasZnach > 10) {
                        hasZnach = false;
                    }
                }
                if (!hasZnach) {
                    toastr.error("Необходимо добавить до 10 значений", "Ошибка");
                    return false;
                }
                break;
        }
        return true;
    }

    $('a[data-click="prototype"]').on('click', function () {
        addUsl.typeFrom = $(this).attr('data-id');

        $('#addFormModal').modal('show');
    });

    $('#addFormModal').on('show.bs.modal', function () {
    });

    $('#addFormModalOk').on('click', function () {
        var nameForm = 'Заказ';//$('#addFormModalBody').find('[name="nameForm"]').val();
        $('#uslbody').html(tplUsl.form(addUsl.typeFrom));
        $('#uslname').html(nameForm);
        //$('[name="NameUsl"]').val(coment);
        $('[name="NameForm"]').val(nameForm);
        $('[name="TypeTemplate"]').val(addUsl.typeFrom);


        addUsl.setSortable('listgroupsortable');
        if (addUsl.typeFrom === "two") {
            addUsl.setSortable('listgroupsortable2');
        }
        $('#addFormModal').modal('hide');
    });

    $('.modal').on('change', '[name^="valFile"]', function () {
        var file = this.files[0];
        if (file !== undefined) {

            //$('#selFile').html(file.name);
            console.log(file.type);

            if (file.name.length < 1) {
                $(this).val('');
                //$('#selFile').html('Файл не выбран');
            } else if (file.size > 20000000) {
                toastr.error("Файл слишком большой", "Ошибка");
                $(this).val('');
                //$('#selFile').html('Файл не выбран');
            } else if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' &&
                file.type !== 'image/jpeg') {
                toastr.error("Можно выбрать только картинку", "Ошибка");
                $(this).val('');
                //$('#selFile').html('Файл не выбран');
            }
        } else {
            $(this).val('');
            //$('#selFile').html('Файл не выбран');
        }
    });

    //$('#uslname').on('click', function () {
    //});

    $('#uslFormSave').on('click', function () {

        if (!validateFields()) return false;

        var form = new FormData($("#uslForm")[0]);
        $.each($("#fielsForm").find('input[name="inp[]"]'), function (i, item) {
            form.append('inp1[' + i + ']', $(item).val());
        });
        $.each($("#fielsForm").find('input[name^=valFile]'), function (i, item) {
            console.log(item);
            form.append($(item).attr('name'), $(item).val());
        });
        $.each($("#fielsForm2").find('input[name="inp[]"]'), function (i, item) {
            form.append('inp2[' + i + ']', $(item).val());
        });
        $.each($("#fielsForm2").find('input[name^=valFile]'), function (i, item) {
            console.log(item);
            form.append($(item).attr('name'), $(item).val());
        });

        $.ajax({
            type: "POST",
            url: "/partner/uslug/addpost",
            beforeSend: function () {
                $("#uslFormSave").prop("disabled", true);
            },
            data: form,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                $("#uslFormSave").prop("disabled", false);
                if (data.status === 1) {
                    toastr.success("Ok", "Виджет сохранен");
                    $('[name="IdUsl"]').val(data.idusl);
                } else {
                    toastr.error("Ошибка сохранения", "Ошибка");
                }
            },
            error: function () {
                $("#uslFormSave").prop("disabled", false);
                toastr.error("Ошибка сохранения виджета", "Ошибка");
            }

        });
    });

    function validateFields() {
        var cntFld = $('#listgroupsortable').find('.list-group-item').length +
            $('#listgroupsortable2').find('.list-group-item').length;
        if (!cntFld) {
            toastr.error("Необходимо добавить элементы", "Ошибка");
            return false;
        }
        var hasSumm = $('#listgroupsortable').find('label.summfield').length +
            $('#listgroupsortable2').find('label.summfield').length;
        if (!hasSumm) {
            toastr.error("Необходимо добавить элемент суммы", "Ошибка");
            return false;
        }
        return true;
    }

    function onLoadForm() {
        var IdUsl = $('[name="IdUsl"]').val();
        if (!IdUsl) {
            addUsl.addSortable("two");
        } else {
            var IdPartner = $('[name="IdPartner"]').val();
            $.ajax({
                type: "GET",
                url: "/partner/uslug/add?IdUsl=" + IdUsl + "&IdPartner=" + IdPartner,
                beforeSend: function () {
                },
                success: function (data) {
                    if (data.status === 1) {
                        //console.log(data);
                        addUsl.addSortable(data.CustomData.TypeTemplate);
                        $('[name="NameForm"]').val(data.CustomData.NameForm);
                        $('#uslname').html(data.CustomData.NameForm);
                        $('[name="TypeTemplate"]').val(data.CustomData.TypeTemplate);

                        var dropFrom = 'listgroupsortable';
                        $.each(data.CustomData.fields1, function (i, item) {
                            var fld;
                            //console.log(item);
                            if (item.type === "summ") {
                                fld = addUsl.addField(item.type, item.fixed);
                            } else {
                                fld = addUsl.addField(item.type, item.label, item.coment, item.vals, item.valsRFl);
                            }

                            if (fld !== '') {
                                $('#' + dropFrom).append(fld);
                                if (item.type === "datetime") {
                                    $('.datetimepicker').datetimepicker({
                                        format: 'DD.MM.YYYY HH:mm'
                                    });
                                }
                            }
                        });

                        dropFrom = 'listgroupsortable2';
                        $.each(data.CustomData.fields2, function (i, item) {
                            var fld;
                            if (item.type === "summ") {
                                fld = addUsl.addField(item.type, item.fixed);
                            } else {
                                fld = addUsl.addField(item.type, item.label, item.coment, item.vals, item.valsRFl);
                            }

                            if (fld !== '') {
                                $('#' + dropFrom).append(fld);
                                if (item.type === "datetime") {
                                    $('.datetimepicker').datetimepicker();
                                }
                            }
                        });

                    } else {
                    }
                },
                error: function () {
                    //$("#contact_submit_modal").prop("disabled", false);
                }

            });
        }
    }
}

"use strict";

var addUsl = {
    sortable: null,
    sortable2: null,
    idField: null,
    typeFrom: null,

    typesFlt: [
        "text", "dig", "datetime", "summ",
        "statictext", "staticimage",
        "selects", "radios", "radioimgs", "checks", "checkimgs"],

    dropFrom: '',
    dropPosY: 0,

    addSortable: function (TypeTemplate) {
        if (TypeTemplate === "one") {
            $('#uslbody').append(tplUsl.form('one'));
            addUsl.setSortable('listgroupsortable');
        } else {
            $('#uslbody').append(tplUsl.form('two'));
            addUsl.setSortable('listgroupsortable');
            addUsl.setSortable('listgroupsortable2');
        }
    },

    setSortable: function (idElem) {
        var el = document.getElementById(idElem);
        if (el !== undefined && idElem === 'listgroupsortable') {
            addUsl.sortable = Sortable.create(el, {
                group: {
                    name: 'fields',
                    pull: true,
                    put: function (to) {
                        if (to.el.children.length <= 4) {
                            return true;
                        } else {
                            /*message: "Ошибка: элементов слишком много"*/
                            return false;
                        }

                    }
                },
                animation: 150
            });
        }
        if (el !== undefined && idElem === 'listgroupsortable2') {
            addUsl.sortable2 = Sortable.create(el, {
                group: {
                    name: 'fields',
                    pull: true,
                    put: function (to) {
                        return to.el.children.length <= 4;
                    }
                },
                animation: 150
            });
        }
    },

    delField: function (fld) {
        $(fld).parent().remove();
    },

    addField: function (idField, label, coment, vals, valsRFl) {
        var fld = "";
        switch (idField) {
            case "text":
            case "dig":
                fld = tplUsl.field(tplUsl.input(label, coment, idField));
                break;

            case "datetime":
                fld = tplUsl.field(tplUsl.datetime(label, coment));
                break;

            case "summ":
                var elemSumm = $('#listgroupsortable').find('label.summfield');
                if (!elemSumm.length) {
                    elemSumm = $('#listgroupsortable2').find('label.summfield');
                }
                if (elemSumm) {
                    addUsl.delField(elemSumm.parent());
                }
                fld = tplUsl.field(tplUsl.summ(label));
                break;

            case "statictext":
                fld = tplUsl.field(tplUsl.statictext(label));
                break;

            case "staticimage":
                fld = tplUsl.field(tplUsl.staticimage(label, vals, valsRFl));
                break;

            case "selects":
                fld = tplUsl.field(tplUsl.selects(label, coment, vals));
                break;

            case "radios":
                fld = tplUsl.field(tplUsl.radios(label, coment, vals));
                break;

            case "checks":
                fld = tplUsl.field(tplUsl.checks(label, coment, vals));
                break;

            case "radioimgs":
                fld = tplUsl.field(tplUsl.radioimgs(label, vals, valsRFl));
                break;
        }
        return fld;
    },

    dragElem: function (event) {
        var id = $(event.target).attr('data-id');
        if (!id) {
            id = $(event.target).parent().attr('data-id');
        }
        event.dataTransfer.setData("text/plain", id);
    },

    makeDroppable: function (event) {
        event.preventDefault();
    },

    dropElem: function (event) {
        event.preventDefault();

        addUsl.dropPosY = event.pageY;
        addUsl.dropFrom = event.target.id;
        if (addUsl.dropFrom === '') {
            addUsl.dropFrom = $(event.target).closest('ul').attr('id');
        }
        var rval = event.dataTransfer.getData("text/plain");
        if (addUsl.typesFlt.indexOf(rval) !== -1) {
            var cntFld = $("#"+addUsl.dropFrom).find('.list-group-item').length;
            if (cntFld > 4) {
                toastr.error("Элементов слишком много", "Ошибка");
                return false;
            } else {
                addUsl.idField = event.dataTransfer.getData("text/plain");
                $('#addFeildModal').modal('show');
            }
        }
    }
};

