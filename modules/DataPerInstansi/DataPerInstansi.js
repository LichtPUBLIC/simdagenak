// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    MyApp.$me.dataRow1 = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');
    /*load daftar instansi*/
    var cb = $('#cbInstansi');
    var params05 = {
        option: 'ACTION',
        action: 'getListInstansi',
        year: 2018
    };
    MyApp.ajax(params05).done(function(response) {
        var listInstansi = response.list_instansi;
        cb.html("<option value=''>--Pilih Instansi--</option>");
        for (var x = 0; x < listInstansi.length; x++) {
            cb.append('<option value="' + listInstansi[x].id_instansi + '">' +
                listInstansi[x].kode_instansi + '. ' + listInstansi[x].instansi + '</option>');
        }
        cb.select2();
    });


    MyApp.$me.selectorTable = $('#datatable_fixed_column').DataTable({
        serverSide: true,
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
                draw: function () {
                    MyApp.$me.draw++;
                    return MyApp.$me.draw;
                }
            }
        },
        "columns": [
            {
                "data": "id_data_per_instansi",
                width: 5
            },
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow1[val.id_data_per_instansi] = val;
                    btn = "<a style='padding: 2px;' href='#' class='btUpdate' data-index='" + val.id_data_per_instansi + "'" +
                        " title='Update data'><i class='fa fa-pencil-square fa-2x'></i></a>" +
                        "<a style='color:#e80531;padding: 2px;' href='#' data-index='" + val.id_data_per_instansi + "' title='Hapus Data' class='btHapus'>" +
                        "<i class='fa fa-trash-o fa-2x'></i></a>" +
                    "<a style='color:#6BAB93;padding: 2px;' href='#' data-index='" + val.id_data_per_instansi + "' title='Tambah Data Per Tahun' class='btDataTahun'>" +
                        "<i class='fa fa-plus fa-2x'></i></a>"
                    ;
                    MyApp.$me.index++;
                    return btn;
                },
                width: '10%'
            },
            {"data": "deskripsi"},
            {"data": "instansi"},
            {"data": "jenis_data"}
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

            button = '<button data-aksi="refresh" style="margin: 5px 5px -15px 0" class="btn btn-danger btTop">' +
                '<i class="fa fa-refresh"></i> <span> Refresh</span>' +
                '</button>' +
                '<button data-aksi="add" style="margin: 5px 5px -15px 0" class="btn btn-success btTop">' +
                '<i class="fa fa-plus"></i> <span> Tambah Data</span>' +
                '</button>' +
                '<button data-aksi="pdf" style="margin: 5px 5px -15px 0" class="btn btn-warning btTop">' +
                '<i class="fa fa-file-pdf-o"></i> <span> PDF</span>' +
                '</button>'+
                /*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                 '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                 '</button>'+*/
                '<button data-aksi="print" style="margin: 5px 5px -15px 0" class="btn btn-primary btTop">' +
                '<i class="fa fa-print"></i> <span> Print</span>' +
                '</button>'
            ;
            $('#datatable_fixed_column_filter').append(button);
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
            selectortable.on('click','.btUpdate',function () {
                MyApp.$me.aksi = 'update';
                $('#myModal').modal('show');
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow1[index];
                MyApp.setFormValues(idForm,dataRow);
            });

            /*Hapus data*/
            selectortable.on('click','.btHapus',function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow1[index];
                konfirmasi = confirm('Apakah anda yakin ingin menghapus data '+dataRow.id);
                if(konfirmasi){
                    hapus(dataRow);
                }
            });

            /*tambah data per tahun*/
            selectortable.on('click','.btDataTahun',function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow1[index];
                debugger;
                MyApp.openModuleWindow(dataRow.jenis_data,dataRow);
            });

        }
    });


    $('#form_target_realisasi').submit(function (e) {
        e.preventDefault();
    });

    /*export excel*/
    var export_excel = function () {
        MyApp.showLoading('Export excel');
        var param = {
            option: 'ACTION',
            action: 'excel',
            export_type : 'stream'
        };
        MyApp.ajax(param).done(function (resp) {
            if (resp.success) {
                window.location = MyApp.rootPath() + resp.filename;
                MyApp.hideLoading();
            }

        });
    };

    /*export pdf*/
    var export_pdf = function () {
        var params = {
            option: 'ACTION',
            action: 'pdf',
            export_type: 'stream'
        }
        MyApp.ajax(params).done(function (resp) {
            if (resp.success) {
                MyApp.openPdf(resp.filename, 'export_pdf');
            }
        });
    };

    /*Print Halaman*/
    var print_hal = function () {
        var params = {
            option: 'ACTION',
            action: 'listPrint'
        }
        MyApp.ajax(params).done(function (resp) {
            console.log(resp);
            if (resp.success) {
                MyApp.loadModuleFile('template/tpl_pdf.html', function (tpl) {
                    var dataRow = [];
                    data2 =[];
                    for (i=0;i<resp.result.length;i++){
                        data2[i]={};data2[i].id_data_per_instansi = nama=resp.result[i].id_data_per_instansi;data2[i].deskripsi = nama=resp.result[i].deskripsi;data2[i].id_instansi = nama=resp.result[i].id_instansi;data2[i].jenis_data = nama=resp.result[i].jenis_data;data2[i].user_input = nama=resp.result[i].user_input;data2[i].user_update = nama=resp.result[i].user_update;data2[i].tgl_input = nama=resp.result[i].tgl_input;data2[i].tgl_update = nama=resp.result[i].tgl_update;
                    }

                    dataRow.value = data2;
                    dataRow.judul = "Data Print ";
                    var rendered = Mustache.render(tpl, dataRow);
                    var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1200, height=600");
                    win.document.write(rendered);
                    win.document.write("<script type='text/javascript'>window.onload = function() { setTimeout(function() { window.print(); }, 500); }</script>");
                    win.document.close();
                }).done(function () {

                });
            }
        });
    };

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
                alert(resp.msg);
                MyApp.$me.selectorTable.ajax.reload();
            } else {
                alert(resp.msg);
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
