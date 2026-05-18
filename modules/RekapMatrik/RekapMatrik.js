// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss();
    MyApp.$me.index = 0;
    MyApp.$me.nomor = 0;
    MyApp.$me.dataRow = [];
    MyApp.$me.table;
    MyApp.$me.userid;

    MyApp.$me.userid=$('#user-data').html();
    
    MyApp.$me.userid=JSON.parse(MyApp.$me.userid.trim())
    // console.log(userid);
  
    var params = {
        option: 'rekap',
        action: 'list',       
        
        // user: MyApp.module.data     
    };

    MyApp.ajax(params)
            .done(function(response) {
                 MyApp.$me.nomor = 0;
                var data = response.result;     
                console.log(response);
                $('#rekap').DataTable({
                lengthChange: false,
                info: false,
                bFilter: false,
                bPaginate: true,
                scrollX: true,
                paging: false,
                data: data,
                columns: [  { title: "No", data: function(){
                                MyApp.$me.nomor++;
                                return MyApp.$me.nomor;
                            }}, 
                            { title: "Instansi", data:"instansi" }, 
                            { title: "Jumlah", data:"jml" }, 
                           
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
                    $(".btPetugas").click(function () {
                        index = $(this).data('index');
                        dataRow = MyApp.$me.dataRow[index];
                        // console.log(dataRow.nama_petugas);
                        
                        $('#myModal').modal('show');
                        var params1 = {
                                option: 'operator',
                                action: 'list',       
                                
                                // user: MyApp.module.data     
                            };
                            MyApp.ajax(params1)
                            .done(function(response) {
                                    var data = response.result;     
                                    console.log(dataRow.nama_petugas);
                                    var cenPetugas= $('.petugas');
                                    $("input[name=id]").val(dataRow.id);
                                    cenPetugas.html('');
                                    
                                    for (var i= 0; i < data.length; i++)
                                    {

                                        if (dataRow.nama_petugas != null) {
                                            if(dataRow.nama_petugas.includes(data[i].user_id))
                                                cenPetugas.append('<div class="col"><label class="checkbox state-success"><input type="checkbox" name="petugas[]" value="'+data[i].user_id+'" checked><i></i>'+data[i].user_id+'</label><div>')
                                            else
                                                cenPetugas.append('<div class="col"><label class="checkbox state-success"><input type="checkbox" name="petugas[]" value="'+data[i].user_id+'" ><i></i>'+data[i].user_id+'</label><div>')
                                        }
                                        else 
                                            cenPetugas.append('<div class="col"><label class="checkbox state-success"><input type="checkbox" name="petugas[]" value="'+data[i].user_id+'" ><i></i>'+data[i].user_id+'</label><div>')
                                        
                                    }



                            }); 

                        console.log('update')
                    });
                    $("#rekomendasi").on('click','.update', function () {
                        index = $(this).data('index');
                        dataRow = MyApp.$me.dataRow[index];
                        MyApp.openModule('entry-aduan',dataRow);

                        
                        console.log('update')
                    });
                    $("#rekomendasi").on('click','.lihat', function (){
                        index = $(this).data('index');
                        dataRow = MyApp.$me.dataRow[index];
                        var params = {
                            option: 'ACTION',
                            action: 'show',       
                            lokasi_id: dataRow.lokasi_id
                        }
                        MyApp.ajax(params).done(function(resp) {
                            if (resp.success) {
                                $('#myModal').modal()
                                var x= resp.result;

                                $("#lokasi_id").val(x.lokasi_id);
                                $("#lokasi_nama").html(x.lokasi_nama);
                                $("#lokasi_namapj").html(x.lokasi_namapj);
                                $("#lokasi_telepon").html(x.lokasi_telepon);
                                $("#lokasi_alamat").html(x.lokasi_alamat);
                                $("#kecamatan").html(x.lokasi_kecamatan);
                                $("#desa").html(x.lokasi_kelurahan);
                                $("#timestampx").html(x.timestampx);

                                $("#fo_namaperangkat").html(x.fo_perangkat);
                                $("#fo_toBone").html(x.fo_tobone);
                                $("#fo_macaddress").html(x.fo_macaddress);
                                $("#fo_btestlocal").html(x.fo_btestlocal);
                                $("#fo_btestinternet").html(x.fo_btestinternet);

                                $("#wan_jenisapoutdor").html(x.wan_jenisap)
                                $("#wan_toap").html(x.wan_toap)
                                $("#wan_macaddress").html(x.wan_macaddress)
                                $("#wan_signal").html(x.wan_signal)
                                $("#wan_troughput").html(x.wan_troughput)
                                $("#wan_ccq").html(x.wan_ccq)

                                $("#tower_jumlahstage").html(x.tower_jumlahstage)
                                $("#tower_kondisistage").html(x.tower_kondisistage);
                                $("#tower_kondisispaner").html(x.tower_kondisispaner);
                                $("#tower_kondisigrounding").html(x.tower_kondisigrounding);
                                $("#tower_kondisipenangkalpetir").html(x.tower_kondisipenangkalpetir);

                                $("#wr_tipe").html(x.wr_tipe)
                                $("#wr_kondisi").html(x.wr_kondisi)

                                $("#poe_kondisi").html(x.poe_kondisi)

                                $("#poe_kondisi").html(x.poe_kondisi);
                                $("#switch_jumlahport").html(x.switch_jumlahport) 
                                $("#switch_seri").html(x.switch_seri)
                                $("#switch_serialnumber").html(x.switch_serialnumber)
                                $("#switch_merk").html(x.switch_merk)
                                $("#switch_kondisi").html(x.switch_kondisi)


                                $("#lan_jenis").html(x.lan_jenis)
                                $("#lan_jumlah").html(x.lan_jumlah) 
                                $("#lan_aktif").html(x.lan_aktif)
                                $("#lan_pasif").html(x.lan_pasif)
                                $("#lan_label").html(x.lan_label);

                                $("#hotspot_status").html(x.hotspot_status);
                                $("#hotspot_serirb").html(x.hotspot_serirb)
                                $("#hotspot_merk").html(x.hotspot_merk)
                                $("#hotspot_apseri").html(x.hotspot_apseri)
                                $("#hotspot_macaddress").html(x.hotspot_macaddress)
                                $("#hotspot_kondisi").html(x.hotspot_kondisi);

                                $("#voip_status").html(x.voip_status);
                                $("#voip_kondisi").html(x.voip_kondisi);
                                $("#voip_jumlah").html(x.voip_jumlah)
                                $("#voip_ipaddress").html(x.voip_ipaddress)
                                $("#voip_macaddress").html(x.voip_macaddress)

                                $("#finger_status").html(x.finger_status);
                                $("#finger_kondisi").html(x.finger_kondisi);
                                $("#finger_jumlah").html(x.finger_jumlah)
                                $("#finger_ipaddress").html(x.finger_ipaddress)
                                $("#finger_macaddress").html(x.finger_macaddress)
                                
                                 
                            }
                        });


                    })
                    $("#rekomendasi").on('click','.delete', function () {
                         var txt;
                          var r = confirm("Anda Yakin akan menghapus?");
                          if (r == true) {
                                index = $(this).data('index');
                                dataRow = MyApp.$me.dataRow[index];
                                var params = {
                                    option: 'delete',
                                    action: 'lokasi',       
                                    lokasi_id: dataRow.lokasi_id
                                }
                                MyApp.ajax(params).done(function(resp) {
                                    if (resp.success) {                                                                            
                                        MyApp.openModule('data-aduan')
                                       
                                         
                                    }
                                });
                          } 

                        console.log('delete')
                    });

                }

            });

        }); 
               
        
    $('#tambah').click(function(){
            // $('#myModal').modal('show');
            MyApp.openModule('entry-aduan', null);

        });




    $(".btSimpan").on("click", function(e) {

        e.preventDefault();
            var datastring = $('.form_input').serializeArray();           
            console.log(datastring);
            var params = {
                option: 'save',
                action: 'petugas',
                data: datastring,
            }

            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    alert(resp.msg)
                }
            });
        })

    console.log(params);

        // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        // initMap();
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=DataAduan.js
