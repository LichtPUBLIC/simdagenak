// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    // MyApp.loadModuleCss();
    MyApp.loadModuleCss('style.css');
    MyApp.$me.index = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.table;


    var params = {
        option: 'data',
        action: 'list',       
        
        // user: MyApp.module.data     
    };
    MyApp.ajax(params)
            .done(function(response) {
                var data = response.result;     
                console.log(data);
                $('#daftarpengguna').DataTable({
                lengthChange: false,
                info: false,
                bFilter: false,
                bPaginate: true,
                scrollX: true,
                // paging: true,
                data: data,
                columns: [  { title: "Username", data: "username"}, 
                            { title: "Nama", data:"nama" }, 
                            { title: "Email", data:"email" }, 
                            { title: "Jabatan", data:function(){
                                jabatan="Nama Jabatan";
                                return jabatan;
                            } },
                            
                            

                            
                            
                            {
                        title: "Aksi",
                        data: function (data,val, index){
                            MyApp.$me.dataRow[MyApp.$me.index] = data;
                            btn = '<a href=# class="btTampilkan update" data-index="' + MyApp.$me.index + '"><i class="fa fa-edit fa-2x"></i></a>';
                            btn = btn+'<a href=# class="btUpload upload"><i class="fa fa-location-arrow fa-2x"></i></a>';
                            btn = btn+'<a href=# class="btDelete delete" data-index="' + MyApp.$me.index + '"><i class="fa fa-minus-circle fa-2x"></i></a>';
                       
                       MyApp.$me.index++;
                           return btn; 
                        }
                    }

                ],
                // pagingType: "bootstrap_full_number",
                // language: {
                //     "search": "My search: ",
                //     "lengthMenu": "  _MENU_ records",
                //     "paginate": {
                //         "previous": "Prev",
                //         "next": "Next",
                //         "last": "Last",
                //         "first": "First"
                //     }
                // },
                initComplete: function () {
                    $("#daftarpengguna").on("click", ".update", function(){
                    // $(".update").click(function () {
                        index = $(this).data('index');
                        dataRow = MyApp.$me.dataRow[index];
                        MyApp.openModule('entry-pengguna',dataRow);
                        // console.log('update')
                    });
                    $("#daftarpengguna").on("click", ".upload", function(){
                    // $(".upload").click(function () {
                        $('#myModal1').modal('show');
                        console.log('upload')
                    });
                    $("#daftarpengguna").on("click", ".delete", function(){
                    // $(".delete").click(function(){
                        index = $(this).data('index');
                        dataRow = MyApp.$me.dataRow[index];
                        
                        var r = confirm("Anda yakin akan menghapus user ini?");
                        if (r == true) {
                            var params = {
                                    option: 'ACTION',
                                    action: 'deletedata',
                                    instansi: dataRow                            
                                }
                                // kedepan fungsi ini dimasukkan ke data module, sebagai master
                            MyApp.ajax(params).done(function(resp) {
                                if (resp.success) {
                                    alert(resp.msg)
                                    MyApp.openModule('daftar-pengguna');
                                }
                            })
                                
                        } else {
                            
                        }
                    });

                }

            });

        }); 

    $('.cb-nomor').on("change", function() {
        var a = $(this).val() + "/XXX";
        $('.nomor-surat').val(a)
    })
    $('#cari_pengguna').on("click", function() {
        var instansi = $('.instansi').val();

        var params = {
                option: 'data',
                action: 'list',
                instansi: instansi,
                start: 0,
                limit: 15
            }
            // kedepan fungsi ini dimasukkan ke data module, sebagai master
        MyApp.ajax(params).done(function(resp) {
            if (resp.success) {
                MyApp.module.tableInstansi.jsGrid("loadData");
            }
        })
    })
    $('.addAnggota').on("click", function() {
            MyApp.openModule('entry-pengguna', null);
        })
        // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=MDInstansi.js