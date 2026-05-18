// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();

    // Custom validator untuk field
    jsGrid.validators.moduleClass = {
        message: 'Nama Module hanya huruf saja (a-zA-Z) dengan CamelCase',
        validator: function(value, item) {
            return /^[a-zA-Z]+$/.test(value);
        }
    };

    jsGrid.validators.moduleId = {
        message: 'Module ID harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    };

    // inisialisasi jsGrid
    MyApp.$me('.table-modules').jsGrid({
        width: '100%',
        //height: 'auto',
        noDataContent: 'Not found',
        inserting: true,
        editing: true,
        sorting: true,
        paging: true,
        autoload: true,
        deleteConfirm: 'Yakin akan menghapus data yg dipilih?\n' +
            'Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..',
        invalidMessage: 'Ada data yang tidak valid!',
        controller: {
            loadData: function() {
                var d = $.Deferred();
                MyApp.ajax(['sample', 'list']).done(function(resp) {
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
                        alert('Update Gagal\n' + resp.msg);
                    }
                });
                return d.promise();
            },
        },
        fields: [
            { name: 'module_id', type: 'text', width: 100, editing: true, visible: true, validate : 'moduleId' },
            { name: 'module', type: 'text', width: 100, validate: 'moduleClass' },
            { name: 'name', type: 'text', title: 'Name Menu', width: 120, validate: 'required' },
            { name: 'description', type: 'text', width: 200 },
            { name: 'menu', type: 'text', width: 100 },
            { name: 'active', type: 'checkbox', title: 'Aktif', sorting: true, width: 35 },
            { name: 'onmenu', type: 'checkbox', width: 35 },
            { type: 'control' }
        ],
        // onDataLoading: function(args) {}, // before controller.loadData
        // onDataLoaded: function(args) {}, // on done of controller.loadData
        // onItemInserting: function(args) {}, // before controller.insertItem
        // onItemInserted: function(args) {}, // on done of controller.insertItem

        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        // onItemUpdated: function(args) {}, // on done of controller.updateItem
        // onItemDeleting: function(args) {}, // before controller.deleteItem
        // onItemDeleted: function(args) {}, // on done of controller.deleteItem
        // onItemInvalid: function(args) {}, // after item validation, in case data is invalid
        // onError: function(args) {}, // on fail of any controller call
        // onOptionChanging: function(args) {}, // before changing the grid option
        // onOptionChanged: function(args) {}, // after changing the grid option
        // onRefreshing: function(args) {}, // before grid refresh
        // onRefreshed: function(args) {}, // after grid refresh
    }); // EndOf inisialisasi jsGrid

    MyApp.$me.listModules = [];
    MyApp.$me.listActions = [];

    // kedepan fungsi ini dimasukkan ke data module, sebagai master
    MyApp.ajax(['PUBLIC', 'moduleList']).done(function(resp) {
        if (resp.success) {
            MyApp.$me.listModules = resp.result;
            // build option untuk combo modules
            var cbMaster = $('.cb-modules');
            // clear combo modules dulu
            cbMaster.html('');
            var data = resp.result;
            for (var i = 0; i < data.length; i++) {
                cbMaster.append('<option value="' + data[i].module_id + '">' +
                    data[i].module + '</option>');
            }

            // load semua actions
            MyApp.ajax(['PUBLIC', 'actionList']).done(function(resp) {
                if (resp.success) {
                    MyApp.$me.listActions = resp.result;
                    // trigger onchange list combo Master pertama kali
                    cbMaster.trigger('change');
                }
            });

            // event on select/change combo master
            cbMaster.on('change', function() {
                var cbDetail = $('.cb-actions');
                cbDetail.html('');
                var dataAct = MyApp.$me.listActions;
                var modSelected = $(this).val();
                for (var i = 0; i < dataAct.length; i++) {
                    if (dataAct[i].module_id === modSelected) {
                        cbDetail.append('<option value="' + dataAct[i].action_id + '">' +
                        dataAct[i].action_id + '</option>');
                    }
                }
            });

        }
    });

    MyApp.$me('.btnModal').on('click', function(){
        var data = {};
        data.konten = 'Data ini dikirim dari SampleModule';
        MyApp.openModuleWindow('sample-window',data);
    });

    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=SampleModule.js
