// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    jsGrid.validators.moduleId = {
        message: 'Module ID harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    }

    MyApp.module.tableGroups = MyApp.$me('.table-groups');
    MyApp.module.tableUsers = MyApp.$me('.table-users');
    var maxH = MyApp.getContentHeight() - 50;

    // inisialisasi jsGrid
    MyApp.module.tableGroups.jsGrid({
        width: '100%',
        height: maxH,
        noDataContent: "Not found",
        // inserting: true,
        editing: true,
        sorting: true,
        paging: false,
        autoload: true,
        loadIndication: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" +
            "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function() {
                var d = $.Deferred();
                MyApp.ajax(['group', 'list']).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                // jika object after edit dan before edit sama, skip proses editing
                // ternyata ada perbedaan item : true menjadi 1, jadi ini tidak selalu benar
                // if (JSON.stringify(item) === JSON.stringify(MyApp.$me.previousItem)) {
                //     d.resolve(MyApp.$me.previousItem);
                //     alert('Tidak ada yg berubah');
                // }

                MyApp.ajax(['sample', 'edit']).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                    } else {
                        d.resolve(MyApp.$me.previousItem);
                        alert("Update Gagal\n" + resp.msg);
                    }
                });
                return d.promise();
            },
        },
        fields: [
            { type: 'action', title: 'Aksi', width: 40 },
            { name: "group_id", type: "text", width: 50, editing: true, visible: true, validate: 'moduleId' },
            { name: "description", type: "text" },
            { name: "active", title: "Aktif", type: "checkbox", width: 20, sorting: false }
        ],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowClick: function(args) {
            if (!args.event.target.classList.contains('action-field')) {
                // Jalankan jika bukan field action yg di klik
                MyApp.module.tableUsers.jsGrid('loadData', args.item);
            }
        }
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-groups');

    MyApp.$me('.addUser').on('click', function() {
        var data = {};
        data.konten = 'Data ini dikirim dari SampleModule';
        MyApp.openModuleWindow('sample-window', data);
    });
    // inisialisasi jsGrid
    MyApp.module.tableUsers.jsGrid({
        width: '100%',
        height: maxH,
        noDataContent: "Not found",
        // inserting: true,
        editing: true,
        sorting: true,
        paging: false,
        autoload: false,
        loadIndication: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" +
            "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function(item) {
                var d = $.Deferred();
                MyApp.ajax(['user', 'list', item.group_id]).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                // jika object after edit dan before edit sama, skip proses editing
                // ternyata ada perbedaan item : true menjadi 1, jadi ini tidak selalu benar
                // if (JSON.stringify(item) === JSON.stringify(MyApp.$me.previousItem)) {
                //     d.resolve(MyApp.$me.previousItem);
                //     alert('Tidak ada yg berubah');
                // }

                MyApp.ajax(['sample', 'edit']).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                    } else {
                        d.resolve(MyApp.$me.previousItem);
                        alert("Update Gagal\n" + resp.msg);
                    }
                });
                return d.promise();
            },
        },
        fields: [
            { title: 'Aksi', type: 'action', width: 40 },
            { name: "username", type: "text", width: 50, editing: true, visible: true, validate: 'moduleId' },
            { name: "nama", type: "text" },
            { name: "active", type: "checkbox", width: 45, sorting: false }
        ],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowClick: $.noop
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-users');

    $("#btn-refresh-user").click(function(e) {
        e.preventDefault();
        $(".table-users").jsGrid("loadData");
    });

    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=UserGroups.js
