/**
 * module.js
 * Script yang berisi fungsi2 dan method yang berkaitan dengan module
 */

$(document).ready(function() {
    MyApp.$me = {};
    // akan mengambil current module, agar class sama beda module tidak masalah
    // return jQuery object
    MyApp.$me = function(selector) {
        var cls = '';
        if (MyApp.curWindow) {
            cls = '#myAppModal';
        } else {
            cls = '.app-panel.' + MyApp.curModClass;
        }
        var jq = $(cls);
        return (selector) ? $(cls + ' ' + selector) : jq;
    };

    // Load css file dari current directory module
    MyApp.loadModuleCss = function(cssfile) {
        // default nama file css yg akan di load
        cssfile = cssfile || 'style.css';
        var cur = MyApp.curWindow ? MyApp.curWindow : MyApp;
        MyApp.loadCss(cur[cur.curMod].curDir + cssfile);
    };

    // load script dari current module directory
    // callback = function(data, textStatus, jqXHR)
    MyApp.loadModuleScript = function(script, callback) {
        var cur = MyApp.curWindow ? MyApp.curWindow : MyApp;
        return $.ajax({
            url: cur[cur.curMod].curDir + script,
            dataType: 'script',
            success: callback
        });
    };

    // Load file dari current module, lihat dokumentasi jQuery.get()
    // callback = function(data, textStatus, jqXHR)
    MyApp.loadModuleFile = function(file, callback) {
        var cur = MyApp.curWindow ? MyApp.curWindow : MyApp;
        return $.ajax({
            url: cur[cur.curMod].curDir + file,
            success: callback,
            // dataType: dataType
        });
    };

    /**
     * Membuka module aplikasi dengan onview panel
     * @param  {string} moduleId Nama module id seperti nama di databases
     * @param  {object} data     Data yang akan dikirim ke modul yg dibuka
     *    data bisa diakses dengan MyApp.module.data
     * @return {}           module akan aktif
     */
    MyApp.openModule = function(moduleId, data) {
        var d = $.Deferred();
        d.notify('init module ' + moduleId);
        MyApp.showLoading('Membuka Modul ' + moduleId);
        $.getJSON('service.php', {
                _init: moduleId
            }).done(function(resp) {
                d.notify('init module finish');
                if (resp.success) {
                    d.notify('user diijinkan mengakses module');
                    // var modCls = $
                    var _module = resp.data;
                    var modCls = _module.module;
                    MyApp.curMod = modCls;
                    MyApp.curModClass = 'mod-' + _module.module_id;
                    MyApp.title = _module.name;
                    MyApp.titleDesc = _module.description;
                    // buat object baru dengan nama property module Class
                    MyApp[modCls] = MyApp[modCls] || {};
                    MyApp.module = MyApp[modCls];
                    MyApp.module.data = data;
                    // tambah class 'panel' untuk membedakan dengan onview 'window'
                    MyApp[modCls].tpl = resp.tpl.replace('{{module_id_class}}', 'app-panel ' + MyApp.curModClass);
                    MyApp[modCls].curDir = 'modules/' + modCls + '/';
                    // unser variabel curWindow
                    MyApp.curWindow = null;

                    // $getScript defaultnya cache : false, jadi langsung gunakan ajax
                    d.notify('start load script: ' + modCls + '.js');
                    $.ajax({
                            url: MyApp[modCls].curDir + modCls + '.js',
                            dataType: 'script',
                            // untuk release/production, set ke true
                            cache: false,
                        })
                        .done(function(script, textStatus) {
                            // debugger;
                            d.notify('berhasil meload script: ' + modCls + '.js');
                            $('.mod-title .description').html(MyApp.title);
                            // $('.mod-title .description').html(MyApp.titleDesc);
                            d.resolve('Load module dan js sukses');
                        })
                        .fail(function(jqxhr, settings, exception) {
                            // debugger;
                            // d.notify('gagal meload script: ' + modCls + '.js');
                            d.reject('gagal meload script: ' + modCls + '.js');
                            // console.log('Error in ' + modCls + '.js :' + exception.message);
                        })
                        .always(function() {
                            MyApp.hideLoading();
                        });
                } else {
                    // d.notify('user tidak berhak mengakses modul');
                    d.reject('user tidak berhak mengakses modul');
                    if (resp.msg) {
                        window.location='index.php';
                        alert(resp.msg);
                    } else {
                        alert('Maaf, anda tidak berhak mengakses module ini');
                    }
                }
            })
            .fail(function(jqxhr, settings, exception) {
                d.reject('Gagal init module: ' + moduleId);
            })
            .always(function() {
                MyApp.hideLoading();
            });
        return d.promise();
    };

    /**
     * Membuka module aplikasi dalam Window Modal
     * @param  {string} moduleId Nama module id seperti nama di databases
     * @param  {object} data     Data yang akan dikirim ke modul yg dibuka
     *    data bisa diakses dengan MyApp.curWindow.module.data
     * @return {}           module akan aktif
     */
    // variabel hold Modal dialog
    MyApp.modal = $('#myAppModal');
    MyApp.modal.on('hidden.bs.modal', function(e) {
        MyApp.modal.removeClass(MyApp.curWindow.curModClass);
        MyApp.curWindow = null; // Reset modal Classes
    });

    // $.fn.modal.Constructor.DEFAULTS.keyboard = false;
    $.fn.modal.Constructor.DEFAULTS.backdrop = 'static'; // Mencegah menutup modal pada backdrop click

    // 'private' function openModuleWindow
    MyApp._openModuleWindow = function(moduleId, data, modalSize) {
        var d = $.Deferred();
        d.notify('init module window ' + moduleId);
        MyApp.showLoading('Membuka Modul Window ' + moduleId);
        $.getJSON('service.php', {
                _init: moduleId
            }).done(function(resp) {
                d.notify('init module finish');
                if (resp.success) {
                    d.notify('user diijinkan mengakses module');
                    // var modCls = $
                    var _module = resp.data;
                    var modCls = _module.module;
                    // Ini untuk mengatur ukuran modal
                    if (modalSize == 'large') {
                        modalSize = 'modal-lg';
                    } else if (modalSize == 'small') {
                        modalSize = 'modal-sm';
                    } else {
                        modalSize = 'modal-md';
                    }
                    var cmw = {}; // current Modal Window
                    cmw.curmodId = moduleId;
                    cmw.curMod = modCls;
                    cmw.curModClass = 'mod-' + _module.module_id + ' ' + modalSize;
                    cmw.title = _module.name;
                    cmw.titleDesc = _module.description;
                    // buat object baru dengan nama property module Class
                    cmw[modCls] = cmw[modCls] || {};
                    cmw.module = cmw[modCls];
                    cmw.module.data = data;
                    cmw[modCls].tpl = resp.tpl.replace('{{module_id_class}}', cmw.curModClass);
                    cmw[modCls].curDir = 'modules/' + modCls + '/';
                    // Untuk membedakan dengan module dengan onview = panel
                    MyApp.curWindow = cmw;

                    // $getScript defaultnya cache : false, jadi langsung gunakan ajax
                    d.notify('start load script: ' + modCls + '.js');
                    $.ajax({
                            url: MyApp.curWindow[modCls].curDir + modCls + '.js',
                            dataType: 'script',
                            // untuk release/production, set ke true
                            cache: false,
                        })
                        .done(function(script, textStatus) {
                            d.notify('berhasil meload script: ' + modCls + '.js');
                            d.resolve('Load module dan js sukses');
                        })
                        .fail(function(jqxhr, settings, exception) {
                            // d.notify('gagal meload script: ' + modCls + '.js');
                            d.reject('gagal meload script: ' + modCls + '.js');
                            // console.log('Error in ' + modCls + '.js :' + exception.message);
                        })
                        .always(function() {
                            MyApp.hideLoading();
                        });
                } else {
                    // d.notify('user tidak berhak mengakses modul');
                    d.reject('user tidak berhak mengakses modul');
                    if (resp.msg) {
                        alert(resp.msg);
                    } else {
                        alert('Maaf, anda tidak berhak mengakses module ini');
                    }
                }
            })
            .fail(function(jqxhr, settings, exception) {
                d.reject('Gagal init module: ' + moduleId);
            })
            .always(function() {
                MyApp.hideLoading();
            });
        return d.promise();
    };

    MyApp.openModuleWindow = function(moduleId, data, modalSize) {
        if (MyApp.curWindow !== null) {
            // Handler untuk membuka dua modal secara bergantian
            MyApp.modal.modal('hide');
            // agar bisa di off setelah selesai digunakan, tambah saja 'wind2'
            MyApp.modal.on('hidden.bs.modal.wind2', function(e) {
                MyApp._openModuleWindow(moduleId, data, modalSize).then(function() {
                    MyApp.modal.off('hidden.bs.modal.wind2');
                });
            });
        } else {
            MyApp._openModuleWindow(moduleId, data, modalSize);
        }
    };
});

//# sourceURL=app/module.js