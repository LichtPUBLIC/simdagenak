(function () {
    MyApp.renderMainTpl();
    
    // Fetch Dinas (Instansi) and Years
    var loadDropdowns = function () {
        // 1. Get Dinas
        MyApp.ajax({
            Module: MyApp.curMod,
            option: 'ACTION',
            action: 'getDinas'
        }).done(function (resp) {
            var $dinasSelect = $('#idDinas');
            $dinasSelect.empty().append('<option value="">-- Pilih Dinas / Instansi --</option>');
            if (resp.result && resp.result.length > 0) {
                $.each(resp.result, function (idx, val) {
                    $dinasSelect.append('<option value="' + val.instansi + '">' + val.instansi + '</option>');
                });
            }
        });

        // 2. Get Years
        MyApp.ajax({
            Module: MyApp.curMod,
            option: 'ACTION',
            action: 'getTahun'
        }).done(function (resp) {
            var $tahunSelect = $('#idTahun');
            $tahunSelect.empty().append('<option value="">-- Pilih Tahun --</option>');
            if (resp.result && resp.result.length > 0) {
                $.each(resp.result, function (idx, val) {
                    $tahunSelect.append('<option value="' + val.tahun + '">' + val.tahun + '</option>');
                });
            }
        });
    };

    loadDropdowns();

    // Handle export click
    $('#btnProsesExport').on('click', function () {
        var dinas = $('#idDinas').val();
        var tahun = $('#idTahun').val();

        if (!dinas) {
            alert('Silakan pilih Dinas / Instansi terlebih dahulu!');
            return;
        }
        if (!tahun) {
            alert('Silakan pilih Tahun terlebih dahulu!');
            return;
        }

        // Trigger file download directly
        window.location.href = 'service.php?Module=ExportExcel&option=ACTION&action=excel&dinas=' + encodeURIComponent(dinas) + '&tahun=' + tahun;
    });

    $('.modal-backdrop').addClass('hide');
    setTimeout(function () {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=ExportExcel.js
