$(document).ready(function () {
    var cbInstansi = $('#cbInstansi');
    var cbJenisdata = $('#cbJenisdata');
    var cbTahun = $('#cbTahun');
    var btnCari = $('#btnCari');
    var MyApp = {};
    MyApp.$me = {};
    MyApp.$me.totalLp = 0;
    var currentKode = null;
    var currentTahun = null;

    // ================================================================
    // INIT: Load dropdown Instansi & Tahun on page load
    // ================================================================
    function loadInstansi() {
        $.ajax({
            url: '../public-service.php',
            data: { action: 'listInstansi' },
            method: 'POST'
        }).done(function (resp) {
            resp = JSON.parse(resp);
            cbInstansi.html('<option value="">-- Pilih Instansi --</option>');
            $.each(resp, function (i, val) {
                cbInstansi.append('<option value="' + val.instansi + '">' + val.instansi + '</option>');
            });
        });
    }

    function loadTahun() {
        $.ajax({
            url: '../public-service.php',
            data: { action: 'listTahun' },
            method: 'POST'
        }).done(function (resp) {
            resp = JSON.parse(resp);
            cbTahun.html('<option value="">-- Tahun --</option>');
            var curYear = new Date().getFullYear().toString();
            $.each(resp, function (i, val) {
                var sel = (val.tahun === curYear) ? ' selected' : '';
                cbTahun.append('<option value="' + val.tahun + '"' + sel + '>' + val.tahun + '</option>');
            });
        });
    }

    // Load on page ready
    loadInstansi();
    loadTahun();

    // ================================================================
    // CASCADE: Instansi → Jenis Data
    // ================================================================
    function loadJenisData(instansiVal, selectedKode = null) {
        cbJenisdata.html('<option value="">-- Pilih Jenis Data --</option>');
        if (!instansiVal) return $.Deferred().resolve().promise();
        return $.ajax({
            url: '../public-service.php',
            data: {
                instansi: instansiVal,
                action: 'loadJenisData'
            },
            method: 'POST'
        }).done(function (resp) {
            resp = JSON.parse(resp);
            $.each(resp, function (index, val) {
                var sel = (val.kode_data_pilah === selectedKode) ? ' selected' : '';
                cbJenisdata.append('<option value="' + val.kode_data_pilah + '"' + sel + '>' + val.judul_data_pilah + '</option>');
            });
            if (selectedKode) {
                cbJenisdata.val(selectedKode);
            }
        });
    }

    cbInstansi.on('change', function () {
        loadJenisData(cbInstansi.val());
    });

    // ================================================================
    // CARI: Load data + render table + charts
    // ================================================================
    btnCari.on('click', function () {
        var kode = cbJenisdata.val();
        var tahun = cbTahun.val();

        if (!kode) { alert('Pilih jenis data terlebih dahulu!'); return; }
        if (!tahun) { alert('Pilih tahun terlebih dahulu!'); return; }

        loadDataPilah(kode, tahun);
    });

    // Sidebar menu click (legacy support)
    $('.menudatapilah').click(function (e) {
        e.preventDefault();
        var instansi = $(this).closest('.slide').find('.side-menu__label').text().trim();
        var kode = $(this).data('kodedatapilah');

        // Update Instansi dropdown
        cbInstansi.val(instansi);

        // Load Jenis Data, select the clicked one, and auto load the table
        loadJenisData(instansi, kode).done(function () {
            var tahun = cbTahun.val() || new Date().getFullYear().toString();
            loadDataPilah(kode, tahun);
        });

        // Ensure we switch to matrix view if we are on dashboard
        if ($('#viewDashboard').hasClass('active')) {
            $('body').removeClass('no-sidebar');
            $('#viewDashboard').removeClass('active').hide();
            $('#viewMatrix').show().addClass('active');
            $(window).trigger('resize');
        }
    });

    // ================================================================
    // LOAD DATA PILAH (tabel + grafik)
    // ================================================================
    function loadDataPilah(kode, tahun) {
        currentKode = kode;
        currentTahun = tahun;

        $('#dataTables').html('<h4 class="text-center text-muted"><i class="fe fe-loader"></i> Memuat data...</h4>');
        $('#rowTabel').show();
        $('#rowCharts').hide();

        $.ajax({
            url: '../public-service.php',
            data: {
                kode_data_pilah: kode,
                show_data: 1,
                tahun: tahun
            },
            method: 'POST'
        }).done(function (resp) {
            try {
                resp = JSON.parse(resp);
            } catch (e) {
                $('#dataTables').html('<h4 class="text-danger text-center">Error: Data tidak dapat dimuat</h4>');
                return;
            }

            if (!resp.success) {
                $('#dataTables').html('<h4 class="text-danger text-center">Data tidak ditemukan</h4>');
                return;
            }

            MyApp.$me.kolom = resp.kolom;
            MyApp.$me.totalLp = 0;

            // Build table body
            var cell = '';
            var td = '';
            $.each(resp.result, function (index, val) {
                var no = index + 1;
                td += "<td style='text-align: center'>" + no + "</td>";
                $.each(val, function (i, record) {
                    if (record == null) { record = 0; }
                    if (i != 'kode_baris' && i != 'kode_data_pilah') {
                        if (i == 'nama_baris') {
                            MyApp.$me.namabaris = record;
                            td += "<td style='text-align: left;font-weight: bold;'>" + record + "</td>";
                        } else {
                            var numVal = parseFloat(record);
                            if (!isNaN(numVal)) {
                                td += "<td>" + numVal.toLocaleString('id') + "</td>";
                            } else {
                                td += "<td>" + record + "</td>";
                            }
                        }
                    }
                });
                cell += "<tr>" + td + "</tr>";
                td = '';
            });

            var judul = $("#cbJenisdata option:selected").text();
            if (!judul || judul.indexOf('--') === 0) {
                // If not from dropdown (e.g. sidebar), try to get from header or global
                judul = $('.judul-header').first().text() || 'Data Matriks';
            }

            $('.judul-header').html(judul);
            $('#judulData').html(judul + ' — Tahun ' + tahun);
            
            // Re-create thead and tbody for correct CSS styling and semantic HTML
            $('#dataTables').html('<thead class="header-kolom"></thead><tbody class="body-kolom"></tbody>');
            $('#dataTables thead').html(resp.head_table);
            $('#dataTables tbody').html(cell);
            
            $('#rowCharts').show();

            // Render charts
            renderBarChart(resp, judul);
            renderPieChart(resp, judul);
        }).fail(function () {
            $('#dataTables').html('<h4 class="text-danger text-center">Error: Gagal memuat data dari server</h4>');
        });
    }

    // ================================================================
    // BAR CHART (Highcharts)
    // ================================================================
    function renderBarChart(data, judul) {
        var kategori = [];
        var dataSeri = [];

        $.each(data.kolomsingle, function (index, val) {
            dataSeri[index] = {};
            dataSeri[index]['name'] = val;
            dataSeri[index]['data'] = [];
            dataSeri[index]['lineWidth'] = 3;
        });

        for (var k = 0; k < data.result.length; k++) {
            var m = 0;
            $.each(data.result[k], function (i, record) {
                if (record == null) { record = 0; }
                if (i == 'nama_baris') {
                    kategori.push(record);
                }
                if (i != 'kode_baris' && i != 'kode_data_pilah' && i != 'nama_baris') {
                    dataSeri[m].data.push(parseFloat(record) || 0);
                    m++;
                }
            });
        }

        Highcharts.chart('chartBar', {
            chart: {
                type: 'column',
                style: { fontFamily: 'Arial, sans-serif' }
            },
            title: {
                text: judul || 'Grafik Batang',
                style: { fontSize: '15px', fontWeight: '700' }
            },
            xAxis: {
                categories: kategori,
                labels: {
                    rotation: -45,
                    style: { fontSize: '11px' }
                }
            },
            yAxis: {
                min: 0,
                title: { text: 'Jumlah' }
            },
            tooltip: {
                shared: true,
                pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: <b>{point.y:,.0f}</b><br/>'
            },
            plotOptions: {
                column: {
                    borderRadius: 3,
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            colors: ['#3276b1', '#d9534f', '#5db85d', '#f0ad4e', '#5bc0de', '#9b59b6', '#e67e22'],
            series: dataSeri,
            credits: { enabled: false }
        });
    }

    // ================================================================
    // PIE CHART (Highcharts) — Total agregat per kolom
    // ================================================================
    function renderPieChart(data, judul) {
        var pieData = [];

        // Calculate totals per column
        $.each(data.kolomsingle, function (index, val) {
            pieData[index] = { name: val, y: 0 };
        });

        for (var k = 0; k < data.result.length; k++) {
            var m = 0;
            $.each(data.result[k], function (i, record) {
                if (i != 'kode_baris' && i != 'kode_data_pilah' && i != 'nama_baris') {
                    pieData[m].y += parseFloat(record) || 0;
                    m++;
                }
            });
        }

        // Make the largest slice "sliced"
        var maxIdx = 0;
        var maxVal = 0;
        $.each(pieData, function (i, v) {
            if (v.y > maxVal) { maxVal = v.y; maxIdx = i; }
        });
        if (pieData.length > 0) {
            pieData[maxIdx].sliced = true;
            pieData[maxIdx].selected = true;
        }

        Highcharts.chart('chartPie', {
            chart: {
                type: 'pie',
                style: { fontFamily: 'Arial, sans-serif' }
            },
            title: {
                text: 'Komposisi: ' + (judul || 'Data'),
                style: { fontSize: '14px', fontWeight: '700' }
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y:,.0f}</b> ({point.percentage:.1f}%)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: { fontSize: '11px' }
                    },
                    showInLegend: true
                }
            },
            colors: ['#3276b1', '#d9534f', '#5db85d', '#f0ad4e', '#5bc0de', '#9b59b6', '#e67e22'],
            series: [{
                name: 'Total',
                colorByPoint: true,
                data: pieData
            }],
            credits: { enabled: false }
        });
    }

    // ================================================================
    // TAHUN change via dropdown (legacy support for sidebar clicks)
    // ================================================================
    cbTahun.on('change', function () {
        // If data is already loaded, auto-reload is optional
        // User can click Cari again
    });

    // ================================================================
    // EXPORT: Excel, PDF, Print
    // ================================================================

    // Excel export
    $(document).on('click', '.btExcel', function (e) {
        e.preventDefault();
        if (!currentKode || !currentTahun) {
            alert('Silakan cari data terlebih dahulu!');
            return;
        }
        window.location.href = '../public-export.php?type=excel&kode=' + encodeURIComponent(currentKode) + '&tahun=' + encodeURIComponent(currentTahun);
    });

    // PDF export
    $(document).on('click', '.btPdf', function (e) {
        e.preventDefault();
        if (!currentKode || !currentTahun) {
            alert('Silakan cari data terlebih dahulu!');
            return;
        }
        window.open('../public-export.php?type=pdf&kode=' + encodeURIComponent(currentKode) + '&tahun=' + encodeURIComponent(currentTahun), '_blank');
    });

    // Print
    $(document).on('click', '.btPrint', function (e) {
        e.preventDefault();
        if (!currentKode || !currentTahun) {
            alert('Silakan cari data terlebih dahulu!');
            return;
        }

        // Simply print the current page - use CSS @media print to control visibility
        window.print();
    });

});