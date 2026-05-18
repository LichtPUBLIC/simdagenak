<?php
if (!defined('PATH_TEMPLATE')) define('PATH_TEMPLATE', 'template/smartadmin/');
?>
<html lang="en-us" id="extr-page">
<head>
    <meta charset="utf-8">
    <title> SmartAdmin (AJAX)</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" type="text/css" href="https://www.highcharts.com/media/com_demo/css/highslide.css" />
    <!-- #CSS Links -->
    <!-- Basic Styles -->
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo PATH_TEMPLATE ?>css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo PATH_TEMPLATE ?>css/font-awesome.min.css">

    <!-- SmartAdmin Styles : Caution! DO NOT change the order -->
    <link rel="stylesheet" type="text/css" media="screen"
          href="<?php echo PATH_TEMPLATE ?>css/smartadmin-production.min.css">
    <link rel="stylesheet" type="text/css" media="screen"
          href="<?php echo PATH_TEMPLATE ?>css/smartadmin-skins.min.css">

    <link rel="stylesheet" type="text/css" media="screen"
          href="plugins/select2/select2.min.css">

    <!-- SmartAdmin RTL Support -->
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo PATH_TEMPLATE ?>css/smartadmin-rtl.min.css">

    <!-- We recommend you use "your_style.css" to override SmartAdmin
         specific styles this will also ensure you retrain your customization with each SmartAdmin update.
    <link rel="stylesheet" type="text/css" media="screen" href="css/your_style.css"> -->

    <!-- #FAVICONS -->
    <link rel="shortcut icon" href="<?php echo PATH_TEMPLATE ?>img/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?php echo PATH_TEMPLATE ?>img/favicon/favicon.ico" type="image/x-icon">

    <!-- #GOOGLE FONT -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">

    <!-- #APP SCREEN / ICONS -->
    <!-- Specifying a Webpage Icon for Web Clip
         Ref: https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html -->
    <link rel="apple-touch-icon" href="<?php echo PATH_TEMPLATE ?>img/splash/sptouch-icon-iphone.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo PATH_TEMPLATE ?>img/splash/touch-icon-ipad.png">
    <link rel="apple-touch-icon" sizes="120x120"
          href="<?php echo PATH_TEMPLATE ?>img/splash/touch-icon-iphone-retina.png">
    <link rel="apple-touch-icon" sizes="152x152"
          href="<?php echo PATH_TEMPLATE ?>img/splash/touch-icon-ipad-retina.png">

    <!-- iOS web-app metas : hides Safari UI Components and Changes Status Bar Appearance -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <!-- Startup image for web apps -->
    <link rel="apple-touch-startup-image" href="<?php echo PATH_TEMPLATE ?>img/splash/ipad-landscape.png"
          media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)">
    <link rel="apple-touch-startup-image" href="<?php echo PATH_TEMPLATE ?>img/splash/ipad-portrait.png"
          media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)">
    <link rel="apple-touch-startup-image" href="<?php echo PATH_TEMPLATE ?>img/splash/iphone.png"
          media="screen and (max-device-width: 320px)">


    <style>
        th {
            text-align: center;
        }
    </style>
</head>
<body id="login">
<!-- possible classes: minified, no-right-panel, fixed-ribbon, fixed-header, fixed-width-->
<!--<header id="header">
    <div id="logo-group">
        <span id="logo"> <img src="<?php /*echo PATH_TEMPLATE */ ?>img/logo.png" alt="SmartAdmin"> </span>
    </div>
    <span id="extr-page-header-space">
        <a href="index.php" class="btn btn-danger">Login</a>
    </span>
</header>-->
<header id="header">
    <div style="width: 100%;margin-top: 10px;">
				<span id="logo2">
                    <h1 class="txt-color-red login-header-big"> <?php echo APP_TITLE; ?></h1>
                </span>
    </div>
</header>
<div id="main" role="main">

    <!-- MAIN CONTENT -->
    <div id="content" class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="well no-padding">
                    <form action="" method="POST" id="smart-form-filter" class="smart-form client-form">
                        <input type="hidden" name="show-data" value="1">
                        <header>
                            Data Publik
                        </header>
                        <fieldset style="padding-top: 0px">
                            <section style="color: #e8233a;font-weight: bold;font-style: italic;margin-left: 15px">
                                <ul>
                                    <?= @$msg ?>
                                </ul>
                            </section>
                            <section>
                                <label style="margin: 5px;" class="input col-md-3">
                                    <select style="width: 100%" name="instansi" id="" class="cbInstansi">
                                        <option value="">Pilih Instansi</option>
                                        <option value="Dinas Sosial">Dinas Sosial</option>
                                        <option value="Dinas Tenaga Kerja">Dinas Tenaga Kerja</option>
                                        <option value="Dinas Pendidikan">Dinas Pendidikan</option>
                                        <option value="Bidang KB">Bidang KB</option>
                                        <option value="Kemenag">Kemenag</option>
                                        <option value="Tapem">Tapem</option>
                                        <option value="Dukcapil">Dukcapil</option>
                                        <option value="Dinas Kesehatan">Dinas Kesegatan</option>
                                        <option value="BPBD">BPBD</option>
                                        <option value="Koperasi">Koperasi</option>
                                        <option value=""></option>
                                    </select>
                                    <!--<input type="text" maxlength="20" value="<? /*=@$username */ ?>" name="username" placeholder="Instansi">
                                    <b class="tooltip tooltip-bottom-right">Username wajib diisi</b> -->
                                </label>
                            </section>
                            <section>
                                <label style="margin: 5px" class="input col-md-3">
                                    <select style="width: 100%" name="kode_data_pilah" id="" class="cbJenisdata">
                                        <option value="">-- Pilih data --</option>
                                    </select>
                                    <!--<input type="text" value="<? /*=@$nama */ ?>"  name="nama" placeholder="Data">
                                    <b class="tooltip tooltip-bottom-right">Nama wajib diisi</b> -->
                                </label>
                            </section>
                            <section>
                                <label style="margin: 5px" class="input col-md-3">
                                    <select style="width: 100%" name="tahun" id="" class="cbTahun">
                                        <option value="2016">2016</option>
                                        <option value="2017">2017</option>
                                        <option value="2018">2018</option>
                                    </select>
                                    <!--<input type="text" name="password" placeholder="Tahun">
                                    <b class="tooltip tooltip-bottom-right">Password wajib diisi</b>-->
                                </label>
                            </section>
                            <section>
                                <label style="margin: 5px" class="input col-md-2">
                                    <button type="submit" class="btn btn-danger form-control">
                                        Tampilkan
                                    </button>
                                </label>
                            </section>
                        </fieldset>
                        <div class="message">
                            <i class="fa fa-check"></i>
                            <p>
                                <?= @$msg ?>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12 ">
                <div class="col-md-6 renderedFormat" id="myModalFormatForm">
                    <h3 class="judul"></h3>
                    <table id='dataTables' class='table-bordered table'>
                    </table>
                </div>
            </div>
        </div>

        <hr>
        <div class="col-md-12 pad">
            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
        </div>
    </div>
</div>

<!-- Link to Google CDN's jQuery + jQueryUI; fall back to local -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script> if (!window.jQuery) {
        document.write('<script src="js/libs/jquery-2.1.1.min.js"><\/script>');
    } </script>

<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script> if (!window.jQuery.ui) {
        document.write('<script src="js/libs/jquery-ui-1.10.3.min.js"><\/script>');
    } </script>

<!-- IMPORTANT: APP CONFIG -->
<!--<script src="--><?php //echo PATH_TEMPLATE ?><!--js/app.config.seed.js"></script>-->

<!-- JS TOUCH : include this plugin for mobile drag / drop touch events
<script src="js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script> -->

<!-- JQUERY SELECT2 INPUT -->
<script src="plugins/select2/select2.min.js"></script>

<!-- BOOTSTRAP JS -->
<script src="<?php echo PATH_TEMPLATE ?>js/bootstrap/bootstrap.min.js"></script>

<!--<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>-->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/series-label.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>

<!-- Additional files for the Highslide popup effect -->
<script src="https://www.highcharts.com/media/com_demo/js/highslide-full.min.js"></script>
<script src="https://www.highcharts.com/media/com_demo/js/highslide.config.js" charset="utf-8"></script>


<script src="public.js"></script>

<!--[if IE 8]>

<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

<![endif]-->

<!-- MAIN APP JS FILE -->
<!--<script src="--><?php //echo PATH_TEMPLATE ?><!--js/app.seed.js"></script>-->


</body>
</html>

