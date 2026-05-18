// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss();

    MyApp.module.tableSesi = MyApp.$me('.table-sesi')
    MyApp.module.tableUser = MyApp.$me('.table-user')
    var maxH = MyApp.getContentHeight() - 20;

    // Fungsi membalik tanggal 2016-06-05 11:02:35 -> 05-06-2016 11:02:35
    var reverseDate = function(dateString) {
        var tmp = dateString.split(' ');
        return tmp[0].split('-').reverse().join('/') + ' ' + tmp[1];
    }

    var getTime = function(dateString) {
        return dateString.split(' ')[1];
    }

    var getDate = function(dateString) {
            return dateString.split(' ')[0].split('-').reverse().join('/');
        }
        // inisialisasi jsGrid
    MyApp.module.tableSesi.jsGrid({
        width: '100%',
        height: maxH,
        noDataContent: "Not found",
        sorting: true,
        paging: true,
        autoload: true,
        loadIndication: false,
        controller: {
            loadData: function() {
                var d = $.Deferred();
                MyApp.ajax(['session', 'list']).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            }
        },
        fields: [{
            title: 'User/ IP /Status/ Session',
            width: 70,
            itemTemplate: function(value, item) {
                return '<span class="user">' + item.user_id + '</span> | ' +
                    '<span class="status">' + item.session_status + '</span><br>' +
                    item.ip_address + '<br>' +
                    '<span class="sesi">' + item.session_id.substring(0, 16) + '...</span>';
            }
        }, {
            title: 'Login/ Updated/ Logout',
            width: 80,
            itemTemplate: function(value, item) {
                return 'Login: ' + getDate(item.time_login) + '<br>' +
                    'Jam: <span class="login">'+ getTime(item.time_login) + '</span><br>' +
                    getTime(item.time_updated) + ' | ' +
                    getTime(item.time_logout);
            }
        }, {
            title: 'Browser Agent',
            width: 150,
            name: 'user_agent'
        }],
        rowClick: function(args) {
            MyApp.module.tableUser.jsGrid('loadData', args.item);
        }
    });

    // inisialisasi jsGrid
    MyApp.module.tableUser.jsGrid({
        width: '100%',
        height: maxH,
        noDataContent: "Not found",
        sorting: true,
        paging: true,
        autoload: false,
        loadIndication: false,
        controller: {
            loadData: function(item) {
                var d = $.Deferred();
                MyApp.ajax(['log', 'list', item.session_id]).done(function(resp) {
                    d.resolve(resp.result);
                });
                return d.promise();
            }
        },
        fields: [{
            title: 'Jam &amp; Aksi',
            width: 40,
            itemTemplate: function(value, item) {
                return getTime(item.date) + '<br><span class="action">' +
                    item.action + '</span>';
            }
        }, {
            title: 'Alamat IP',
            width: 35,
            itemTemplate: function(value, item) {
                return item.remote_addr;
            }
        }, {
            title: 'Data',
            name: 'data',
            cellRenderer: function(value, item) {
                return '<td style="word-wrap: break-word;">' + value + '</td>'
            }
        }],
        // rowClass: function(item, itemIndex) {
        //     return 'row-user';
        // }
    }); // EndOf inisialisasi jsGrid
})();

//# sourceURL=LogActivity.js
