<?php
require_once('../includeFiles/init.inc.php');
require_once('../includeFiles/db.inc.php');
//定義本頁面的代碼，用於 SideBar 的開合效果以及本頁面管理權限使用。
define('PAGE_MARK','PG_TEMP');
define('PAGE_ITEM','ITEM_TEMP');
//頁面基本功能檔案(必須放置於 PAGE_MARK 定義之後)
define('auBROWSE',1);
define('auADDNEW',1);
define('auEDIT',0);
define('auDELETE',0);
//require_once('../includeFiles/page.basic.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <META HTTP-EQUIV="EXPIRES" CONTENT="Mon, 22 Jul 2002 11:12:01 GMT">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../imageFiles/favicon.ico" type="image/ico" />
	
  <title> Fifth Season Life 人生第五季 | 工作測試暫存區 </title>
<!-- -------------------------- CSS Link -------------------------- -->
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../pluginFiles/bootstrap5/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../pluginFiles/fontawesome-free/css/all.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="../../_1/pluginFiles/sweetalert2/sweetalert2.min.css">
  <!-- Toastr -->
<link rel="stylesheet" href="../../_1/pluginFiles/toastr/toastr.min.css">
  <!-- Ion Slider -->
  <link rel="stylesheet" href="../../_1/pluginFiles/ion-rangeslider/css/ion.rangeSlider.min.css">
  <!-- bootstrap slider -->
  <link rel="stylesheet" href="../../_1/pluginFiles/bootstrap-slider/css/bootstrap-slider.min.css">
  <!-- daterange picker -->
  <link rel="stylesheet" href="../../_1/pluginFiles/daterangepicker/daterangepicker.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../pluginFiles/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="../../_1/pluginFiles/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../_1/pluginFiles/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="../../_1/pluginFiles/select2/css/select2.min.css">
  <link rel="stylesheet" href="../../_1/pluginFiles/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="../../_1/pluginFiles/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <!-- BS Stepper -->
  <link rel="stylesheet" href="../../_1/pluginFiles/bs-stepper/css/bs-stepper.min.css">
  <!-- dropzonejs -->
  <link rel="stylesheet" href="../../_1/pluginFiles/dropzone/min/dropzone.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../_1/pluginFiles/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../_1/pluginFiles/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../_1/pluginFiles/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../cssFiles/5sladmin.light.css">
<!-- -------------------------- /CSS Link -------------------------- -->
<style>
    .color-palette {
      height: 35px;
      line-height: 35px;
      text-align: right;
      padding-right: .75rem;
    }

    .color-palette.disabled {
      text-align: center;
      padding-right: 0;
      display: block;
    }

    .color-palette-set {
      margin-bottom: 15px;
    }

    .color-palette span {
      display: none;
      font-size: 12px;
    }

    .color-palette:hover span {
      display: block;
    }

    .color-palette.disabled span {
      display: block;
      text-align: left;
      padding-left: .75rem;
    }

    .color-palette-box h4 {
      position: absolute;
      left: 1.25rem;
      margin-top: .75rem;
      color: rgba(255, 255, 255, 0.8);
      font-size: 12px;
      display: block;
      z-index: 7;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <!-- 全頁框 -->
  <div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="../imageFiles/preloaderLOGO.png" alt="Fifth Season Life LOGO" height="60" width="60"> 
    </div>
    <!-- /Preloader -->
    <!-- 頁內全頁內容 -->
    <div class="content-wrapper">
      <!-- 頁頭導覽條 -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h4 class="m-0">工作測試暫存區</h4>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right small">
                <li class="breadcrumb-item">首頁</li>
                <li class="breadcrumb-item active">工作測試暫存區</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <!-- /頁頭導覽條 -->
      <!-- 主要內容區域 ------------------------------------- -->
     <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-edit"></i>
                  工作測試區域 非請勿入！！
                </h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-12 col-lg-6">
                    <h4>Default Navbar <small>(navbar-light)</small></h4>
                    <nav class="navbar navbar-expand navbar-light">
                      <!-- Left navbar links -->
                      <ul class="navbar-nav">
                        <li class="nav-item">
                          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="../console3.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="#" class="nav-link">Contact</a>
                        </li>
                      </ul>

                      <!-- Right navbar links -->
                      <ul class="navbar-nav ml-auto">
                        <!-- Navbar Search -->
                        <li class="nav-item">
                          <a class="nav-link" data-widget="navbar-search" data-target="#navbar-search1" href="#" role="button">
                            <i class="fas fa-search"></i>
                          </a>
                          <div class="navbar-search-block" id="navbar-search1">
                            <form class="form-inline">
                              <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                  <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </li>

                        <!-- Messages Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">Call me whenever you can

                                  </p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">I got your message bro</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">The subject goes here</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                          </div>
                        </li>
                        <!-- Notifications Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-envelope mr-2"></i> 4 new messages
                              <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-users mr-2"></i> 8 friend requests
                              <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-file mr-2"></i> 3 new reports
                              <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                          </div>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                  <div class="col-12 col-lg-6">
                    <h4>Default Navbar <small>(navbar-white navbar-light)</small></h4>
                    <nav class="navbar navbar-expand navbar-white navbar-light">
                      <!-- Left navbar links -->
                      <ul class="navbar-nav">
                        <li class="nav-item">
                          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="../console3.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="#" class="nav-link">Contact</a>
                        </li>
                      </ul>

                      <!-- Right navbar links -->
                      <ul class="navbar-nav ml-auto">
                        <!-- Navbar Search -->
                        <li class="nav-item">
                          <a class="nav-link" data-widget="navbar-search" data-target="#navbar-search2" href="#" role="button">
                            <i class="fas fa-search"></i>
                          </a>
                          <div class="navbar-search-block" id="navbar-search2">
                            <form class="form-inline">
                              <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                  <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </li>

                        <!-- Messages Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">Call me whenever you can

                                  </p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">I got your message bro</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">The subject goes here</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                          </div>
                        </li>
                        <!-- Notifications Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-envelope mr-2"></i> 4 new messages
                              <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-users mr-2"></i> 8 friend requests
                              <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-file mr-2"></i> 3 new reports
                              <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                          </div>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12 col-lg-6">
                    <h4 class="mt-4">Primary Navbar <small><b>recommended</b> (navbar-primary navbar-dark)</small></h4>
                    <nav class="navbar navbar-expand navbar-primary navbar-dark">
                      <!-- Left navbar links -->
                      <ul class="navbar-nav">
                        <li class="nav-item">
                          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="../console3.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="#" class="nav-link">Contact</a>
                        </li>
                      </ul>

                      <!-- Right navbar links -->
                      <ul class="navbar-nav ml-auto">
                        <!-- Navbar Search -->
                        <li class="nav-item">
                          <a class="nav-link" data-widget="navbar-search" data-target="#navbar-search3" href="#" role="button">
                            <i class="fas fa-search"></i>
                          </a>
                          <div class="navbar-search-block" id="navbar-search3">
                            <form class="form-inline">
                              <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                  <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </li>

                        <!-- Messages Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">Call me whenever you can

                                  </p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">I got your message bro</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">The subject goes here</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                          </div>
                        </li>
                        <!-- Notifications Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-envelope mr-2"></i> 4 new messages
                              <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-users mr-2"></i> 8 friend requests
                              <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-file mr-2"></i> 3 new reports
                              <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                          </div>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                  <div class="col-12 col-lg-6">
                    <h4 class="mt-4">Primary Navbar <small>(navbar-primary navbar-light)</small></h4>
                    <nav class="navbar navbar-expand navbar-primary navbar-light">
                      <!-- Left navbar links -->
                      <ul class="navbar-nav">
                        <li class="nav-item">
                          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="../console3.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="#" class="nav-link">Contact</a>
                        </li>
                      </ul>

                      <!-- Right navbar links -->
                      <ul class="navbar-nav ml-auto">
                        <!-- Navbar Search -->
                        <li class="nav-item">
                          <a class="nav-link" data-widget="navbar-search" data-target="#navbar-search4" href="#" role="button">
                            <i class="fas fa-search"></i>
                          </a>
                          <div class="navbar-search-block" id="navbar-search4">
                            <form class="form-inline">
                              <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                  <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </li>

                        <!-- Messages Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">Call me whenever you can

                                  </p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">I got your message bro</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">The subject goes here</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                          </div>
                        </li>
                        <!-- Notifications Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-envelope mr-2"></i> 4 new messages
                              <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-users mr-2"></i> 8 friend requests
                              <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-file mr-2"></i> 3 new reports
                              <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                          </div>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>
                <div class="row">
                  <div class="col-12 col-lg-6">
                    <h4 class="mt-4">Orange Navbar <small><b>recommended</b> (navbar-orange navbar-light)</small></h4>
                    <nav class="navbar navbar-expand navbar-orange navbar-light">
                      <!-- Left navbar links -->
                      <ul class="navbar-nav">
                        <li class="nav-item">
                          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="../console3.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="#" class="nav-link">Contact</a>
                        </li>
                      </ul>

                      <!-- Right navbar links -->
                      <ul class="navbar-nav ml-auto">
                        <!-- Navbar Search -->
                        <li class="nav-item">
                          <a class="nav-link" data-widget="navbar-search" data-target="#navbar-search5" href="#" role="button">
                            <i class="fas fa-search"></i>
                          </a>
                          <div class="navbar-search-block" id="navbar-search5">
                            <form class="form-inline">
                              <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                  <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </li>

                        <!-- Messages Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">Call me whenever you can

                                  </p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">I got your message bro</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">The subject goes here</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                          </div>
                        </li>
                        <!-- Notifications Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-envelope mr-2"></i> 4 new messages
                              <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-users mr-2"></i> 8 friend requests
                              <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-file mr-2"></i> 3 new reports
                              <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                          </div>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                  <div class="col-12 col-lg-6">
                    <h4 class="mt-4">Orange Navbar <small>(navbar-orange navbar-dark)</small></h4>
                    <nav class="navbar navbar-expand navbar-orange navbar-dark">
                      <!-- Left navbar links -->
                      <ul class="navbar-nav">
                        <li class="nav-item">
                          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="../console3.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block">
                          <a href="#" class="nav-link">Contact</a>
                        </li>
                      </ul>

                      <!-- Right navbar links -->
                      <ul class="navbar-nav ml-auto">
                        <!-- Navbar Search -->
                        <li class="nav-item">
                          <a class="nav-link" data-widget="navbar-search" data-target="#navbar-search6" href="#" role="button">
                            <i class="fas fa-search"></i>
                          </a>
                          <div class="navbar-search-block" id="navbar-search6">
                            <form class="form-inline">
                              <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                  <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </li>

                        <!-- Messages Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-comments"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">Call me whenever you can

                                  </p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">I got your message bro</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <!-- Message Start -->
                              <div class="media">
                                <img src="../imageFiles/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
                                <div class="media-body">
                                  <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                  </h3>
                                  <p class="text-sm">The subject goes here</p>
                                  <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                              </div>
                              <!-- Message End -->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                          </div>
                        </li>
                        <!-- Notifications Dropdown Menu -->
                        <li class="nav-item dropdown">
                          <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                          </a>
                          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-envelope mr-2"></i> 4 new messages
                              <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-users mr-2"></i> 8 friend requests
                              <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                              <i class="fas fa-file mr-2"></i> 3 new reports
                              <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                          </div>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
          <!-- /.col -->
        </div>
        <!-- ./row -->
        <div class="row">
          <div class="col-12">
            <h4>Nav Tabs Overlay for loading</h4>
          </div>
        </div>





        <div class="row">
          <div class="col-md-12">
            <div class="card card-primary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                  <!--  
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-five-overlay-tab" data-toggle="pill" href="#custom-tabs-five-overlay" role="tab" aria-controls="custom-tabs-five-overlay" aria-selected="true">Overlay</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-overlay-dark-tab" data-toggle="pill" href="#custom-tabs-five-overlay-dark" role="tab" aria-controls="custom-tabs-five-overlay-dark" aria-selected="false">Overlay Dark</a>
                  </li>
                  -->    
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">Slider</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal2" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">網頁圖標</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal3" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">Ribbon</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal4" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">Forms</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal5" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">Tables</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal6" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">Buttons</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal7" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">General Color</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal8" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">###2</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal9" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">###3</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-five-tabContent">
                  <!--
                  <div class="tab-pane fade show active" id="custom-tabs-five-overlay" role="tabpanel" aria-labelledby="custom-tabs-five-overlay-tab">
                     
                    <div class="overlay-wrapper">
                      <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>
                      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                    </div>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-five-overlay-dark" role="tabpanel" aria-labelledby="custom-tabs-five-overlay-dark-tab">
                    <div class="overlay-wrapper">
                      <div class="overlay dark"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>
                      Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                    </div>
                  </div>
                  -->
                  <div class="tab-pane fade" id="custom-tabs-five-normal" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                    <section class="content">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-12">
                            <div class="card card-primary">
                              <div class="card-header">
                                <h3 class="card-title">Ion Slider</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div class="row margin">
                                  <div class="col-sm-6">
                                    <input id="range_1" type="text" name="range_1" value="">
                                  </div>

                                  <div class="col-sm-6">
                                    <input id="range_2" type="text" name="range_2" value="1000;100000" data-type="double"
                                           data-step="500" data-postfix=" &euro;" data-from="30000" data-to="90000" data-hasgrid="true">
                                  </div>
                                </div>
                                <div class="row margin">
                                  <div class="col-sm-6">
                                    <input id="range_5" type="text" name="range_5" value="">
                                  </div>
                                  <div class="col-sm-6">
                                    <input id="range_6" type="text" name="range_6" value="">
                                  </div>
                                </div>
                                <div class="row margin">
                                  <div class="col-sm-12">
                                    <input id="range_4" type="text" name="range_4" value="10000;100000">
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->
                        </div>
                        <!-- /.row -->

                        <div class="row">
                          <div class="col-12">
                            <div class="card card-primary">
                              <div class="card-header">
                                <h3 class="card-title">Bootstrap Slider</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div class="row margin">
                                  <div class="col-sm-6">
                                    <div class="slider-red">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>

                                    <p>.slider-red input.slider</p>
                                    <div class="slider-blue">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>

                                    <p>.slider-blue input.slider</p>
                                    <div class="slider-green">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>

                                    <p>.slider-green input.slider</p>
                                    <div class="slider-yellow">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>

                                    <p>.slider-yellow input.slider</p>
                                    <div class="slider-teal">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>

                                    <p>.slider-teal input.slider</p>
                                    <div class="slider-purple">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="horizontal"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>

                                    <p>.slider-purple input.slider</p>
                                  </div>
                                  <div class="col-sm-6 d-flex justify-content-center">
                                    <div class="slider-red mx-3">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>
                                    <div class="slider-blue mx-3">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>
                                    <div class="slider-green mx-3">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>
                                    <div class="slider-yellow mx-3">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>
                                    <div class="slider-teal mx-3">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>
                                    <div class="slider-purple mx-3">
                                      <input type="text" value="" class="slider form-control" data-slider-min="-200" data-slider-max="200"
                                           data-slider-step="5" data-slider-value="[-100,100]" data-slider-orientation="vertical"
                                           data-slider-selection="before" data-slider-tooltip="show">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->
                        </div>
                        <!-- /.row -->
                      </div><!-- /.container-fluid -->
                    </section>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-five-normal2" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                <!-- -------------------------------- ICONs ------------------------------- -->
                    <?php //include_once('_bk/icons.php'); ?>
                <!-- -------------------------------- /ICONs ------------------------------- -->  
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-five-normal3" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                <!-- -------------------------------- 3 ------------------------------- -->
                    <section class="content">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-12">
                            <div class="card card-primary">
                              <div class="card-header">
                                <h3 class="card-title">Ribbons</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div class="row">
                                  <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-gray" style="height: 180px">
                                      <div class="ribbon-wrapper">
                                        <div class="ribbon bg-primary">
                                          Ribbon
                                        </div>
                                      </div>
                                      Ribbon Default <br />
                                      <small>.ribbon-wrapper.ribbon-lg .ribbon</small>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-gray" style="height: 180px">
                                      <div class="ribbon-wrapper ribbon-lg">
                                        <div class="ribbon bg-info">
                                          Ribbon Large
                                        </div>
                                      </div>
                                      Ribbon Large <br />
                                      <small>.ribbon-wrapper.ribbon-lg .ribbon</small>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-gray" style="height: 180px">
                                      <div class="ribbon-wrapper ribbon-xl">
                                        <div class="ribbon bg-secondary">
                                          Ribbon Extra Large
                                        </div>
                                      </div>
                                      Ribbon Extra Large <br />
                                      <small>.ribbon-wrapper.ribbon-xl .ribbon</small>
                                    </div>
                                  </div>
                                </div>
                                <div class="row mt-4">
                                  <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-gray" style="height: 180px">
                                      <div class="ribbon-wrapper ribbon-lg">
                                        <div class="ribbon bg-success text-lg">
                                          Ribbon
                                        </div>
                                      </div>
                                      Ribbon Large <br /> with Large Text <br />
                                      <small>.ribbon-wrapper.ribbon-lg .ribbon.text-lg</small>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-gray" style="height: 180px">
                                      <div class="ribbon-wrapper ribbon-xl">
                                        <div class="ribbon bg-warning text-lg">
                                          Ribbon
                                        </div>
                                      </div>
                                      Ribbon Extra Large <br /> with Large Text <br />
                                      <small>.ribbon-wrapper.ribbon-xl .ribbon.text-lg</small>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-gray" style="height: 180px">
                                      <div class="ribbon-wrapper ribbon-xl">
                                        <div class="ribbon bg-danger text-xl">
                                          Ribbon
                                        </div>
                                      </div>
                                      Ribbon Extra Large <br /> with Extra Large Text <br />
                                      <small>.ribbon-wrapper.ribbon-xl .ribbon.text-xl</small>
                                    </div>
                                  </div>
                                </div>
                                <div class="row mt-4">
                                  <div class="col-sm-4">
                                    <div class="position-relative">
                                      <img src="../imageFiles/img/photo1.png" alt="Photo 1" class="img-fluid">
                                      <div class="ribbon-wrapper ribbon-lg">
                                        <div class="ribbon bg-success text-lg">
                                          Ribbon
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="position-relative">
                                      <img src="../imageFiles/img/photo2.png" alt="Photo 2" class="img-fluid">
                                      <div class="ribbon-wrapper ribbon-xl">
                                        <div class="ribbon bg-warning text-lg">
                                          Ribbon
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="position-relative" style="min-height: 180px;">
                                      <img src="../imageFiles/img/photo3.jpg" alt="Photo 3" class="img-fluid">
                                      <div class="ribbon-wrapper ribbon-xl">
                                        <div class="ribbon bg-danger text-xl">
                                          Ribbon
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->
                        </div>
                        <!-- /.row -->
                      </div>
                      <!-- /.container-fluid -->
                    </section>
                <!-- -------------------------------- /3 ------------------------------- -->  
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-five-normal4" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                <!-- -------------------------------- 4 ------------------------------- -->
                    <section class="content">
                      <div class="container-fluid">
                        <!-- SELECT2 EXAMPLE -->
                        <div class="card card-default">
                          <div class="card-header">
                            <h3 class="card-title">Select2 (Default Theme)</h3>

                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                              </button>
                              <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label>Minimal</label>
                                  <select class="form-control select2" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group">
                                  <label>Disabled</label>
                                  <select class="form-control select2" disabled="disabled" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label>Multiple</label>
                                  <select class="select2" multiple="multiple" data-placeholder="Select a State" style="width: 100%;">
                                    <option>Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group">
                                  <label>Disabled Result</label>
                                  <select class="form-control select2" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option disabled="disabled">California (disabled)</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->

                            <h5>Custom Color Variants</h5>
                            <div class="row">
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label>Minimal (.select2-danger)</label>
                                  <select class="form-control select2 select2-danger" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                              <div class="col-12 col-sm-6">
                                <div class="form-group">
                                  <label>Multiple (.select2-purple)</label>
                                  <div class="select2-purple">
                                    <select class="select2" multiple="multiple" data-placeholder="Select a State" data-dropdown-css-class="select2-purple" style="width: 100%;">
                                      <option>Alabama</option>
                                      <option>Alaska</option>
                                      <option>California</option>
                                      <option>Delaware</option>
                                      <option>Tennessee</option>
                                      <option>Texas</option>
                                      <option>Washington</option>
                                    </select>
                                  </div>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->
                          </div>
                          <!-- /.card-body -->
                          <div class="card-footer">
                            Visit <a href="https://select2.github.io/">Select2 documentation</a> for more examples and information about
                            the plugin.
                          </div>
                        </div>
                        <!-- /.card -->

                        <!-- SELECT2 EXAMPLE -->
                        <div class="card card-default">
                          <div class="card-header">
                            <h3 class="card-title">Select2 (Bootstrap4 Theme)</h3>

                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                              </button>
                              <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label>Minimal</label>
                                  <select class="form-control select2bs4" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group">
                                  <label>Disabled</label>
                                  <select class="form-control select2bs4" disabled="disabled" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label>Multiple</label>
                                  <select class="select2bs4" multiple="multiple" data-placeholder="Select a State"
                                          style="width: 100%;">
                                    <option>Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group">
                                  <label>Disabled Result</label>
                                  <select class="form-control select2bs4" style="width: 100%;">
                                    <option selected="selected">Alabama</option>
                                    <option>Alaska</option>
                                    <option disabled="disabled">California (disabled)</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->
                          </div>
                          <!-- /.card-body -->
                          <div class="card-footer">
                            Visit <a href="https://select2.github.io/">Select2 documentation</a> for more examples and information about
                            the plugin.
                          </div>
                        </div>
                        <!-- /.card -->

                        <div class="card card-default">
                          <div class="card-header">
                            <h3 class="card-title">Bootstrap Duallistbox</h3>

                            <div class="card-tools">
                              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                              </button>
                              <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                              </button>
                            </div>
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <div class="row">
                              <div class="col-12">
                                <div class="form-group">
                                  <label>Multiple</label>
                                  <select class="duallistbox" multiple="multiple">
                                    <option selected>Alabama</option>
                                    <option>Alaska</option>
                                    <option>California</option>
                                    <option>Delaware</option>
                                    <option>Tennessee</option>
                                    <option>Texas</option>
                                    <option>Washington</option>
                                  </select>
                                </div>
                                <!-- /.form-group -->
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->
                          </div>
                          <!-- /.card-body -->
                          <div class="card-footer">
                            Visit <a href="https://github.com/istvan-ujjmeszaros/bootstrap-duallistbox#readme">Bootstrap Duallistbox</a> for more examples and information about
                            the plugin.
                          </div>
                        </div>
                        <!-- /.card -->

                        <div class="row">
                          <div class="col-md-6">

                            <div class="card card-danger">
                              <div class="card-header">
                                <h3 class="card-title">Input masks</h3>
                              </div>
                              <div class="card-body">
                                <!-- Date dd/mm/yyyy -->
                                <div class="form-group">
                                  <label>Date masks:</label>

                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="text" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask>
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- Date mm/dd/yyyy -->
                                <div class="form-group">
                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="text" class="form-control" data-inputmask-alias="datetime" data-inputmask-inputformat="mm/dd/yyyy" data-mask>
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- phone mask -->
                                <div class="form-group">
                                  <label>US phone mask:</label>

                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" class="form-control" data-inputmask='"mask": "(999) 999-9999"' data-mask>
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- phone mask -->
                                <div class="form-group">
                                  <label>Intl US phone mask:</label>

                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="text" class="form-control"
                                           data-inputmask="'mask': ['999-999-9999 [x99999]', '+099 99 99 9999[9]-9999']" data-mask>
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- IP mask -->
                                <div class="form-group">
                                  <label>IP mask:</label>

                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="fas fa-laptop"></i></span>
                                    </div>
                                    <input type="text" class="form-control" data-inputmask="'alias': 'ip'" data-mask>
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->

                            <div class="card card-info">
                              <div class="card-header">
                                <h3 class="card-title">Color & Time Picker</h3>
                              </div>
                              <div class="card-body">
                                <!-- Color Picker -->
                                <div class="form-group">
                                  <label>Color picker:</label>
                                  <input type="text" class="form-control my-colorpicker1">
                                </div>
                                <!-- /.form group -->

                                <!-- Color Picker -->
                                <div class="form-group">
                                  <label>Color picker with addon:</label>

                                  <div class="input-group my-colorpicker2">
                                    <input type="text" class="form-control">

                                    <div class="input-group-append">
                                      <span class="input-group-text"><i class="fas fa-square"></i></span>
                                    </div>
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- time Picker -->
                                <div class="bootstrap-timepicker">
                                  <div class="form-group">
                                    <label>Time picker:</label>

                                    <div class="input-group date" id="timepicker" data-target-input="nearest">
                                      <input type="text" class="form-control datetimepicker-input" data-target="#timepicker"/>
                                      <div class="input-group-append" data-target="#timepicker" data-toggle="datetimepicker">
                                          <div class="input-group-text"><i class="far fa-clock"></i></div>
                                      </div>
                                      </div>
                                    <!-- /.input group -->
                                  </div>
                                  <!-- /.form group -->
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->

                          </div>
                          <!-- /.col (left) -->
                          <div class="col-md-6">
                            <div class="card card-primary">
                              <div class="card-header">
                                <h3 class="card-title">Date picker</h3>
                              </div>
                              <div class="card-body">
                                <!-- Date -->
                                <div class="form-group">
                                  <label>Date:</label>
                                    <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                        <input type="text" class="form-control datetimepicker-input" data-target="#reservationdate"/>
                                        <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Date and time -->
                                <div class="form-group">
                                  <label>Date and time:</label>
                                    <div class="input-group date" id="reservationdatetime" data-target-input="nearest">
                                        <input type="text" class="form-control datetimepicker-input" data-target="#reservationdatetime"/>
                                        <div class="input-group-append" data-target="#reservationdatetime" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.form group -->
                                <!-- Date range -->
                                <div class="form-group">
                                  <label>Date range:</label>

                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                      </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservation">
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- Date and time range -->
                                <div class="form-group">
                                  <label>Date and time range:</label>

                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="far fa-clock"></i></span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="reservationtime">
                                  </div>
                                  <!-- /.input group -->
                                </div>
                                <!-- /.form group -->

                                <!-- Date and time range -->
                                <div class="form-group">
                                  <label>Date range button:</label>

                                  <div class="input-group">
                                    <button type="button" class="btn btn-default float-right" id="daterange-btn">
                                      <i class="far fa-calendar-alt"></i> Date range picker
                                      <i class="fas fa-caret-down"></i>
                                    </button>
                                  </div>
                                </div>
                                <!-- /.form group -->
                              </div>
                                <div class="card-footer">
                                  Visit <a href="https://getdatepicker.com/5-4/">tempusdominus </a> for more examples and information about
                                  the plugin.
                                </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->

                            <!-- iCheck -->
                            <div class="card card-success">
                              <div class="card-header">
                                <h3 class="card-title">iCheck Bootstrap - Checkbox &amp; Radio Inputs</h3>
                              </div>
                              <div class="card-body">
                                <!-- Minimal style -->
                                <div class="row">
                                  <div class="col-sm-6">
                                    <!-- checkbox -->
                                    <div class="form-group clearfix">
                                      <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="checkboxPrimary1" checked>
                                        <label for="checkboxPrimary1">
                                        </label>
                                      </div>
                                      <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="checkboxPrimary2">
                                        <label for="checkboxPrimary2">
                                        </label>
                                      </div>
                                      <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="checkboxPrimary3" disabled>
                                        <label for="checkboxPrimary3">
                                          Primary checkbox
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-6">
                                    <!-- radio -->
                                    <div class="form-group clearfix">
                                      <div class="icheck-primary d-inline">
                                        <input type="radio" id="radioPrimary1" name="r1" checked>
                                        <label for="radioPrimary1">
                                        </label>
                                      </div>
                                      <div class="icheck-primary d-inline">
                                        <input type="radio" id="radioPrimary2" name="r1">
                                        <label for="radioPrimary2">
                                        </label>
                                      </div>
                                      <div class="icheck-primary d-inline">
                                        <input type="radio" id="radioPrimary3" name="r1" disabled>
                                        <label for="radioPrimary3">
                                          Primary radio
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <!-- Minimal red style -->
                                <div class="row">
                                  <div class="col-sm-6">
                                    <!-- checkbox -->
                                    <div class="form-group clearfix">
                                      <div class="icheck-danger d-inline">
                                        <input type="checkbox" checked id="checkboxDanger1">
                                        <label for="checkboxDanger1">
                                        </label>
                                      </div>
                                      <div class="icheck-danger d-inline">
                                        <input type="checkbox" id="checkboxDanger2">
                                        <label for="checkboxDanger2">
                                        </label>
                                      </div>
                                      <div class="icheck-danger d-inline">
                                        <input type="checkbox" disabled id="checkboxDanger3">
                                        <label for="checkboxDanger3">
                                          Danger checkbox
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-6">
                                    <!-- radio -->
                                    <div class="form-group clearfix">
                                      <div class="icheck-danger d-inline">
                                        <input type="radio" name="r2" checked id="radioDanger1">
                                        <label for="radioDanger1">
                                        </label>
                                      </div>
                                      <div class="icheck-danger d-inline">
                                        <input type="radio" name="r2" id="radioDanger2">
                                        <label for="radioDanger2">
                                        </label>
                                      </div>
                                      <div class="icheck-danger d-inline">
                                        <input type="radio" name="r2" disabled id="radioDanger3">
                                        <label for="radioDanger3">
                                          Danger radio
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <!-- Minimal red style -->
                                <div class="row">
                                  <div class="col-sm-6">
                                    <!-- checkbox -->
                                    <div class="form-group clearfix">
                                      <div class="icheck-success d-inline">
                                        <input type="checkbox" checked id="checkboxSuccess1">
                                        <label for="checkboxSuccess1">
                                        </label>
                                      </div>
                                      <div class="icheck-success d-inline">
                                        <input type="checkbox" id="checkboxSuccess2">
                                        <label for="checkboxSuccess2">
                                        </label>
                                      </div>
                                      <div class="icheck-success d-inline">
                                        <input type="checkbox" disabled id="checkboxSuccess3">
                                        <label for="checkboxSuccess3">
                                          Success checkbox
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-6">
                                    <!-- radio -->
                                    <div class="form-group clearfix">
                                      <div class="icheck-success d-inline">
                                        <input type="radio" name="r3" checked id="radioSuccess1">
                                        <label for="radioSuccess1">
                                        </label>
                                      </div>
                                      <div class="icheck-success d-inline">
                                        <input type="radio" name="r3" id="radioSuccess2">
                                        <label for="radioSuccess2">
                                        </label>
                                      </div>
                                      <div class="icheck-success d-inline">
                                        <input type="radio" name="r3" disabled id="radioSuccess3">
                                        <label for="radioSuccess3">
                                          Success radio
                                        </label>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                              <div class="card-footer">
                                Many more skins available. <a href="https://bantikyan.github.io/icheck-bootstrap/">Documentation</a>
                              </div>
                            </div>
                            <!-- /.card -->

                            <!-- Bootstrap Switch -->
                            <div class="card card-secondary">
                              <div class="card-header">
                                <h3 class="card-title">Bootstrap Switch</h3>
                              </div>
                              <div class="card-body">
                                <input type="checkbox" name="my-checkbox" checked data-bootstrap-switch>
                                <input type="checkbox" name="my-checkbox" checked data-bootstrap-switch data-off-color="danger" data-on-color="success">
                              </div>
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col (right) -->
                        </div>
                        <!-- /.row -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="card card-default">
                              <div class="card-header">
                                <h3 class="card-title">bs-stepper</h3>
                              </div>
                              <div class="card-body p-0">
                                <div class="bs-stepper">
                                  <div class="bs-stepper-header" role="tablist">
                                    <!-- your steps here -->
                                    <div class="step" data-target="#logins-part">
                                      <button type="button" class="step-trigger" role="tab" aria-controls="logins-part" id="logins-part-trigger">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Logins</span>
                                      </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step" data-target="#information-part">
                                      <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Various information</span>
                                      </button>
                                    </div>
                                  </div>
                                  <div class="bs-stepper-content">
                                    <!-- your steps content here -->
                                    <div id="logins-part" class="content" role="tabpanel" aria-labelledby="logins-part-trigger">
                                      <div class="form-group">
                                        <label for="exampleInputEmail1">Email address</label>
                                        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                                      </div>
                                      <div class="form-group">
                                        <label for="exampleInputPassword1">Password</label>
                                        <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                                      </div>
                                      <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                                    </div>
                                    <div id="information-part" class="content" role="tabpanel" aria-labelledby="information-part-trigger">
                                      <div class="form-group">
                                        <label for="exampleInputFile">File input</label>
                                        <div class="input-group">
                                          <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="exampleInputFile">
                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                          </div>
                                          <div class="input-group-append">
                                            <span class="input-group-text">Upload</span>
                                          </div>
                                        </div>
                                      </div>
                                      <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                      <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                              <div class="card-footer">
                                Visit <a href="https://github.com/Johann-S/bs-stepper/#how-to-use-it">bs-stepper documentation</a> for more examples and information about the plugin.
                              </div>
                            </div>
                            <!-- /.card -->
                          </div>
                        </div>
                        <!-- /.row -->
                        <div class="row">
                          <div class="col-md-12">
                            <div class="card card-default">
                              <div class="card-header">
                                <h3 class="card-title">Dropzone.js <small><em>jQuery File Upload</em> like look</small></h3>
                              </div>
                              <div class="card-body">
                                <div id="actions" class="row">
                                  <div class="col-lg-6">
                                    <div class="btn-group w-100">
                                      <span class="btn btn-success col fileinput-button">
                                        <i class="fas fa-plus"></i>
                                        <span>Add files</span>
                                      </span>
                                      <button type="submit" class="btn btn-primary col start">
                                        <i class="fas fa-upload"></i>
                                        <span>Start upload</span>
                                      </button>
                                      <button type="reset" class="btn btn-warning col cancel">
                                        <i class="fas fa-times-circle"></i>
                                        <span>Cancel upload</span>
                                      </button>
                                    </div>
                                  </div>
                                  <div class="col-lg-6 d-flex align-items-center">
                                    <div class="fileupload-process w-100">
                                      <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="table table-striped files" id="previews">
                                  <div id="template" class="row mt-2">
                                    <div class="col-auto">
                                        <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
                                    </div>
                                    <div class="col d-flex align-items-center">
                                        <p class="mb-0">
                                          <span class="lead" data-dz-name></span>
                                          (<span data-dz-size></span>)
                                        </p>
                                        <strong class="error text-danger" data-dz-errormessage></strong>
                                    </div>
                                    <div class="col-4 d-flex align-items-center">
                                        <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                          <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                        </div>
                                    </div>
                                    <div class="col-auto d-flex align-items-center">
                                      <div class="btn-group">
                                        <button class="btn btn-primary start">
                                          <i class="fas fa-upload"></i>
                                          <span>Start</span>
                                        </button>
                                        <button data-dz-remove class="btn btn-warning cancel">
                                          <i class="fas fa-times-circle"></i>
                                          <span>Cancel</span>
                                        </button>
                                        <button data-dz-remove class="btn btn-danger delete">
                                          <i class="fas fa-trash"></i>
                                          <span>Delete</span>
                                        </button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                              <div class="card-footer">
                                Visit <a href="https://www.dropzonejs.com">dropzone.js documentation</a> for more examples and information about the plugin.
                              </div>
                            </div>
                            <!-- /.card -->
                          </div>
                        </div>
                        <!-- /.row -->
                      </div>
                      <!-- /.container-fluid -->
                    </section>
                   
                <!-- -------------------------------- /4 ------------------------------- -->  
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-five-normal5" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                <!-- -------------------------------- 5 ------------------------------- -->
                <section class="content">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-12">
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">DataTable with minimal features & hover style</h3>
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                              <thead>
                              <tr>
                                <th>Rendering engine</th>
                                <th>Browser</th>
                                <th>Platform(s)</th>
                                <th>Engine version</th>
                                <th>CSS grade</th>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 4.0
                                </td>
                                <td>Win 95+</td>
                                <td> 4</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 5.0
                                </td>
                                <td>Win 95+</td>
                                <td>5</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 5.5
                                </td>
                                <td>Win 95+</td>
                                <td>5.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 6
                                </td>
                                <td>Win 98+</td>
                                <td>6</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet Explorer 7</td>
                                <td>Win XP SP2+</td>
                                <td>7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>AOL browser (AOL desktop)</td>
                                <td>Win XP</td>
                                <td>6</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 1.0</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 1.5</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 2.0</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 3.0</td>
                                <td>Win 2k+ / OSX.3+</td>
                                <td>1.9</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Camino 1.0</td>
                                <td>OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Camino 1.5</td>
                                <td>OSX.3+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Netscape 7.2</td>
                                <td>Win 95+ / Mac OS 8.6-9.2</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Netscape Browser 8</td>
                                <td>Win 98SE+</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Netscape Navigator 9</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.0</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.1</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.2</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.2</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.3</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.3</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.4</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.4</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.5</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.6</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.6</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.7</td>
                                <td>Win 98+ / OSX.1+</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.8</td>
                                <td>Win 98+ / OSX.1+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Seamonkey 1.1</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Epiphany 2.20</td>
                                <td>Gnome</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 1.2</td>
                                <td>OSX.3</td>
                                <td>125.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 1.3</td>
                                <td>OSX.3</td>
                                <td>312.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 2.0</td>
                                <td>OSX.4+</td>
                                <td>419.3</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 3.0</td>
                                <td>OSX.4+</td>
                                <td>522.1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>OmniWeb 5.5</td>
                                <td>OSX.4+</td>
                                <td>420</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>iPod Touch / iPhone</td>
                                <td>iPod</td>
                                <td>420.1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>S60</td>
                                <td>S60</td>
                                <td>413</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 7.0</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 7.5</td>
                                <td>Win 95+ / OSX.2+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 8.0</td>
                                <td>Win 95+ / OSX.2+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 8.5</td>
                                <td>Win 95+ / OSX.2+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 9.0</td>
                                <td>Win 95+ / OSX.3+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 9.2</td>
                                <td>Win 88+ / OSX.3+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 9.5</td>
                                <td>Win 88+ / OSX.3+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera for Wii</td>
                                <td>Wii</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Nokia N800</td>
                                <td>N800</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Nintendo DS browser</td>
                                <td>Nintendo DS</td>
                                <td>8.5</td>
                                <td>C/A<sup>1</sup></td>
                              </tr>
                              <tr>
                                <td>KHTML</td>
                                <td>Konqureror 3.1</td>
                                <td>KDE 3.1</td>
                                <td>3.1</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>KHTML</td>
                                <td>Konqureror 3.3</td>
                                <td>KDE 3.3</td>
                                <td>3.3</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>KHTML</td>
                                <td>Konqureror 3.5</td>
                                <td>KDE 3.5</td>
                                <td>3.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Tasman</td>
                                <td>Internet Explorer 4.5</td>
                                <td>Mac OS 8-9</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Tasman</td>
                                <td>Internet Explorer 5.1</td>
                                <td>Mac OS 7.6-9</td>
                                <td>1</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Tasman</td>
                                <td>Internet Explorer 5.2</td>
                                <td>Mac OS 8-X</td>
                                <td>1</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>NetFront 3.1</td>
                                <td>Embedded devices</td>
                                <td>-</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>NetFront 3.4</td>
                                <td>Embedded devices</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>Dillo 0.8</td>
                                <td>Embedded devices</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>Links</td>
                                <td>Text only</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>Lynx</td>
                                <td>Text only</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>IE Mobile</td>
                                <td>Windows Mobile 6</td>
                                <td>-</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>PSP browser</td>
                                <td>PSP</td>
                                <td>-</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Other browsers</td>
                                <td>All others</td>
                                <td>-</td>
                                <td>-</td>
                                <td>U</td>
                              </tr>
                              </tbody>
                              <tfoot>
                              <tr>
                                <th>Rendering engine</th>
                                <th>Browser</th>
                                <th>Platform(s)</th>
                                <th>Engine version</th>
                                <th>CSS grade</th>
                              </tr>
                              </tfoot>
                            </table>
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->

                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">DataTable with default features</h3>
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                              <thead>
                              <tr>
                                <th>Rendering engine</th>
                                <th>Browser</th>
                                <th>Platform(s)</th>
                                <th>Engine version</th>
                                <th>CSS grade</th>
                              </tr>
                              </thead>
                              <tbody>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 4.0
                                </td>
                                <td>Win 95+</td>
                                <td> 4</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 5.0
                                </td>
                                <td>Win 95+</td>
                                <td>5</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 5.5
                                </td>
                                <td>Win 95+</td>
                                <td>5.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet
                                  Explorer 6
                                </td>
                                <td>Win 98+</td>
                                <td>6</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>Internet Explorer 7</td>
                                <td>Win XP SP2+</td>
                                <td>7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Trident</td>
                                <td>AOL browser (AOL desktop)</td>
                                <td>Win XP</td>
                                <td>6</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 1.0</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 1.5</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 2.0</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Firefox 3.0</td>
                                <td>Win 2k+ / OSX.3+</td>
                                <td>1.9</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Camino 1.0</td>
                                <td>OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Camino 1.5</td>
                                <td>OSX.3+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Netscape 7.2</td>
                                <td>Win 95+ / Mac OS 8.6-9.2</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Netscape Browser 8</td>
                                <td>Win 98SE+</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Netscape Navigator 9</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.0</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.1</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.2</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.2</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.3</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.3</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.4</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.4</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.5</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.6</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>1.6</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.7</td>
                                <td>Win 98+ / OSX.1+</td>
                                <td>1.7</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Mozilla 1.8</td>
                                <td>Win 98+ / OSX.1+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Seamonkey 1.1</td>
                                <td>Win 98+ / OSX.2+</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Gecko</td>
                                <td>Epiphany 2.20</td>
                                <td>Gnome</td>
                                <td>1.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 1.2</td>
                                <td>OSX.3</td>
                                <td>125.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 1.3</td>
                                <td>OSX.3</td>
                                <td>312.8</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 2.0</td>
                                <td>OSX.4+</td>
                                <td>419.3</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>Safari 3.0</td>
                                <td>OSX.4+</td>
                                <td>522.1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>OmniWeb 5.5</td>
                                <td>OSX.4+</td>
                                <td>420</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>iPod Touch / iPhone</td>
                                <td>iPod</td>
                                <td>420.1</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Webkit</td>
                                <td>S60</td>
                                <td>S60</td>
                                <td>413</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 7.0</td>
                                <td>Win 95+ / OSX.1+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 7.5</td>
                                <td>Win 95+ / OSX.2+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 8.0</td>
                                <td>Win 95+ / OSX.2+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 8.5</td>
                                <td>Win 95+ / OSX.2+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 9.0</td>
                                <td>Win 95+ / OSX.3+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 9.2</td>
                                <td>Win 88+ / OSX.3+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera 9.5</td>
                                <td>Win 88+ / OSX.3+</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Opera for Wii</td>
                                <td>Wii</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Nokia N800</td>
                                <td>N800</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Presto</td>
                                <td>Nintendo DS browser</td>
                                <td>Nintendo DS</td>
                                <td>8.5</td>
                                <td>C/A<sup>1</sup></td>
                              </tr>
                              <tr>
                                <td>KHTML</td>
                                <td>Konqureror 3.1</td>
                                <td>KDE 3.1</td>
                                <td>3.1</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>KHTML</td>
                                <td>Konqureror 3.3</td>
                                <td>KDE 3.3</td>
                                <td>3.3</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>KHTML</td>
                                <td>Konqureror 3.5</td>
                                <td>KDE 3.5</td>
                                <td>3.5</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Tasman</td>
                                <td>Internet Explorer 4.5</td>
                                <td>Mac OS 8-9</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Tasman</td>
                                <td>Internet Explorer 5.1</td>
                                <td>Mac OS 7.6-9</td>
                                <td>1</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Tasman</td>
                                <td>Internet Explorer 5.2</td>
                                <td>Mac OS 8-X</td>
                                <td>1</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>NetFront 3.1</td>
                                <td>Embedded devices</td>
                                <td>-</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>NetFront 3.4</td>
                                <td>Embedded devices</td>
                                <td>-</td>
                                <td>A</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>Dillo 0.8</td>
                                <td>Embedded devices</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>Links</td>
                                <td>Text only</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>Lynx</td>
                                <td>Text only</td>
                                <td>-</td>
                                <td>X</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>IE Mobile</td>
                                <td>Windows Mobile 6</td>
                                <td>-</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Misc</td>
                                <td>PSP browser</td>
                                <td>PSP</td>
                                <td>-</td>
                                <td>C</td>
                              </tr>
                              <tr>
                                <td>Other browsers</td>
                                <td>All others</td>
                                <td>-</td>
                                <td>-</td>
                                <td>U</td>
                              </tr>
                              </tbody>
                              <tfoot>
                              <tr>
                                <th>Rendering engine</th>
                                <th>Browser</th>
                                <th>Platform(s)</th>
                                <th>Engine version</th>
                                <th>CSS grade</th>
                              </tr>
                              </tfoot>
                            </table>
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- /.row -->
                  </div>
                  <!-- /.container-fluid -->
                </section>
                <!-- -------------------------------- /5 ------------------------------- -->  
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-five-normal6" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                <!-- -------------------------------- 6 ------------------------------- -->
                <section class="content">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="card card-primary card-outline">
                          <div class="card-header">
                            <h3 class="card-title">
                              <i class="fas fa-edit"></i>
                              Buttons
                            </h3>
                          </div>
                          <div class="card-body pad table-responsive">
                            <p>Various types of buttons. Using the base class <code>.btn</code></p>
                            <table class="table table-bordered text-center">
                              <tr>
                                <th>Normal</th>
                                <th>Large <code>.btn-lg</code></th>
                                <th>Small <code>.btn-sm</code></th>
                                <th>Extra Small <code>.btn-xs</code></th>
                                <th>Flat <code>.btn-flat</code></th>
                                <th>Disabled <code>.disabled</code></th>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-default">Default</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-default btn-lg">Default</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-default btn-sm">Default</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-default btn-xs">Default</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-default btn-flat">Default</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-default disabled">Default</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-primary">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-primary btn-lg">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-primary btn-sm">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-primary btn-xs">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-primary btn-flat">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-primary disabled">Primary</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-secondary">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-secondary btn-lg">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-secondary btn-sm">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-secondary btn-xs">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-secondary btn-flat">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-secondary disabled">Secondary</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-success">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-success btn-lg">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-success btn-sm">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-success btn-xs">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-success btn-flat">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-success disabled">Success</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-info">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-info btn-lg">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-info btn-sm">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-info btn-xs">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-info btn-flat">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-info disabled">Info</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-danger">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-danger btn-lg">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-danger btn-sm">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-danger btn-xs">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-danger btn-flat">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-danger disabled">Danger</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-warning">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-warning btn-lg">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-warning btn-sm">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-warning btn-xs">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-warning btn-flat">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-warning disabled">Warning</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-light">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-light btn-lg">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-light btn-sm">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-light btn-xs">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-light btn-flat">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-light disabled">Light</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-dark">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-dark btn-lg">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-dark btn-sm">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-dark btn-xs">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-dark btn-flat">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-dark disabled">Dark</button>
                                </td>
                              </tr>
                            </table>
                          </div>
                          <!-- /.card -->
                        </div>
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- ./row -->

                    <div class="row">
                      <div class="col-md-12">
                        <div class="card card-primary card-outline">
                          <div class="card-header">
                            <h3 class="card-title">
                              <i class="fas fa-edit"></i>
                              Outline Buttons
                            </h3>
                          </div>
                          <div class="card-body pad table-responsive">
                            <p>Various types of buttons. Using the base class <code>.btn</code></p>
                            <table class="table table-bordered text-center">
                              <tr>
                                <th>Normal</th>
                                <th>Large <code>.btn-lg</code></th>
                                <th>Small <code>.btn-sm</code></th>
                                <th>Extra Small <code>.btn-xs</code></th>
                                <th>Flat <code>.btn-flat</code></th>
                                <th>Disabled <code>.disabled</code></th>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-primary">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-primary btn-lg">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-primary btn-sm">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-primary btn-xs">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-primary btn-flat">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-primary disabled">Primary</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-secondary">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-secondary btn-lg">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-secondary btn-sm">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-secondary btn-xs">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-secondary btn-flat">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-secondary disabled">Secondary</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-success">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-success btn-lg">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-success btn-sm">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-success btn-xs">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-success btn-flat">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-success disabled">Success</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-info">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-info btn-lg">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-info btn-sm">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-info btn-xs">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-info btn-flat">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-info disabled">Info</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-danger">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-danger btn-lg">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-danger btn-sm">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-danger btn-xs">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-danger btn-flat">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-danger disabled">Danger</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-warning">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-warning btn-lg">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-warning btn-sm">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-warning btn-xs">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-warning btn-flat">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-warning disabled">Warning</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-light">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-light btn-lg">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-light btn-sm">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-light btn-xs">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-light btn-flat">Light</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-light disabled">Light</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-dark">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-dark btn-lg">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-dark btn-sm">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-dark btn-xs">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-dark btn-flat">Dark</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block btn-outline-dark disabled">Dark</button>
                                </td>
                              </tr>
                            </table>
                          </div>
                          <!-- /.card -->
                        </div>
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- ./row -->

                    <div class="row">
                      <div class="col-md-12">
                        <div class="card card-primary card-outline">
                          <div class="card-header">
                            <h3 class="card-title">
                              <i class="fas fa-edit"></i>
                              Gradient Buttons (bg-gradient-*)
                            </h3>
                          </div>
                          <div class="card-body pad table-responsive">
                            <p>Various types of buttons. Using the base class <code>.btn</code></p>
                            <table class="table table-bordered text-center">
                              <tr>
                                <th>Normal</th>
                                <th>Large <code>.btn-lg</code></th>
                                <th>Small <code>.btn-sm</code></th>
                                <th>Extra Small <code>.btn-xs</code></th>
                                <th>Flat <code>.btn-flat</code></th>
                                <th>Disabled <code>.disabled</code></th>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-primary">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-primary btn-lg">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-primary btn-sm">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-primary btn-xs">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-primary btn-flat">Primary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-primary disabled">Primary</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-secondary">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-secondary btn-lg">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-secondary btn-sm">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-secondary btn-xs">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-secondary btn-flat">Secondary</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-secondary disabled">Secondary</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-success">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-success btn-lg">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-success btn-sm">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-success btn-xs">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-success btn-flat">Success</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-success disabled">Success</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-info">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-info btn-lg">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-info btn-sm">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-info btn-xs">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-info btn-flat">Info</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-info disabled">Info</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-danger">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-danger btn-lg">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-danger btn-sm">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-danger btn-xs">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-danger btn-flat">Danger</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-danger disabled">Danger</button>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-warning">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-warning btn-lg">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-warning btn-sm">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-warning btn-xs">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-warning btn-flat">Warning</button>
                                </td>
                                <td>
                                  <button type="button" class="btn btn-block bg-gradient-warning disabled">Warning</button>
                                </td>
                              </tr>
                            </table>
                          </div>
                          <!-- /.card -->
                        </div>
                      </div>
                      <!-- /.col -->
                    </div>
                      <!-- ./row -->
                    <div class="row">
                      <div class="col-md-6">
                        <!-- Buttons with Icons -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Buttons with Icons</h3>
                          </div>
                          <div class="card-body row">
                            <div class="col-md-6">
                              <button type="button" class="btn btn-primary btn-block"><i class="fa fa-bell"></i> .btn-block</button>
                              <button type="button" class="btn btn-info btn-block btn-flat"><i class="fa fa-bell"></i> .btn-block .btn-flat</button>
                              <button type="button" class="btn btn-danger btn-block btn-sm"><i class="fa fa-bell"></i> .btn-block .btn-sm</button>
                            </div>
                            <div class="col-md-6">
                              <button type="button" class="btn btn-outline-primary btn-block"><i class="fa fa-bell"></i> .btn-block</button>
                              <button type="button" class="btn btn-outline-info btn-block btn-flat"><i class="fa fa-book"></i> .btn-block .btn-flat</button>
                              <button type="button" class="btn btn-outline-danger btn-block btn-sm"><i class="fa fa-book"></i> .btn-block .btn-sm</button>
                            </div>
                          </div>
                        </div>
                        <!-- /.card -->

                        <!-- Block buttons -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Block Buttons</h3>
                          </div>
                          <div class="card-body">
                            <button type="button" class="btn btn-default btn-block">.btn-block</button>
                            <button type="button" class="btn btn-default btn-block btn-flat">.btn-block .btn-flat</button>
                            <button type="button" class="btn btn-default btn-block btn-sm">.btn-block .btn-sm</button>
                          </div>
                        </div>
                        <!-- /.card -->

                        <!-- Horizontal grouping -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Horizontal Button Group</h3>
                          </div>
                          <div class="card-body table-responsive pad">
                            <p>
                              Horizontal button groups are easy to create with bootstrap. Just add your buttons
                              inside <code>&lt;div class="btn-group"&gt;&lt;/div&gt;</code>
                            </p>

                            <table class="table table-bordered">
                              <tr>
                                <th>Button</th>
                                <th>Icons</th>
                                <th>Flat</th>
                                <th>Dropdown</th>
                              </tr>
                              <!-- Default -->
                              <tr>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-default">Left</button>
                                    <button type="button" class="btn btn-default">Middle</button>
                                    <button type="button" class="btn btn-default">Right</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-default">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-default">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-default">1</button>
                                    <button type="button" class="btn btn-default">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                      </button>
                                      <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- ./default -->
                              <!-- Info -->
                              <tr>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-info">Left</button>
                                    <button type="button" class="btn btn-info">Middle</button>
                                    <button type="button" class="btn btn-info">Right</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-info">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-info">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-info">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-info">1</button>
                                    <button type="button" class="btn btn-info">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                      </button>
                                      <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /. info -->
                              <!-- /.danger -->
                              <tr>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-danger">Left</button>
                                    <button type="button" class="btn btn-danger">Middle</button>
                                    <button type="button" class="btn btn-danger">Right</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-danger">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-danger btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-danger">1</button>
                                    <button type="button" class="btn btn-danger">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-danger dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                      </button>
                                      <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /.danger -->
                              <!-- warning -->
                              <tr>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-warning">Left</button>
                                    <button type="button" class="btn btn-warning">Middle</button>
                                    <button type="button" class="btn btn-warning">Right</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-warning">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-warning btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-warning">1</button>
                                    <button type="button" class="btn btn-warning">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-warning dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                      </button>
                                      <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /.warning -->
                              <!-- success -->
                              <tr>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-success">Left</button>
                                    <button type="button" class="btn btn-success">Middle</button>
                                    <button type="button" class="btn btn-success">Right</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-success">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-success">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-success">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-success btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-success">1</button>
                                    <button type="button" class="btn btn-success">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                      </button>
                                      <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                        <a class="dropdown-item" href="#">Dropdown link</a>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /.success -->
                            </table>
                          </div>
                        </div>
                        <!-- /.card -->

                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Appended Buttons</h3>
                          </div>
                          <div class="card-body">
                            <strong>With dropdown</strong>
                            <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                  Action
                                </button>
                                <div class="dropdown-menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <!-- /btn-group -->
                              <input type="text" class="form-control">
                            </div>
                            <!-- /input-group -->
                            <strong>Normal</strong>
                            <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Action</button>
                              </div>
                              <!-- /btn-group -->
                              <input type="text" class="form-control">
                            </div>
                            <!-- /input-group -->
                            <strong>Flat</strong>
                            <div class="input-group mb-3">
                              <input type="text" class="form-control rounded-0">
                              <span class="input-group-append">
                                <button type="button" class="btn btn-info btn-flat">Go!</button>
                              </span>
                            </div>
                            <!-- /input-group -->
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                        <!-- split buttons box -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Split buttons</h3>
                          </div>
                          <div class="card-body">
                            <!-- Split button -->
                            <p class="mb-1">Normal split buttons:</p>

                            <div class="margin">
                              <div class="btn-group">
                                <button type="button" class="btn btn-default">Action</button>
                                <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-info">Action</button>
                                <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-danger">Action</button>
                                <button type="button" class="btn btn-danger dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-success">Action</button>
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-warning">Action</button>
                                <button type="button" class="btn btn-warning dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                            </div>

                            <!-- flat split buttons -->
                            <p class="mt-3 mb-1">Flat split buttons:</p>

                            <div class="margin">
                              <div class="btn-group">
                                <button type="button" class="btn btn-default btn-flat">Action</button>
                                <button type="button" class="btn btn-default btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-info btn-flat">Action</button>
                                <button type="button" class="btn btn-info btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-danger btn-flat">Action</button>
                                <button type="button" class="btn btn-danger btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-success btn-flat">Action</button>
                                <button type="button" class="btn btn-success btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-warning btn-flat">Action</button>
                                <button type="button" class="btn btn-warning btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                            </div>

                            <!-- Split button -->
                            <p class="mt-3 mb-1">Hoverable split buttons:</p>
                            <div class="margin">
                              <div class="btn-group">
                                <button type="button" class="btn btn-default">Action</button>
                                <button type="button" class="btn btn-default dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-info">Action</button>
                                <button type="button" class="btn btn-info dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-danger">Action</button>
                                <button type="button" class="btn btn-danger dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-success">Action</button>
                                <button type="button" class="btn btn-success dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                              <div class="btn-group">
                                <button type="button" class="btn btn-warning">Action</button>
                                <button type="button" class="btn btn-warning dropdown-toggle dropdown-hover dropdown-icon" data-toggle="dropdown">
                                  <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                  <a class="dropdown-item" href="#">Action</a>
                                  <a class="dropdown-item" href="#">Another action</a>
                                  <a class="dropdown-item" href="#">Something else here</a>
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                              </div>
                            </div>

                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- end split buttons box -->
                      </div>
                      <!-- /.col -->
                      <div class="col-md-6">
                        <!-- Application buttons -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Application Buttons</h3>
                          </div>
                          <div class="card-body">
                            <p>Add the classes <code>.btn.btn-app</code> to an <code>&lt;a></code> tag to achieve the following:</p>
                            <a class="btn btn-app">
                              <i class="fas fa-edit"></i> Edit
                            </a>
                            <a class="btn btn-app">
                              <i class="fas fa-play"></i> Play
                            </a>
                            <a class="btn btn-app">
                              <i class="fas fa-pause"></i> Pause
                            </a>
                            <a class="btn btn-app">
                              <i class="fas fa-save"></i> Save
                            </a>
                            <a class="btn btn-app">
                              <span class="badge bg-warning">3</span>
                              <i class="fas fa-bullhorn"></i> Notifications
                            </a>
                            <a class="btn btn-app">
                              <span class="badge bg-success">300</span>
                              <i class="fas fa-barcode"></i> Products
                            </a>
                            <a class="btn btn-app">
                              <span class="badge bg-purple">891</span>
                              <i class="fas fa-users"></i> Users
                            </a>
                            <a class="btn btn-app">
                              <span class="badge bg-teal">67</span>
                              <i class="fas fa-inbox"></i> Orders
                            </a>
                            <a class="btn btn-app">
                              <span class="badge bg-info">12</span>
                              <i class="fas fa-envelope"></i> Inbox
                            </a>
                            <a class="btn btn-app">
                              <span class="badge bg-danger">531</span>
                              <i class="fas fa-heart"></i> Likes
                            </a>

                            <p>Application Buttons with Custom Colors</p>
                            <a class="btn btn-app bg-secondary">
                              <span class="badge bg-success">300</span>
                              <i class="fas fa-barcode"></i> Products
                            </a>
                            <a class="btn btn-app bg-success">
                              <span class="badge bg-purple">891</span>
                              <i class="fas fa-users"></i> Users
                            </a>
                            <a class="btn btn-app bg-danger">
                              <span class="badge bg-teal">67</span>
                              <i class="fas fa-inbox"></i> Orders
                            </a>
                            <a class="btn btn-app bg-warning">
                              <span class="badge bg-info">12</span>
                              <i class="fas fa-envelope"></i> Inbox
                            </a>
                            <a class="btn btn-app bg-info">
                              <span class="badge bg-danger">531</span>
                              <i class="fas fa-heart"></i> Likes
                            </a>
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->

                        <!-- Vertical grouping -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Vertical Button Group</h3>
                          </div>
                          <div class="card-body table-responsive pad">

                            <p>
                              Vertical button groups are easy to create with bootstrap. Just add your buttons
                              inside <code>&lt;div class="btn-group-vertical"&gt;&lt;/div&gt;</code>
                            </p>

                            <table class="table table-bordered">
                              <tr>
                                <th>Button</th>
                                <th>Icons</th>
                                <th>Flat</th>
                                <th>Dropdown</th>
                              </tr>
                              <!-- Default -->
                              <tr>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-default">Top</button>
                                    <button type="button" class="btn btn-default">Middle</button>
                                    <button type="button" class="btn btn-default">Bottom</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-default">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-default">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-default btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-default">1</button>
                                    <button type="button" class="btn btn-default">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                      </ul>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- ./default -->
                              <!-- Info -->
                              <tr>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-info">Top</button>
                                    <button type="button" class="btn btn-info">Middle</button>
                                    <button type="button" class="btn btn-info">Bottom</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-info">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-info">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-info">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-info btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-info">1</button>
                                    <button type="button" class="btn btn-info">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                      </ul>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /. info -->
                              <!-- /.danger -->
                              <tr>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-danger">Top</button>
                                    <button type="button" class="btn btn-danger">Middle</button>
                                    <button type="button" class="btn btn-danger">Bottom</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-danger">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-danger btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-danger">1</button>
                                    <button type="button" class="btn btn-danger">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                      </ul>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /.danger -->
                              <!-- warning -->
                              <tr>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-warning">Top</button>
                                    <button type="button" class="btn btn-warning">Middle</button>
                                    <button type="button" class="btn btn-warning">Bottom</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-warning">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-warning btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-warning">1</button>
                                    <button type="button" class="btn btn-warning">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                      </ul>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /.warning -->
                              <!-- success -->
                              <tr>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-success">Top</button>
                                    <button type="button" class="btn btn-success">Middle</button>
                                    <button type="button" class="btn btn-success">Bottom</button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-success">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-success">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-success">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-success btn-flat">
                                      <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-flat">
                                      <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-flat">
                                      <i class="fas fa-align-right"></i>
                                    </button>
                                  </div>
                                </td>
                                <td>
                                  <div class="btn-group-vertical">
                                    <button type="button" class="btn btn-success">1</button>
                                    <button type="button" class="btn btn-success">2</button>

                                    <div class="btn-group">
                                      <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                      </button>
                                      <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                        <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                      </ul>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                              <!-- /.success -->
                            </table>
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->

                        <!-- Radio Buttons -->
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Radio Button Group</h3>
                          </div>
                          <div class="card-body table-responsive pad">
                            <p class="mb-1">Radio Button Group with <code>.btn-secondary</code></p>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                              <label class="btn btn-secondary active">
                                <input type="radio" name="options" id="option_a1" autocomplete="off" checked> Active
                              </label>
                              <label class="btn btn-secondary">
                                <input type="radio" name="options" id="option_a2" autocomplete="off"> Radio
                              </label>
                              <label class="btn btn-secondary">
                                <input type="radio" name="options" id="option_a3" autocomplete="off"> Radio
                              </label>
                            </div>

                            <p class="mt-3 mb-1">Radio Button Group with <code>.bg-olive</code></p>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                              <label class="btn bg-olive active">
                                <input type="radio" name="options" id="option_b1" autocomplete="off" checked> Active
                              </label>
                              <label class="btn bg-olive">
                                <input type="radio" name="options" id="option_b2" autocomplete="off"> Radio
                              </label>
                              <label class="btn bg-olive">
                                <input type="radio" name="options" id="option_b3" autocomplete="off"> Radio
                              </label>
                            </div>
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- /. row -->
                  </div><!-- /.container-fluid -->
                </section>
                <!-- -------------------------------- /6 ------------------------------- -->  
                  </div>
                <!-- -------------------------------- 7 ------------------------------- -->
                  <div class="tab-pane fade" id="custom-tabs-five-normal7" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                    <section class="content">
                      <div class="container-fluid">
                        <!-- COLOR PALETTE -->
                        <div class="card card-default color-palette-box">
                          <div class="card-header">
                            <h3 class="card-title">
                              <i class="fas fa-tag"></i>
                              Color Palette
                            </h3>
                          </div>
                          <div class="card-body">
                            <div class="col-12">
                              <h5>Theme Colors</h5>
                            </div>
                            <!-- /.col-12 -->
                            <div class="row">
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Primary</h4>

                                <div class="color-palette-set">
                                  <div class="bg-primary color-palette"><span>#007bff</span></div>
                                  <div class="bg-primary disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Secondary</h4>

                                <div class="color-palette-set">
                                  <div class="bg-secondary color-palette"><span>#6c757d</span></div>
                                  <div class="bg-secondary disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Info</h4>

                                <div class="color-palette-set">
                                  <div class="bg-info color-palette"><span>#17a2b8</span></div>
                                  <div class="bg-info disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Success</h4>

                                <div class="color-palette-set">
                                  <div class="bg-success color-palette"><span>#28a745</span></div>
                                  <div class="bg-success disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-warning">Warning</h4>

                                <div class="color-palette-set">
                                  <div class="bg-warning color-palette"><span>#ffc107</span></div>
                                  <div class="bg-warning disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Danger</h4>

                                <div class="color-palette-set">
                                  <div class="bg-danger color-palette"><span>#dc3545</span></div>
                                  <div class="bg-danger disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->
                            <div class="col-12">
                              <h5 class="mt-3">Black/White Nuances</h5>
                            </div>
                            <!-- /.col-12 -->
                            <div class="row">
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Black</h4>

                                <div class="color-palette-set">
                                  <div class="bg-black color-palette"><span>#000000</span></div>
                                  <div class="bg-black disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Gray Dark</h4>

                                <div class="color-palette-set">
                                  <div class="bg-gray-dark color-palette"><span>#343a40</span></div>
                                  <div class="bg-gray-dark disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center">Gray</h4>

                                <div class="color-palette-set">
                                  <div class="bg-gray color-palette"><span>#adb5bd</span></div>
                                  <div class="bg-gray disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-light">Light</h4>

                                <div class="color-palette-set">
                                  <div class="bg-light color-palette"><span>#1f2d3d</span></div>
                                  <div class="bg-light disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->
                            <div class="col-12">
                              <h5 class="mt-3">Colors</h5>
                            </div>
                            <!-- /.col-12 -->
                            <div class="row">
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-indigo">Indigo</h4>

                                <div class="color-palette-set">
                                  <div class="bg-indigo color-palette"><span>#6610f2</span></div>
                                  <div class="bg-indigo disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-lightblue">Lightblue</h4>

                                <div class="color-palette-set">
                                  <div class="bg-lightblue color-palette"><span>#3c8dbc</span></div>
                                  <div class="bg-lightblue disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-navy">Navy</h4>

                                <div class="color-palette-set">
                                  <div class="bg-navy color-palette"><span>#001f3f</span></div>
                                  <div class="bg-navy disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-purple">Purple</h4>

                                <div class="color-palette-set">
                                  <div class="bg-purple color-palette"><span>#605ca8</span></div>
                                  <div class="bg-purple disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-fuchsia">Fuchsia</h4>

                                <div class="color-palette-set">
                                  <div class="bg-fuchsia color-palette"><span>#f012be</span></div>
                                  <div class="bg-fuchsia disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-pink">Pink</h4>

                                <div class="color-palette-set">
                                  <div class="bg-pink color-palette"><span>#e83e8c</span></div>
                                  <div class="bg-pink disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-maroon">Maroon</h4>

                                <div class="color-palette-set">
                                  <div class="bg-maroon color-palette"><span>#d81b60</span></div>
                                  <div class="bg-maroon disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-orange">Orange</h4>

                                <div class="color-palette-set">
                                  <div class="bg-orange color-palette"><span>#ff851b</span></div>
                                  <div class="bg-orange disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-lime">Lime</h4>

                                <div class="color-palette-set">
                                  <div class="bg-lime color-palette"><span>#01ff70</span></div>
                                  <div class="bg-lime disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-teal">Teal</h4>

                                <div class="color-palette-set">
                                  <div class="bg-teal color-palette"><span>#39cccc</span></div>
                                  <div class="bg-teal disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                              <div class="col-sm-4 col-md-2">
                                <h4 class="text-center bg-olive">Olive</h4>

                                <div class="color-palette-set">
                                  <div class="bg-olive color-palette"><span>#3d9970</span></div>
                                  <div class="bg-olive disabled color-palette"><span>Disabled</span></div>
                                </div>
                              </div>
                              <!-- /.col -->
                            </div>
                            <!-- /.row -->
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                        <!-- START ALERTS AND CALLOUTS -->
                        <h5 class="mt-4 mb-2">Alerts and Callouts</h5>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="card card-default">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-exclamation-triangle"></i>
                                  Alerts
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div class="alert alert-danger alert-dismissible">
                                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                  <h5><i class="icon fas fa-ban"></i> Alert!</h5>
                                  Danger alert preview. This alert is dismissable. A wonderful serenity has taken possession of my
                                  entire
                                  soul, like these sweet mornings of spring which I enjoy with my whole heart.
                                </div>
                                <div class="alert alert-info alert-dismissible">
                                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                  <h5><i class="icon fas fa-info"></i> Alert!</h5>
                                  Info alert preview. This alert is dismissable.
                                </div>
                                <div class="alert alert-warning alert-dismissible">
                                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                  <h5><i class="icon fas fa-exclamation-triangle"></i> Alert!</h5>
                                  Warning alert preview. This alert is dismissable.
                                </div>
                                <div class="alert alert-success alert-dismissible">
                                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                  <h5><i class="icon fas fa-check"></i> Alert!</h5>
                                  Success alert preview. This alert is dismissable.
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->

                          <div class="col-md-6">
                            <div class="card card-default">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-bullhorn"></i>
                                  Callouts
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div class="callout callout-danger">
                                  <h5>I am a danger callout!</h5>

                                  <p>There is a problem that we need to fix. A wonderful serenity has taken possession of my entire
                                    soul,
                                    like these sweet mornings of spring which I enjoy with my whole heart.</p>
                                </div>
                                <div class="callout callout-info">
                                  <h5>I am an info callout!</h5>

                                  <p>Follow the steps to continue to payment.</p>
                                </div>
                                <div class="callout callout-warning">
                                  <h5>I am a warning callout!</h5>

                                  <p>This is a yellow callout.</p>
                                </div>
                                <div class="callout callout-success">
                                  <h5>I am a success callout!</h5>

                                  <p>This is a green callout.</p>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->
                        </div>
                        <!-- /.row -->
                        <!-- END ALERTS AND CALLOUTS -->
                        <h5 class="mt-4 mb-2">Tabs in Cards</h5>

                        <div class="row">
                          <div class="col-12">
                            <!-- Custom Tabs -->
                            <div class="card">
                              <div class="card-header d-flex p-0">
                                <h3 class="card-title p-3">Tabs</h3>
                                <ul class="nav nav-pills ml-auto p-2">
                                  <li class="nav-item"><a class="nav-link active" href="#tab_1" data-toggle="tab">Tab 1</a></li>
                                  <li class="nav-item"><a class="nav-link" href="#tab_2" data-toggle="tab">Tab 2</a></li>
                                  <li class="nav-item"><a class="nav-link" href="#tab_3" data-toggle="tab">Tab 3</a></li>
                                  <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                                      Dropdown <span class="caret"></span>
                                    </a>
                                    <div class="dropdown-menu">
                                      <a class="dropdown-item" tabindex="-1" href="#">Action</a>
                                      <a class="dropdown-item" tabindex="-1" href="#">Another action</a>
                                      <a class="dropdown-item" tabindex="-1" href="#">Something else here</a>
                                      <div class="dropdown-divider"></div>
                                      <a class="dropdown-item" tabindex="-1" href="#">Separated link</a>
                                    </div>
                                  </li>
                                </ul>
                              </div><!-- /.card-header -->
                              <div class="card-body">
                                <div class="tab-content">
                                  <div class="tab-pane active" id="tab_1">
                                    A wonderful serenity has taken possession of my entire soul,
                                    like these sweet mornings of spring which I enjoy with my whole heart.
                                    I am alone, and feel the charm of existence in this spot,
                                    which was created for the bliss of souls like mine. I am so happy,
                                    my dear friend, so absorbed in the exquisite sense of mere tranquil existence,
                                    that I neglect my talents. I should be incapable of drawing a single stroke
                                    at the present moment; and yet I feel that I never was a greater artist than now.
                                  </div>
                                  <!-- /.tab-pane -->
                                  <div class="tab-pane" id="tab_2">
                                    The European languages are members of the same family. Their separate existence is a myth.
                                    For science, music, sport, etc, Europe uses the same vocabulary. The languages only differ
                                    in their grammar, their pronunciation and their most common words. Everyone realizes why a
                                    new common language would be desirable: one could refuse to pay expensive translators. To
                                    achieve this, it would be necessary to have uniform grammar, pronunciation and more common
                                    words. If several languages coalesce, the grammar of the resulting language is more simple
                                    and regular than that of the individual languages.
                                  </div>
                                  <!-- /.tab-pane -->
                                  <div class="tab-pane" id="tab_3">
                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                                    Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                                    when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                                    It has survived not only five centuries, but also the leap into electronic typesetting,
                                    remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset
                                    sheets containing Lorem Ipsum passages, and more recently with desktop publishing software
                                    like Aldus PageMaker including versions of Lorem Ipsum.
                                  </div>
                                  <!-- /.tab-pane -->
                                </div>
                                <!-- /.tab-content -->
                              </div><!-- /.card-body -->
                            </div>
                            <!-- ./card -->
                          </div>
                          <!-- /.col -->
                        </div>
                        <!-- /.row -->
                        <!-- END CUSTOM TABS -->
                        <!-- START PROGRESS BARS -->
                        <h5 class="mt-4 mb-2">Progress Bars</h5>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Progress Bars Different Sizes</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <p><code>.progress</code></p>

                                <div class="progress">
                                  <div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
                                      aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                                    <span class="sr-only">40% Complete (success)</span>
                                  </div>
                                </div>
                                <p><code>.progress-sm</code></p>

                                <div class="progress progress-sm active">
                                  <div class="progress-bar bg-success progress-bar-striped" role="progressbar"
                                      aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                                    <span class="sr-only">20% Complete</span>
                                  </div>
                                </div>
                                <p><code>.progress-xs</code></p>

                                <div class="progress progress-xs">
                                  <div class="progress-bar bg-warning progress-bar-striped" role="progressbar"
                                      aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                    <span class="sr-only">60% Complete (warning)</span>
                                  </div>
                                </div>
                                <p><code>.progress-xxs</code></p>

                                <div class="progress progress-xxs">
                                  <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar"
                                      aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                    <span class="sr-only">60% Complete (warning)</span>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col (left) -->
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Progress bars</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div class="progress mb-3">
                                  <div class="progress-bar bg-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                                      aria-valuemax="100" style="width: 40%">
                                    <span class="sr-only">40% Complete (success)</span>
                                  </div>
                                </div>
                                <div class="progress mb-3">
                                  <div class="progress-bar bg-info" role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                      aria-valuemax="100" style="width: 20%">
                                    <span class="sr-only">20% Complete</span>
                                  </div>
                                </div>
                                <div class="progress mb-3">
                                  <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0"
                                      aria-valuemax="100" style="width: 60%">
                                    <span class="sr-only">60% Complete (warning)</span>
                                  </div>
                                </div>
                                <div class="progress mb-3">
                                  <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0"
                                      aria-valuemax="100" style="width: 80%">
                                    <span class="sr-only">80% Complete</span>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col (right) -->
                        </div>
                        <!-- /.row -->
                        <div class="row">
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Vertical Progress Bars Different Sizes</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body text-center">
                                <p>By adding the class <code>.vertical</code> and <code>.progress-sm</code>, <code>.progress-xs</code>
                                  or
                                  <code>.progress-xxs</code> we achieve:</p>

                                <div class="progress vertical active">
                                  <div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
                                      aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="height: 40%">
                                    <span class="sr-only">40%</span>
                                  </div>
                                </div>
                                <div class="progress vertical progress-sm">
                                  <div class="progress-bar bg-success" role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                      aria-valuemax="100" style="height: 100%">
                                    <span class="sr-only">100%</span>
                                  </div>
                                </div>
                                <div class="progress vertical progress-xs">
                                  <div class="progress-bar bg-warning progress-bar-striped" role="progressbar"
                                      aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="height: 60%">
                                    <span class="sr-only">60%</span>
                                  </div>
                                </div>
                                <div class="progress vertical progress-xxs">
                                  <div class="progress-bar bg-info progress-bar-striped" role="progressbar" aria-valuenow="60"
                                      aria-valuemin="0" aria-valuemax="100" style="height: 60%">
                                    <span class="sr-only">60%</span>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col (left) -->
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Vertical Progress bars</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body text-center">
                                <p>By adding the class <code>.vertical</code> we achieve:</p>

                                <div class="progress vertical">
                                  <div class="progress-bar bg-success" role="progressbar" aria-valuenow="40" aria-valuemin="0"
                                      aria-valuemax="100" style="height: 40%">
                                    <span class="sr-only">40%</span>
                                  </div>
                                </div>
                                <div class="progress vertical">
                                  <div class="progress-bar bg-info" role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                      aria-valuemax="100" style="height: 20%">
                                    <span class="sr-only">20%</span>
                                  </div>
                                </div>
                                <div class="progress vertical">
                                  <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0"
                                      aria-valuemax="100" style="height: 60%">
                                    <span class="sr-only">60%</span>
                                  </div>
                                </div>
                                <div class="progress vertical">
                                  <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0"
                                      aria-valuemax="100" style="height: 80%">
                                    <span class="sr-only">80%</span>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col (right) -->
                        </div>
                        <!-- /.row -->
                        <!-- END PROGRESS BARS -->

                        <!-- START ACCORDION & CAROUSEL-->
                        <h5 class="mt-4 mb-2">Bootstrap Accordion & Carousel</h5>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Collapsible Accordion</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <!-- we are adding the accordion ID so Bootstrap's collapse plugin detects it -->
                                <div id="accordion">
                                  <div class="card card-primary">
                                    <div class="card-header">
                                      <h4 class="card-title w-100">
                                        <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                                          Collapsible Group Item #1
                                        </a>
                                      </h4>
                                    </div>
                                    <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                      <div class="card-body">
                                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid.
                                        3
                                        wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt
                                        laborum
                                        eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee
                                        nulla
                                        assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred
                                        nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft
                                        beer
                                        farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus
                                        labore sustainable VHS.
                                      </div>
                                    </div>
                                  </div>
                                  <div class="card card-danger">
                                    <div class="card-header">
                                      <h4 class="card-title w-100">
                                        <a class="d-block w-100" data-toggle="collapse" href="#collapseTwo">
                                          Collapsible Group Danger
                                        </a>
                                      </h4>
                                    </div>
                                    <div id="collapseTwo" class="collapse" data-parent="#accordion">
                                      <div class="card-body">
                                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid.
                                        3
                                        wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt
                                        laborum
                                        eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee
                                        nulla
                                        assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred
                                        nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft
                                        beer
                                        farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus
                                        labore sustainable VHS.
                                      </div>
                                    </div>
                                  </div>
                                  <div class="card card-success">
                                    <div class="card-header">
                                      <h4 class="card-title w-100">
                                        <a class="d-block w-100" data-toggle="collapse" href="#collapseThree">
                                          Collapsible Group Success
                                        </a>
                                      </h4>
                                    </div>
                                    <div id="collapseThree" class="collapse" data-parent="#accordion">
                                      <div class="card-body">
                                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid.
                                        3
                                        wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt
                                        laborum
                                        eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee
                                        nulla
                                        assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred
                                        nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft
                                        beer
                                        farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus
                                        labore sustainable VHS.
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Carousel</h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                                  <ol class="carousel-indicators">
                                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                                  </ol>
                                  <div class="carousel-inner">
                                    <div class="carousel-item active">
                                      <img class="d-block w-100" src="https://placehold.it/900x500/39CCCC/ffffff&text=I+Love+Bootstrap" alt="First slide">
                                    </div>
                                    <div class="carousel-item">
                                      <img class="d-block w-100" src="https://placehold.it/900x500/3c8dbc/ffffff&text=I+Love+Bootstrap" alt="Second slide">
                                    </div>
                                    <div class="carousel-item">
                                      <img class="d-block w-100" src="https://placehold.it/900x500/f39c12/ffffff&text=I+Love+Bootstrap" alt="Third slide">
                                    </div>
                                  </div>
                                  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                                    <span class="carousel-control-custom-icon" aria-hidden="true">
                                      <i class="fas fa-chevron-left"></i>
                                    </span>
                                    <span class="sr-only">Previous</span>
                                  </a>
                                  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                                    <span class="carousel-control-custom-icon" aria-hidden="true">
                                      <i class="fas fa-chevron-right"></i>
                                    </span>
                                    <span class="sr-only">Next</span>
                                  </a>
                                </div>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- /.col -->
                        </div>
                        <!-- /.row -->
                        <!-- END ACCORDION & CAROUSEL-->

                        <!-- START TYPOGRAPHY -->
                        <h5 class="mt-4 mb-2">Typography</h5>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Headlines
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <h1>h1. Bootstrap heading</h1>

                                <h2>h2. Bootstrap heading</h2>

                                <h3>h3. Bootstrap heading</h3>
                                <h4>h4. Bootstrap heading</h4>
                                <h5>h5. Bootstrap heading</h5>
                                <h6>h6. Bootstrap heading</h6>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Text Emphasis
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <p class="lead">Lead to emphasize importance</p>

                                <p class="text-success">Text green to emphasize success</p>

                                <p class="text-info">Text aqua to emphasize info</p>

                                <p class="text-primary">Text light blue to emphasize info (2)</p>

                                <p class="text-danger">Text red to emphasize danger</p>

                                <p class="text-warning">Text yellow to emphasize warning</p>

                                <p class="text-muted">Text muted to emphasize general</p>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                        </div>
                        <!-- /.row -->

                        <div class="row">
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Primary Block Quotes
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <blockquote>
                                  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                                  <small>Someone famous in <cite title="Source Title">Source Title</cite></small>
                                </blockquote>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Secondary Block Quotes
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body clearfix">
                                <blockquote class="quote-secondary">
                                  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                                  <small>Someone famous in <cite title="Source Title">Source Title</cite></small>
                                </blockquote>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                        </div>
                        <!-- /.row -->

                        <div class="row">
                          <div class="col-md-4">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Unordered List
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <ul>
                                  <li>Lorem ipsum dolor sit amet</li>
                                  <li>Consectetur adipiscing elit</li>
                                  <li>Integer molestie lorem at massa</li>
                                  <li>Facilisis in pretium nisl aliquet</li>
                                  <li>Nulla volutpat aliquam velit
                                    <ul>
                                      <li>Phasellus iaculis neque</li>
                                      <li>Purus sodales ultricies</li>
                                      <li>Vestibulum laoreet porttitor sem</li>
                                      <li>Ac tristique libero volutpat at</li>
                                    </ul>
                                  </li>
                                  <li>Faucibus porta lacus fringilla vel</li>
                                  <li>Aenean sit amet erat nunc</li>
                                  <li>Eget porttitor lorem</li>
                                </ul>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                          <div class="col-md-4">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Ordered Lists
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <ol>
                                  <li>Lorem ipsum dolor sit amet</li>
                                  <li>Consectetur adipiscing elit</li>
                                  <li>Integer molestie lorem at massa</li>
                                  <li>Facilisis in pretium nisl aliquet</li>
                                  <li>Nulla volutpat aliquam velit
                                    <ol>
                                      <li>Phasellus iaculis neque</li>
                                      <li>Purus sodales ultricies</li>
                                      <li>Vestibulum laoreet porttitor sem</li>
                                      <li>Ac tristique libero volutpat at</li>
                                    </ol>
                                  </li>
                                  <li>Faucibus porta lacus fringilla vel</li>
                                  <li>Aenean sit amet erat nunc</li>
                                  <li>Eget porttitor lorem</li>
                                </ol>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                          <div class="col-md-4">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Unstyled List
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <ul class="list-unstyled">
                                  <li>Lorem ipsum dolor sit amet</li>
                                  <li>Consectetur adipiscing elit</li>
                                  <li>Integer molestie lorem at massa</li>
                                  <li>Facilisis in pretium nisl aliquet</li>
                                  <li>Nulla volutpat aliquam velit
                                    <ul>
                                      <li>Phasellus iaculis neque</li>
                                      <li>Purus sodales ultricies</li>
                                      <li>Vestibulum laoreet porttitor sem</li>
                                      <li>Ac tristique libero volutpat at</li>
                                    </ul>
                                  </li>
                                  <li>Faucibus porta lacus fringilla vel</li>
                                  <li>Aenean sit amet erat nunc</li>
                                  <li>Eget porttitor lorem</li>
                                </ul>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                        </div>
                        <!-- /.row -->

                        <div class="row">
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Description
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <dl>
                                  <dt>Description lists</dt>
                                  <dd>A description list is perfect for defining terms.</dd>
                                  <dt>Euismod</dt>
                                  <dd>Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
                                  <dd>Donec id elit non mi porta gravida at eget metus.</dd>
                                  <dt>Malesuada porta</dt>
                                  <dd>Etiam porta sem malesuada magna mollis euismod.</dd>
                                </dl>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                          <div class="col-md-6">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">
                                  <i class="fas fa-text-width"></i>
                                  Description Horizontal
                                </h3>
                              </div>
                              <!-- /.card-header -->
                              <div class="card-body">
                                <dl class="row">
                                  <dt class="col-sm-4">Description lists</dt>
                                  <dd class="col-sm-8">A description list is perfect for defining terms.</dd>
                                  <dt class="col-sm-4">Euismod</dt>
                                  <dd class="col-sm-8">Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
                                  <dd class="col-sm-8 offset-sm-4">Donec id elit non mi porta gravida at eget metus.</dd>
                                  <dt class="col-sm-4">Malesuada porta</dt>
                                  <dd class="col-sm-8">Etiam porta sem malesuada magna mollis euismod.</dd>
                                  <dt class="col-sm-4">Felis euismod semper eget lacinia</dt>
                                  <dd class="col-sm-8">Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo
                                    sit amet risus.
                                  </dd>
                                </dl>
                              </div>
                              <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                          </div>
                          <!-- ./col -->
                        </div>
                        <!-- /.row -->
                        <div class="row">
                          <div class="col-12">
                            <div class="card">
                              <div class="card-header">
                                <h3 class="card-title">Pagination Month</h3>
                              </div>
                              <div class="card-body">
                                <ul class="pagination pagination-month justify-content-center">
                                  <li class="page-item"><a class="page-link" href="#">«</a></li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Jan</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item active">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Feb</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Mar</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Apr</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">May</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Jun</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Jul</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Aug</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Sep</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Oct</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Nov</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item">
                                      <a class="page-link" href="#">
                                          <p class="page-month">Dec</p>
                                          <p class="page-year">2021</p>
                                      </a>
                                  </li>
                                  <li class="page-item"><a class="page-link" href="#">»</a></li>
                                </ul>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- /.row -->
                        <!-- END TYPOGRAPHY -->
                      </div><!-- /.container-fluid -->
                    </section>
                  </div>
                <!-- -------------------------------- /7 ------------------------------- -->
                <!-- -------------------------------- 8 ------------------------------- -->
                  <div class="tab-pane fade" id="custom-tabs-five-normal8" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                  
                  </div>
                <!-- -------------------------------- /8 ------------------------------- -->
                <!-- -------------------------------- 9 ------------------------------- -->
                  <div class="tab-pane fade" id="custom-tabs-five-normal9" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                  
                  </div>
                <!-- -------------------------------- /9 ------------------------------- -->
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>
        <!-- ./row -->
        <div class="row">
          <div class="col-12">
            <h4>Nav Tabs inside Card Header <small>card-tabs / card-outline-tabs</small></h4>
          </div>
        </div>
        <div class="row">
          <div class="col-12 col-sm-6">
            <div class="card card-primary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home" aria-selected="true">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab" aria-controls="custom-tabs-one-profile" aria-selected="false">Profile</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-messages-tab" data-toggle="pill" href="#custom-tabs-one-messages" role="tab" aria-controls="custom-tabs-one-messages" aria-selected="false">Messages</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-settings-tab" data-toggle="pill" href="#custom-tabs-one-settings" role="tab" aria-controls="custom-tabs-one-settings" aria-selected="false">Settings</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-one-tabContent">
                  <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel" aria-labelledby="custom-tabs-one-home-tab">
                     Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel" aria-labelledby="custom-tabs-one-profile-tab">
                     Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-messages" role="tabpanel" aria-labelledby="custom-tabs-one-messages-tab">
                     Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-settings" role="tabpanel" aria-labelledby="custom-tabs-one-settings-tab">
                     Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="card card-primary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                  <li class="pt-2 px-3"><h3 class="card-title">Card Title</h3></li>
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill" href="#custom-tabs-two-home" role="tab" aria-controls="custom-tabs-two-home" aria-selected="true">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill" href="#custom-tabs-two-profile" role="tab" aria-controls="custom-tabs-two-profile" aria-selected="false">Profile</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-two-messages-tab" data-toggle="pill" href="#custom-tabs-two-messages" role="tab" aria-controls="custom-tabs-two-messages" aria-selected="false">Messages</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-two-settings-tab" data-toggle="pill" href="#custom-tabs-two-settings" role="tab" aria-controls="custom-tabs-two-settings" aria-selected="false">Settings</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                  <div class="tab-pane fade show active" id="custom-tabs-two-home" role="tabpanel" aria-labelledby="custom-tabs-two-home-tab">
                     Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-two-profile" role="tabpanel" aria-labelledby="custom-tabs-two-profile-tab">
                     Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-two-messages" role="tabpanel" aria-labelledby="custom-tabs-two-messages-tab">
                     Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-two-settings" role="tabpanel" aria-labelledby="custom-tabs-two-settings-tab">
                     Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12 col-sm-6">
            <div class="card card-primary card-outline card-tabs">
              <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-three-home-tab" data-toggle="pill" href="#custom-tabs-three-home" role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill" href="#custom-tabs-three-profile" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">Profile</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-messages-tab" data-toggle="pill" href="#custom-tabs-three-messages" role="tab" aria-controls="custom-tabs-three-messages" aria-selected="false">Messages</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-settings-tab" data-toggle="pill" href="#custom-tabs-three-settings" role="tab" aria-controls="custom-tabs-three-settings" aria-selected="false">Settings</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-three-tabContent">
                  <div class="tab-pane fade show active" id="custom-tabs-three-home" role="tabpanel" aria-labelledby="custom-tabs-three-home-tab">
                     Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-profile" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab">
                     Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-messages" role="tabpanel" aria-labelledby="custom-tabs-three-messages-tab">
                     Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-settings" role="tabpanel" aria-labelledby="custom-tabs-three-settings-tab">
                     Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="card card-primary card-outline card-outline-tabs">
              <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-four-home-tab" data-toggle="pill" href="#custom-tabs-four-home" role="tab" aria-controls="custom-tabs-four-home" aria-selected="true">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-four-profile-tab" data-toggle="pill" href="#custom-tabs-four-profile" role="tab" aria-controls="custom-tabs-four-profile" aria-selected="false">Profile</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-four-messages-tab" data-toggle="pill" href="#custom-tabs-four-messages" role="tab" aria-controls="custom-tabs-four-messages" aria-selected="false">Messages</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-four-settings-tab" data-toggle="pill" href="#custom-tabs-four-settings" role="tab" aria-controls="custom-tabs-four-settings" aria-selected="false">Settings</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                  <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel" aria-labelledby="custom-tabs-four-home-tab">
                     Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-four-profile" role="tabpanel" aria-labelledby="custom-tabs-four-profile-tab">
                     Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-four-messages" role="tabpanel" aria-labelledby="custom-tabs-four-messages-tab">
                     Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-four-settings" role="tabpanel" aria-labelledby="custom-tabs-four-settings-tab">
                     Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>
        <!-- /.row -->
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-edit"></i>
              Vertical Tabs Examples
            </h3>
          </div>
          <div class="card-body">
            <h4>Left Sided</h4>
            <div class="row">
              <div class="col-5 col-sm-3">
                <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                  <a class="nav-link active" id="vert-tabs-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab" aria-controls="vert-tabs-home" aria-selected="true">Home</a>
                  <a class="nav-link" id="vert-tabs-profile-tab" data-toggle="pill" href="#vert-tabs-profile" role="tab" aria-controls="vert-tabs-profile" aria-selected="false">Profile</a>
                  <a class="nav-link" id="vert-tabs-messages-tab" data-toggle="pill" href="#vert-tabs-messages" role="tab" aria-controls="vert-tabs-messages" aria-selected="false">Messages</a>
                  <a class="nav-link" id="vert-tabs-settings-tab" data-toggle="pill" href="#vert-tabs-settings" role="tab" aria-controls="vert-tabs-settings" aria-selected="false">Settings</a>
                </div>
              </div>
              <div class="col-7 col-sm-9">
                <div class="tab-content" id="vert-tabs-tabContent">
                  <div class="tab-pane text-left fade show active" id="vert-tabs-home" role="tabpanel" aria-labelledby="vert-tabs-home-tab">
                     Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                  </div>
                  <div class="tab-pane fade" id="vert-tabs-profile" role="tabpanel" aria-labelledby="vert-tabs-profile-tab">
                     Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                  </div>
                  <div class="tab-pane fade" id="vert-tabs-messages" role="tabpanel" aria-labelledby="vert-tabs-messages-tab">
                     Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
                  </div>
                  <div class="tab-pane fade" id="vert-tabs-settings" role="tabpanel" aria-labelledby="vert-tabs-settings-tab">
                     Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                  </div>
                </div>
              </div>
            </div>
            <h4 class="mt-4">Right Sided <small>(nav-tabs-right)</small></h4>
            <div class="row">
              <div class="col-7 col-sm-9">
                <div class="tab-content" id="vert-tabs-right-tabContent">
                  <div class="tab-pane fade show active" id="vert-tabs-right-home" role="tabpanel" aria-labelledby="vert-tabs-right-home-tab">
                     Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                  </div>
                  <div class="tab-pane fade" id="vert-tabs-right-profile" role="tabpanel" aria-labelledby="vert-tabs-right-profile-tab">
                     Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                  </div>
                  <div class="tab-pane fade" id="vert-tabs-right-messages" role="tabpanel" aria-labelledby="vert-tabs-right-messages-tab">
                     Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
                  </div>
                  <div class="tab-pane fade" id="vert-tabs-right-settings" role="tabpanel" aria-labelledby="vert-tabs-right-settings-tab">
                     Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
                  </div>
                </div>
              </div>
              <div class="col-5 col-sm-3">
                <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                  <a class="nav-link active" id="vert-tabs-right-home-tab" data-toggle="pill" href="#vert-tabs-right-home" role="tab" aria-controls="vert-tabs-right-home" aria-selected="true">Home</a>
                  <a class="nav-link" id="vert-tabs-right-profile-tab" data-toggle="pill" href="#vert-tabs-right-profile" role="tab" aria-controls="vert-tabs-right-profile" aria-selected="false">Profile</a>
                  <a class="nav-link" id="vert-tabs-right-messages-tab" data-toggle="pill" href="#vert-tabs-right-messages" role="tab" aria-controls="vert-tabs-right-messages" aria-selected="false">Messages</a>
                  <a class="nav-link" id="vert-tabs-right-settings-tab" data-toggle="pill" href="#vert-tabs-right-settings" role="tab" aria-controls="vert-tabs-right-settings" aria-selected="false">Settings</a>
                </div>
              </div>
            </div>
          </div>
          <!-- /.card -->
        </div>
        <!-- /.card -->
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-edit"></i>
              Tabs Custom Content Examples
            </h3>
          </div>
          <div class="card-body">
            <h4>Custom Content Below</h4>
            <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="custom-content-below-home-tab" data-toggle="pill" href="#custom-content-below-home" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="custom-content-below-profile-tab" data-toggle="pill" href="#custom-content-below-profile" role="tab" aria-controls="custom-content-below-profile" aria-selected="false">Profile</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="custom-content-below-messages-tab" data-toggle="pill" href="#custom-content-below-messages" role="tab" aria-controls="custom-content-below-messages" aria-selected="false">Messages</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="custom-content-below-settings-tab" data-toggle="pill" href="#custom-content-below-settings" role="tab" aria-controls="custom-content-below-settings" aria-selected="false">Settings</a>
              </li>
            </ul>
            <div class="tab-content" id="custom-content-below-tabContent">
              <div class="tab-pane fade show active" id="custom-content-below-home" role="tabpanel" aria-labelledby="custom-content-below-home-tab">
                 Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
              </div>
              <div class="tab-pane fade" id="custom-content-below-profile" role="tabpanel" aria-labelledby="custom-content-below-profile-tab">
                 Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
              </div>
              <div class="tab-pane fade" id="custom-content-below-messages" role="tabpanel" aria-labelledby="custom-content-below-messages-tab">
                 Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
              </div>
              <div class="tab-pane fade" id="custom-content-below-settings" role="tabpanel" aria-labelledby="custom-content-below-settings-tab">
                 Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
              </div>
            </div>
            <div class="tab-custom-content">
              <p class="lead mb-0">Custom Content goes here</p>
            </div>
            <h4 class="mt-5 ">Custom Content Above</h4>
            <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="custom-content-above-home-tab" data-toggle="pill" href="#custom-content-above-home" role="tab" aria-controls="custom-content-above-home" aria-selected="true">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="custom-content-above-profile-tab" data-toggle="pill" href="#custom-content-above-profile" role="tab" aria-controls="custom-content-above-profile" aria-selected="false">Profile</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="custom-content-above-messages-tab" data-toggle="pill" href="#custom-content-above-messages" role="tab" aria-controls="custom-content-above-messages" aria-selected="false">Messages</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="custom-content-above-settings-tab" data-toggle="pill" href="#custom-content-above-settings" role="tab" aria-controls="custom-content-above-settings" aria-selected="false">Settings</a>
              </li>
            </ul>
            <div class="tab-custom-content">
              <p class="lead mb-0">Custom Content goes here</p>
            </div>
            <div class="tab-content" id="custom-content-above-tabContent">
              <div class="tab-pane fade show active" id="custom-content-above-home" role="tabpanel" aria-labelledby="custom-content-above-home-tab">
                 Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
              </div>
              <div class="tab-pane fade" id="custom-content-above-profile" role="tabpanel" aria-labelledby="custom-content-above-profile-tab">
                 Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
              </div>
              <div class="tab-pane fade" id="custom-content-above-messages" role="tabpanel" aria-labelledby="custom-content-above-messages-tab">
                 Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat augue id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem, ac tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt eleifend ac ornare magna.
              </div>
              <div class="tab-pane fade" id="custom-content-above-settings" role="tabpanel" aria-labelledby="custom-content-above-settings-tab">
                 Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus turpis ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate. Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst. Praesent imperdiet accumsan ex sit amet facilisis.
              </div>
            </div>
          </div>
          <!-- /.card -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
    
      <!-- /主要內容區域 ------------------------------------ -->
    </div>
    <!-- /頁內全頁內容 -->

    <!-- jQuery -->
    <script src="../pluginFiles/jquery/jquery.min.js"></script>

    <!-- 右側功能控制區 -->
      <aside class="control-sidebar control-sidebar-dark">
        <!-- 功能區內容 ------------------------------------ -->

        <!-- /功能區內容 ----------------------------------- -->
      </aside>
    <!-- /右側功能控制區 -->
  </div>
  <!-- /全頁框 -->
<!-- ------------------------ Scripts Link ------------------------ -->
<!-- jQuery is in front of footer file. -->
<!-- Bootstrap 4 -->
<!-- <script src="../pluginFiles/bootstrap/js/bootstrap.bundle.min.js"></script> -->
<!-- Bootstrap 5 -->
<script src="../pluginFiles/bootstrap5/bootstrap.bundle.min.js"></script>
<!-- Ion Slider -->
<script src="../../_1/pluginFiles/ion-rangeslider/js/ion.rangeSlider.min.js"></script>
<!-- Bootstrap slider -->
<script src="../../_1/pluginFiles/bootstrap-slider/bootstrap-slider.min.js"></script>
<!-- Select2 -->
<script src="../../_1/pluginFiles/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="../../_1/pluginFiles/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask 日期輸入元件JS -->
<script src="../pluginFiles/moment/moment.min.js"></script>
<script src="../../_1/pluginFiles/inputmask/jquery.inputmask.min.js"></script>
<!-- date-range-picker -->
<script src="../pluginFiles/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="../../_1/pluginFiles/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../pluginFiles/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Bootstrap Switch -->
<script src="../../_1/pluginFiles/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="../../_1/pluginFiles/bs-stepper/js/bs-stepper.min.js"></script>
<!-- dropzonejs -->
<script src="../../_1/pluginFiles/dropzone/min/dropzone.min.js"></script>
<!-- DataTables2 & Plugins -->
<!--
<script src="../../_1/pluginFiles/datatables/jquery.dataTables.min.js"></script>
<script src="../../_1/pluginFiles/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../_1/pluginFiles/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../_1/pluginFiles/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../_1/pluginFiles/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../_1/pluginFiles/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../_1/pluginFiles/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../_1/pluginFiles/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../_1/pluginFiles/datatables-buttons/js/buttons.colVis.min.js"></script>
-->
<script src="../../_1/pluginFiles/jszip/jszip.min.js"></script>
<script src="../../_1/pluginFiles/pdfmake/pdfmake.min.js"></script>
<script src="../../_1/pluginFiles/pdfmake/vfs_fonts.js"></script>
<!-- DataTables3 & Plugins -->
<script src="../pluginFiles/datatables3/datatables.min.js"></script>

<!-- 5SeasonLife App -->
<script src="../scriptFiles/5sladmin.js"></script>
<script src="../scriptFiles/page.basic.js"></script>
<script src="../scriptFiles/iframe-state-keeper.js"></script>
<script src="../scriptFiles/demo.js"></script>
<!-- ------------------------ /Scripts Link ------------------------ --></body>
<script>
////////////////////////////////////////////////////////////////
// ---------------- jQuery FUNCTIONs 區域調用 ---------------- //
// ---------------------------------------------------------- //
////////////////////////////////////////////////////////////////
  $(document).ready(function() {
    //* iFrame 頁面中安全滯留倒數功能 scripts
    $('body').on('keypress click', function(){
      let timerElm = window.parent.$('#timer_on_exhausted');
      if( timerElm.length ){
        timerElm.backward_timer('reset');
      }
    });
    //------------------------------------------------------
    /* BOOTSTRAP SLIDER */
    $('.slider').bootstrapSlider()

    /* ION SLIDER */
    $('#range_1').ionRangeSlider({
      min     : 0,
      max     : 5000,
      from    : 1000,
      to      : 4000,
      type    : 'double',
      step    : 1,
      prefix  : '$',
      prettify: false,
      hasGrid : true
    })
    $('#range_2').ionRangeSlider()

    $('#range_5').ionRangeSlider({
      min     : 0,
      max     : 10,
      type    : 'single',
      step    : 0.1,
      postfix : ' mm',
      prettify: false,
      hasGrid : true
    })
    $('#range_6').ionRangeSlider({
      min     : -50,
      max     : 50,
      from    : 0,
      type    : 'single',
      step    : 1,
      postfix : '°',
      prettify: false,
      hasGrid : true
    })

    $('#range_4').ionRangeSlider({
      type      : 'single',
      step      : 100,
      postfix   : ' light years',
      from      : 55000,
      hideMinMax: true,
      hideFromTo: false
    })
    $('#range_3').ionRangeSlider({
      type    : 'double',
      postfix : ' miles',
      step    : 10000,
      from    : 25000000,
      to      : 35000000,
      onChange: function (obj) {
        var t = ''
        for (var prop in obj) {
          t += prop + ': ' + obj[prop] + '\r\n'
        }
        $('#result').html(t)
      },
      onLoad  : function (obj) {
        //
      }
    })
      
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
    //Datemask2 mm/dd/yyyy
    $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
    //Money Euro
    $('[data-mask]').inputmask()

    //Date picker
    $('#reservationdate').datetimepicker({
        format: 'L'
    });

    //Date and time picker
    $('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });

    //Date range picker
    $('#reservation').daterangepicker()
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'MM/DD/YYYY hh:mm A'
      }
    })
    //Date range as a button
    $('#daterange-btn').daterangepicker(
      {
        ranges   : {
          'Today'       : [moment(), moment()],
          'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month'  : [moment().startOf('month'), moment().endOf('month')],
          'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate  : moment()
      },
      function (start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
      }
    )

    //Timepicker
    $('#timepicker').datetimepicker({
      format: 'LT'
    })

    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()

    $('.my-colorpicker2').on('colorpickerChange', function(event) {
      $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
    })

    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    })
      
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
    
  })
    
  document.addEventListener('DOMContentLoaded', function () {
    window.stepper = new Stepper(document.querySelector('.bs-stepper'))
  })

  // DropzoneJS Demo Code Start
  Dropzone.autoDiscover = false

  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNode = document.querySelector("#template")
  previewNode.id = ""
  var previewTemplate = previewNode.parentNode.innerHTML
  previewNode.parentNode.removeChild(previewNode)

  var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
    url: "/target-url", // Set the url
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
    previewTemplate: previewTemplate,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#previews", // Define the container to display the previews
    clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
  })

  myDropzone.on("addedfile", function(file) {
    // Hookup the start button
    file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file) }
  })

  // Update the total progress bar
  myDropzone.on("totaluploadprogress", function(progress) {
    document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
  })

  myDropzone.on("sending", function(file) {
    // Show the total progress bar when upload starts
    document.querySelector("#total-progress").style.opacity = "1"
    // And disable the start button
    file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
  })

  // Hide the total progress bar when nothing's uploading anymore
  myDropzone.on("queuecomplete", function(progress) {
    document.querySelector("#total-progress").style.opacity = "0"
  })

  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions .start").onclick = function() {
    myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
  }
  document.querySelector("#actions .cancel").onclick = function() {
    myDropzone.removeAllFiles(true)
  }
  // DropzoneJS Demo Code End

</script>
</html>
