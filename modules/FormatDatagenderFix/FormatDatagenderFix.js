// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');
    // debugger;
    var params = {
        option: 'ACTION',
        action: 'list',     
        // user: MyApp.module.data     
    };

    MyApp.ajax(params)
            .done(function(response) {
                var data = response.result;
                // console.log(data);
                var tablexxx = $('#datatable_fixed_column').DataTable({
                    // lengthChange: false,
                    // info: false,
                    // bFilter: true,
                    bPaginate: true,
                    scrollX: true,
                    data: data,

                    columns: [  
                                                
                                { 
                                    title: "No", data: null, render: function (data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1;
                                    } 
                                },
                                {
                                    title: "Aksi",
                                    data: function (data,val, index){
                                        MyApp.$me.dataRow[data.id_data_pilah] = data;
                                        btn ="<a style='color:#175fd3;padding: 2px;' href='#' data-index='" + data.id_data_pilah + "' title='Lihat Data' class='btTambahData'>" +
                                            "<i class='fa fa-search fa-2x'></i></a>" +
                                            "";
                                        MyApp.$me.index++;
                                        return btn;
                                    }
                                },
                                { title: "Judul Data Pilah", data: "judul_data_pilah" },
                                { title: "Instansi", data: "instansi" }
                    ],

                 

                    initComplete: function () {
                        cariField = $('input[type=search]');
                        cariField.addClass('form-control');
                        cariField.attr("placeholder", "Cari Data");

                        //$('#datatable_fixed_column_filter').append(btn);
                        $('.btTop').click(function () {
                            aksi = $(this).data('aksi');
                            if(aksi === 'add'){
                                MyApp.$me.aksi = 'add';
                                $('#myModal').modal('show');
                            }else if(aksi === 'refresh'){
                                MyApp.ajax.reload();
                            }else if(aksi === 'pdf'){
                                export_pdf();
                            }else if(aksi === 'print'){
                                print_hal();
                            }else if(aksi === 'excel'){
                                export_excel();
                            }
                        });
            
                        /*Tambah data*/
                        $('#datatable_fixed_column').on('click','.btTambahData',function () {
                            index = $(this).data('index');
                            dataRow = MyApp.$me.dataRow[index];
                            loadFormFormat(dataRow);
                        });


                                                   
                    }
                })
            })


    /*on change combo tahun*/
    $('#myModalFormatForm').on('change','#idTahun',function () {
        $tahun = $(this).val();
        loadFormFormat(MyApp.$me.dataRowFormat,$tahun);
    })

   
    

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
                                td +="<td><input readonly class='"+classInput+"' style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
                            }else{
                                td +="<td><input readonly style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
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

        })
    }

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
            export_type: 'stream',
            data: data,
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

//# sourceURL=FormatDataGenderfix.js
