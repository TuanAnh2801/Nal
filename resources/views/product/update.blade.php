
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>AdminLTE 2 | Dashboard</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/css/all.min.css">
    <link rel="stylesheet" href="/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/css/style.css">

    <link rel="stylesheet" href="/css/_all-skins.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <!-- Logo -->
        <a href="index2.html" class="logo">

            <span class="logo-mini"><b>A</b>LT</span>

            <span class="logo-lg"><b>Menshop</b></span>
        </a>

        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <i class="fa fa-bars"></i>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="" class="user-image" alt="User Image">
                            <span class="hidden-xs"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="" class="img-circle" alt="User Image">

                                <p>


                                    <small>Thành viên từ năm </small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="" class="btn btn-default btn-flat">Sign
                                        out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">

        <section class="sidebar">
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">LAOYOUT ADMIN</li>

                <li>
                    <a href="">
                        <i class="fa fa-th"></i> <span>Quản lý danh mục</span>
                        <span class="pull-right-container">
            </span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fa fa-code"></i> <span>Quản lý sản phẩm</span>
                        <span class="pull-right-container">
            </span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fa fa-user"></i> <span>Quản lý user</span>
                        <span class="pull-right-container">
            </span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fas fa-info"></i> <span>Quản lý chi tiết sản phẩm</span>
                        <span class="pull-right-container">
            </span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fas fa-shopping-cart"></i> <span>Quản lý chi tiết sản phẩm</span>
                        <span class="pull-right-container">
            </span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="breadcrumb-wrap content-wrap content-wrapper">
    </div>



    <div class="content-wrapper">

        <section class="content">
            @foreach($product_alone as $product_alone)

            <h2>Cập nhật sản phẩm</h2>
            <form action="{{route('product_update',$product_alone->id)}}" method="post" enctype="multipart/form-data">
     @csrf
                <div class="form-group">
                    <label for="title">Nhập tên sản phẩm</label>
                    <input type="text" name="title"
                           value="{{$product_alone->title}}"
                           class="form-control" id="title"/>
                </div>
                <div class="form-group">
                    <label for="avatar">Ảnh đại diện</label>
                    <input type="file" name="avatar" value="" class="form-control" id="avatar"/>
                    <img src="#" id="img-preview" style="display: none" width="100" height="100"/>

                    <img height="80" src="{{asset('images/'.$product_alone->avatar)}}"/>

                </div>
                <div class="form-group">
                    <label for="price">Giá</label>
                    <input type="number" name="price"
                           value="{{$product_alone->price}}"
                           class="form-control" id="price"/>
                </div>
                <div class="form-group">
                    <label for="price">Discount</label>
                    <input type="number" name="discount"
                           value=""
                           class="form-control" id="price"/>
                </div>
                <div class="form-group">
                    <label for="amount">Số lượng</label>
                    <input type="number" name="amount"
                           value="{{$product_alone->amount}}"
                           class="form-control" id="amount"/>
                </div>
                <div class="form-group">
                    <label for="summary">Mô tả ngắn sản phẩm</label>
                    <textarea name="summary" id="summary"
                              class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="description">Mô tả chi tiết sản phẩm</label>
                    <textarea name="content" id="seo_description"
                              class="form-control" placeholder=""></textarea>
                </div>

                <div class="form-group">
                    <label for="seo-title">Seo title</label>
                    <input type="text" name="seo_title" value=""
                           class="form-control" id="seo-title"/>
                </div>
                <div class="form-group">
                    <label for="seo-description">Seo description</label>
                    <input type="text" name="description" value="{{$product_alone->description}}"
                           class="form-control" id="seo-description"/>
                </div>

                <div class="form-group">
                    <label for="seo-keywords">Seo keywords</label>
                    <input type="text" name="seo_keywords" value=""
                           class="form-control" id="seo-keywords"/>
                </div>

                <div class="form-group">
                    <input type="submit" name="submit" value="Save" class="btn btn-primary"/>
                    <a href="" class="btn btn-default">Back</a>
                </div>
            </form>
            @endforeach

        </section>

    </div>


    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 2.4.13-pre
        </div>
        <strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE</a>.</strong> All rights
        reserved.
    </footer>
    <div class="control-sidebar-bg"></div>


</div>


<!-- jQuery 3 -->
<script src="assets/js/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="assets/js/jquery-ui.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="assets/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/js/adminlte.min.js"></script>
<!--CKEditor -->
<script src="assets/ckeditor/ckeditor.js"></script>
<!--My SCRIPT-->
<script src="assets/js/script.js"></script>
</body>
</html>
