// Script ini akan dijalankan setelah template utama *.html selesai di load
(function() {
    MyApp.renderMainTpl();
    MyApp.loadModuleCss();

    //Date range picker
    $('.tgl').datepicker({
        dateFormat: 'yy-mm-dd'
    });


    var datastring = $('#form-search').serializeArray();
    var params = {
            option: 'ACTION',
            action: 'groupList'
        }
        // kedepan fungsi ini dimasukkan ke data module, sebagai master
    MyApp.ajax(params).done(function(resp) {
        if (resp.success) {
            var cbMaster = $('.cb-group');
            // clear combo modules dulu
            cbMaster.html('');
            cbMaster.append('<option value="0">--Pilih Group--</option>');
            var data = resp.result;
            for (var i = 0; i < data.length; i++) {
                cbMaster.append('<option value="' + data[i].group_id + '">' +
                    data[i].description + '</option>');
            }
        }
    });
    var params = {           
            option: 'ACTION',
            action: 'unitkerjaList2',
        }
        // kedepan fungsi ini dimasukkan ke data module, sebagai master
    MyApp.ajax(params).done(function(resp) {
        if (resp.success) {
            var cbMaster = $('.cb-unit');
            // clear combo modules dulu
            cbMaster.html('');
            cbMaster.append('<option value="0">--Pilih Instansi--</option>');
            var data = resp.result;
            for (var i = 0; i < data.length; i++) {
                cbMaster.append('<option value="' + data[i].id_unit_kerja + '">' +
                    data[i].text + '</option>');
            }
        }
    });
    if (MyApp.module.data) {
        $('.user_id').val(MyApp.module.data);
        if (MyApp.module.data) {
            //var unit = $('#form-kasus').serializeArray();

            var params = {
                option: 'ACTION',
                action: 'dataList',
                user_id: MyApp.module.data
            }
            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    if (resp.result.PenggunaDS[0]) {
                        MyApp.setFormValues('#form-pengguna', resp.result.PenggunaDS[0]);
                        console.log(resp.result.PenggunaDS[0])
                    }
                }
            })
        }
    }

    function formatRepo(repo) {
        //console.log(repo);
        if (repo.loading) return repo.text;

        var markup = "<div class='select2-result-repository__title'>" + repo.unit_kerja + "</div>";


        return markup;
    }

    function formatRepoSelection(repo) {

        return repo.unit_kerja || repo.text;
    }
    $('.select1').select2({
        ajax: {
            url: "./service.php",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                console.log(params)
                return {
                    q: params.term, // search term
                    page: params.page,
                    Module: 'Disposisi',
                    option: 'ACTION',
                    action: 'unitkerjaList2',
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;
                //console.log(data)
                return {
                    results: data.result,
                    pagination: {
                        more: (params.page * 30) < data.total
                    },

                };
            },
            cache: true
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        // createSearchChoice: function(term, results) {
        //     if ($(results).filter(function() {
        //             return this.text.localeCompare(term) === 0;
        //         }).length === 0) {
        //         return {
        //             id: term,
        //             text: term
        //         };
        //     }
        // },
        // let our custom formatter work
        minimumInputLength: 5,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection, // omitted for brevity, see the source of this page
        placeholder: 'SKPD Sleman'
    });
    $('.tags').select2({
        //data: ["Clare", "Cork", "South Dublin"],
        tags: true,
        tokenSeparators: [','],
        placeholder: 'Instansi Luar Sleman/Swasta'
    });



    $('.select3').select2({
        ajax: {
            url: "./service.php",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                console.log(params)
                return {
                    q: params.term, // search term
                    page: params.page,
                    Module: 'EntrySuratMasuk',
                    option: 'ACTION',
                    action: 'unitkerjaList2',
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;
                //console.log(data)
                return {
                    results: data.result,
                    pagination: {
                        more: (params.page * 30) < data.total
                    },

                };
            },
            cache: true
        },
        escapeMarkup: function(markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 5,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection, // omitted for brevity, see the source of this page
    });


    $(".save-pengguna").on("click", function() {
            var datastring = $('.form-insert').serializeArray();
            var klasifikasi = $('.jenis-surat').val()
            var asal_surat = $('.select3').text()
            var params = {
                option: 'save',
                action: 'data',
                data: datastring,
            }

            MyApp.ajax(params).done(function(resp) {
                if (resp.success) {
                    alert(resp.msg)
                }
            });
        })
        // simulasi loading... hide setelah 500ms
    setTimeout(function() {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=SuratKeluar.js