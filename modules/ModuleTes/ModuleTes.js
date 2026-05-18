// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');
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
                "data": "nama",
                width: 5
            },
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow[MyApp.$me.index] = val;
                    btn = "<a style='padding: 2px;' href='#' class='btUpdate' data-index='" + MyApp.$me.index + "'" +
                        " title='Update data'><i class='fa fa-pencil-square fa-2x'></i></a>" +
                        "<a style='color:#e80531;padding: 2px;' href='#' data-index='" + MyApp.$me.index + "' title='Hapus Data' class='btHapus'>" +
                        "<i class='fa fa-trash-o fa-2x'></i></a>";
                    MyApp.$me.index++;
                    return btn;
                },
                width: '8%'
            },
            {"data": "nama"},
            {"data": "alamat"},
            {"data": "jenis_kelamin"}
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
                '<i class="fa fa-file-pdf-o"></i> <span> Lihat PDF</span>' +
                '</button>'+
                /*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                '</button>'+*/
                '<button data-aksi="print" style="margin: 5px 5px 0 0" class="btn btn-primary btTop">' +
                '<i class="fa fa-print"></i> <span> Print</span>' +
                '</button>'+
                '<button data-aksi="upload" style="margin: 5px 5px 0 0" class="btn btn-default btTop">' +
                '<i class="fa fa-upload"></i> <span> Upload</span>' +
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
                }else if(aksi === 'upload'){
                    $('#myModalUpload').modal('show');
                }
            });

            /*update data*/
            $('.btUpdate').click(function () {
                MyApp.$me.aksi = 'update';
                $('#myModal').modal('show');
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow[index];
                MyApp.setFormValues(idForm,dataRow);
            });

            /*Hapus data*/
            $('.btHapus').click(function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow[index];
                konfirmasi = confirm('Apakah anda yakin ingin menghapus data '+dataRow.id);
                if(konfirmasi){
                    hapus(dataRow);
                }
            });

        }
    });


    $('#form_target_realisasi').submit(function (e) {
        e.preventDefault();
    });

    /*export pdf*/
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
                    tahunNow = new Date().getFullYear();
                    tahunNow = 2016;
                    tahun = [];
                    for (var i=0;i<6;i++){
                        tahun[i] = tahunNow+i;
                    }

                    data2 =[];
                    for (i=0;i<resp.result.length;i++){
                        data2[i]={};
                        data2[i].id=nama=resp.result[i].id;
                        data2[i].nama=resp.result[i].nama;
                        data2[i].alamat=resp.result[i].alamat;
                        data2[i].jenis_kelamin=resp.result[i].jenis_kelamin;
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

    /*upload data*/
    $('#form_input_upload').on('submit', (function (e) {
            e.preventDefault();
            if ($('.file').val()==''){
                alert('Anda harus melampirkan file');
            }else{
                var param = {
                    url: "service.php?Module="+MyApp.curMod+"&option=PUBLIC&action=upload", // Url to which the request is send
                    type: "POST", // Type of request to be send, called as method
                    data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                    contentType: false, // The content type used when sending data to the server.
                    cache: false, // To unable request pages to be cached
                    processData: false, // To send DOMDocument or non processed data file it is set to false
                };
                $.ajax(param).done(function (resp) {
                    console.log(resp);
                    // MyApp.hideLoading();
                    var data = JSON.parse(resp);
                    if (data.success == true) {
                        $('.keterangan').val('');
                        $('.sumber').val('');
                        $('.file').val('');
                        alert("Upload Berhasil\n Upload file berhasil");
                        MyApp.$me('.tabel').jsGrid('loadData');
                    }else{
                        alert("Upload Gagal\n Upload file Gagal");
                    }
                });
            }
        })
    );

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

    $('#startdate').datepicker({
        dateFormat: 'dd.mm.yy',
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        onSelect: function (selectedDate) {
            $('#finishdate').datepicker('option', 'minDate', selectedDate);
        }
    });

    /*MathJax.Hub.Register.StartupHook('End', function () {
        MathJax.Hub.processSectionDelay = 0
        var demoSource = document.getElementById('demoSource')
        var demoRendering = document.getElementById('demoRendering')
        var math = MathJax.Hub.getAllJax('demoRendering')[0];
        debugger;
        demoSource.addEventListener('input', function () {
            MathJax.Hub.Queue(['Text', math, demoSource.value])
        })
    })*/
    CKEDITOR.replace( 'ckeditor', { height: '380px', startupFocus : true} );


    // simulasi loading... hide setelah 500ms
    $('.modal-backdrop').addClass('hide');
    setTimeout(function () {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=SampleModule.js
