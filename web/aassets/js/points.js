(function ($) {

    "use strict";

    var linklink = 0;
    var points = {

        listinit: function () {
            $('a[data-action="delPoint"]').on('click', function () {
                var delPointId = $(this).attr('data-id');
                swal({
                    title: "Подтвердите удаление точки?",
                    text: "Послу удаление точки её использование будет невозможно!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Да, удалить!",
                    cancelButtonText: "Отмена",
                    closeOnConfirm: false
                }, function () {
                    points.delPoint(delPointId);
                });
            });

            $('select[name="partnersel"]').on('change', function () {
                var selm = $('select[name="magazsel"] :selected').val();
                var selp = $(this).val();
                selFilter(selm, selp);
            });

            $('select[name="magazsel"]').on('change', function () {
                var selp = $('select[name="partnersel"] :selected').val();
                var selm = $(this).val();
                selFilter(selm, selp);
            });

            function selFilter(selm, selp) {
                if (selm > 0 && selp > 0) {
                    $('tr[data-partner = "'+selp+'"][data-magaz = "'+selm+'"]').show();
                    $('tr[data-magaz][data-partner != "'+selp+'"], tr[data-magaz][data-magaz != "'+selm+'"]').hide();
                    $('#PointAddBtn').attr('href', 'point-add/' + selp);

                } else if (selp > 0) {
                    $('tr[data-partner = "'+selp+'"]').show();
                    $('tr[data-magaz][data-partner != "'+selp+'"]').hide();
                    $('#PointAddBtn').attr('href', 'point-add/' + selp);

                } else if (selm > 0) {
                    $('tr[data-magaz = "'+selm+'"]').show();
                    $('tr[data-magaz][data-magaz != "'+selm+'"]').hide();

                } else {
                    $('tr[data-magaz]').show();
                }
            }

        },
        init: function () {
            $('#pointeditform').on('submit', function () {
                points.savePoint();
                return false;
            });

            $('select[name="IsCustom"]').on('change', function () {
                if ($(this).val() == 1) {
                    $('#siteConstructBtn').show();
                } else {
                    $('#siteConstructBtn').hide();
                }
            }).trigger('change');
            
            $('#siteConstructBtn').on('click', function () {
                var IdUsl = $('#pointeditform').find('[name="ID"]').val();
                var IdPartner = $('#pointeditform').find('[name="idpart"]').val();
                if (IdUsl > 0 && IdPartner > 0) {
                    window.location = '/partner/uslug/add?IdPartner=' + IdPartner + '&IdUsl=' + IdUsl;
                }
            });

            $('#showKodModal').on('show.bs.modal', function (e) {
                var idWgt = $(e.relatedTarget).attr('data-id');
                $(e.currentTarget).find('.modal-body').html(
                    '<p>Код для размещения на сайте: ' +
                    '<p><code>&lt;script src="https://qroplata.ru/shop.js"&gt;&lt;/script&gt;<br>' +
                    '&lt;a href="javascript:windowOpen.showQR({id:&apos;'+idWgt+'&apos;});">Оплатить&lt;/a&gt;' +
                    '</code>');
            });

            $('#modalMailInfo').on('show.bs.modal', function (e) {
                $('#summernote').summernote({
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']]
                    ]
                });

                $('#summernote').summernote('code', $('input[name="EmailShablon"]').val());
                $('#summernote').summernote('focus');
            });

            $('#modalMailInfoSave').on('click', function () {
                var text = $('#summernote').summernote('code');
                $('#summernote').summernote('destroy');
                $('input[name="EmailShablon"]').val(text);
                $('#modalMailInfo').modal('hide');
            });

            $('#colorpickerField1, #colorpickerField2').ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val(hex);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            }).on('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
            });
        },

        savePoint: function () {

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1000
            };

            var form = new FormData($("#pointeditform")[0]);

            if (linklink) {
                linklink.abort();
            }

            linklink = $.ajax({
                type: "POST",
                url: '/partner/uslug/point-save',
                data: form,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#pointeditform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    if (data.status == 1) {
                        toastr.success("Ok", "Точка сохранена");
                        if (data.id) {
                            window.location = '/partner/uslug/point-edit/' + data.id;
                        }
                    } else {
                        toastr.error(data.error, "Ошибка");
                    }
                    $('#pointeditform').closest('.ibox-content').toggleClass('sk-loading');
                },
                error: function (data) {
                    $('#pointeditform').closest('.ibox-content').toggleClass('sk-loading');
                    toastr.error("Ошибка запроса", "Ошибка");
                }
            });

        },

        delPoint: function (id) {
            if (linklink) {
                linklink.abort();
            }

            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var partner = $('#IdPartner').val();
            linklink = $.ajax({
                type: "POST",
                url: '/partner/uslug/point-del',
                data: {'id': id, '_csrf': csrfToken},
                beforeSend: function () {
                },
                success: function (data) {
                    swal({
                        title: "Точка удалена!",
                        text: "Точка была удалена.",
                        type: "success"
                    }, function () {
                        window.location = '/partner/uslug/index';
                    });
                },
                error: function (data) {
                }
            });
        }

    };

    window.points = points;

}(jQuery || $));

