// Script ini akan dijalankan setelah template utama *.html selesai di load
(function () {
    var dataModule = MyApp.curWindow.module.data;
    console.log(dataModule);
    var kode_indikator = dataModule.kode_indikator;
    MyApp.renderMainTpl();
    MyApp.$me.nomor = 0;
    MyApp.$me.index = 0;
    // MyApp.$me.draw = 0;
    MyApp.$me.aksi = 'add';
    var cbTahun = $('.cbTahun');
    cbTahun.select2();
    var cbSumberData = $('.cbSumberData');
    var btTambahkan = $('.btTambahkan');
    $('.indikator-h2').html("Indikator : " + dataModule.indikator);
    var bodisimbol = $('.bodisimbol');
    var idForm = $('#form_input');
    /*var idTable = $('#datatable_fixed_column_pop');
    idTable.DataTable({destroy:true});*/

    /*load data perbulan*/
    var loadDataPerbulan = function () {
        params = {
            Module: 'HitungRumus',
            option: "ACTION",
            action: "list",
            kode_indikator:kode_indikator,
            id_capaian:dataModule.id_capaian,
            tahun : cbTahun.val()
        }
        MyApp.ajax(params).done(function (resp) {
            tahun = cbTahun.val();
            $.each(resp.data,function (index, val) {
                dataKe = index + 1;
                domData = "data"+dataKe;
                domKeterangan = "keterangan"+dataKe;
                // debugger;
                if (val.dataku == null){
                    val.dataku=0;
                }
                if (val.keterangan == null){
                    val.keterangan='';
                }
                htmlData = val.dataku;
                htmlKet = val.keterangan+
                    "<a href='#' class='pull-right editDataCapaian' data-bulan='"+dataKe+"'" +
                    "data-tahun='"+tahun+"' data-dom='"+domData+"' data-domketerangan='"+domKeterangan+"'>" +
                    "<i class='fa fa-edit'></i>" +
                    "</a>";
                $('.'+domData).html(htmlData);
                $('.'+domKeterangan).html(htmlKet);
            })
        });
    };
    loadDataPerbulan();

    /*on click edit data capaian*/
    $('#tabelDataCapaian').on('click','.editDataCapaian',function () {
        curTahun = $(this).data('tahun');
        curBulan = $(this).data('bulan');
        curDom = $(this).data('dom');
        curDomKet = $(this).data('domketerangan');
        htmlEdit = "<input style='padding: 2px' type='text' class='inputDataCapaian"+curDom+"' value='"+$('.'+curDom).text()+"'>" ;

        htmlKeterangan = "<input style='padding: 2px;width: 90%' type='text' class='inputDataCapaian"+curDomKet+"' value='"+$('.'+curDomKet).text()+"'>" +
            "<a href='#' title='Simpan Data' class='pull-right saveDataCapaian' data-tahun='"+curTahun+"' style='margin: 2px;' " +
            "data-bulan='"+curBulan+"' data-dom='"+curDom+"' data-domketerangan='"+curDomKet+"'>"+
            "<i class='fa fa-save'></i>" +
            "</a>" +
            "<a href='#' title='set data null' class='pull-right setNullData' data-tahun='"+curTahun+"' style='color: red;margin: 2px' " +
            "data-bulan='"+curBulan+"' data-dom='"+curDom+"' data-domketerangan='"+curDomKet+"'>" +
            "<i class='fa fa-refresh'></i>" +
            "</a>";

        $('.'+curDom).html(htmlEdit);
        $('.'+curDomKet).html(htmlKeterangan);
    });

    /*on click save data capaian*/
    $('#tabelDataCapaian').on('click','.setNullData',function () {
        curTahun = $(this).data('tahun');
        curBulan = $(this).data('bulan');
        curDom = $(this).data('dom');
        domketerangan = $(this).data('domketerangan');
        data_fix = $('.inputDataCapaian'+curDom).val();
        data_ket = $('.inputDataCapaian'+domketerangan).val();
        params2 = {
            option : "PUBLIC",
            action : "simpanDataCapaian",
            tahun : curTahun,
            data_fix : data_fix,
            bulan : curBulan,
            data_ket:data_ket,
            id_capaian : dataModule.id_capaian,
            set_null : true
        }
        MyApp.ajax(params2).done(function (resp) {
            htmlData = data_fix;
            htmlKeterangan = data_ket+
                "<a href='#' class='pull-right editDataCapaian' data-bulan='"+curBulan+"'" +
                "data-tahun='"+curTahun+"' data-dom='"+curDom+"' data-domketerangan='"+domketerangan+"'>" +
                "<i class='fa fa-edit'></i>" +
                "</a>";
            $('.'+curDom).html(0);
            $('.'+domketerangan).html(htmlKeterangan);
        })
    });

    /*on click set null*/
    $('#tabelDataCapaian').on('click','.saveDataCapaian',function () {
        curTahun = $(this).data('tahun');
        curBulan = $(this).data('bulan');
        curDom = $(this).data('dom');
        domketerangan = $(this).data('domketerangan');
        data_fix = $('.inputDataCapaian'+curDom).val();
        data_ket = $('.inputDataCapaian'+domketerangan).val();
        params2 = {
            option : "PUBLIC",
            action : "simpanDataCapaian",
            tahun : curTahun,
            data_fix : data_fix,
            bulan : curBulan,
            data_ket:data_ket,
            id_capaian : dataModule.id_capaian
        }
        MyApp.ajax(params2).done(function (resp) {
            htmlData = data_fix;
            htmlKeterangan = data_ket+
                "<a href='#' class='pull-right editDataCapaian' data-bulan='"+curBulan+"'" +
                "data-tahun='"+curTahun+"' data-dom='"+curDom+"' data-domketerangan='"+domketerangan+"'>" +
                "<i class='fa fa-edit'></i>" +
                "</a>";
            $('.'+curDom).html(htmlData);
            $('.'+domketerangan).html(htmlKeterangan);
        })
    });

    /*on change cb tahun*/
    cbTahun.on('change',function () {
        loadDataPerbulan();
    });


    /*Datatables*/
    /*MyApp.$me.draw=0;
    var loadTabelData = function () {
        MyApp.$me.selectorTable2 = idTable.DataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            bInfo: true,
            scrollY:'50vh',
            buttons: [],
            ajax: {
                url: "service.php",
                method: "POST",
                data: {
                    Module: 'HitungRumus',
                    option: "ACTION",
                    action: "list",
                    kode_indikator:kode_indikator,
                    draw: function () {
                        MyApp.$me.draw++;
                        return MyApp.$me.draw;
                    }
                }
            },
            /!*"aoColumns" : [
                { sWidth: '50px' },
                { sWidth: '100px' },
                { sWidth: '120px' },
                { sWidth: '30px' }
            ],*!/
            "columns": [
                {
                    "data": "tahun",
                    width: 5
                },
                {"data": "tahun"},
                {"data": "bulan"},
                {"data": "data"}
            ],
            dom: 'Bfrtip',
            rowCallback: function (row, data, index) {
                $('td:eq(0)', row).html(index + 1);
                return row;
            },
            initComplete: function () {
                cariField = $('input[type=search]');
                cariField.addClass('form-control');
                cariField.attr("placeholder", "Cari Data");

                button = '<button data-aksi="refresh" style="margin: 5px 5px 0 0" class="btn btn-danger btTop">' +
                    '<i class="fa fa-refresh"></i> <span> Refresh</span>' +
                    '</button>' +
                    '<button data-aksi="add" style="margin: 5px 5px 0 0" class="btn btn-success btTop">' +
                    '<i class="fa fa-plus"></i> <span> Tambah Data</span>' +
                    '</button>' +
                    '<button data-aksi="pdf" style="margin: 5px 5px 0 0" class="btn btn-warning btTop">' +
                    '<i class="fa fa-file-pdf-o"></i> <span> PDF</span>' +
                    '</button>'+
                    /!*'<button data-aksi="excel" style="margin: 0 5px 0 0" class="btn btn-app btTop">' +
                     '<i class="fa fa-file-excel-o"></i> <span> Excel</span>' +
                     '</button>'+*!/
                    '<button data-aksi="print" style="margin: 5px 5px 0 0" class="btn btn-primary btTop">' +
                    '<i class="fa fa-print"></i> <span> Print</span>' +
                    '</button>'
                ;
                $('#datatable_fixed_column_pop_filter').append(button);
                $('.btTop').click(function () {
                    aksi = $(this).data('aksi');
                    if(aksi === 'add'){
                    }else if(aksi === 'refresh'){
                        MyApp.$me.selectorTable2.ajax.reload();
                    }else if(aksi === 'pdf'){
                        // export_pdf();
                    }else if(aksi === 'print'){
                        // print_hal();
                    }else if(aksi === 'excel'){
                        // export_excel();
                    }
                });
                selectortable =  $('table');
                /!*update data*!/
                selectortable.on('click','.btUpdate',function () {
                    MyApp.$me.aksi = 'update';
                    $('#myModal').modal('show');
                    index = $(this).data('index');
                    dataRow = MyApp.$me.dataRow[index];
                    MyApp.setFormValues(idForm,dataRow);
                });

                /!*Hapus data*!/
                selectortable.on('click','.btHapus',function () {
                    index = $(this).data('index');
                    dataRow = MyApp.$me.dataRow[index];
                    konfirmasi = confirm('Apakah anda yakin ingin menghapus data '+dataRow.id);
                    if(konfirmasi){
                        hapus(dataRow);
                    }
                });
            }
        });
    };*/
    // loadTabelData();



    var loadTabelSimbol = function () {
        var paramsKode = {
            option: "ACTION",
            action: "listSimbol",
            kode_indikator: dataModule.kode_indikator
        };
        MyApp.ajax(paramsKode).done(function (resp) {
            dataSimbolRumus = resp.result;
            bodisimbol.html('');
            $.each(resp.result, function (index, val) {
                bodisimbol.append("" +
                    "<tr>" +
                    "<td><button class='btn btn-danger btHapus' data-id='"+val.id+"'><i class='fa fa-trash-o'></i></button></td>" +
                    "<td>" + val.simbol_temp + "</td>" +
                    "<td>" + val.sumberdata + "</td>" +
                    "<td>" + val.simbol_sumber_data + "</td>" +
                    "<td>" +
                    "<input type='number' value='"+val.tahun+"' class='numberTahun"+val.simbol_temp+"' readonly  style='width: 50px;padding: 2px;'><br> " +

                    "<a title='Edit' href='#' data-classcancel='cancel"+val.simbol_temp+"' data-classsimpan='simpanTahun"+val.simbol_temp+"' data-class='numberTahun"+val.simbol_temp+"' class='editTahun'>" +
                    "<i class='fa fa-edit'></i></a> " +

                    "<a title='Simpan' href='#' style='color: #739e73' data-class='numberTahun"+val.simbol_temp+"' " +
                    "data-classcancel='cancel"+val.simbol_temp+"' data-id='"+val.id+"' class='btSimpanTahun simpanTahun"+val.simbol_temp+"'>" +
                    "<i class='fa fa-save'></i></a>" +

                    "<a title='Batal' href='#' style='color: red' data-class='numberTahun"+val.simbol_temp+"' " +
                    "data-classcancel='cancel"+val.simbol_temp+"' data-classsimpan='simpanTahun"+val.simbol_temp+"' " +
                    "class='btCancel cancel"+val.simbol_temp+"'> " +
                    "<strong>X</strong></a>" +

                    "</td>" +
                    "</tr>");
                $('.simpanTahun'+val.simbol_temp).hide();
                $('.cancel'+val.simbol_temp).hide();
            });
        });
    };
    loadTabelSimbol();

    /*edit tahun pada tabel simbol
    * pada variabel sumber data bisa diambil tahun saat ini atau tahun kemaren dengan menambahkan penjumlah atau pengurang tahun
    * @contributor arkan
    * */
    $('.tabelsimbol').on('click','.editTahun',function () {
        classInput =  $('.'+$(this).data('class'));
        classInput.prop('readonly',false);
        classInput.focus();
        classSave = $('.'+$(this).data('classsimpan'));
        classSave.show();
        classCancel = $('.'+$(this).data('classcancel'));
        classCancel.show();
    })

    /*on click simpan data tahun*/
    $('.tabelsimbol').on('click','.btSimpanTahun',function () {
        classInput =  $('.'+$(this).data('class'));
        classInput.prop('readonly',true);
        classCancel = $('.'+$(this).data('classcancel'));
        classCancel.hide();
        $(this).hide();
        id = $(this).data('id');
        paramsku = {
            option : "PUBLIC",
            action : "updateTahunRelatif",
            id : id,
            tahun:classInput.val()
        }
        MyApp.ajax(paramsku).done(function (resp) {

        })
    })

    /*on click cancel data tahun*/
    $('.tabelsimbol').on('click','.btCancel',function () {
        classInput =  $('.'+$(this).data('class'));
        classInput.prop('readonly',true);
        classSave = $('.'+$(this).data('classsimpan'));
        classSave.hide();
        $(this).hide();
    })

    /*render tampilan equation*/
    if (dataModule.json_rumus) {
        equation = eqEd.Equation.constructFromJsonObj(JSON.parse(dataModule.json_rumus));
    } else {
        // jsonRumus = '{"type":"Equation","value":null,"operands":{"topLevelContainer":[{"type":"Symbol","value":"x","operands":null},{"type":"Symbol","value":"x","operands":null},{"type":"Symbol","value":"x","operands":null}]}}';
        jsonRumus = '{"type":"Equation","value":null,"operands":{"topLevelContainer":[{"type":"StackedFraction","value":null,"operands":{"numerator":[],"denominator":[]}}]}}';
        equation = eqEd.Equation.constructFromJsonObj(JSON.parse(jsonRumus));
    }
    $('#renderedEq').empty();
    $('#renderedEq').append(equation.domObj.value);
    equation.updateAll();


    /*load data indikator*/
    var paramSumberData = {
        option: "ACTION",
        action: "sumberList"
    };
    MyApp.ajax(paramSumberData).done(function (resp) {
        cbSumberData.html("<option value=''>-pilih Sumber Data-</option>");
        $.each(resp.result, function (index, val) {
            cbSumberData.append("<option value='" + val.kode_sumberdata + "'>" + val.kode_sumberdata + " - " + val.sumberdata + "</option>");
        })
        cbSumberData.select2().select2('val', '');
    });

    function nextChar(c) {
        return String.fromCharCode(c.charCodeAt(0) + 1);
    }

    var dataSelected = [];
    var indexSelected = 0;
    var tempSimbol = "a";
    var simbolArr = [];
    btTambahkan.click(function () {
        dataSelected[indexSelected] = {};
        dataSelected[indexSelected].simbol = cbSumberData.val();
        dataSelected[indexSelected].tempSimbol = tempSimbol;
        dataSelected[indexSelected].kode_indikator = kode_indikator;
        dataSelected[indexSelected].text = $('.cbSumberData option:selected').text();
        simbolArr[tempSimbol] = cbSumberData.val();
        tempSimbol = nextChar(tempSimbol);
        // bodisimbol.html('');
        params = {
            option: "ACTION",
            action: "addSbData",
            data: dataSelected[indexSelected]
        };
        // debugger;
        MyApp.ajax(params).done(function (resp) {
            $.each(resp.result, function (index, val) {
                bodisimbol.append(
                    "<tr>" +
                    "<td><button class='btn btn-danger btHapus' data-id='"+val.id+"'><i class='fa fa-trash-o'></i></button></td>" +
                    "<td>" + val.simbol_temp + "</td>" +
                    "<td>" + val.sumberdata + "</td>" +
                    "<td>" + val.simbol_sumber_data + "</td>" +
                    "<td>" +
                    "<input type='number' value='0' class='numberTahun"+val.simbol_temp+"'  style='width: 50px;padding: 2px;'> <br>" +

                    "<a title='Edit' href='#' data-classcancel='cancel"+val.simbol_temp+"' data-classsimpan='simpanTahun"+val.simbol_temp+"' data-class='numberTahun"+val.simbol_temp+"' class='editTahun'>" +
                    "<i class='fa fa-edit'></i></a> " +

                    "<a title='Simpan' href='#' style='color: #739e73' data-class='numberTahun"+val.simbol_temp+"' " +
                    "data-classcancel='cancel"+val.simbol_temp+"' data-id='"+val.id+"' class='btSimpanTahun simpanTahun"+val.simbol_temp+"'>" +
                    "<i class='fa fa-save'></i></a>" +

                    "<a title='Batal' href='#' style='color: red' data-class='numberTahun"+val.simbol_temp+"' " +
                    "data-classcancel='cancel"+val.simbol_temp+"' data-classsimpan='simpanTahun"+val.simbol_temp+"' " +
                    "class='btCancel cancel"+val.simbol_temp+"'> " +
                    "<strong>X</strong></a>" +

                    "</td>"+
                    "</tr>");
                $('.numberTahun'+val.simbol_temp).focus();
            });
        });

        indexSelected++;
    });

    var proses = function (type, operands) {
        var hasilReturn = '';
        switch (type) {
            case 'StackedFraction' :
                /*numerator adalah pembilang, dicek dulu tipenya*/
                var numeratorArr = operands.numerator;
                var numerator = '';
                $.each(numeratorArr, function (index, val) {
                    if (val.type == 'Symbol') {
                        if (simbolArr[val.value]) {
                            numerator += simbolArr[val.value];
                        } else {
                            numerator += val.value;
                        }
                    } else {
                        numerator += proses(val.type, val.operands)
                    }
                });

                /*denominator adalah penyebut diproses di cek tipenya */
                var denominatorArr = operands.denominator;
                var denominator = '';
                $.each(denominatorArr, function (index, val) {
                    if (val.type == 'Symbol') {
                        if (simbolArr[val.value]) {
                            denominator += simbolArr[val.value];
                        } else {
                            denominator += val.value;
                        }
                    } else {
                        denominator += "(" + proses(val.type, val.operands);
                    }
                    if (denominatorArr.length == index + 1) {
                        // denominator+=")";
                    }
                });
                hasilReturn += "(" + numerator + ")/(" + denominator + ')';
                // debugger;
                // hasil = "parseFloat("+numerator+")/parseFloat("+denominator+")";
                break;

            case 'BigOperator' :
                // return "sum";
                break;

            case 'SquareRoot' :
                // var value = '';
                $.each(operands.radicand, function (index, val) {
                    if (val.type == 'Symbol') {
                        if (simbolArr[val.value]) {
                            hasilReturn += simbolArr[val.value];
                        } else {
                            hasilReturn += val.value;
                        }
                    } else {
                        hasilReturn += "(" + proses(val.type, val.operands);
                    }
                });
                hasilReturn = "sqrt("+hasilReturn+")";
                break;
        }
        return hasilReturn;
    }

    MyApp.$me.jsonString='';
    $('.generate').click(function () {
        var jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
        MyApp.$me.jsonString = JSON.stringify(jsonObj);
        renderRumusTeks(MyApp.$me.jsonString);
    })

    var renderRumusTeks = function () {
        var paramsKode = {
            option: "ACTION",
            action: "listSimbol",
            kode_indikator: dataModule.kode_indikator
        };
        MyApp.ajax(paramsKode).done(function (resp) {
            if(MyApp.$me.jsonString ==''){
                var jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
                var stringJson = JSON.stringify(jsonObj);
            }
            $.each(resp.result,function (index, val) {
                toReplace = '{"type":"Symbol","value":"'+val.simbol_temp+'","operands":null}';
                stringData = val.sumberdata.split(' ').join('-');
                newJson = [];
                var i = stringData.length;
                idx=0;
                while (i--) {
                    newJson.push('{"type":"Symbol","value":"'+stringData.charAt(idx)+'","operands":null,"edited":null}');
                    idx++;
                }
                MyApp.$me.jsonString = MyApp.$me.jsonString.replace(toReplace,newJson.join())
            })
            equation = eqEd.Equation.constructFromJsonObj(JSON.parse(MyApp.$me.jsonString));
            $('#renderedEq2').empty();
            $('#renderedEq2').append(equation.domObj.value);
            equation.updateAll();
        })
    }

    $('.btRender').click(function () {
        var jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
        var stringJson = JSON.stringify(jsonObj);
        MyApp.$me.jsonString = stringJson;
        var hasilRender = '';
        $.each(jsonObj.operands.topLevelContainer, function (index, val) {
            // debugger;
            if (val.type == 'Symbol') {
                hasilRender += val.value;
            } else {
                hasilRender += "(" + proses(val.type, val.operands) + ")";
            }
        });

        renderRumusTeks();

        html2canvas($('#renderedEq'), {
            onrendered: function(canvas) {
                var theCanvas = canvas.toDataURL();

                html2canvas($('#renderedEq2'), {
                    onrendered: function(canvas) {
                        var theCanvas2 = canvas.toDataURL();
                        params = {
                            option: "ACTION",
                            method:"POST",
                            url:"service.php?option=PUBLIC&action=updateIndikator&tabel=operasi_math&Module=HitungRumus",
                            action: "updateIndikator",
                            tabel: 'operasi_math',
                            data: {
                                rumus: hasilRender,
                                stringJson: stringJson,
                                kode_indikator: kode_indikator,
                                operasi_math: "operasi",
                                canvas:theCanvas,
                                canvas2:theCanvas2
                            }
                        }
                        $.ajax(params).done(function (resp) {
                            MyApp.$me.jsonString='';
                            resp = JSON.parse(resp);
                            alert(resp.msg);
                        })
                    }
                });
            }
        });


        /*MyApp.ajax(params).done(function (resp) {
            alert(resp.msg);
            /!*koding disini setelah sukses*!/
        });*/
    });

    /*render equation*/
    if (!imagesLoaded) {
        loadFonts(setup);
        loadImages([], setup);
    }

    /*hitung oprerasi*/
    hitung = function (data) {
        data = JSON.parse(data);
        tipe = data.operands.topLevelContainer[0].type;
        if (tipe == 'StackedFraction') {
            numeratorArr = data.operands.topLevelContainer[0].operands.numerator;
            numerator = '';
            $.each(numeratorArr, function (index, val) {
                numerator += val.value;
            });

            denominatorArr = data.operands.topLevelContainer[0].operands.denominator;
            denominator = '';
            $.each(denominatorArr, function (index, val) {
                denominator += val.value;
            });
            hasil = parseFloat(numerator) / parseFloat(denominator);
            alert("hasil nya adalah : " + hasil);
        }
    };
    /*END hitung operasi*/


    /*simpan form*/
    $('.btSimpan').on('click', function () {
        idForm = $('#form_input');
        dataForm = MyApp.getFormValues(idForm);
        debugger;
        params = {
            option: 'ACTION',
            action: MyApp.$me.aksi,
            data: dataForm
        };
        MyApp.ajax(params).done(function (resp) {
            if (resp.success == true) {
                $('#myModal').modal('hide');
                alert(resp.msg);
                MyApp.$me.selectorTable2.ajax.reload();
            } else {
                alert(resp.msg);
            }
        })
    });

    /*on click button btHapus pada tabel simbol*/
    $('.bodisimbol').on('click','.btHapus',function () {
        id = $(this).data('id');
        hapus(id);
    });

    /*hapus data*/
    var hapus = function (id) {
        params = {
            option: 'ACTION',
            action: 'delete',
            id: id
        };
        MyApp.ajax(params).done(function (resp) {
            if (resp.success == true) {
                alert(resp.msg);
                loadTabelSimbol();
            } else {
                alert(resp.msg);
            }
        })
    };

    // simulasi loading... hide setelah 500ms
    $('.modal-backdrop').addClass('hide');
    setTimeout(function () {
        MyApp.$me('.overlay').hide();
    }, 500);
})();

//# sourceURL=SampleModule.js
