// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    MyApp.$me.dataRowSend={};
    var idForm = $('#form_input');
    var cbTahun = $('.cbTahun');
    /*set tabel data per tahun di pop up*/
    var loadTableDataTahun = function (dataRow) {
        params = {
            option : "PUBLIC",
            action : "getDataPerTahun",
            id_sumber_data : dataRow.id_sumberdata,
            tahun : cbTahun.val()
        };
        var selectorBody = $('.bodiku');
        selectorBody.html('');
        MyApp.ajax(params).done(function (resp) {
            $.each(resp.result,function (index, val) {
                // debugger;
                if (index.substring(0, 7)=='dikunci'){
                    $('.'+index).prop('checked',val);
                }else{
                    $('.'+index).val(val);
                }


                /*var bgClass = '';
                if(val.tahun == tahun && val.bulan == bulan){
                    bgClass='bg-danger';
                }
                htmlku = '<tr class="'+bgClass+'">' +
                    '<th>'+val.tahun+'</th>' +
                    '<th>'+val.bulan+'</th>' +
                    '<td>' +val.data+'</td>' +
                    '<th>'+val.keterangan+'</th>' +
                    '<th>'+val.lock_data+'</th>' +
                    '</tr>';
                selectorBody.append(htmlku);*/
            })

        });
    };
    cbTahun.on('change', function () {
        loadTableDataTahun(MyApp.$me.dataRowSend);
    });

    /*set data tahun*/
    cbTahun.html('');
    for(var taun=2016;taun<2030;taun++){
        cbTahun.append('<option value="'+taun+'">'+taun+'</option>');
    }
    cbTahun.select2();


    MyApp.$me.selectorTable = $('#datatable_fixed_column').DataTable({
        serverSide: true,
        processing: true,
        destroy: true,
        bInfo: true,
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
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
                "data": "id_sumberdata",
                width: 5
            },
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow[MyApp.$me.index] = val;
                    btn = "<a style='padding: 2px;' href='#' class='btUpdate' data-index='" + MyApp.$me.index + "'" +
                        " title='Update data'><i class='fa fa-pencil-square fa-2x'></i></a>" +
                        "<a style='color:green;padding: 2px;' href='#' data-index='" + MyApp.$me.index + "' title='Lihat Data' class='btLihatData'>" +
                        "<i class='fa fa-search fa-2x'></i></a>"+
                        "<a style='color:#e80531;padding: 2px;' href='#' data-index='" + MyApp.$me.index + "' title='Hapus Data' class='btHapus'>" +
                        "<i class='fa fa-trash-o fa-2x'></i></a>";
                    MyApp.$me.index++;
                    return btn;
                },
                width: '10%'
            },
            {"data": "kode_sumberdata"},
            {"data": "sumberdata"},{"data": "indikator_terlibat"}
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
            $('#datatable_fixed_column_filter').append(button);
            $('.btTop').click(function () {
                aksi = $(this).data('aksi');
                if(aksi === 'add'){
                    MyApp.$me.aksi = 'add';
                    $('#myModal').modal('show');
                }else if(aksi === 'refresh'){
                    MyApp.$me.selectorTable.ajax.reload();
                }else if(aksi === 'pdf'){
                    $('#myModalLoading').modal('show')
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
                MyApp.$me.dataRowSend = MyApp.$me.dataRow[index];
                // debugger;
                loadTableDataTahun(MyApp.$me.dataRowSend);
                MyApp.setFormValues(idForm,MyApp.$me.dataRowSend);
            });

            /*lihat data*/
            selectortable.on('click','.btLihatData',function () {
                MyApp.$me.aksi = 'view';
                $('#myModalViewData').modal('show');
                index = $(this).data('index');
                MyApp.$me.dataRowSend = MyApp.$me.dataRow[index];
                loadViewData(MyApp.$me.dataRowSend);
                loadTableDataTahun(MyApp.$me.dataRowSend);
                MyApp.setFormValues($('#form_input2'),MyApp.$me.dataRowSend);
            });

            /*Hapus data*/
            selectortable.on('click','.btHapus',function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow[index];
                konfirmasi = confirm('Apakah anda yakin ingin menghapus data '+dataRow.id);
                if(konfirmasi){
                    hapus(dataRow);
                }
            });

        }
    });

    var loadViewData = function(params){
        MyApp.$me.selectorTable = $('#tabel_data_bulan').DataTable({
            serverSide: false,
            processing: true,
            destroy: true,
            paging:false,
            bInfo: true,
            bFilter: false,
            buttons: [],
            ajax: {
                url: "service.php",
                method: "POST",
                data: {
                    Module: MyApp.curMod,
                    option: "ACTION",
                    action: "listViewDataBulan",
                    id_sumber_data : params.id_sumberdata,
                    draw: function () {
                        MyApp.$me.draw++;
                        return MyApp.$me.draw;
                    }
                }
            },
            "columns": [
                {
                    "data": "tahun",
                    width: 5
                },{
                    "data":"tahun",
                    title:"Tahun"
                },{
                    "data": function (val) {
                        if(!val.jan){
                            return 0;
                        }else{
                            return val.jan
                        }
                    },
                    title:"Jan"
                },{
                    "data": function (val) {
                        if(!val.feb){
                            return 0;
                        }else{
                            return val.feb
                        }
                    },
                    title:"Feb"
                },{
                    "data": function (val) {
                        if(!val.mar){
                            return 0;
                        }else{
                            return val.mar
                        }
                    },
                    title:"Mar"
                },{
                    "data": function (val) {
                        if(!val.aprl){
                            return 0;
                        }else{
                            return val.aprl
                        }
                    },
                    title:"Aprl"
                },{
                    "data": function (val) {
                        if(!val.mei){
                            return 0;
                        }else{
                            return val.mei
                        }
                    },
                    title:"Mei"
                },{
                    "data": function (val) {
                        if(!val.jun){
                            return 0;
                        }else{
                            return val.jun
                        }
                    },
                    title:"Jun"
                },{
                    "data": function (val) {
                        if(!val.jul){
                            return 0;
                        }else{
                            return val.jul
                        }
                    },
                    title:"Jul"
                },{
                    "data": function (val) {
                        if(!val.agt){
                            return 0;
                        }else{
                            return val.agt
                        }
                    },
                    title:"Agt"
                },{
                    "data": function (val) {
                        if(!val.sept){
                            return 0;
                        }else{
                            return val.sept
                        }
                    },
                    title:"Sept"
                },{
                    "data": function (val) {
                        if(!val.okt){
                            return 0;
                        }else{
                            return val.okt
                        }
                    },
                    title:"Okt"
                },{
                    "data": function (val) {
                        if(!val.nov){
                            return 0;
                        }else{
                            return val.nov
                        }
                    },
                    title:"Nov"
                }, {
                    "data": function (val) {
                        if(!val.des){
                            return 0;
                        }else{
                            return val.des
                        }
                    },
                    title:"Des"
                }
            ],
            dom: 'Bfrtip',
            rowCallback: function (row, data, index) {
                $('td:eq(0)', row).html(index + 1);
                return row;
            },
            "createdRow": function ( row, data, index ) {
                if(index%2==0){
                    $('td', row).eq(1).addClass('highlight2');
                    $('td', row).eq(2).addClass('highlight2');
                    $('td', row).eq(3).addClass('highlight2');
                    $('td', row).eq(4).addClass('highlight2');
                    $('td', row).eq(5).addClass('highlight2');
                    $('td', row).eq(6).addClass('highlight2');
                    $('td', row).eq(7).addClass('highlight2');
                    $('td', row).eq(8).addClass('highlight2');
                    $('td', row).eq(9).addClass('highlight2');
                    $('td', row).eq(10).addClass('highlight2');
                    $('td', row).eq(11).addClass('highlight2');
                    $('td', row).eq(12).addClass('highlight2');
                    $('td', row).eq(13).addClass('highlight2');
                    $('td', row).eq(14).addClass('highlight2');
                }
            },
            initComplete: function () {
                /*cariField = $('input[type=search]');
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
                    /!*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                     '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                     '</button>'+*!/
                    '<button data-aksi="print" style="margin: 5px 5px 0 0" class="btn btn-primary btTop">' +
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

                selectortable =  $('table');*/
            }
        });
    }

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
            // export_type: 'stream'
        }
        MyApp.ajax(params).done(function (resp) {
            $('#myModalLoading').modal('hide');
            window.open('download.php?file='+resp.signed_file,'_blank');
        });
    };

    /*on change checkbox kunci data*/
    $('.check_kunci').on("change",function () {
        var checked = $(this).prop('checked');
        idForm = $('#form_input');
        dataForm = MyApp.getFormValues(idForm);
        params = {
            option : "sumberData",
            action : "kunci",
            locked : checked,
            data : dataForm
        };
        MyApp.ajax(params).done(function (resp) {
            if(resp.success){
                loadTableDataTahun(dataForm);
                alert(resp.msg)
            }
        });
    })

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
                        data2[i]={};data2[i].id_sumberdata = nama=resp.result[i].id_sumberdata;data2[i].kode_sumberdata = nama=resp.result[i].kode_sumberdata;data2[i].sumberdata = nama=resp.result[i].sumberdata;data2[i].T2016 = nama=resp.result[i].T2016;data2[i].T2017 = nama=resp.result[i].T2017;
                    }

                    dataRow.value = data2;
                    dataRow.judul = "Data Print ";
                    tpl = tpl+"<script type='javascript'>$(document).ready(function() { window.print() })</script>";
                    var rendered = Mustache.render(tpl, dataRow);
                    var win = window.open("", "Title", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1200, height=600");
                    win.document.body.innerHTML = rendered;
                    win.print();
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
    /*Tambahkan data per tahun form*/
    $('.btTambahDataTahun').on('click', function () {
        idForm = $('#form_input');
        dataForm = MyApp.getFormValues(idForm);
        params = {
            option: 'ACTION',
            action: 'addDataTahun',
            data: dataForm
        };
        MyApp.ajax(params).done(function (resp) {
            if (resp.success == true) {
                loadTableDataTahun(dataForm);
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
