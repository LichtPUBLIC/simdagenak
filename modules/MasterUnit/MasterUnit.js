(function() {
    MyApp.renderMainTpl();
    var $me = MyApp.$me;
    var aksi = 'add';
    var dataRow = [];

    var oTable = $me('#tableMasterUnit').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: "service.php",
            method: "POST",
            data: { Module: MyApp.curMod, option: "ACTION", action: "list" }
        },
        columns: [
            { data: "id", width: "5%", render: function(d,t,r,m){ return m.row + 1; } },
            { data: "id_instansi" },
            { data: "nama_instansi" },
            { 
                data: function(val, type, row, meta) {
                    dataRow[meta.row] = val;
                    return '<button class="btn btn-xs btn-primary btEdit" data-index="'+meta.row+'"><i class="fa fa-pencil"></i></button> ' +
                           '<button class="btn btn-xs btn-danger btHapus" data-index="'+meta.row+'"><i class="fa fa-trash"></i></button>';
                },
                width: "15%"
            }
        ],
        dom: "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>t" +
             "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        initComplete: function() {
            $me("div.toolbar").html('<button class="btn btn-success btAdd" style="margin:5px"><i class="fa fa-plus"></i> Tambah Unit</button>');
            $me('.btAdd').click(function() {
                aksi = 'add';
                $('#modalMasterUnit .modal-title').text("Tambah Unit Kerja");
                $('#formMasterUnit')[0].reset();
                $('#modalMasterUnit').modal('show');
            });
        }
    });

    $me('#tableMasterUnit').on('click', '.btEdit', function() {
        aksi = 'update';
        var idx = $(this).data('index');
        var row = dataRow[idx];
        $('#modalMasterUnit .modal-title').text("Update Unit Kerja");
        MyApp.setFormValues($('#formMasterUnit'), row);
        $('#modalMasterUnit').modal('show');
    });

    $me('#tableMasterUnit').on('click', '.btHapus', function() {
        var idx = $(this).data('index');
        var row = dataRow[idx];
        if (confirm('Hapus unit "' + row.nama_instansi + '"?')) {
            MyApp.ajax({
                option: 'ACTION', action: 'delete', data: { data: row }
            }, function(resp) {
                oTable.ajax.reload();
            });
        }
    });

    $('.btSimpanUnit').on('click', function() {
        var dataForm = MyApp.getFormValues($('#formMasterUnit'));
        MyApp.ajax({
            option: 'ACTION', action: aksi, data: { data: dataForm }
        }, function(resp) {
            if (resp.success) {
                $('#modalMasterUnit').modal('hide');
                oTable.ajax.reload();
            } else { alert(resp.msg); }
        });
    });

})();
