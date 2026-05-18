<?php
include_once '../public-service.php';
?>

<!doctype html>
<html lang="id" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="SIMDAGENAK - Sistem Informasi Data Gender dan Anak Kabupaten Sleman">
    <meta name="author" content="SIMDAGENAK">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="./assets/images/brand/favicon.ico" />

    <!-- TITLE -->
    <title>SIMDAGENAK | Sistem Informasi Data Gender dan Anak</title>

    <!-- BOOTSTRAP CSS -->
    <link href="./assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="./assets/css/style.css" rel="stylesheet" />
    <link href="./assets/css/skin-modes.css" rel="stylesheet" />

    <!-- SIDE-MENU CSS -->
    <link href="./assets/plugins/sidemenu/sidemenu.css" rel="stylesheet">

    <!-- C3 CHARTS CSS -->
    <link href="./assets/plugins/charts-c3/c3-chart.css" rel="stylesheet" />

    <!-- CUSTOM SCROLL BAR CSS-->
    <link href="./assets/plugins/scroll-bar/jquery.mCustomScrollbar.css" rel="stylesheet" />

    <!-- SELECT2 CSS -->
    <link href="./assets/plugins/select2/select2.min.css" rel="stylesheet" />

    <!-- TABS STYLES -->
    <link href="./assets/plugins/tabs/tabs.css" rel="stylesheet" />

    <!-- FONT-ICONS CSS -->
    <link href="./assets/css/icons.css" rel="stylesheet" />

    <!-- SIDEBAR CSS -->
    <link href="./assets/plugins/sidebar/sidebar.css" rel="stylesheet">

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="./assets/colors/color1.css" />

    <!-- DATA TABLE CSS -->
    <link href="./assets/plugins/datatable/dataTables.bootstrap4.min.css" rel="stylesheet" />

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&display=swap"
        rel="stylesheet">

    <style>
        /* ============================================
           IMPECCABLE DESIGN TOKENS (Government Theme)
           Palette: Deep Teal + Warm Gold
           ============================================ */
        :root {
            --gov-primary: #1a5c5a;
            --gov-primary-deep: #0f3d3b;
            --gov-primary-light: #267a77;
            --gov-accent: #c8973e;
            --gov-accent-light: #dbb469;
            --gov-accent-pale: #f5eed9;
            --gov-surface: #f6f8f8;
            --gov-surface-raised: #ffffff;
            --gov-text: #1c2e2d;
            --gov-text-muted: #5a7574;
            --gov-border: #d8e2e1;
            --gov-success: #2d8659;
            --gov-info: #2874a6;
            --gov-danger: #c0392b;
            --ease-out-quart: cubic-bezier(0.25, 1, 0.5, 1);
            --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
        }

        th {
            vertical-align: middle !important;
            text-align: center !important;
        }

        /* ---- FILTER CARD (Matrix View) ---- */
        .filter-card {
            background: linear-gradient(135deg, var(--gov-primary) 0%, var(--gov-primary-deep) 100%);
            border: none;
            border-radius: 12px;
        }

        .filter-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .filter-card .card-header h3 {
            color: #fff;
        }

        .filter-card label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
        }

        .chart-card {
            border-radius: 10px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--gov-border);
        }

        .chart-card .card-header {
            background: var(--gov-surface);
            border-bottom: 1px solid var(--gov-border);
        }

        .btn-cari {
            background: var(--gov-accent);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 8px 30px;
            border-radius: 6px;
            transition: all 0.35s var(--ease-out-quart);
        }

        .btn-cari:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(200, 151, 62, 0.4);
            color: #fff;
            background: var(--gov-accent-light);
        }

        /* ---- PREMIUM TABLE AESTHETICS ---- */
        .tab_wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--gov-border);
            background: #fff;
        }

        #dataTables {
            font-size: 13px;
            font-variant-numeric: tabular-nums;
            border-collapse: collapse !important;
            width: 100% !important;
            margin: 0 !important;
        }

        /* Ensure uniform borders, compact padding, and perfect vertical alignment for header cells */
        #dataTables thead th {
            border: 1px solid #dee2e6 !important;
            padding: 6px 8px !important;
            vertical-align: middle !important;
            text-align: center !important;
            font-weight: 700 !important;
        }

        #dataTables tbody td {
            border: 1px solid #dee2e6 !important;
            padding: 5px 8px !important;
        }

        /* Body Row Styles - Alternating Colors Applied Directly to td */
        #dataTables tbody tr:nth-of-type(odd) td {
            background-color: #ffffff !important;
        }

        /* Zebra Striping with pleasant soft teal tint (comfortable for eyes) */
        #dataTables tbody tr:nth-of-type(even) td {
            background-color: rgba(26, 92, 90, 0.045) !important;
        }

        #dataTables tbody td {
            color: var(--gov-text) !important;
            vertical-align: middle !important;
            text-align: center !important;
            transition: all 0.2s ease;
        }

        /* Left-align Kecamatan Column text for standard readability */
        #dataTables tbody tr td:nth-child(2) {
            text-align: left !important;
        }

        /* Center No Column */
        #dataTables tbody tr td:first-child {
            text-align: center !important;
        }

        /* Smooth, subtle hover effect on rows */
        #dataTables tbody tr:hover td {
            background-color: rgba(26, 92, 90, 0.08) !important;
        }

        .judul-data {
            font-size: 18px;
            font-weight: 800;
            color: var(--gov-primary-deep);
            letter-spacing: -0.3px;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        /* Subtle accent line below the table title */
        .judul-data::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--gov-accent);
            margin: 8px auto 0 auto;
            border-radius: 2px;
        }

        /* ============================================
           VIEW SWITCHING
           ============================================ */
        .view-section {
            display: none;
        }

        .view-section.active {
            display: block;
            animation: impFadeIn 0.6s var(--ease-out-expo) both;
        }

        @keyframes impFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================
           HERO SECTION — Government Identity
           ============================================ */
        .hero-banner {
            background: var(--gov-primary-deep);
            border-radius: 16px;
            padding: 0;
            color: #fff;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: stretch;
            min-height: 340px;
        }

        /* Batik-inspired subtle pattern overlay */
        .hero-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(200, 151, 62, 0.06) 0%, transparent 40%),
                repeating-linear-gradient(45deg,
                    transparent,
                    transparent 30px,
                    rgba(255, 255, 255, 0.015) 30px,
                    rgba(255, 255, 255, 0.015) 31px);
            pointer-events: none;
        }

        .hero-content {
            flex: 1;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .hero-eyebrow {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--gov-accent-light);
            margin-bottom: 12px;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.75rem, 2.5vw + 0.5rem, 2.5rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            text-wrap: balance;
        }

        .hero-subtitle {
            font-size: 1rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 32px;
            max-width: 560px;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gov-accent);
            color: var(--gov-primary-deep);
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.4s var(--ease-out-quart);
            align-self: flex-start;
        }

        .hero-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(200, 151, 62, 0.35);
            color: var(--gov-primary-deep);
            background: var(--gov-accent-light);
            text-decoration: none;
        }

        .hero-visual {
            width: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-shrink: 0;
        }

        .hero-visual img {
            width: 180px;
            height: auto;
            opacity: 0.15;
            filter: brightness(2) grayscale(0.3);
        }

        @media (max-width: 991px) {
            .hero-banner {
                min-height: 280px;
            }

            .hero-content {
                padding: 40px 32px;
            }

            .hero-visual {
                width: 200px;
            }

            .hero-visual img {
                width: 140px;
            }
        }

        @media (max-width: 768px) {
            .hero-banner {
                flex-direction: column;
                min-height: auto;
            }

            .hero-visual {
                display: none;
            }

            .hero-content {
                padding: 32px 24px;
            }
        }

        /* Hide breadcrumb on landing dashboard to save space */
        body.no-sidebar .page-header {
            display: none !important;
        }

        /* ============================================
           STATS — Surface tints, no border-stripes
           ============================================ */
        .stats-row {
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--gov-surface-raised);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.35s var(--ease-out-quart);
            border: 1px solid var(--gov-border);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        }

        .stat-card-inner {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-icon-wrap.teal {
            background: rgba(26, 92, 90, 0.1);
            color: var(--gov-primary);
        }

        .stat-icon-wrap.gold {
            background: rgba(200, 151, 62, 0.12);
            color: var(--gov-accent);
        }

        .stat-icon-wrap.green {
            background: rgba(45, 134, 89, 0.1);
            color: var(--gov-success);
        }

        .stat-icon-wrap.blue {
            background: rgba(40, 116, 166, 0.1);
            color: var(--gov-info);
        }

        .stat-info {}

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gov-text);
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--gov-text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }

        /* ============================================
           FEATURES — Numbered list, varied spacing
           ============================================ */
        .features-section {
            margin-bottom: 40px;
        }

        .features-heading {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gov-text);
            margin-bottom: 6px;
            text-align: center;
            text-wrap: balance;
        }

        .features-subheading {
            color: var(--gov-text-muted);
            font-size: 0.95rem;
            margin-bottom: 32px;
            text-align: center;
        }

        .feature-card {
            background: var(--gov-surface-raised);
            border: 1px solid var(--gov-border);
            border-radius: 12px;
            padding: 28px 24px;
            height: 100%;
            transition: all 0.35s var(--ease-out-quart);
            position: relative;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            border-color: var(--gov-primary-light);
        }

        .feature-card .feature-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--gov-accent);
            opacity: 0.25;
            line-height: 1;
            margin-bottom: 16px;
        }

        .feature-card h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gov-text);
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 0.875rem;
            color: var(--gov-text-muted);
            margin: 0;
            line-height: 1.6;
        }

        /* ============================================
           SIDEBAR / NO-SIDEBAR TOGGLE
           ============================================ */
        body.no-sidebar .app-sidebar {
            display: none;
        }

        body.no-sidebar .app-sidebar__toggle {
            display: none !important;
        }

        body.no-sidebar .app-content {
            margin-left: 0 !important;
        }

        body.no-sidebar .app-header {
            margin-left: 0 !important;
            padding-left: 0 !important;
            left: 0 !important;
            width: 100% !important;
        }

        body.no-sidebar .footer {
            margin-left: 0 !important;
            padding-left: 0 !important;
            left: 0 !important;
            width: 100% !important;
        }

        @media (max-width: 991px) {
            body.no-sidebar .app-content {
                padding-top: 20px !important;
            }
        }

        /* ============================================
           BRANDING — Logo + collapsible text
           ============================================ */
        .page-main {
            background: var(--gov-surface);
        }

        .header-brand-img {
            height: 36px;
            width: auto;
            margin-right: 10px;
        }

        .header-brand-text {
            font-size: 18px;
            font-weight: 800;
            color: var(--gov-primary-deep);
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 0;
        }

        .header-brand-text .brand-label {
            transition: opacity 0.3s var(--ease-out-quart), width 0.3s var(--ease-out-quart);
            overflow: hidden;
            white-space: nowrap;
        }

        .header-brand-text .brand-accent {
            color: var(--gov-accent);
        }

        /* Hide brand text when sidebar is toggled */
        .sidebar-mini.sidenav-toggled .brand-label {
            opacity: 0;
            width: 0 !important;
            display: none;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            background: var(--gov-primary-deep);
            color: rgba(255, 255, 255, 0.7);
            border-top: 3px solid var(--gov-accent);
        }

        .footer a {
            color: var(--gov-accent-light);
        }

        .footer a:hover {
            color: #fff;
            text-decoration: none;
        }

        /* ============================================
           PRINT STYLES
           ============================================ */
        @media print {
            body {
                background: #fff !important;
            }

            .app-header,
            .mobile-header,
            .app-sidebar,
            .app-sidebar__overlay,
            .page-header,
            #viewDashboard,
            .filter-card,
            .card-options,
            .footer,
            #back-to-top,
            #global-loader,
            #btnBackToHome,
            .hero-banner,
            .stat-card,
            .feature-num-item,
            .no-print {
                display: none !important;
            }

            .app-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .page-main {
                background: #fff !important;
                background-image: none !important;
            }

            .page,
            .side-app {
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-header {
                background: none !important;
                border: none !important;
            }

            .card-body {
                padding: 0 !important;
            }

            #rowTabel,
            #rowCharts {
                display: block !important;
            }

            #dataTables {
                font-size: 11px;
            }

            #dataTables th,
            #dataTables td {
                border: 1px solid #000 !important;
                padding: 4px 8px !important;
                color: #000 !important;
                background-color: #fff !important;
            }

            #dataTables th {
                font-weight: bold !important;
            }

            #dataTables tbody tr td {
                background-color: #fff !important;
                color: #000 !important;
            }

            .judul-data {
                font-size: 18px !important;
                text-align: center;
                margin-bottom: 15px;
                color: #000 !important;
                font-weight: bold !important;
            }

            .judul-data::after {
                display: none !important;
            }

            .judul-header {
                font-size: 16px !important;
                text-align: center;
                color: #000 !important;
            }

            .chart-card {
                page-break-inside: avoid;
            }

            #chartBar,
            #chartPie {
                max-width: 100%;
            }
        }
    </style>

</head>

<body class="app sidebar-mini Left-menu-Default Sidemenu-left-icons no-sidebar">

    <!-- GLOBAL-LOADER -->
    <div id="global-loader">
        <img src="./assets/images/loader.svg" class="loader-img" alt="Loader">
    </div>
    <!-- /GLOBAL-LOADER -->

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!--APP-SIDEBAR-->
            <div class="app-header header-search-icon">
                <div class="header-style1">
                    <a class="header-brand" href="index.php" style="text-decoration:none; padding-top: 15px;">
                        <div class="header-brand-text">
                            <img src="../img/logo.png" alt="Kabupaten Sleman" class="header-brand-img">
                            <span class="brand-label">SIMDA<span class="brand-accent">GENAK</span></span>
                        </div>
                    </a><!-- LOGO -->
                </div>
                <div class="app-sidebar__toggle" data-toggle="sidebar">
                    <a class="open-toggle" href="#"><i class="fe fe-align-left"></i></a>
                    <a class="close-toggle" href="#"><i class="fe fe-x"></i></a>
                </div>
                <div class="d-flex  ml-auto header-right-icons">
                    <div class="dropdown d-md-flex">
                        <a class="nav-link icon full-screen-link nav-link-bg">
                            <i class="fe fe-minimize fullscreen-button"></i>
                        </a>
                    </div><!-- FULL-SCREEN -->
                </div>
            </div>
            <!--APP-SIDEBAR-->

            <!--APP-SIDEBAR-->
            <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
            <aside class="app-sidebar">

                <ul class="side-menu menu-utama">
                    <li>
                        <h3>Data Pilah</h3>
                    </li>
                    <?php
                    foreach ($menu as $idx => $val) {
                        ?>
                        <li class="slide">
                            <a class="side-menu__item" data-toggle="slide" href="#">
                                <i class="angle fe fe-chevron-right"></i><span class="side-menu__label"><?= $idx ?></span><i
                                    class="side-menu__icon fe fe-airplay"></i>
                            </a>
                            <ul class="slide-menu">
                                <?php
                                foreach ($val as $i) {
                                    ?>
                                    <li>
                                        <a href="#" class="slide-item menudatapilah"
                                            data-kodedatapilah="<?= $i['kode_data_pilah'] ?>"
                                            data-judul="<?= $i['judul_data_pilah'] ?>">
                                            <i class="sidemenu-icon fe fe-chevrons-right"></i>
                                            <?= $i['judul_data_pilah'] ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>

            </aside>
            <!--/APP-SIDEBAR-->

            <!-- Mobile Header -->
            <div class="mobile-header">
                <div class="container-fluid">
                    <div class="d-flex">
                        <div class="app-sidebar__toggle" data-toggle="sidebar">
                            <a class="open-toggle" href="#"><i class="fe fe-align-left"></i></a>
                            <a class="close-toggle" href="#"><i class="fe fe-x"></i></a>
                        </div>
                        <a class="header-brand" href="index.php" style="text-decoration:none;">
                            <div class="header-brand-text">
                                <img src="../img/logo.png" alt="Kabupaten Sleman" class="header-brand-img">
                                <span class="brand-label">SIMDA<span class="brand-accent">GENAK</span></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Mobile Header -->

            <!--app-content open-->
            <div class="app-content">
                <div class="side-app">

                    <!-- PAGE-HEADER -->
                    <div class="page-header">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Visualisasi Data</li>
                        </ol>
                    </div>
                    <!-- PAGE-HEADER END -->

                    <!-- ============================================ -->
                    <!-- VIEW 1: LANDING DASHBOARD (DITAMPILKAN AWAL) -->
                    <!-- ============================================ -->
                    <div id="viewDashboard" class="view-section active">
                        <!-- HERO: Two-column with Sleman watermark -->
                        <div class="hero-banner">
                            <div class="hero-content">
                                <div class="hero-eyebrow">Kabupaten Sleman</div>
                                <h1 class="hero-title">Sistem Informasi Data Gender dan Anak</h1>
                                <p class="hero-subtitle">
                                    Platform digital resmi untuk mengintegrasikan, mengelola, dan menyajikan data pilah
                                    gender dan anak dari seluruh OPD Kabupaten Sleman secara transparan dan akuntabel.
                                </p>
                                <button class="hero-cta" id="btnExplore">
                                    <i class="fe fe-layers"></i> Jelajahi Data Matriks
                                </button>
                            </div>
                            <div class="hero-visual">
                                <img src="../img/logo.png" alt="Logo Kabupaten Sleman">
                            </div>
                        </div>

                        <!-- STATS: Surface tints, inline icons, no border-stripes -->
                        <div class="row stats-row">
                            <div class="col-sm-6 col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-card-inner">
                                        <div class="stat-icon-wrap teal"><i class="fe fe-home"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-value">42</div>
                                            <div class="stat-label">Instansi Terhubung</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-card-inner">
                                        <div class="stat-icon-wrap gold"><i class="fe fe-grid"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-value">1.248</div>
                                            <div class="stat-label">Total Matriks Data</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-card-inner">
                                        <div class="stat-icon-wrap green"><i class="fe fe-check-square"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-value">100%</div>
                                            <div class="stat-label">Data Terverifikasi</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-card-inner">
                                        <div class="stat-icon-wrap blue"><i class="fe fe-calendar"></i></div>
                                        <div class="stat-info">
                                            <div class="stat-value">2026</div>
                                            <div class="stat-label">Tahun Aktif</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FEATURES: Centered heading + 3-column cards -->
                        <div class="features-section">
                            <h3 class="features-heading">Mengapa Menggunakan SIMDAGENAK?</h3>
                            <p class="features-subheading">Platform terpadu untuk memantau kesejahteraan gender dan anak
                                secara real-time di Kabupaten Sleman.</p>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="feature-card">
                                        <div class="feature-number">01</div>
                                        <h4>Akses Data yang Cepat</h4>
                                        <p>Seluruh data dapat diakses dan divisualisasikan hanya dalam beberapa klik,
                                            tanpa perlu proses manual.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-card">
                                        <div class="feature-number">02</div>
                                        <h4>Sumber Data Tervalidasi</h4>
                                        <p>Data bersumber langsung dari OPD terkait yang telah melalui proses verifikasi
                                            dan validasi ketat.</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-card">
                                        <div class="feature-number">03</div>
                                        <h4>Visualisasi Interaktif</h4>
                                        <p>Grafik bar dan pie chart interaktif mempermudah analisis tren data dari tahun
                                            ke tahun.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- VIEW 2: MATRIX DATA (DISEMBUNYIKAN AWAL)     -->
                    <!-- ============================================ -->
                    <div id="viewMatrix" class="view-section">
                        <div class="mb-4">
                            <button class="btn btn-outline-primary" id="btnBackToHome">
                                <i class="fe fe-arrow-left"></i> Kembali ke Beranda
                            </button>
                        </div>

                        <!-- FILTER CARD: 3 Dropdown + Tombol Cari -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card filter-card">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fe fe-filter"></i> Filter Data</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row align-items-end">
                                            <div class="col-md-3">
                                                <label>Instansi</label>
                                                <select class="form-control cbInstansi" id="cbInstansi">
                                                    <option value="">-- Pilih Instansi --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Data / Matriks</label>
                                                <select class="form-control cbJenisdata" id="cbJenisdata">
                                                    <option value="">-- Pilih Jenis Data --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Tahun</label>
                                                <select class="form-control cbTahun" id="cbTahun">
                                                    <option value="">-- Tahun --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-cari btn-block" id="btnCari">
                                                    <i class="fe fe-search"></i> Cari Data
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- TABEL DATA                                   -->
                        <!-- ============================================ -->
                        <div class="row" id="rowTabel" style="display:none;">
                            <div class="col-md-12">
                                <div class="card chart-card">
                                    <div class="card-header">
                                        <h3 class="card-title judul-header">Data</h3>
                                        <div class="card-options">
                                            <a href="#" class="btExcel btn-sm btn btn-success button-icon mr-2">
                                                <span><i class="fe fe-file-text"></i> Excel</span>
                                            </a>
                                            <a href="#" class="btPdf btn-sm btn btn-danger button-icon mr-2">
                                                <span><i class="fe fe-file"></i> PDF</span>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-primary button-icon mr-2 btPrint">
                                                <span><i class="fe fe-printer"></i> Print</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="judul-data text-center" id="judulData"></div>
                                        <div class="panel panel-primary">
                                            <div class="tab_wrapper" style="overflow: auto">
                                                <table id="dataTables"
                                                    class="table table-striped table-bordered dataTable no-footer table-sm">
                                                    <thead class="header-kolom"></thead>
                                                    <tbody class="body-kolom"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- COL-END -->
                        </div>

                        <!-- ============================================ -->
                        <!-- GRAFIK: Bar Chart + Pie Chart                -->
                        <!-- ============================================ -->
                        <div class="row" id="rowCharts" style="display:none;">
                            <div class="col-md-7">
                                <div class="card chart-card">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fe fe-bar-chart-2"></i> Bar Chart</h3>
                                    </div>
                                    <div class="card-body p-4">
                                        <div id="chartBar" style="min-width: 310px; height: 400px; margin: 0 auto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="card chart-card">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fe fe-pie-chart"></i> Pie Chart</h3>
                                    </div>
                                    <div class="card-body p-4">
                                        <div id="chartPie" style="min-width: 280px; height: 400px; margin: 0 auto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ROW CHARTS CLOSED -->

                </div><!-- /#viewMatrix -->

            </div>
        </div>
        <!-- CONTAINER CLOSED -->
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center flex-row-reverse">
                <div class="col-md-12 col-sm-12 text-center">
                    Copyright &copy; 2026 <a href="#">SIMDAGENAK</a> &mdash; Sistem Informasi Data Gender dan Anak
                    Kabupaten Sleman.
                </div>
            </div>
        </div>
    </footer>
    <!-- FOOTER CLOSED -->
    </div>

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="./assets/js/jquery-3.4.1.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="./assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/plugins/bootstrap/js/popper.min.js"></script>

    <!-- SPARKLINE JS -->
    <script src="./assets/js/jquery.sparkline.min.js"></script>

    <!-- CHART-CIRCLE JS -->
    <script src="./assets/js/circle-progress.min.js"></script>

    <!-- SIDE-MENU JS -->
    <script src="./assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- TABS JS -->
    <script src="./assets/plugins/tabs/jquery.multipurpose_tabcontent.js"></script>
    <script src="./assets/plugins/tabs/tab-content.js"></script>

    <!-- CUSTOM SCROLL BAR JS-->
    <script src="./assets/plugins/scroll-bar/jquery.mCustomScrollbar.concat.min.js"></script>

    <!-- DATA TABLE JS-->
    <script src="./assets/plugins/datatable/jquery.dataTables.min.js"></script>
    <script src="./assets/plugins/datatable/dataTables.bootstrap4.min.js"></script>

    <!-- SIDEBAR JS -->
    <script src="./assets/plugins/sidebar/sidebar.js"></script>

    <!-- SELECT2 JS -->
    <script src="./assets/plugins/select2/select2.full.min.js"></script>

    <!-- CUSTOM JS-->
    <script src="./assets/js/custom.js"></script>

    <!-- HIGHCHARTS (local) -->
    <script src="../plugins/highcharts/js/highcharts.js"></script>
    <script src="../plugins/highcharts/js/modules/exporting.js"></script>

    <!-- PUBLIC JS -->
    <script src="../public.js?v=<?= time() ?>"></script>

    <script>
        $(document).ready(function () {
            // Toggle View Logic
            $('#btnExplore').on('click', function () {
                $('body').removeClass('no-sidebar');
                $('#viewDashboard').removeClass('active');
                setTimeout(function () {
                    $('#viewDashboard').hide();
                    $('#viewMatrix').show().addClass('active');
                    // Refresh layout if needed
                    $(window).trigger('resize');
                }, 100);
            });

            $('#btnBackToHome').on('click', function () {
                $('body').addClass('no-sidebar');
                $('#viewMatrix').removeClass('active');
                setTimeout(function () {
                    $('#viewMatrix').hide();
                    $('#viewDashboard').show().addClass('active');
                }, 100);
            });
        });
    </script>

</body>

</html>