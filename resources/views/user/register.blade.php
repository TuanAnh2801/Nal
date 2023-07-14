<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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

</body>

</html>
<div class="login">
    <img src=" " >
</div>
<div class="container" style="max-width: 500px">
    <form action="{{route('register')}}" method="post" enctype="multipart/form-data">
        @csrf
        <h2>Form register</h2>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" value="" id="username" class="form-control"/>
            <span style="color: red">@error('username'){{$message}} @enderror</span>
        </div>
        <div class="form-group">
            <label for="password">Email</label>
            <input type="email" name="email" value="" id="password" class="form-control"/>
            <span style="color: red">@error('emails'){{$message}} @enderror</span>

        </div>
        <div class="form-group">
            <label for="password-confirm">Password</label>
            <input type="password" name="password" value="" id="password-confirm" class="form-control"/>
            <span style="color: red">@error('password'){{$message}} @enderror</span>

        </div>
        <div class="form-group">
            <input type="submit" name="submit" value="Đăng ký" class="btn btn-primary"/>
            <p>
                Đã có tài khoản, <a href="{{route('login.form')}}">Đăng nhập ngay</a>
            </p>
        </div>
    </form>
</div>
