// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');var tablexx;
    // debugger;
    
    var datalist = function (){
        var params = {
            option: 'ACTION',
            action: 'list',     
            data: $('.cb-instansi').val()
            // user: MyApp.module.data     
        };

        MyApp.ajax(params)
                .done(function(response) {
                    var data = response.result;
                    // console.log(data);
                     tablexx= $('#datatable_fixed_column').DataTable({
                        lengthChange: false,
                        info: false,
                        bFilter: true,
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
                                            btn ="<a style='color:#00ce7b;padding: 2px;' href='#' data-index='" + data.id_data_pilah + "' title='Input Data' class='btTambahData'>" +
                                                "<i class='fa fa-plus fa-2x'></i></a>" +
                                                /*"<a style='color:#175fd3;padding: 2px;margin-left: 10px;' href='#' data-index='" + val.id_data_pilah + "' title='view Data' class='btView'>" +
                                                "<i class='fa fa-search fa-2x'></i></a>" +*/
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

    }

    $('#btnTampil').on('click',function(e){
         e.preventDefault();
         tablexx.destroy();
                    $('#datatable_fixed_column').empty(); 
         datalist();

    })

    /*on change combo tahun*/
    $('#myModalFormatForm').on('change','#idTahun',function () {
        $tahun = $(this).val();
        loadFormFormat(MyApp.$me.dataRowFormat,$tahun);
    })

    $('#myModalFormatForm').on('shown.bs.modal', function (e) {
        // $('#idTahun[value=2016]').attr('selected','selected');
        $("#idTahun").val("2016").change();
        console.log('wowowowo')
    })

    var instansiChange = function(){
        var params = {
                option: 'ACTION',
                action: 'instansi'
            }
            // kedepan fungsi ini dimasukkan ke data module, sebagai master
        MyApp.ajax(params).done(function(resp) {
            if (resp.success) {
                var cbMaster = $('.cb-instansi');
                // clear combo modules dulu
                cbMaster.html('');
                cbMaster.append('<option value="0">--Pilih Instansi--</option>');
                var data = resp.result;
                for (var i = 0; i < data.length; i++) {
                    cbMaster.append('<option value="' + data[i].id_instansi + '">' +
                        data[i].nama_instansi + '</option>');
                }
                 
            }
        });
    }
    instansiChange();
    datalist();
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

            // Grouping columns sequentially
            var kolomGroups = [];
            var currentGroup = null;
            $.each(resp.koloms_raw, function (i, k) {
                var hdr = k.header_kolom ? k.header_kolom.trim() : '';
                if (hdr === '-' || hdr === '0') {
                    hdr = '';
                }
                var name = k.nama_kolom ? k.nama_kolom.trim() : '';
                var tipe = k.tipe_kolom ? k.tipe_kolom.trim() : '';
                
                if (currentGroup === null || currentGroup.header !== hdr || hdr === '') {
                    currentGroup = {
                        header: hdr,
                        cols: []
                    };
                    kolomGroups.push(currentGroup);
                }
                currentGroup.cols.push({
                    kode: k.kode_kolom,
                    nama: name,
                    tipe: tipe
                });
                colspantahun++;
            });

            $.each(kolomGroups, function (gi, group) {
                if (group.header !== '') {
                    kolomParent += "<th class='th-format' colspan='" + group.cols.length + "'>" + group.header + "</th>";
                    $.each(group.cols, function (ci, col) {
                        kolomChild += "<th class='th-format'>" + (col.nama || '-') + "</th>";
                    });
                } else {
                    var col = group.cols[0];
                    kolomParent += "<th class='th-format' rowspan='2'>" + (col.nama || col.header || '-') + "</th>";
                }
            });

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
                            indexKolom = i.substring(0, i.lastIndexOf('_'));
                            var colObj = MyApp.$me.kolom[indexKolom];
                            if(colObj){
                                var colName = colObj.nama;
                                var colHeader = colObj.header || '';
                                var isTotalCol = (colObj.tipe === 'jumlah');
                                var safeHeader = btoa(unescape(encodeURIComponent(colHeader))).replace(/=/g, '');
                                
                                if(isTotalCol){
                                    td +="<td><input readonly class='gender-total form-control input-sm' data-row='"+index+"' data-header='"+safeHeader+"' style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
                                }else{
                                    td +="<td><input class='gender-input form-control input-sm' data-row='"+index+"' data-header='"+safeHeader+"' style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
                                }
                            }else{
                                td +="<td><input class='form-control input-sm' style='width: 100%;' name='"+i+"_"+index+"' value='"+record+"'/></td>";
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
            
            // Recalculate all totals on load
            $('.renderedFormat .gender-total').each(function () {
                var $totalInput = $(this);
                var row = $totalInput.data('row');
                var header = $totalInput.data('header');
                
                var $inputs = $('.renderedFormat .gender-input[data-row="' + row + '"][data-header="' + header + '"]');
                var sum = 0;
                $inputs.each(function () {
                    sum += parseFloat($(this).val()) || 0;
                });
                $totalInput.val(sum);
            });

            $('.titleDataFormat').html(MyApp.$me.dataRowFormat.judul_data_pilah);
            
            // Check Verifikasi Status
            MyApp.ajax({
                Module: 'VerifikasiDataMatrix',
                option: 'ACTION', action: 'getVerifStatus',
                kode_data_pilah: MyApp.$me.dataRowFormat.kode_data_pilah,
                tahun: curtahun
            }).done(function (resp) {
                var isVerif = false;
                if(resp.success && resp.is_verified == 1) {
                    isVerif = true;
                }
                $('#chkVerifikasi').prop('checked', isVerif);
                if (isVerif) {
                    $('.gender-input').prop('readonly', true).css({'background-color':'#f5f5f5', 'cursor':'not-allowed'});
                    $('.btSimpanDataFormat').hide();
                } else {
                    $('.gender-input').prop('readonly', false).css({'background-color':'', 'cursor':''});
                    $('.btSimpanDataFormat').show();
                }
            });

            // Handle Verification toggle
            $('#chkVerifikasi').off('change').on('change', function() {
                var checked = $(this).is(':checked') ? 1 : 0;
                var instansi = $('.cb-instansi').val() || 0;
                if(confirm("Apakah Anda yakin ingin mengubah status verifikasi data ini?")) {
                    MyApp.ajax({
                        Module: 'VerifikasiDataMatrix',
                        option: 'ACTION', action: 'toggleVerif',
                        kode_data_pilah: MyApp.$me.dataRowFormat.kode_data_pilah,
                        tahun: curtahun,
                        is_verified: checked,
                        id_instansi: instansi
                    }).done(function (res) {
                        if(res.success) {
                            alert("Status verifikasi berhasil diubah.");
                            if(checked) {
                                $('.gender-input').prop('readonly', true).css({'background-color':'#f5f5f5', 'cursor':'not-allowed'});
                                $('.btSimpanDataFormat').hide();
                            } else {
                                $('.gender-input').prop('readonly', false).css({'background-color':'', 'cursor':''});
                                $('.btSimpanDataFormat').show();
                            }
                        } else {
                            alert("Gagal mengubah status verifikasi.");
                            $('#chkVerifikasi').prop('checked', !checked); // revert
                        }
                    });
                } else {
                    $(this).prop('checked', !checked); // revert
                }
            });

            $('#myModalFormatForm').modal('show');
            

        })
    }

    /* Real-time sum for gender/general inputs */
    $('#myModalFormatForm').on('input', '.gender-input', function () {
        var $el = $(this);
        var row = $el.data('row');
        var header = $el.data('header');

        // Find all individual inputs in the same row with the same header
        var $inputs = $('.gender-input[data-row="' + row + '"][data-header="' + header + '"]');
        
        // Find the total input in the same row with the same header
        var $totalInput = $('.gender-total[data-row="' + row + '"][data-header="' + header + '"]');

        if ($totalInput.length > 0) {
            var sum = 0;
            $inputs.each(function () {
                sum += parseFloat($(this).val()) || 0;
            });
            $totalInput.val(sum);
        }
    });

    /*on submit form data format*/
    $('#myModalFormatForm').on('click','.btSimpanDataFormat',function () {
        dataForm = MyApp.getFormValues($('#myModalFormatForm form#formDataFormat'));
        // dataForm= new FormData(formDataFormat);
        dataForm['thun']=$('#idTahun').val();
        // formData.append('username', 'Chris');
        console.log(dataForm);
        var $dataAll = $.extend(dataForm,MyApp.$me.dataRowFormat);
       
        $.ajax({
            url : "service.php?option=PUBLIC&action=UpdateDataFormat&Module=VerifikasiDataMatrix",
            data:{
                data:$dataAll
            },
            method : "POST",
            dataType: "JSON"
        }).done(function (resp) {
            if(resp.success) {
                alert('Berhasil Menyimpan');
                $('#myModalFormatForm').modal('hide');
                if (tablexx) {
                    tablexx.destroy();
                    $('#datatable_fixed_column').empty();
                }
                datalist();
            } else {
                alert(resp.msg || 'Gagal menyimpan data.');
            }
        }).fail(function(){
            alert('Terjadi kesalahan saat menyimpan data.');
        });

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

//# sourceURL=FormatDataGender.js
