// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss('style.css');
    MyApp.$me('.overlay').hide();

    // Custom validator untuk field
    jsGrid.validators.moduleClass = {
        message: "Nama Module hanya huruf saja (a-zA-Z) dengan CamelCase",
        validator: function(value, item) {
            return /^[a-zA-Z]+$/.test(value);
        }
    }

    jsGrid.validators.moduleId = {
        message: 'Module ID harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    }

    // inisialisasi jsGrid tabel modules
    MyApp.$me('.table-modules').jsGrid({
        width: '100%',
        height: '300px',
        inserting: true,
        editing: true,
        sorting: true,
        // paging: true,
        autoload: true,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" +
            "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function() {
                var d = $.Deferred();
                MyApp.ajax(['module', 'list']).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                MyApp.ajax(['module', 'edit']).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                    } else {
                        d.resolve(MyApp.$me.previousItemModule);
                        alert("Update Gagal\n" + resp.msg);
                    }
                });
                return d.promise();
            },
            insertItem: function(item) {
                var d = $.Deferred();

                var params = {
                    option: 'insert',
                    action: 'module',
                    data: item
                }
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
                         $(".table-modules").jsGrid("loadData");
                    }
                });
                return d.promise();
            },
        },
        fields: [
            { title: 'Aksi', type: 'action', width: 70},
            { name: "module_id", type: "text", width: 80, editing: true, visible: true, validate: 'moduleId' },
            { name: "module", type: "text", width: 80, validate: "moduleClass" },
            { name: "name", type: "text", title: "Nama Menu", width: 150, validate: "required" },
            { name: "description", type: "text", width: 190 },
            { name: "menu", type: "text", title : "Folder menu", width: 100 },
            { name: "icon", type: "text", width: 80 },
            { name: "iconcls", type: "text", width: 100 },
            { name: "active", type: "checkbox", title: "Aktif", sorting: true, width: 50 },
            { name: "onmenu", type: "checkbox", title: "Menu", width: 50 },
            // { type: "control", width: 100 }
        ],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItemModule = args.previousItem;
        },
        rowClick: function(args) {
            var tr = $('.table-modules tbody tr');
            tr.removeClass('selected2');
            tr.eq(args.itemIndex).addClass('selected2');
            if (!args.event.target.classList.contains('action-field')) {
                // Jalankan jika bukan field action yg di klik
                MyApp.$me('.overlay').show();
                MyApp.$me('.table-actions').jsGrid('loadData', args).done(function() {
                    MyApp.$me('.overlay').hide();
                })
            }
        },
        rowDoubleClick: function(args) {
            this.editItem(args.item);
        }
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-modules');


    // karena belum tahu cara filter banyak kolom di js grid, maka manual
    MyApp.$me('.search-module').on('input', function(e) {
        // loop semua row di tabel modules
        var key = this.value;
        MyApp.$me('.table-modules tbody tr').each(function(index) {
            if (key.length < 3) {
                $(this).show();
            } else {
                if ($(this).text().toLowerCase().indexOf(key) === -1) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            }
        })
    });

    // filter actions
    // ini bisa dijadikan method di global aplikasi MyApp
    MyApp.$me('.search-action').on('input', function(e) {
        // loop semua row di tabel modules
        var key = this.value;
        MyApp.$me('.table-actions tbody tr').each(function(index) {
            if (key.length < 3) {
                $(this).show();
            } else {
                if ($(this).text().toLowerCase().indexOf(key) === -1) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            }
        })
    });

    // inisialisasi jsGrid tabel modules
    MyApp.$me('.table-actions').jsGrid({
        width: '100%',
        // height: 'auto',
        inserting: true,
        editing: true,
        sorting: true,
        // paging: true,
        autoload: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" +
            "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        noDataContent: "Klik module diatas untuk menampilkan daftar actions",
        controller: {
            loadData: function(filter) {
                var d = $.Deferred();
                MyApp.ajax(['action', 'list', filter.item.module_id]).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                MyApp.ajax(['action', 'edit']).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                    } else {
                        d.resolve(MyApp.$me.previousItemAction);
                        alert("Update Gagal\n" + resp.msg);
                    }
                });
                return d.promise();
            },
            insertItem: function(item) {
                var d = $.Deferred();

                var params = {
                    option: 'insert',
                    action: 'action',
                    data: item
                }
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
                        //$(".table-actions").jsGrid("loadData");
                    }
                });
                return d.promise();
            },
        },
        fields: [
            { type: 'action', width : 70},
            { name: "module_id", type: "text", width: 100, editing: false }, {
                name: "action_id",
                type: "text",
                width: 120,
                validate: {
                    message: 'Action ID harus huruf abjad dengan pemisah underscore',
                    validator: function(value) {
                        return /^[a-zA-Z_]+$/.test(value);
                    }
                }
            },
            { name: "option", type: "text", width: 80 },
            { name: "action", type: "text", width: 80 },
            { name: "description", type: "textarea", width: 250 },
            { name: "log", type: "checkbox", title: "Log", sorting: false, width: 50 },
            // { type: "control", width: 100 }
        ],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItemAction = args.previousItem;
        }
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-actions');

    MyApp.$me('.btn-add-modul').click(function() {
        MyApp.$me('.table-actions').jsGrid("insertItem");
    })

    // ketika sidebar toggle, refresh untuk calculate ulang kolom
    $('.sidebar-toggle').click(function() {
        setTimeout(function() {
            MyApp.$me('.table-modules').jsGrid("refresh");
            MyApp.$me('.table-actions').jsGrid("refresh");
        }, $.AdminLTE.options.animationSpeed);
    })

})();

//# sourceURL=CodeModule.js
