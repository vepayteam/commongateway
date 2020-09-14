$(document).ready(function() {
    'use strict';

    if(typeof datatableColumns == 'undefined') {
        return false;
    }

    var datatable = $('#example').DataTable({
        "scrollX": true,
        "processing": true,
        "serverSide": true,
        "colReorder": true,
        fixedHeader: true,
        orderCellsTop: true,
        "dom": 'lBCrtip',
        "lengthMenu": [[25, 100, 500, -1], [25, 100, 500, "All"]],
        "buttons": [
            'copy', 'excel'
        ],

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

    yadcf.init(datatable, [

        {
            column_number: 0,
            filter_type: "text",
            filter_delay: 500
        },
        {
            column_number: 1,
            filter_type: "text",
            filter_delay: 500
        },
        {
            column_number: 4,
            filter_type: "text",
            filter_delay: 500
        },

    ]);


    $("#parts-balance__form__submit").click(function(e) {
        e.preventDefault();
        datatable.ajax.reload();
        return false;
    });
});
