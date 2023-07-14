<!DOCTYPE html>
<html>
<head>
    <title>Title of the document</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/css/all.min.css">
    <link rel="stylesheet" href="/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/css/style.css">

    <link rel="stylesheet" href="/css/_all-skins.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<div class="login">
    <img src=" " >
</div>

<div class="container" style="max-width: 500px">
    <div class="alert alert-error" role="alert">{{session('error')}}</div>
    <form method="post" action="{{route('login')}}">
        @csrf
        <h2>Form login</h2>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" value="" id="username" class="form-control"/>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" value="" id="password" class="form-control"/>
        </div>
        <div class="form-group">
            <input type="submit" name="submit" value="Đăng nhập" class="btn btn-primary" id="btn-primary"/>
            <p>
                Chưa có tài khoản, <a href="{{route('register.form')}}">Đăng ký</a>
            </p>
        </div>
    </form>
</div>

<script>
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
</script>
</body>

</html>

