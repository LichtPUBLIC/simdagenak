// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    $('.select2').select2();
    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=SampleModule.js
