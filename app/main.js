/**
 * main.js
 * Script ini yang akan di load oleh ./dashboard.html
 * Selanjutnya script2 lain di folder ./app/ akan di load dibagian bawah
 * @type {Object}
 */
$(document).ready(function () {
    /**
     * Global variabel untuk aplikasi/framework
     * Induk untuk semua variabel dan Fungsi di framework
     */
    window.MyApp = {};
    // Untuk menyimpan master data Global aplikasi (dataStore)
    MyApp.ds = {};

    /**
     * Render main template ke content area aplikasi
     * @param  {array} data data yang akan di render ke template dengan Mustache
     * @return content yang akan tampil
     */
    MyApp.renderMainTpl = function (data, callback) {
        var html;
        // Jika yg diload buka tipe 'window', misal tipe: panel
        if (!MyApp.curWindow) {
            html = Mustache.render(MyApp[MyApp.curMod].tpl, data || {});
            $('#content-id').html(html);
        } else {
            // Jika tipe 'window' modal
            var cw = MyApp.curWindow;
            html = Mustache.render(cw[cw.curMod].tpl, data || {});
            $('#myAppModal').addClass(cw.curModClass);
            $('#myAppModal .modal-content').html(html);
            if ($.isFunction(callback)) {
                MyApp.modal.on('shown.bs.modal.myapp', function (e) {
                    callback(e);
                    MyApp.modal.off('shown.bs.modal.myapp');
                });
            }
            MyApp.modal.modal('show');
        }
    };

    /**
     * Mengambil main template dari current module
     * @return {string} html template (NamaModule.html)
     */
    MyApp.getMainTpl = function () {
        var cur = MyApp.curWindow ? MyApp.curWindow : MyApp;
        return cur[cur.curMod].tpl;
    };

    /**
     * Mengambil bagian template dari main template
     * template diletakkan diantara <script id="tpl-xyz" type="x-tmpl-mustache"></script>
     * @param  {string} selector jQuery : id, class, dll
     * @return {string}          string template
     */
    MyApp.getTemplate = function (selector) {
        var tpl = $(MyApp.getMainTpl()).find(selector);
        return tpl ? tpl.html() : '';
    };

    // Jika param adalah array string, maka itu adalah [option, action]
    // jika object maka akan di merge dengan defParam
    MyApp.ajax = function (param, callback) {
        var cur = MyApp.curWindow ? MyApp.curWindow : MyApp;
        var defParam = {
            Module: cur.curMod
        };

        // Jika parameter adalah array, jadikan object
        // result = {
        //    option : param[0],
        //    action = param[1],
        //    par1 = param[2],
        //    par2 = param[3],
        //    ...}
        if ($.isArray(param)) {
            var tmp = {};
            for (var i = 0; i < param.length; i++) {
                if (i === 0) {
                    tmp.option = param[i];
                } else if (i === 1) {
                    tmp.action = param[i];
                } else {
                    tmp['par' + (i - 1)] = param[i];
                }
            }
            param = tmp;
        }

        return $.ajax({
            url: 'service.php',
            type: 'GET',
            cache: false,
            dataType: 'json',
            data: $.extend(defParam, param)
        })
            .done(callback)
            .fail(function (jqxhr, status, error) {
                console.log('error' + status);
            })
            .always(function (data, status, obj) {
                // status : "success", "notmodified", "nocontent", "error", "timeout", "abort", or "parsererror"
                if (status === 'success') {
                    //  x == null artinya -> (variable === undefined || variable === null)
                    if (data.success != null && (data.success === false)) {
                        alert(data.msg);
                    }
                } else if (status === 'parsererror') {
                    console.log(data.responseText);
                    alert('Parse Error:\n' + obj.message + '\nSilahkan melihat response text di console');
                } else {
                    alert('Error status:' + status + '\n' + obj.message);
                }
            });
    };

    $.getScript('app/util.js');
    $.getScript('app/module.js').done(function () {
        $.getScript('app/dashboard.js');
    });
    $.getScript('app/extend.jsgrid.js');
});
