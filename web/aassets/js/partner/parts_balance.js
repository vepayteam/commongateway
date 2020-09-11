$(document).ready(function() {
    var datatable = $('#example').DataTable({
        "processing": true,
        "serverSide": true,
        "columns": datatableColumns,
        "ajax": function (data, callback) {

            var dataRequest = {
                'draw': data['draw'],
                'columns': data['columns'],
                'length': data['length'],
                'order': data['order'],
                'start': data['start'],
                'filters': {
                    'partnerId': $("#part-balance__form__partnerId").val(),
                    'datefrom': $("#part-balance__form__datefrom").val(),
                    'dateto': $("#part-balance__form__dateto").val()
                }
            };
            // debugger;
            $.ajax({
                'method': 'POST',
                'url': '/partner/mfo/parts-balance-processing',
                'data': dataRequest
            }).done(function(response) {
                callback(response);
            });
        }
    } );

    // $("#parts-balance__form__submit").click(function(e) {
    //     e.preventDefault();
    //     datatable.ajax.reload();
    //     return false;
    // });
});
