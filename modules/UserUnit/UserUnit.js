// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');
    /*load daftar instansi*/
    var cb = $('.cbUser');
    var params05 = {
        option: 'ACTION',
        action: 'userList',
        year: 2018
    };
    MyApp.ajax(params05).done(function(response) {
        var listUser = response.result;
        // debugger;
        cb.html("<option value=''>--Pilih User--</option>");
        for (var x = 0; x < listUser.length; x++) {
            cb.append('<option value="' + listUser[x].user_id + '">' +
                listUser[x].user_id + ' - ' + listUser[x].nama + '</option>');
        }
        cb.select2();
    });

    MyApp.$me.selectorTable = $('#datatable_fixed_column').DataTable({
        serverSide: false,
        processing: true,
        destroy: true,
        bInfo: true,
        buttons: [],
        ajax: {
            url: "service.php",
            method: "POST",
            data: {
                Module: MyApp.curMod,
                option: "ACTION",
                action: "list",
                year: 2018,
                user_id: function () {
                    return cb.val()
                },
                draw: function () {
                    MyApp.$me.draw++;
                    return MyApp.$me.draw;
                }
            }
        },
        "columns": [
            {
                "data": "kode_instansi",
                width: 5
            },
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow[MyApp.$me.index] = val;
                    btn = "<a style='padding: 2px;' href='#' class='btTambahkan' data-index='" + MyApp.$me.index + "'" +
                        " title='Tambahkan data'><i class='fa fa-plus fa-2x'></i></a>";
                    MyApp.$me.index++;
                    return btn;
                },
                width: '8%'
            },{"data": "kode_instansi"},{"data": "instansi"},
        ],
        dom: 'Bfrtip',
        rowCallback: function (row, data, index) {
            $('td:eq(0)', row).html(index + 1);
            return row;
        },
        initComplete: function () {
            cariField = $('input[type=search]');
            cariField.addClass('form-control');
            cariField.attr("placeholder", "Cari Data");

            button = '<button data-aksi="refresh" style="margin: 5px 5px 0 0" class="btn btn-danger btTop">' +
                '<i class="fa fa-refresh"></i> <span> Refresh</span>' +
                '</button>' +
                '<button data-aksi="add" style="margin: 5px 5px 0 0" class="btn btn-success btTop">' +
                '<i class="fa fa-plus"></i> <span> Tambah Data</span>' +
                '</button>' +
                '<button data-aksi="pdf" style="margin: 5px 5px 0 0" class="btn btn-warning btTop">' +
                '<i class="fa fa-file-pdf-o"></i> <span> PDF</span>' +
                '</button>'+
                /*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                 '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                 '</button>'+*/
                '<button data-aksi="print" style="margin: 5px 5px 0 0" class="btn btn-primary btTop">' +
                '<i class="fa fa-print"></i> <span> Print</span>' +
                '</button>'
            ;
            // $('#datatable_fixed_column_filter').append(button);
            $('.btTop').click(function () {
                aksi = $(this).data('aksi');
                if(aksi === 'add'){
                    MyApp.$me.aksi = 'add';
                    $('#myModal').modal('show');
                }else if(aksi === 'refresh'){
                    MyApp.$me.selectorTable.ajax.reload();
                }else if(aksi === 'pdf'){
                    export_pdf();
                }else if(aksi === 'print'){
                    print_hal();
                }else if(aksi === 'excel'){
                    export_excel();
                }
            });
            selectortable =  $('table');
            /*update data*/
            selectortable.on('click','.btTambahkan',function () {
                if(cb.val()==""){
                    alert("Anda harus memilih instansi terlebih dahulu")
                }else{
                    MyApp.$me.aksi = 'tambahkan';
                    index = $(this).data('index');
                    dataRow = MyApp.$me.dataRow[index];
                    dataRow.user_id = cb.val();
                    params = {
                        option: 'ACTION',
                        action: 'tambahkan',
                        data: dataRow
                    };
                    MyApp.ajax(params).done(function (resp) {
                        if (resp.success == true) {
                            // alert(resp.msg);
                            MyApp.$me.selectorTable.ajax.reload();
                            MyApp.$me.selectorTable2.ajax.reload();
                        } else {
                            // alert(resp.msg);
                        }
                    })
                }
            });
        }
    });

    MyApp.$me.selectorTable2 = $('#datatable_fixed_column2').DataTable({
        serverSide: false,
        processing: true,
        destroy: true,
        bInfo: true,
        buttons: [],
        ajax: {
            url: "service.php",
            method: "POST",
            data: {
                Module: MyApp.curMod,
                option: "ACTION",
                action: "list2",
                year: 2018,
                user_id: function () {
                    return cb.val()
                },
                draw: function () {
                    MyApp.$me.draw++;
                    return MyApp.$me.draw;
                }
            }
        },
        "columns": [
            {
                "data": "kode_instansi",
                width: 5
            },
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow[MyApp.$me.index] = val;
                    btn = "<a style='padding: 2px;color: red;' href='#' class='btHapus' data-index='" + MyApp.$me.index + "'" +
                        " title='Hapus data'><i class='fa fa-trash-o fa-2x'></i></a>";
                    MyApp.$me.index++;
                    return btn;
                },
                width: '8%'
            },{"data": "kode_instansi",width:"10%"},{"data": "instansi"},
        ],
        dom: 'Bfrtip',
        rowCallback: function (row, data, index) {
            $('td:eq(0)', row).html(index + 1);
            return row;
        },
        initComplete: function () {
            cariField = $('input[type=search]');
            cariField.addClass('form-control');
            cariField.attr("placeholder", "Cari Data");

            button = '<button data-aksi="refresh" style="margin: 5px 5px 0 0" class="btn btn-danger btTop">' +
                '<i class="fa fa-refresh"></i> <span> Refresh</span>' +
                '</button>' +
                '<button data-aksi="add" style="margin: 5px 5px 0 0" class="btn btn-success btTop">' +
                '<i class="fa fa-plus"></i> <span> Tambah Data</span>' +
                '</button>' +
                '<button data-aksi="pdf" style="margin: 5px 5px 0 0" class="btn btn-warning btTop">' +
                '<i class="fa fa-file-pdf-o"></i> <span> PDF</span>' +
                '</button>'+
                /*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                 '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                 '</button>'+*/
                '<button data-aksi="print" style="margin: 5px 5px 0 0" class="btn btn-primary btTop">' +
                '<i class="fa fa-print"></i> <span> Print</span>' +
                '</button>'
            ;
            // $('#datatable_fixed_column_filter').append(button);
            $('.btTop').click(function () {
                aksi = $(this).data('aksi');
                if(aksi === 'add'){
                    MyApp.$me.aksi = 'add';
                    $('#myModal').modal('show');
                }else if(aksi === 'refresh'){
                    MyApp.$me.selectorTable.ajax.reload();
                }else if(aksi === 'pdf'){
                    export_pdf();
                }else if(aksi === 'print'){
                    print_hal();
                }else if(aksi === 'excel'){
                    export_excel();
                }
            });
            selectortable =  $('table');

            /*Hapus data*/
            selectortable.on('click','.btHapus',function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow[index];
                dataRow.user_id = cb.val();
                hapus(dataRow);
            });

        }
    });


    $('#form_target_realisasi').submit(function (e) {
        e.preventDefault();
    });

    /*on change cb instansi*/
    cb.on('change',function () {
        MyApp.$me.selectorTable.ajax.reload();
        MyApp.$me.selectorTable2.ajax.reload();
    })


    /*simpan form*/
    $('.btSimpan').on('click', function () {
        idForm = $('#form_input');
        dataForm = MyApp.getFormValues(idForm);
        params = {
            option: 'ACTION',
            action: MyApp.$me.aksi,
            data: dataForm
        };
        MyApp.ajax(params).done(function (resp) {
            if (resp.success == true) {
                $('#myModal').modal('hide');
                alert(resp.msg);
                MyApp.$me.selectorTable.ajax.reload();
            } else {
                alert(resp.msg);
            }
        })
    });

    /*hapus data*/
    var hapus = function (dataForm) {
        params = {
            option: 'ACTION',
            action: 'delete',
            data: dataForm
        };
        MyApp.ajax(params).done(function (resp) {
            if (resp.success == true) {
                // alert(resp.msg);
                MyApp.$me.selectorTable.ajax.reload();
                MyApp.$me.selectorTable2.ajax.reload();
            } else {
                // alert(resp.msg);
            }
        })
    }
    // simulasi loading... hide setelah 500ms
    $('.modal-backdrop').addClass('hide');
    setTimeout(function () {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=SampleModule.js
