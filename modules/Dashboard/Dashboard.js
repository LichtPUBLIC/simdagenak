(function() {
    MyApp.renderMainTpl();
    var $me = MyApp.$me;

    // Load Summary Data
    MyApp.ajax({
        option: 'ACTION',
        action: 'getSummary'
    }, function(resp) {
        if (resp.success) {
            $me('#countMatrix').text(resp.countMatrix);
            $me('#countUnit').text(resp.countUnit);
            
            // Render Chart
            Highcharts.chart('chartContainer', {
                chart: { type: 'column' },
                title: { text: '' },
                xAxis: { categories: resp.years },
                yAxis: { title: { text: 'Jumlah Data Diinput' } },
                series: [{
                    name: 'Jumlah Entri',
                    data: resp.chartData,
                    color: '#3276b1'
                }]
            });
        }
    });

    setTimeout(function() {
        $me('.overlay').hide();
    }, 500);

})();