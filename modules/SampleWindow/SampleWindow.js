// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    // mengambil data (jika ada) yang dikirim dari si pemanggil modul ini
    var data = MyApp.curWindow.module.data;

    // Init data jika ada yg ingin ditampilkan di template (html)
    // gunakan Mustache templating
    var init = {
        title_add: 'Inisialisasi Awal renderMainTpl',
        data_init: 'Data init on renderMainTpl',
        data_src: (data && data.konten) || 'empty'
    };

    MyApp.renderMainTpl(init, function(e) {
        // Semua event di element template (html) harus ditaruh di sini
        MyApp.$me('.clickme').on('click', function() {
            // clickCount global MyApp, jd nambah terus
            MyApp.clickCount ? MyApp.clickCount++ : MyApp.clickCount = 1;
            if(console.warn) console.warn('You click Me %s times', MyApp.clickCount);
        });

        MyApp.ajax(['PUBLIC', 'sample'], function(data) {
            var list = [];
            data.result.forEach(function(obj) {
                list.push(obj.name);
            });
            var html = '<ul><li>' + list.join('</li><li>') + '</li></ul>';
            MyApp.$me('.target').html('Contoh data dari Ajax:<br>' + html);
        });
    });
    MyApp.loadModuleCss('style.css');
})();

//# sourceURL=SampleWindow.js
