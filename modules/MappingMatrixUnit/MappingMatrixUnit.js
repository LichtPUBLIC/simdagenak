(function() {
    MyApp.renderMainTpl();
    var $me = MyApp.$me;

    // Load Units
    MyApp.ajax({
        Module: 'DataPilah', // Re-use action from DataPilah
        option: 'ACTION',
        action: 'listUnit'
    }, function(resp) {
        if (resp.success) {
            var html = '<option value="0">-- Pilih Dinas --</option>';
            $.each(resp.result, function(i, v) {
                html += '<option value="' + v.id + '">' + v.text + '</option>';
            });
            $me('#cbMappingUnit').html(html);
        }
    });

    $me('#cbMappingUnit').on('change', function() {
        loadLists();
    });

    function loadLists() {
        var idUnit = $me('#cbMappingUnit').val();
        if (idUnit == 0) {
            $me('#listAvailable, #listAssigned').html('<p class="text-muted text-center" style="margin-top: 20px;">Pilih dinas dulu</p>');
            return;
        }

        // Available
        MyApp.ajax({
            option: 'ACTION',
            action: 'listAvailable',
            id_instansi: idUnit
        }, function(resp) {
            var html = '';
            $.each(resp.result, function(i, v) {
                html += '<div class="list-item">' +
                        '<span>' + v.text + '</span>' +
                        '<button class="btn btn-primary btn-action btAssign" data-id="' + v.id + '"><i class="fa fa-plus"></i></button>' +
                        '</div>';
            });
            if (resp.result.length == 0) html = '<p class="text-muted text-center" style="margin-top: 20px;">Semua matrix sudah dipakai</p>';
            $me('#listAvailable').html(html);
        });

        // Assigned
        MyApp.ajax({
            option: 'ACTION',
            action: 'listAssigned',
            id_instansi: idUnit
        }, function(resp) {
            var html = '';
            $.each(resp.result, function(i, v) {
                html += '<div class="list-item">' +
                        '<span>' + v.text + '</span>' +
                        '<button class="btn btn-danger btn-action btUnassign" data-id="' + v.id + '"><i class="fa fa-minus"></i></button>' +
                        '</div>';
            });
            if (resp.result.length == 0) html = '<p class="text-muted text-center" style="margin-top: 20px;">Belum ada matrix dipakai</p>';
            $me('#listAssigned').html(html);
        });
    }

    $me('#listAvailable').on('click', '.btAssign', function() {
        var id = $(this).data('id');
        var idUnit = $me('#cbMappingUnit').val();
        MyApp.ajax({
            option: 'ACTION', action: 'assign',
            id_data_pilah: id, id_instansi: idUnit
        }, function(resp) {
            loadLists();
        });
    });

    $me('#listAssigned').on('click', '.btUnassign', function() {
        var id = $(this).data('id');
        var idUnit = $me('#cbMappingUnit').val();
        MyApp.ajax({
            option: 'ACTION', action: 'unassign',
            id_data_pilah: id, id_instansi: idUnit
        }, function(resp) {
            loadLists();
        });
    });

})();
