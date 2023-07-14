
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
    <link rel="stylesheet" href="/css/  style.css">

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
                                    <a href="index.php?controller=user&action=logout" class="btn btn-default btn-flat">Sign
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

    <div class="content-wrapper">

        <section class="content">
{{--        @if(\Illuminate\Support\Facades\Session::get('success'))--}}
        <div class="alert alert-success">
            <span>{{session('success')}}</span>
        </div>
            <div class="alert alert-error">
                <span>{{session('error')}}</span>
            </div>
{{--            @endif--}}


            <h2>Danh sách sản phẩm</h2>
            <a href="{{route('product_create')}}" class="btn btn-success">
                <i class="fa fa-plus"></i> Thêm mới
            </a>
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <th>Category name</th>
                    <th>Title</th>
                    <th>Avatar</th>
                    <th>Price</th>
                    <th>Content</th>
                    <th>Description</th>
                    <th>Amount</th>

                </tr>
                @foreach($products as $product)
                <tr>
                    <td>{{$product->id}}</td>
                    <td>{{$product->name}}</td>
                    <td>{{$product->title}}</td>
                    <td>
                        <img height="80px" src="{{asset('uploads'.'/'.date('Y').'/'.date('m').'/'.date('d').'/'.$product->avatar) }}"/>

                    </td>
                    <td>{{$product->price}}</td>
                    <td>{{$product->content}}</td>
                    <td>{{$product->description}}</td>
                    <td>{{$product->amount}}</td>
                    <td>
                        <a title="Update" href="{{route('product_edit',$product->id)}}"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a title="Xóa" href="{{route('product_delete',$product->id)}}" onclick="return confirm('Are you sure delete?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>


                @endforeach
            </table>

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
