// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    jsGrid.validators.moduleId = {
        message: 'Module ID harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    }

    var AddField = function(config) {
        jsGrid.Field.call(this, config);
    };
    AddField.prototype = new jsGrid.Field({
        width: 30,
        css: "action-field-container", // redefine general property 'css'
        align: "center", // redefine general property 'align'

        myCustomProperty: "foo", // custom property
        sorter: $.noop,
        itemTemplate: function(value, item) {
            return '<button data-id=' + item.id_unit_kerja + ' data-whatever=' + item.kode_unit_kerja + ' class="fa fa-plus item-add action-field" title="Tambah data"></button>'
        }
    });

    jsGrid.fields.add = AddField;

    MyApp.module.tableUnit = MyApp.$me('.table-unit')
    MyApp.module.tableUsers = MyApp.$me('.table-users')

    // inisialisasi jsGrid
    MyApp.module.tableUnit.jsGrid({
        width: '100%',
        //height: 'auto',
        noDataContent: "Not found",
        // inserting: true,
        autoload: true,
        paging: true,
        pageLoading: true,
        pageSize: 30,
        pageIndex: 1,
        pageButtonCount: 5,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" +
            "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function(filter) {
                var d = $.Deferred();
                var a = $(".cb-user").val();
                var startIndex = (filter.pageIndex - 1) * filter.pageSize;

                var params = {
                    Module: MyApp.curMod,
                    option: 'ACTION',
                    action: 'unitList',
                    data:a,
                    start: startIndex,
                    limit: filter.pageSize
                }
                MyApp.ajax(params).done(function(resp) {
                    var rsl = {};
                    var total = resp.total
                    rsl = resp.result;
                    var res=[];
                    //console.log(now);
                    for (var i = 0; i < rsl.length; i++) {                        
                        res.push({
                            id_unit_kerja:rsl[i].id_unit_kerja,
                            kepala: rsl[i].caption_kepala_unit_kerja,
                            nama_lengkap: rsl[i].nama_unit_kerja_lengkap,
                            kode_unit_kerja:rsl[i].kode_unit_kerja,

                        });
                        //console.log(rsl[i].nama_bidang);

                    }
                    var rsl2 = {
                        data: res,
                        itemsCount: total
                    };
                    d.resolve(rsl2);
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
            }
        },
        fields: [{
            title: 'Aksi',
            type: 'add',
            width: 20
        }, {
            name: "nama_lengkap",
            title: 'Nama Unit Kerja',
            type: "text",
            width: 50,
            editing: true,
            visible: true,
            validate: 'moduleId'
        }, {
            name: "id_unit_kerja",
            type: "text",
            width: 50,
            editing: true,
            visible: false
        },{
            name: "kode_unit_kerja",
            type: "text",
            width: 50,
            editing: true,
            visible: false
        }, {
            name: "kepala",
            title:"Kepala",
            type: "text",
            visible: false
        }],
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
    jsGrid.setActionFieldEvent('.table-unit');

    var DeleteField = function(config) {
        jsGrid.Field.call(this, config);
    };
    DeleteField.prototype = new jsGrid.Field({
        width: 30,
        css: "action-field-container", // redefine general property 'css'
        align: "center", // redefine general property 'align'

        myCustomProperty: "foo", // custom property
        sorter: $.noop,
        itemTemplate: function(value, item) {
            return '<button data-id=' + item.id_unit_kerja + ' class="fa fa-remove item-delete action-field" title="Hapus data"></button>'
        }
    });

    jsGrid.fields.del = DeleteField;
    // inisialisasi jsGrid
    MyApp.module.tableUsers.jsGrid({
        width: '100%',
        // height: 'auto',
        noDataContent: "Not found",
        // inserting: true,
        editing: true,
        sorting: true,
        paging: false,
        autoload: false,
        loadIndication: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function(item) {
                var d = $.Deferred();
                var a = $(".cb-user").val();
                MyApp.ajax(['unit', 'list', a]).done(function(resp) {
                    d.resolve(resp.result);

                });
                return d.promise();
            },
            deleteItem: function(item) {
                var d = $.Deferred();
                var uid = $('.cb-user').val();
                var id = item.id_unit_kerja;
                var params = {
                    option: 'ACTION',
                    action: 'delete',
                    data: {
                        user_id: uid,
                        id_unit_kerja: id
                    }
                }
                if (uid == 0) {
                    alert("Hapus data Gagal\n" + "Pilih User Terlebih Dahulu");
                } else {
                    MyApp.ajax(params).done(function(resp) {
                        if (resp.success) {
                            alert(resp.msg);
                            $(".table-unit").jsGrid("loadData");
                            $(".table-users").jsGrid("loadData");
                        } else {
                            alert("Hapus data Gagal\n" + resp.msg);
                        }
                    });
                    //alert("coba-coba")
                }
                return d.promise();
            },
        },
        fields: [{
            title: 'Aksi',
            type: 'del'
        }, {
            name: "nama_unit_kerja_lengkap",
            title:"Nama Unit Kerja",
            type: "text",
            width: 60,
            editing: true,
            visible: true
        }, {
            name: "id_unit_kerja",
            type: "text",
            width: 50,
            editing: true,
            visible: false
        }, {
            name: "caption_kepala_unit_kerja",
            title:"Kepala",
            type: "text",
            visible: false
        }],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowClick: $.noop
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-users');
    var setComboValues = function(selector, data, fValue, fText) {
        var cb = $(selector);
        if (cb.length === 0) return;

        cb.html('');
        cb.append('<option value="0">--Pilih User--</option>');
        for (var i = 0; i < data.length; i++) {
            cb.append('<option value="' + data[i][fValue] + '">' +
                data[i][fText] + '</option>');
        }
        return cb;
    }

    var filterComboValues = function(data, id, value) {
        return data.filter(function(obj) {
            // tanda '+' mengubah value menjadi integer..
            return obj[id] === +value;
        });
    }

    MyApp.ajax(['PUBLIC', 'loadAllData']).done(function(resp) {
        if (resp.success) {
            var ds = resp.result;
            // simpan data ke variabel global module agar bisa diakses kemudian
            MyApp.module.ds = {};
            for (var obj in ds) {
                if (ds.hasOwnProperty(obj)) {
                    MyApp.module.ds[obj] = ds[obj];
                }
            }
            // combo berelasi
            var cbk1 = setComboValues('.cb-user', ds.UserDS, 'user_id', 'nama');


            // // combo berelasi
            // var cbp1 = setComboValues('.cb-bpelayanan', ds.BpelayananDS, 'id_kategori_pelayanan', 'kategori_pelayanan');
            // cbp1.on('change', function(e) {
            //     var tmp = filterComboValues(MyApp.module.ds.PelayananDS, 'id_kategori_pelayanan', this.value);
            //     setComboValues('.cb-pelayanan', tmp, 'id_pelayanan', 'pelayanan');
            // });

        } else {
            alert('Info Gagal', resp.msg);
        }
    });
    $("#btn-refresh-user").click(function(e) {
        e.preventDefault();
        $(".table-users").jsGrid("loadData");
    });

    $('.cb-user').on("change", function() {
        $(".table-unit").jsGrid("loadData");
        $(".table-users").jsGrid("loadData");
    });

    $(".table-unit").on("click", ".item-add", function() {
        var uid = $('.cb-user').val();
        var id = $(this).attr('data-id');
        var id2 = $(this).attr('data-whatever');
        console.log(id2);
        var params = {
            option: 'ACTION',
            action: 'insert',
            data: {
                user_id: uid,
                id_unit_kerja: id,
                kode_unit_kerja:id2
            }
        }
        if (uid == 0) {
            alert("Tambah data Gagal\n" + "Pilih User Terlebih Dahulu");
        } else {
            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    alert(resp.msg);
                    $(".table-unit").jsGrid("loadData");
                    $(".table-users").jsGrid("loadData");
                } else {
                    alert("Tambah data Gagal\n" + resp.msg);
                }
            });
            //alert("coba-coba")
        }
    })
    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=UserUnit.js