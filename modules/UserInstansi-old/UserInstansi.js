// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var idForm = $('#form_input');
    var checkbox= $('#btTambahkan');
    /*load daftar instansi*/
    var cb = $('.cbUser');
    var params05 = {
        option: 'ACTION',
        action: 'userList',
    };
    MyApp.ajax(params05).done(function(response) {
        // debugger;
        cb.html("<option value=''>--Pilih User--</option>");
        $.each(response.result, function (index, val) {
            cb.append('<option value="' + val.user_id + '">' + val.user_id + '-' + val.nama + '</option>');
        });
        cb.select2();
    });


    MyApp.$me.selectorTable = $('#datatable_fixed_column').DataTable({
        serverSide: false,
        processing: true,
        destroy: true,
        bInfo: true,
        bPaginate: false,
        buttons: [],
        ajax: {
            url: "service.php",
            method: "POST",
            data: {
                Module: MyApp.curMod,
                user_id: function () {
                    return cb.val()
                },
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
                "data": "id_instansi",
                width: 5
            },
            // kalo id instansi ada di tabel, dicentang sesuai user yg dipilih
            // jika sesuai dgn user id, ada 
            {
                "data": function (val, item, index) {
                    MyApp.$me.dataRow[MyApp.$me.index] = val;
                    btn = "<input type='checkbox' class='btTambahkan' id='instansi"+val.id_instansi+"' data-index='" + MyApp.$me.index + "'" +
                    " title='Tambahkan data'</a>";
                    index = $(this).data('index');
                    dataRow = MyApp.$me.dataRow[index];
                    
                    MyApp.$me.index++;
                    return btn;
                },
                width: '6%'
            },{"data": "id_instansi"},{"data": "nama_instansi"},
        ],
        dom: 'Bfrtip',
        rowCallback: function (row, data, index) {
            $('td:eq(0)', row).html(index + 1);
            return row;
        },
        // "preDrawCallback": function( settings ) {
        //     // $('#example tbody').off( 'click', 'td' );
        //     var api = this.api();                                           
        //     var myData = api.rows({ page: 'current' }).data();
        //     console.log(myData);
        //     var value = cb.val();
        //     console.log(value);
        // },
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
            $('#datatable_fixed_column').on('click',"input[type='checkbox']",function () {
                index = $(this).data('index');
                dataRow = MyApp.$me.dataRow[index];
                var checked = $("input[id='instansi"+dataRow.id_instansi+"']").is(':checked');
                if(cb.val()==""){
                    alert("Anda harus memilih user terlebih dahulu")
                } else if(checked) {
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
                            alert("Otoritas berhasil ditambahkan");
                            // MyApp.$me.selectorTable.ajax.reload();
                        }
                    })
                } 
                else{
                    dataRow.user_id = cb.val(); 
                    hapus(dataRow);
                    alert("Otoritas berhasil dihapus");
                    // MyApp.$me.selectorTable.ajax.reload();
                }
            });


            cb.on('change',function () {
                
                if(cb.val()=="") {
                    alert("Tidak ada user yang dipilih")
                    MyApp.$me.selectorTable.ajax.reload();
                } else {
                    var data = MyApp.$me.selectorTable.rows().data();
                    data.each(function (value, index) {
                        
                        params = {
                            option: 'ACTION',
                            action: 'tampilCheckbox',
                            user_id: function () {
                                return cb.val()
                            },
                            id_instansi: function () {
                                return value.id_instansi
                            }
                            // data: value
                        };
                        MyApp.ajax(params).done(function (resp) {
                            
                            if (resp.success == true) {
                                console.log(value.id_instansi);
                                if(resp.msg == "berhasil") {
                                    $("input[id='instansi"+value.id_instansi+"']").prop('checked', true);
                                    console.log(resp.msg);
                                }else if(resp.msg == "gagal"){
                                    $("input[id='instansi"+value.id_instansi+"']").prop('checked', false);
                                    console.log(resp.msg);
                                }
                            }
                        })

                    });
                }
                
            });
        }
    });


    $('#form_target_realisasi').submit(function (e) {
        e.preventDefault();
    });


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
                // MyApp.$me.selectorTable.ajax.reload();
                // MyApp.$me.selectorTable2.ajax.reload();
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

//# sourceURL=UserInstansi.js
