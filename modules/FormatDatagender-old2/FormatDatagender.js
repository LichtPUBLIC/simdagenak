// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');
    // debugger;
    MyApp.$me.selectorTable = $('#datatable_fixed_column').DataTable({
        serverSide: true,
        processing: true,
        destroy: true,
        paging: true,
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
                "data": "id_data_pilah",
                width: 5
            },
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow[val.id_data_pilah] = val;
                    btn ="<a style='color:#00ce7b;padding: 2px;' href='#' data-index='" + val.id_data_pilah + "' title='Input Data' class='btTambahData'>" +
                        "<i class='fa fa-plus fa-2x'></i></a>" +
                        /*"<a style='color:#175fd3;padding: 2px;margin-left: 10px;' href='#' data-index='" + val.id_data_pilah + "' title='view Data' class='btView'>" +
                        "<i class='fa fa-search fa-2x'></i></a>" +*/
                        "";
                    MyApp.$me.index++;
                    return btn;
                },
                width: '8%'
            },{"data": "judul_data_pilah"}, {"data": "instansi"}
        ],
        dom: 'Bfrtip',
        //rowCallback: function (row, data, index) {
        //    $('td:eq(0)', row).html(index + 1);
        //    return row;
        //},
	fnRowCallback: function (nRow, aData, iDisplayIndex) {
            var info = $(this).DataTable().page.info();
            $("td:nth-child(1)", nRow).html(info.start + iDisplayIndex + 1);
            return nRow;
        },
        initComplete: function () {
            cariField = $('input[type=search]');
            cariField.addClass('form-control');
            cariField.attr("placeholder", "Cari Data");

            button = '<button data-aksi="refresh" style="margin: 5px 5px -41px 0" class="btn btn-danger btTop">' +
                '<i class="fa fa-refresh"></i> <span> Refresh</span>' +
                '</button>' +
                /*'<button data-aksi="add" style="margin: 5px 5px 0 0" class="btn btn-success btTop">' +
                '<i class="fa fa-plus"></i> <span> Tambah Data</span>' +
                '</button>' +*/
                '<button data-aksi="pdf" style="margin: 5px 5px -41px 0" class="btn btn-warning btTop">' +
                '<i class="fa fa-file-pdf-o"></i> <span> PDF</span>' +
                '</button>'+
                /*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                 '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                 '</button>'+*/
                '<button data-aksi="print" style="margin: 5px 5px -41px 0" class="btn btn-primary btTop">' +
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
                dataRow = MyApp.$me.dataRow[index];
                MyApp.setFormValues(idForm,dataRow);
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

            /*Tambah data*/
            selectortable.on('click','.btTambahData',function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow[index];
                loadFormFormat(dataRow);
            });
        }
    });

    /*load form format dari tpl*/
    MyApp.$me.dataRowFormat = {}
    var loadFormFormat = function(dataRow,tahun=2016){
        var curtahun = tahun;
        MyApp.$me.dataRowFormat = dataRow;
        // debugger;
        params = {
            option:'PUBLIC',
            action:'getDataFormat',
            tahun:tahun,
            id_data_pilah:dataRow.id_data_pilah,
            kode_data_pilah:dataRow.kode_data_pilah
        }
        MyApp.ajax(params).done(function (resp) {
            var cell='';
            var td='';
            MyApp.$me.kolom = resp.kolom;
            var colspantahun = 0;
            var kolomChild = "";
            var kolomParent = "";
            $.each(resp.kolomHead, function (index, val) {
                if(index !==0){
                    kolomParent+="<th class='th-format' colspan='"+val.length+"'>"+index+"</th>";
                }

                $.each(val,function (idx, child) {
                    kolomChild+="<th class='th-format'>"+child+"</th>";
                    colspantahun++;
                })

                // else{
                //     $.each(val,function (idx, child) {
                //         kolomChild+="<th>"+child+"</th>";
                //         colspantahun++;
                //     })
                // }
            })
            var kolomHeader = "<tr><th class='th-format' rowspan='3'>No</th><th class='th-format' rowspan='3'>" +
                MyApp.$me.dataRowFormat.header_baris +
                                "</th><th class='th-format' colspan='"+colspantahun+"'>"+curtahun+"</th></tr>" +
                                "<tr>"+kolomParent+"</tr>" +
                                "<tr>"+kolomChild+"</tr>";
            // debugger;

            $.each(resp.result,function (index,val) {
                no = index+1;
                td +="<td style='text-align: center'>"+no+"</td>";
                $.each(val,function (i, record) {
                    if(record == null){
                        record=0;
                    }
                    // debugger;
                    if( i !='kode_baris' && i !='kode_data_pilah'){
                        if(i == 'nama_baris'){
                            MyApp.$me.namabaris = record;
                            td +="<th style='text-align: left;'>"+record+"</th>";
                        }else{
                            indexKolom = i.substring(0,5);
                            if(MyApp.$me.kolom[indexKolom]=='L+P'){
                                td +="<td><input disabled style='width: 100%;' name='"+i+"_"+index+"' value='"+MyApp.$me.totalLp+"'/></td>";
                                MyApp.$me.totalLp = 0;
                            }else if(MyApp.$me.kolom[indexKolom]=='L' || MyApp.$me.kolom[indexKolom]=='P'){
                                MyApp.$me.totalLp = MyApp.$me.totalLp + record;
                                classInput = MyApp.$me.kolom[indexKolom]+MyApp.$me.namabaris;
                                td +="<td><input class='"+classInput+"' style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
                            }else{
                                td +="<td><input style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
                            }

                        }
                    }
                })
                cell +="<tr>"+td+"</tr>";
                td='';
            })
            $table = "<table class='table-bordered2 table-bordered'>"+kolomHeader+
                cell+"</table>";
            $('.renderedFormat').html($table);
            $('.titleDataFormat').html(MyApp.$me.dataRowFormat.judul_data_pilah);
            $('#myModalFormatForm').modal('show');

            /*$('.titleDataFormat').html(resp.result[0].title)
            MyApp.loadModuleFile('template/'+dataRow.grup_format+'.html', function (tpl) {
                var dataRow = [];
                var i = 0;
                $.each(resp.result,function (index, val) {
                    nomor = index+1;
                    resp.result[i].nomor = nomor;
                    i++;
                })
                dataRow.value = resp.result;
                dataRow.tahun = resp.result[0].tahun;
                var rendered = Mustache.render(tpl, dataRow);
                $('.renderedFormat').html(rendered);
                $('#idTahun').val(tahun);
                $('#myModalFormatForm').modal('show');
            }).done(function () {

            });*/
        })
    }

/*on change combo tahun*/
    $('#myModalFormatForm').on('change','#idTahun',function () {
        $tahun = $(this).val();
        loadFormFormat(MyApp.$me.dataRowFormat,$tahun);
    })

    /*on submit form data format*/
    $('#myModalFormatForm').on('click','.btSimpanDataFormat',function () {
        dataForm = MyApp.getFormValues($('#myModalFormatForm form#formDataFormat'));
        var $dataAll = $.extend(dataForm,MyApp.$me.dataRowFormat);
        /*params = {
            option:'PUBLIC',
            action:'UpdateDataFormat',
            data : $dataAll
        }
        MyApp.ajax(params).done(function (resp) {
            alert('berhasil');
            loadFormFormat(MyApp.$me.dataRowFormat,$dataAll.tahun);
        })*/

        $.ajax({
            url : "service.php?option=PUBLIC&action=UpdateDataFormat&Module=FormatDatagender",
            data:{
                data:$dataAll
            },
            method : "POST"
        }).done(function (resp) {
            alert('Berhasil Menyimpan');
            loadFormFormat(MyApp.$me.dataRowFormat,$dataAll.tahun);
        })

    })

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
                        data2[i]={};data2[i].id_format = nama=resp.result[i].id_format;data2[i].title = nama=resp.result[i].title;data2[i].grup_format = nama=resp.result[i].grup_format;
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
