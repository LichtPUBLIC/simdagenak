// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.moduleInit();
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
            return '<button data-id=' + item.user_id + ' class="fa fa-plus item-add action-field" title="Tambah data"></button>';
        }
    });

    jsGrid.fields.add = AddField;

    MyApp.module.tableUserAll = MyApp.$me('.table-user-all')
    MyApp.module.tableUserGroup = MyApp.$me('.table-user-group')

    // inisialisasi jsGrid
    MyApp.module.tableUserAll.jsGrid({
        width: '100%',
        //height: 'auto',
        noDataContent: "Not found",
        // inserting: true,
        sorting: true,
        paging: true,
        autoload: true,
        pageSize: 10,
        pageButtonCount: 5,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" +
            "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function() {
                var d = $.Deferred();
                var group = $(".cb-group").val();
                var cari = $(".allusertxt").val();
                MyApp.ajax(['user', 'list', group,cari]).done(function(resp) {
                    console.log(resp);
                    d.resolve(resp.result);
                    MyApp.boxLoadingHide('.box');
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
            name: "nama",
            type: "text",
            width: 50,
            editing: true,
            visible: true,
            validate: 'moduleId'
        }, {
            name: "user_id",
            type: "text",
            width: 50,
            editing: true,
            visible: true,
        },{
            title: 'Aksi',
            type: 'add',
            width: 5
        }],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowClick: function(args) {
            if (!args.event.target.classList.contains('action-field')) {
                // Jalankan jika bukan field action yg di klik
                MyApp.module.tableUserGroup.jsGrid('loadData', args.item);
            }
        }
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-user-all');

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
            return '<button data-id=' + item.id_unit + ' class="fa fa-remove item-delete action-field" title="Hapus data"></button>'
        }
    });

    jsGrid.fields.del = DeleteField;
    // inisialisasi jsGrid
    MyApp.module.tableUserGroup.jsGrid({
        width: '100%',
        // height: 'auto',
        noDataContent: "Not found",
        // inserting: true,
        editing: true,
        sorting: true,
        paging: true,
        autoload: false,
        loadIndication: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function(item) {
                var d = $.Deferred();
                var group = $(".cb-group").val();
                var cari = $(".userGrupTxt").val();
                MyApp.ajax(['PUBLIC', 'userListOwned', group, cari]).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            deleteItem: function(item) {
                var d = $.Deferred();
                var group = $('.cb-group').val();
                var id = item.user_id;
                var params = {
                    option: 'ACTION',
                    action: 'delete',
                    data: {
                        group: group,
                        user_id: id
                    }
                }
                if (group == 0) {
                    alert("Hapus data Gagal\n" + "Pilih Group Terlebih Dahulu");
                } else {
                    MyApp.ajax(params).done(function(resp) {
                        if (resp.success) {
                            // alert(resp.msg);
                            $(".table-user-all").jsGrid("loadData");
                            $(".table-user-group").jsGrid("loadData");
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
            name: "nama",
            type: "text",
            width: 50,
            editing: true,
            visible: true
        }, {
            name: "user_id",
            type: "text",
            width: 50,
            editing: true,
            visible: true,
        }],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowClick: $.noop
    }); // EndOf inisialisasi jsGrid

    jsGrid.setActionFieldEvent('.table-user-group');
    var setComboValues = function(selector, data, fValue, fText) {
        var cb = $(selector);
        if (cb.length === 0) return;

        cb.html('');
        cb.append('<option value="0">--Pilih Group--</option>');
        for (var i = 0; i < data.length; i++) {
            cb.append('<option value="' + data[i][fValue] + '">' +
                data[i][fValue] + ' - ' + data[i][fText] + '</option>');
        }
        $('.cb-group').select2().select2('val',0);
        return cb;
    };

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
            var cbk1 = setComboValues('.cb-group', ds.GroupDS, 'group_id', 'description');

        } else {
            alert('Info Gagal', resp.msg);
        }
    });
    $("#btn-refresh-user").click(function(e) {
        e.preventDefault();
        $(".table-user-group").jsGrid("loadData");
    });

    $('.cb-group').on("change", function() {
        $(".table-user-all").jsGrid("loadData");
        $(".table-user-group").jsGrid("loadData");
    });

    $(".table-user-all").on("click", ".item-add", function() {
        var group = $('.cb-group').val();
        var id = $(this).attr('data-id');
        var params = {
            option: 'ACTION',
            action: 'insert',
            data: {
                group: group,
                user_id: id
            }
        }
        if (group == 0) {
            alert("Tambah data Gagal\n" + "Pilih group Terlebih Dahulu");
        } else {
            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    // alert(resp.msg);
                    $(".table-user-all").jsGrid("loadData");
                    $(".table-user-group").jsGrid("loadData");
                } else {
                    alert("Tambah data Gagal\n" + resp.msg);
                }
            });
            //alert("coba-coba")
        }
    });

    //tambah user
    MyApp.module.openModal = function(flag, data) {
        var form = $('#modal-user').html();
        $('#submit').attr({
            type: 'submit',
            form: 'form-user',
            value: 'new',
        });
        $('#myAppModal').modal('show').find('.modal-body').html(form);
        $('#myAppModal').find('#flag').val(flag);
        if (flag === 'add') {
            $(".modal-header").text('Tambah User Baru ').addClass('bg-blue');
            $("#group-id").val(data.group_id);
            $('.password').prop('required', true);
        } else {
            if(data.isadmin==1){
                $('#isadmin').prop('checked',true);
            }
            MyApp.setFormValues('#form-user', data);
            $(".modal-header").text('Edit ' + data.nama).addClass('bg-blue');
            $('.password').prop('required', false);
        }

        $('#myAppModal').on('shown.bs.modal', function(e) {
            // loadUserCb($('.group-id').val());
            //onclick tambah user terdaftar
            $('#btAddUser').click(function () {
                var params3 = {
                    option : "PUBLIC",
                    action : "addUserTerdaftar",
                    userid : $('.user_terdaftar').val(),
                    group_id : $('.group-id').val()
                }
                MyApp.ajax(params3).done(function (resp) {
                    if(resp.success){
                        $('.user_terdaftar').val('');
                    }
                })
            });
            $('#btLoadData').click(function (e) {
                e.preventDefault();
                var params3 = {
                    option : "PUBLIC",
                    action : "datapegawai",
                    nip : $('#username').val()
                }
                MyApp.ajax(params3).done(function (resp) {
                    $('#name').val(resp.result[0].biodata.nama);
                })
            });

            $('#submit').validator().on('click', function(e) {
                // debugger;
                if (e.isDefaultPrevented()) {
                } else {
                    var datastring = $('#form-user').serializeArray();
                    e.preventDefault();
                    var params = {};
                    if ($('#flag').val() === "add") {
                        // debugger;
                        params = {
                            option: 'ACTION',
                            action: 'addUser',
                            data: datastring
                        };
                    } else {
                        params = {
                            option: 'user',
                            action: 'edit',
                            data: datastring
                        };
                    }
                    MyApp.ajax(params).done(function(resp) {
                        if (resp.success) {
                            alert("Berhasil menyimpan data");
                            $('#myAppModal').modal('hide');
                            $(".table-user-all").jsGrid("loadData");
                            $(".table-user-group").jsGrid("loadData");
                        }
                    });
                }
            });

        });
        $('#myAppModal').on('hide.bs.modal', function() {
            $(".table-groups").jsGrid("loadData");
            var item = {};
            item.group_id = $('#group-id').val();
            $(".table-users").jsGrid("loadData", item);
        });
    };

    $('.filterAllUser').click(function () {
        $(".table-user-all").jsGrid("loadData");
    });

    $('.filterUserGroup').click(function () {
        $(".table-user-group").jsGrid("loadData");
    });

    $(".btAddUser").click(function () {
        MyApp.module.openModal('add', '');
    });

//tambah grup
    MyApp.module.openModalGrup = function(flag, data) {
        var form = $('#modal-grup').html();
        $('#submit').attr({
            type: 'submit',
            form: 'form-grup',
            value: 'new',
        });
        $('#myAppModal').modal('show').find('.modal-body').html(form);
        $('#myAppModal').find('#flag').val(flag);
        if (flag === 'add') {
            $(".modal-header").text('Tambah grup Baru ').addClass('bg-blue');
        } else {
            MyApp.setFormValues('#form-grup', data);
            $(".modal-header").text('Edit ' + data.nama).addClass('bg-blue');
            $('.password').prop('required', false);
        }

        $('#myAppModal').on('shown.bs.modal', function(e) {
            // loadUserCb($('.group-id').val());
            $('#submit').validator().on('click', function(e) {
                // debugger;
                if (e.isDefaultPrevented()) {
                } else {
                    var datastring = MyApp.getFormValues($('#form-group'));
                    e.preventDefault();
                    var params = {};
                    if ($('#flag').val() === "add") {
                        // debugger;
                        params = {
                            option: 'ACTION',
                            action: 'addGrup',
                            data: datastring
                        };
                    } else {
                        params = {
                            option: 'user',
                            action: 'edit',
                            data: datastring
                        };
                    }
                    MyApp.ajax(params).done(function(resp) {
                        if (resp.success) {
                            alert("Berhasil menyimpan data");
                            $('#myAppModal').modal('hide');
                            $(".table-user-all").jsGrid("loadData");
                            $(".table-user-group").jsGrid("loadData");
                        }
                    });
                }
            });

        });
        $('#myAppModal').on('hide.bs.modal', function() {
            $(".table-groups").jsGrid("loadData");
            var item = {};
            item.group_id = $('#group-id').val();
            $(".table-users").jsGrid("loadData", item);
        });
    };

    //klik tambah grup
    $('.btTambahGrup').click(function (e) {
        e.preventDefault();
        MyApp.module.openModalGrup('add','');
    });

   //klik edit grup
    $('.btEditGrup').click(function (e) {
        e.preventDefault();
        MyApp.module.openModalGrup('edit','')
    });

    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=UserUnit.js
