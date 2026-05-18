// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss();
    jsGrid.validators.moduleId = {
        message: 'Module ID harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    }
    var reverseDate = function(dateString) {
        var tmp = dateString.split(' ');
        return tmp[0].split('-').reverse().join('/') + ' ' + tmp[1];
    }

    var getTime = function(dateString) {
        return dateString.split(' ')[1];
    }

    var getDate = function(dateString) {
            return dateString.split(' ')[0].split('-').reverse().join('/');
        }

    MyApp.module.tableGroups = MyApp.$me('.table-groups')
    MyApp.module.tableUsers = MyApp.$me('.table-users')

    MyApp.ajax(['userinfo','getDataUser']).done(function(resp) {
        var user = resp.result[0];
        var date =  getDate(user.time_login);
        var time =  getTime(user.time_login);
        $('.nama').text(user.nama);
        $('.grup').text(user.group_id);
        $('.login').text(date +' at '+time);
        $("#username").val(user.username);
        $("#nama").val(user.nama);
        $("#email").val(user.email);
        $("#level").val(user.level_name);
        $("#tahun").val(user.param01);
        $("#param2").val(user.param02);
        $("#param3").val(user.param03);
        $("#param4").val(user.param04);
        $("#param5").val(user.param05);
        $("#param6").val(user.param06);
        $("#param7").val(user.param07);
        $("#param8").val(user.param08);
        $("#param9").val(user.param09);
        $("#param10").val(user.param10);

        console.log(resp.result);


    });

    $("#btn-refresh-user").click(function(e) {
        e.preventDefault();
        $(".table-users").jsGrid("loadData");
    });

   
    $('.update-one').on("click", function(e) {
        e.preventDefault();
        var data = $('.form-insert').serializeArray();
        var params = {
            option: 'ACTION',
            action: 'saveUser',
            state:'edit',
            data: data
        }
        MyApp.ajax(params).done(function(resp) {
            if (resp.success) {
                // console.log(resp.result)
                alert(resp.msg)
            }
        })
    })

    $('.update-pass').on("click", function(e) {
        e.preventDefault();

        var data = {};
        data.pass1=$('#password').val();
        data.pass2=$('#password2').val();

        if (data.pass1 !=data.pass2) {
            alert('Password harus sama')
            
        }else {
            var params = {
                option: 'ACTION',
                action: 'password',            
                data: data
            }
            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    // console.log(resp.result)
                    alert(resp.msg)
                }
            })
        }
    })

    $('.simpan-perubahan').on("click", function() {
        var data = $('.form-update').serializeArray();
        var params = {
            option: 'ACTION',
            action: 'saveUser',
            state:'update',
            data: data
        }
        MyApp.ajax(params).done(function(resp) {
            if (resp.success) {
                // console.log(resp.result)
                alert(resp.msg)
            }
        })
    })

    MyApp.ajax(['PUBLIC','getInstansi']).done(function(resp){
        var user = resp.result[0];
        var inst= resp.result;
        // console.log(inst);

        $.each(inst, function(key,value){
            console.log(value)
            $('#select-1').append('<option value="'+value.nama_instansi+'">'+value.nama_instansi+'</option>')
        })
    })
    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=UserGroups.js
