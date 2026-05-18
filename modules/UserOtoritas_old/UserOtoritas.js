// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    // fungsi yg melekat di current module
    // Akan memberi cek di module jika semua action di cek, dan sebaliknya
    MyApp.module.cekModuleCheked = function(module_id) {
        var allCheked = true;
        MyApp.$me('.uo-action.' + module_id + ' :checkbox').each(function() {
            allCheked &= this.checked;
        })

        MyApp.$me('.uo-module.' + module_id + ' :checkbox').prop('checked', allCheked);
    }

    MyApp.ajax(['userotoritas', 'listUserGroup']).done(function(users) {
        MyApp.ajax(['userotoritas', 'list']).done(function(oto) {
            var data = {
                users: users,
                oto: oto.result
            };
            MyApp.renderMainTpl(data);
            MyApp.loadModuleCss('style.css');

            MyApp.$me('.table-otoritas').height(MyApp.getContentHeight() - 80);

            $('.uo-module input:checkbox').on('change', function() {
                var module_id = $(this).data('id');
                var selector = '.uo-action.' + module_id + ' :input';
                MyApp.$me(selector).prop('checked', $(this).prop('checked'));
            });

            $('.uo-action input:checkbox').on('change', function() {
                var module_id = $(this).data('mid');
                MyApp.module.cekModuleCheked(module_id);
            });

            // Filter daftar otoritas
            MyApp.$me('.search-oto').on('input', function(e) {
                // loop semua row di tabel modules
                var key = this.value;
                MyApp.$me('.uo-action').each(function(index) {
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

            MyApp.$me('.simpan-oto').on('click', function(e) {
                e.preventDefault();
                var uid = MyApp.module.uid;
                var gid = MyApp.module.gid;
                // group id harus selalu ada
                if (!gid) {
                    alert('User atau Group belum dipilih!');
                } else {
                    var par = {
                        Module: MyApp.curMod,
                        option: 'userotoritas',
                        action: 'save',
                        mode: uid ? 'user' : 'group',
                        mode_id: uid ? uid : gid
                    };

                    var cks = $('.mod-userotoritas .uo-action :checkbox');
                    var el, obj, actions = [];
                    cks.each(function(i) {
                        el = cks[i];
                        if (el.checked) {
                            obj = {
                                'module_id': el.getAttribute('data-mid'),
                                'action_id': el.getAttribute('data-acid'),
                                'mode_id': uid ? uid : gid,
                                'mode': par.mode
                            };
                            actions.push(obj);
                        }
                    })

                    MyApp.showLoading('Menyimpan Otoritas..');
                    $.ajax({
                            method: 'POST',
                            url: 'service.php?' + $.param(par),
                            contentType: 'application/json; charset=utf-8',
                            dataType: 'json',
                            data: JSON.stringify(actions)
                        })
                        .done(function(msg) {
                            console.log('Data berhasil disimpan, terimakasih');
                        })
                        .always(function(){
                            MyApp.hideLoading();
                        });
                }
            })

            MyApp.$me('.group-menu a').on('click', function(e) {
                e.preventDefault();
                MyApp.$me('.group-menu a').removeClass('active');
                $(this).addClass('active');

                var gid = $(this).data('gid') || '';
                var uid = $(this).data('uid') || '';

                if (!gid) {
                    var child = $(this).parents('.group-list').children('a');
                    gid = child.data('gid');
                    child.addClass('active');
                }

                // simpan agar bisa di akses diluar ajax
                MyApp.module.gid = gid;
                MyApp.module.uid = uid;

                MyApp.ajax(['userotoritas', 'list', gid, uid]).done(function(oto) {
                    if (oto.success) {
                        var cls = '';
                        oto.result.forEach(function(item) {
                            cls = '.' + item.module_id + '_' + item.action_id;
                            $(':checkbox' + cls).prop('checked', item.selected);
                        });

                        // Cek module jika semua action-nya di cek..
                        // ambil semua modul yang tampil
                        var mid; // module_id
                        MyApp.$me('.uo-module input:checkbox').each(function(i) {
                            mid = this.getAttribute('data-id');
                            MyApp.module.cekModuleCheked(mid);
                        })
                    }
                });
            })
        });
    });
})();

//# sourceURL=UserOtoritas.js
