/**
 * dashboard.js
 * Script yang berhubungan dengan dashboard aplikasi
 * seperti : sidebar, header, loading, modal window dsb
 * @type {[type]}
 */

// Ambil userData
MyApp.userData = $.parseJSON($('#user-data').text());
    var mainMenu = $('.main-sidebar .sidebar-menu a');
    mainMenu.click(function(me) {
        me.preventDefault();
        var moduleId = $(this).data('module-id');
        if (!moduleId) return 1;
        // unselect all link and select current link
        $('.main-sidebar .sidebar-menu li').removeClass('active');
        $(this).addClass('active');
        // unselect all parent link treeview
        var pars = mainMenu.parents('li.treeview');
        if (pars.length > 0) {
            pars.removeClass('active');
        }
        // select current parent treeview
        var par = $(this).parents('li.treeview');
        if (par.length > 0) {
            par.addClass('active');
        }

        MyApp.openModule(moduleId)
            .done(function(e) {
                // console.log('berhasil meload modul');
            })
            .fail(function(e) {
                console.log(e);
            })
            .progress(function(e) {
                // console.log('progress: ' + e);
            });
        MyApp.menuClose();
    });

    // User Info
    $('#user-info').on('click', function(me) {
        MyApp.openModule('userinfo');
    });
    // Logout Aplikasi
    $('#logout').on('click', function(me) {
        $.getJSON('loginout.php?logout=1', function(result) {
            if (result.success) {
                window.location = 'index.php';
            }
        });
        me.preventDefault();
    });

    MyApp.showLoading = function(msg) {
        var mask = $('#global-mask');
        mask.height($(window).height());
        mask.removeClass('hide').show();
        mask.find('.msg').html(msg || 'Loading..');
    };

    MyApp.hideLoading = function() {
        $('#global-mask').hide();
    };

    MyApp.getContentHeight = function() {
        var contentPad = $('#content-id').outerHeight() - $('#content-id').height();
        return $(window).height() - $('.main-header').height() - contentPad;
    };

    // Open and close right control-sidebar
    MyApp.controlSidebar = {};
    MyApp._cso = $.AdminLTE.options.controlSidebarOptions;
    // Search source kode di dist/js/app/js bagian $.AdminLTE.controlSidebar
    MyApp.controlSidebar.open = function() {
        $.AdminLTE.controlSidebar.open($(MyApp._cso.selector), MyApp._cso.slide);
    };

    MyApp.controlSidebar.close = function() {
        $.AdminLTE.controlSidebar.close($(MyApp._cso.selector), MyApp._cso.slide);
    };

    //
    MyApp.controlSidebar.html = function(content) {
        return $(MyApp._cso.selector).html(content);
    };

    // Otomatis buka modul dashboard
    // MyApp.openModule('dashboard'); <-- ini bisa, tapi (class) menu tidak active
    $('.sidebar-menu a.dashboard').click();
    MyApp.openModule('dashboard');


    // jika menggunakan plugin bootstrap datepicker, set default format
    if ($.fn.datepicker) $.fn.datepicker.defaults.format = 'dd/mm/yyyy';

    // // Supress alert window, change with modal window from bootstrap maybe
    // (function(proxied) {
    //     window.alert = function() {
    //         // do something here
    //         console.log(arguments);
    //         // return proxied.apply(this, arguments);
    //     };
    // })(window.alert);


//# sourceURL=app/dashboard.js