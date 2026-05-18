// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss();
    MyApp.$me.userparams;
    //Date range picker
    $('.tgl').datepicker({
        dateFormat: 'yy-mm-dd'
    });


    var datastring = $('#form-search').serializeArray();
    var params = {
            option: 'ACTION',
            action: 'groupList'
        }
        // kedepan fungsi ini dimasukkan ke data module, sebagai master
    MyApp.ajax(params).done(function(resp) {
        if (resp.success) {
            var cbMaster = $('.cb-group');
            // clear combo modules dulu
            cbMaster.html('');
            cbMaster.append('<option value="0">--Pilih Group--</option>');
            var data = resp.result;
            for (var i = 0; i < data.length; i++) {
                cbMaster.append('<option value="' + data[i].group_id + '">' +
                    data[i].description + '</option>');
            }



        }
    });


    

    //check password sama
    $('#f_password, #f_password2').on('keyup', function () {
      if ($('#f_password').val() == $('#f_password2').val()) {
        $('#message').html('Password Sama').css('color', 'green');
      } else 
        $('#message').html('Password Tidak Sama').css('color', 'red');
      
    });

    /* Digunakan untuk update data pengguna */
    // if (MyApp.module.data) {
    //     $('.user_id').val(MyApp.module.data);
    //     if (MyApp.module.data) {
    //         //var unit = $('#form-kasus').serializeArray();

    //         var params = {
    //             option: 'ACTION',
    //             action: 'dataList',
    //             user_id: MyApp.module.data
    //         }
    //         MyApp.ajax(params).done(function(resp) {
    //             if (resp.success) {
    //                 if (resp.result.PenggunaDS[0]) {
    //                     MyApp.setFormValues('#form-pengguna', resp.result.PenggunaDS[0]);
    //                     console.log(resp.result.PenggunaDS[0])
    //                 }
    //             }
    //         })
    //     }
    // }




    if (MyApp.module.data) {
            MyApp.$me.userparams=MyApp.module.data;
        var x=MyApp.module.data;
        
        $("#f_username").val(x.username);
        $("#f_username").prop('readonly',true);        
        $("#user_id").val(x.user_id);
        $("#f_nama").val(x.nama);
        $("#f_email").val(x.email);
        
        if(x.active==1){$("#f_aktif").prop('checked',true);}
        if(x.isadmin==1){$("#f_admin").prop('checked',true);}
        
        $("#param02").val(x.param02);
        $("#param03").val(x.param03);
        $("#param04").val(x.param04);
        $("#param05").val(x.param05);
        $("#param06").val(x.param06);
        $("#param07").val(x.param07);
        $("#param08").val(x.param08);
        $("#param09").val(x.param09);
        $("#param10").val(x.param10);
        setTimeout(function() {
            $("#group_id").val(x.group_id);
            if(x.param01!='')
            $("#param01").val(x.param01);
        }, 1000);
        
        console.log(x);
        
    }


    $(".save-pengguna").on("click", function() {
            var datastring = $('.form-insert').serializeArray();            
            var params = {
                option: 'save',
                action: 'data',
                data: datastring,
            }

            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    alert(resp.msg);
                    MyApp.openModule('daftar-pengguna',null);
                }
            });
        })
        // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=EntryPengguna.js