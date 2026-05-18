// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.moduleInit();

    jsGrid.validators.username = {
        message: 'Username / Group harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    };

    MyApp.module.tableGroups = MyApp.$me('.table-groups');
    MyApp.module.tableUsers = MyApp.$me('.table-users');
    MyApp.checkbox = function(item) {
        if (item.active) {
            item.active = 1;
        } else {
            item.active = 0;
        }
        return item;
    };
    var loadUserCb = function (group_id) {
        /*load all user*/
        var params2 ={
            option:"PUBLIC",
            action:"userList",
            group_id : group_id
        };
        var cbUser = $('.user_terdaftar');
        MyApp.ajax(params2).done(function (resp) {
            $(".selection").remove();
            cbUser.html('<option value="">--pilih user terdaftar--</option>');
            $.each(resp.result,function (index, val) {
                cbUser.append('<option value="'+val.user_id+'">'+val.nama+'</option>');
            });
            cbUser.select2().select2('val','');
        });
    }
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
            $(".modal-header").text('Tambah User Baru di ' + data.description).addClass('bg-blue');
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
            loadUserCb($('.group-id').val());
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

            $('#form-user').validator().on('submit', function(e) {
                if (e.isDefaultPrevented()) {
                } else {
                    e.preventDefault();
                    var datastring = $('#form-user').serializeArray();
                    console.log(datastring);
                    var params = {};
                    if (datastring[0].value == "add") {
                        params = {
                            option: 'user',
                            action: 'add',
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
    var maxH = MyApp.getContentHeight() - 50;
    // inisialisasi jsGrid
    MyApp.module.tableGroups.jsGrid({
        width: '100%',
        height: maxH,
        noDataContent: "Group belum ada",
        // inserting: true,
        editing: true,
        inserting: true,
        sorting: true,
        paging: false,
        autoload: true,
        loadIndication: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function() {
                MyApp.boxLoadingHide('.box');
                var d = $.Deferred();
                MyApp.ajax(['PUBLIC', 'glist']).done(function(resp) {
                    var i = 1;
                    $.each(resp.result, function(e, val) {
                        val.no = i++;
                    });
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                MyApp.checkbox(item);
                var params = {
                    option: 'group',
                    action: 'edit',
                    data: item
                };
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        MyApp.module.tableGroups.jsGrid("loadData");
                    } else {
                        d.resolve(MyApp.$me.previousItemModule);
                        // alert("Update Gagal\n" + resp.msg);
                    }
                });
                return d.promise();
            },
            insertItem: function(item) {
                var d = $.Deferred();
                MyApp.checkbox(item);
                var params = {
                    option: 'group',
                    action: 'add',
                    data: item
                };
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
                        MyApp.module.tableGroups.jsGrid("loadData");
                    }
                });
                return d.promise();
            },
            deleteItem: function(item) {
                var d = $.Deferred();
                MyApp.checkbox(item);
                var params = {
                    option: 'group',
                    action: 'delete',
                    data: item
                };
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        // d.resolve(item);
                        alert(resp.msg);
                        MyApp.module.tableGroups.jsGrid("loadData");
                        //$(".table-actions").jsGrid("loadData");
                    }else{
                        alert(resp.msg);
                    }
                });
                return d.promise();
            }
        },
        fields: [{
            name: "no",
            title: "No",
            width: 10
        }, {
            name: "group_id",
            title: "ID Grup",
            type: "text",
            editing: true,
            visible: true,
            width: 50,
            validate: 'username'
        }, {
            name: "id",
            type: "text",
            visible: false
        }, {
            name: "description",
            title: "Nama Grup",
            type: "text"
        }, {
            name: "user",
            width: 20
        }, {
            name: "active",
            title: "Aktif",
            type: "checkbox",
            width: 20,
            sorting: false
        }, {
            type: 'control',
            title: 'Aksi',
            width: 30
        }],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowClick: function(args) {
            var tr = $('.table-groups tbody tr');
            tr.removeClass('selected');
            tr.eq(args.itemIndex).addClass('selected');
            MyApp.$me('.overlay').show();
            if (!args.event.target.classList.contains('action-field')) {
                // Jalankan jika bukan field action yg di klik
                MyApp.$me('.table-users').removeClass('hidden');
                MyApp.module.tableUsers.jsGrid('loadData', args.item);
                MyApp.$me('.group-name').text('di ' + args.item.description);
                MyApp.$me('.add-user, .refresh').removeClass('disabled');
                MyApp.$me('.add-user').on('click', function() {
                    var data = args.item;
                    data.konten = 'Data ini dikirim dari SampleModule';
                    MyApp.module.openModal('add', data);
                });
                MyApp.$me('#group-id').val(args.item.group_id);
                MyApp.$me('#btn-refresh-user').removeClass('disabled');
            }
        },
        rowDoubleClick: function(args) {
            this.editItem(args.item);
        },
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-groups');

    // inisialisasi jsGrid
    MyApp.module.tableUsers.jsGrid({
        width: '100%',
        height: maxH,
        noDataContent: "Tidak ada User di group ini",
        inserting: false,
        editing: true,
        sorting: true,
        paging: true,
        autoload: false,
        pageSize: 50,
        // filtering: true,
        // loadIndication: false,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function(item) {
                var d = $.Deferred();
                MyApp.ajax(['user', 'list', item.group_id]).done(function(resp) {
                    console.log(resp)
                    var i = 1;
                    $.each(resp.result, function(e, val) {
                        val.no = i++;
                    });
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                MyApp.checkbox(item);
                var params = {
                    option: 'user',
                    action: 'edit',
                    data: item
                };
                MyApp.ajax(params).done(function(resp) {
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
                MyApp.checkbox(item);
                item.group_id = $('#group-id').val();
                var params = {
                    option: 'user',
                    action: 'add',
                    data: item
                };
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
                        $(".table-groups").jsGrid("loadData");
                    } else {
                        d.resolve(MyApp.$me.previousItemModule);
                        alert("Update Gagal\n" + resp.msg);
                    }
                });
                return d.promise();
            },
            deleteItem: function(item) {
                var d = $.Deferred();
                var params = {
                    option: 'user',
                    action: 'delete',
                    data: item
                };
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
                        $(".table-groups").jsGrid("loadData");
                        //$(".table-actions").jsGrid("loadData");
                    }
                });
                return d.promise();
            }
        },
        fields: [{
            name: "group_id",
            width: 10,
            visible: false
        }, {
            name: "no",
            title: "No.",
            width: 10
        }, {
            name: "username",
            title: "Username",
            type: "text",
            width: 50,
            editing: true,
            visible: true,
            validate: 'username'
        }, {
            name: "nama",
            title: "Nama",
            type: "text"
        }, {
            name: "active",
            title: "Aktif",
            type: "checkbox",
            width: 20,
            sorting: false
        }, {
            type: "control",
            modeSwitchButton: false,
            editButton: false,
            width: 10

        }],
        // before controller.updateItem, simpan item sebelum edit terjadi
        onItemUpdating: function(args) {
            MyApp.$me.previousItem = args.previousItem;
        },
        rowDoubleClick: function(args) {
            MyApp.module.openModal('edit', args.item);
        },
        rowClick: function(args) {
            var tr = $('.table-users tbody tr');
            tr.removeClass('selected');
            tr.eq(args.itemIndex).addClass('selected');
        }
    }).filterToolbar({
        "stringResult": true
    }); // EndOf inisialisasi jsGrid
    jsGrid.setActionFieldEvent('.table-users');

    MyApp.$me('.refresh-user').click(function(e) {
        var item = {};
        item.group_id = $('#group-id').val();
        MyApp.module.tableUsers.jsGrid('loadData', item);
    });

    // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);

})();
//# sourceURL=UserManagement.js
