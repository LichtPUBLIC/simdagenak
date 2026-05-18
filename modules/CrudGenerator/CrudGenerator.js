// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss();

    /*load data module*/
    var param = {
        option: 'ACTION',
        action: 'getModul'
    };
    domModule = $('.cbModul');
    MyApp.ajax(param).done(function (resp) {
        domModule.html('<option value="">- Pilih Modul -</option>');
        $.each(resp.result, function (index, val) {
            domModule.append('<option value="' + val.module_id + '">' + val.module_id+' -> '+ val.module + '</option>');
        });
        domModule.select2().select2('val', '');
    });

    /*load tabel list di database*/
    var param2 = {
        option: 'ACTION',
        action: 'getTable'
    };
    domcbDatabase = $('.cbDatabase');
    MyApp.ajax(param2).done(function (resp) {
        domcbDatabase.html('<option value="">- Pilih tabel database -</option>');
        $.each(resp.result, function (index, val) {
            domcbDatabase.append('<option value="' + val.table_name + '">' + val.table_name+'</option>');
        });
        domcbDatabase.select2().select2('val', '');
    });

    /*even saat di klik tombol generate*/
    $('#form_generate').submit(function (e) {
        e.preventDefault();
        data = {
            modul : domModule.val(),
            tabel : domcbDatabase.val()
        };
        var param3 = {
            option: 'ACTION',
            action: 'generate',
            data : data
        };
        MyApp.ajax(param3).done(function (resp) {
            alert('berhasil');
        });
    });

    $('.modal-backdrop').hide();
    // simulasi loading... hide setelah 500ms
    setTimeout(function () {
        MyApp.$me('.overlay').hide();
    }, 500);
})();