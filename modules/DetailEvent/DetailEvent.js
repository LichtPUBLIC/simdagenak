// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    // mengambil data (jika ada) yang dikirim dari si pemanggil modul ini
    var data = MyApp.curWindow.module.data;
    data.tgl_acara = dateFormat(data.tgl_acara, "dddd, d mmmm yyyy");
    data.tgl_acara2 = dateFormat(data.tgl_acara2, "dddd, d mmmm yyyy");
    data.tgl_surat = dateFormat(data.tgl_surat, "dddd, d mmmm yyyy");

    MyApp.renderMainTpl(data, function(e) {

        if (data.title == "biasa" || data.title == "biasa-bupati") {
            MyApp.$me('.biasa').removeClass('hide');
        }else{
            MyApp.$me('.undangan').removeClass('hide');
        }
    });
    MyApp.loadModuleCss('style.css');
})();

//# sourceURL=SampleWindow.js