(function() {
    MyApp.renderMainTpl();
    var $me = MyApp.$me;
    var curKode = null;

    function showSection(id) {
        $me('.dp-section').removeClass('active');
        $me('#' + id).addClass('active');
    }

    // Load Tahun
    MyApp.ajax({ Module: 'DataPilah', option: 'ACTION', action: 'listTahun' }, function(resp) {
        if (resp.success) {
            var html = '';
            $.each(resp.result, function(i, v) {
                html += '<option value="' + v.tahun + '">' + v.tahun + '</option>';
            });
            $me('#cbTahunMatriks').html(html);
            $me('#cbTahunMatriks').val(new Date().getFullYear());
        }
    });

    function loadAssigned() {
        MyApp.ajax({ option: 'ACTION', action: 'listAssigned' }, function(resp) {
            if (resp.success) {
                var html = '';
                $.each(resp.data, function(i, v) {
                    html += '<div class="col-md-4">' +
                        '<div class="matrix-card jarviswidget jarviswidget-color-blue" data-kode="' + v.kode_data_pilah + '" data-judul="' + v.judul_data_pilah + '">' +
                        '<div style="padding: 15px; border: 1px solid #ddd; background:#fff; border-radius:4px; height: 120px; overflow: hidden;">' +
                        '<h3 style="margin:0 0 10px 0; font-size:16px; color:#2196F3;">' + v.judul_data_pilah + '</h3>' +
                        '<p class="text-muted" style="font-size:12px;">Kode: ' + v.kode_data_pilah + '</p>' +
                        '<div class="text-right"><i class="fa fa-arrow-circle-right fa-2x" style="color:#eee;"></i></div>' +
                        '</div></div></div>';
                });
                if (resp.data.length == 0) html = '<div class="col-md-12 text-center text-muted" style="padding:50px;">Belum ada matriks yang ditugaskan kepada instansi Anda.</div>';
                $me('#listMatriksAssigned').html(html);
            }
        });
    }

    loadAssigned();

    $me('#listMatriksAssigned').on('click', '.matrix-card', function() {
        curKode = $(this).data('kode');
        var judul = $(this).data('judul');
        $me('#detailJudul').text(judul);
        showSection('sectionEntry');
        loadMatriks();
    });

    $me('.btKembali').on('click', function() {
        showSection('sectionDaftar');
    });

    function loadMatriks() {
        var tahun = $me('#cbTahunMatriks').val();
        MyApp.ajax({
            Module: 'DataPilah', // Reuse logic from DataPilah
            option: 'ACTION', action: 'getMatriks',
            kode_data_pilah: curKode, tahun: tahun
        }, function (resp) {
            if (!resp.success) return;

            var kolom = resp.kolom;
            var baris = resp.baris;
            var id_instansi_res = resp.id_instansi;

            if (kolom.length === 0 || baris.length === 0) {
                $me('#matriksTable').hide();
                $me('#matriksEmpty').show();
                return;
            }

            $me('#matriksEmpty').hide();
            $me('#matriksTable').show();

            // Build header
            var html = '<thead><tr>';
            html += '<th style="width:40px;">No</th>';
            html += '<th style="min-width:140px;">Uraian</th>';
            $.each(kolom, function (i, k) {
                html += '<th>' + (k.header_kolom || k.nama_kolom) + '</th>';
            });
            html += '</tr></thead>';

            // Build body
            html += '<tbody>';
            $.each(baris, function (bi, b) {
                html += '<tr>';
                html += '<td class="text-center">' + (b.no_urut || (bi + 1)) + '</td>';
                html += '<td>' + b.nama_baris + '</td>';
                $.each(b.cells, function (ci, c) {
                    html += '<td><input type="text" class="cell-input" ' +
                        'data-kode-baris="' + b.kode_baris + '" ' +
                        'data-kode-kolom="' + c.kode_kolom + '" ' +
                        'data-id-instansi="' + id_instansi_res + '" ' +
                        'value="' + (c.val !== null && c.val !== '' ? c.val : '') + '"></td>';
                });
                html += '</tr>';
            });
            html += '</tbody>';

            $me('#matriksTable').html(html);
        });
    }

    $me('#matriksContainer').on('blur', '.cell-input', function () {
        var $input = $(this);
        var val = $input.val();
        var kodeBaris = $input.data('kode-baris');
        var kodeKolom = $input.data('kode-kolom');
        var idInstansi = $input.data('id-instansi');
        var tahun = $me('#cbTahunMatriks').val();

        MyApp.ajax({
            Module: 'DataPilah',
            option: 'ACTION', action: 'saveCell',
            data: {
                kode_data_pilah: curKode,
                kode_baris: kodeBaris,
                kode_kolom: kodeKolom,
                tahun: tahun,
                id_instansi: idInstansi,
                val: val
            }
        }, function (resp) {
            if (resp.success) {
                $input.css('background', '#d4edda');
                setTimeout(function () { $input.css('background', ''); }, 600);
            }
        });
    });

    $me('#cbTahunMatriks').on('change', function () { loadMatriks(); });
    $me('.btRefreshMatriks').on('click', function () { loadMatriks(); });

})();
