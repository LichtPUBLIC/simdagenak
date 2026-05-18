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
    };
    jsGrid.validators.username = {
        message: 'Username / Group harus lowercase, pemisah tanda strip',
        validator: function(value, item) {
            return /^[a-z-]+$/.test(value);
        }
    };
    jsGrid.validators.moduleId = {
            message: 'Module ID harus lowercase, pemisah tanda strip',
            validator: function(value, item) {
                return /^[a-z-]+$/.test(value);
            }
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
            // cbUser.select2().select2('val','');
        });
    }
    MyApp.module.openModal = function(flag, data) {
        $('.submit').attr({
            type: 'submit',
            form: 'form-user',
            value: 'new',
        });
        $('#myModal').modal(); 
        $('#myModal').find('#flag').val(flag);
        if (flag === 'add') {
            $("#form-user").trigger("reset");
            // $(".modal-header").text('Tambah User Baru di ' + data.description).addClass('bg-blue');
            $(".modal-title").text('Tambah User Baru di ' + data.description);
            $("#group-id").val(data.group_id);
            $('.password').prop('required', true);
        } else {
            if(data.isadmin==1){
                $('#isadmin').prop('checked',true);
            }
            MyApp.setFormValues('#form-user', data);
            $(".modal-title").text('Edit ' + data.nama);
            $('.password').prop('required', false);
        }
        $('#myModal').on('shown.bs.modal', function(e) {
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
                        alert("Berhasil menyimpan data");
                        $('#myModal').modal('hide');
                    }
                })
            });
            // $('#btLoadData').click(function (e) {
            //     e.preventDefault();
            //     var params3 = {
            //         option : "PUBLIC",
            //         action : "datapegawai",
            //         nip : $('#username').val()
            //     }
            //     MyApp.ajax(params3).done(function (resp) {
            //         $('#name').val(resp.result[0].biodata.nama);
            //     })
            // });

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
                            $('#myModal').modal('hide');

                        }
                    });
                }
            });
            $('#myModal').on('hide.bs.modal', function() {
            $(".table-modules").jsGrid("loadData");
            
            var item = {};
            item.group_id = $('#group-id').val();
            var args ={};
            args.item = item;
            $(".table-actions").jsGrid("loadData", args);
        });

        });
        
    }




    var maxH = MyApp.getContentHeight() - 50;
    var i = 0;
        // inisialisasi jsGrid tabel modules
    MyApp.$me('.table-modules').jsGrid({
        width: '100%',
        height: maxH,
        inserting: true,
        editing: true,
        sorting: true,
        paging: true,
        autoload: true,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" + "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function() {
                var d = $.Deferred();

                var params = {
                    option: 'ACTION',
                    action: 'glist',
                }
                MyApp.ajax(params).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                var params = {
                    option: 'group',
                    action: 'edit',
                    data: item
                }
                console.log(params);
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        item.old_module_id=item.module_id;
                        // console.log(item);
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
                    option: 'group',
                    action: 'add',
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
            deleteItem: function(item) {
                var d = $.Deferred();
                var params = {
                    option: 'group',
                    action: 'delete',
                    tabel: 'modules',
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
            }
        },
        fields: [{
        //     name: "no",
        //     title: "No",
        //     width: 10
        // }, {
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
            MyApp.$me.previousItemModule = args.previousItem;
        },
        rowClick: function(args) {
            var tr = $('.table-modules tbody tr');
            tr.removeClass('selected');
            tr.eq(args.itemIndex).addClass('selected');
            MyApp.$me('.overlay').show();
            $(".judul-user").text('Daftar user di ' + args.item.description);
            MyApp.$me('.table-actions').jsGrid('loadData', args).done(function() {
                MyApp.$me('.overlay').hide();
                MyApp.$me('.add-user, .refresh-user').removeClass('disabled');
                MyApp.$me('.add-user').on('click', function() {
                    var data = args.item;
                    // console.log(data);
                    data.konten = 'Data ini dikirim dari SampleModule';
                    MyApp.module.openModal('add', data);
                });
            })
        },
        rowDoubleClick: function(args) {
            this.editItem(args.item);
        }
    }); // EndOf inisialisasi jsGrid
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
        height: maxH,
        noDataContent: "Tidak ada User di group ini ",
        inserting: false,
        editing: true,
        sorting: true,
        paging: true,
        autoload: false,
        pageSize: 50,
        deleteConfirm: "Yakin akan menghapus data yg dipilih?\n" + "Jangan khawatir, Aksi ini tidak akan menghapus data sebenarnya..",
        invalidMessage: "Ada data yang tidak valid!",
        controller: {
            loadData: function(filter) {
                var d = $.Deferred();
                // console.log(filter);
                var params = {
                    option: 'user',
                    action: 'list',
                    par1: filter.item.group_id
                }
                MyApp.ajax(params).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            },
            updateItem: function(item, editedItem) {
                var d = $.Deferred();
                var params = {
                    option: 'user',
                    action: 'edit',
                    data: item
                }
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        item.old_action_id=item.action_id;
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
                    option: 'user',
                    action: 'add',
                    data: item
                }
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
                        //$(".table-actions").jsGrid("loadData");
                       MyApp.$me('.jsgrid-mode-on-button').trigger('click');                       
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
                }
                MyApp.ajax(params).done(function(resp) {
                    if (resp.success) {
                        d.resolve(item);
                        alert(resp.msg);
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
        // }, {
        //     name: "no",
        //     title: "No.",
        //     width: 10
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
            width: 15

        }],
        // before controller.updateItem, simpan item sebelum edit terjadi
        // onItemUpdating: function(args) {
        //     MyApp.$me.previousItemAction = args.previousItem;
        // }
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
    }); // EndOf inisialisasi jsGrid

    MyApp.$me('.refresh-user').click(function(e) {
        var item = {};
        item.group_id = $('#group-id').val();
        MyApp.module.tableUsers.jsGrid('loadData', item);
    });
    // ketika sidebar toggle, refresh untuk calculate ulang kolom
    $('.sidebar-toggle').click(function() {
        setTimeout(function() {
            MyApp.$me('.table-modules').jsGrid("refresh");
            MyApp.$me('.table-actions').jsGrid("refresh");
        }, $.AdminLTE.options.animationSpeed);
    })
})();
//# sourceURL=CodeModule.js