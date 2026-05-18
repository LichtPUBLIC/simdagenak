(function() {
    MyApp.renderMainTpl();
    var $me = MyApp.$me;
    var curKode = null;

    function showSection(id) {
        $me('.dp-section').removeClass('active');
        $me('#' + id).addClass('active');
    }

    // Load Tahun & Unit
    MyApp.ajax({ Module: 'DataPilah', option: 'ACTION', action: 'listTahun' }, function(resp) {
        if (resp.success) {
            var html = '';
            $.each(resp.result, function(i, v) {
                html += '<option value="' + v.tahun + '">' + v.tahun + '</option>';
            });
            $me('#cbTahunView').html(html);
            $me('#cbTahunView').val(new Date().getFullYear());
        }
    });

    MyApp.ajax({ Module: 'DataPilah', option: 'ACTION', action: 'listUnit' }, function(resp) {
        if (resp.success) {
            var html = '<option value="0">-- Semua Dinas --</option>';
            $.each(resp.result, function(i, v) {
                html += '<option value="' + v.id + '">' + v.text + '</option>';
            });
            $me('#cbUnitView').html(html);
        }
    });

    function loadAll() {
        MyApp.ajax({ option: 'ACTION', action: 'listAll' }, function(resp) {
            if (resp.success) {
                var html = '';
                $.each(resp.data, function(i, v) {
                    html += '<div class="col-md-3">' +
                        '<div class="view-card jarviswidget jarviswidget-color-greenLight" data-kode="' + v.kode_data_pilah + '" data-judul="' + v.judul_data_pilah + '">' +
                        '<div style="padding: 12px; border: 1px solid #ddd; background:#fff; border-radius:4px; height: 100px; display:flex; align-items:center; justify-content:center; text-align:center;">' +
                        '<h4 style="margin:0; font-size:14px;">' + v.judul_data_pilah + '</h4>' +
                        '</div></div></div>';
                });
                $me('#listViewMatriks').html(html);
            }
        });
    }

    loadAll();

    $me('#listViewMatriks').on('click', '.view-card', function() {
        curKode = $(this).data('kode');
        var judul = $(this).data('judul');
        $me('#viewJudul').text(judul);
        showSection('sectionView');
        loadView();
    });

    $me('.btKembali').on('click', function() {
        showSection('sectionDaftar');
    });

    function loadView() {
        var tahun = $me('#cbTahunView').val();
        var id_instansi = $me('#cbUnitView').val();
        MyApp.ajax({
            Module: 'DataPilah',
            option: 'ACTION', action: 'getMatriks',
            kode_data_pilah: curKode, tahun: tahun, id_instansi: id_instansi
        }, function (resp) {
            if (!resp.success) return;

            var kolom = resp.kolom;
            var baris = resp.baris;

            if (kolom.length === 0 || baris.length === 0) {
                $me('#viewTable').html('<tr><td class="text-center text-muted">Data belum tersedia.</td></tr>');
                return;
            }

            var html = '<thead><tr>';
            html += '<th style="width:40px;">No</th>';
            html += '<th>Uraian</th>';
            $.each(kolom, function (i, k) {
                html += '<th>' + (k.header_kolom || k.nama_kolom) + '</th>';
            });
            html += '</tr></thead><tbody>';
            $.each(baris, function (bi, b) {
                html += '<tr>';
                html += '<td class="text-center">' + (b.no_urut || (bi + 1)) + '</td>';
                html += '<td>' + b.nama_baris + '</td>';
                $.each(b.cells, function (ci, c) {
                    html += '<td class="text-right">' + (c.val || '-') + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody>';
            $me('#viewTable').html(html);
        });
    }

    $me('#cbTahunView, #cbUnitView').on('change', function () { loadView(); });
    $me('.btRefreshView').on('click', function () { loadView(); });

})();
