<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
    public function loginForm(){
        return view('user.login');
    }
    public function checkLogin(Request $request){
        if ($request->session()->exists('user')){

            return redirect()->route('home');
        }else{
            return redirect()->route('login.form');
        }
    }
    public function login(Request $request){

        $request->validate([
           'username'=>'required',
            'password'=>'required|max:10'
        ]);

        $user = [
            'username'=>$request->username,
            'password'=>$request->password
        ];
        if (Auth::attempt($user)){
            session(['user',$user]);
            return redirect()->route('home');
        }
        else{

            return redirect()->route('login.form')->with('error','Sai tên hoặc mật khẩu');

        }
    }
    public function registerForm(){
        return view('user.register');
    }
    public function register(Request $request){

        $request->validate([
            'emails'=> 'required|emails',
            'password'=>'required|max:10'
        ]);

      $register = User::create([
          'emails'=> $request->email,
          'password'=> Hash::make($request->password)
      ]);
      if ($register){
          return redirect()->route('login.form');
      }
      else{

          return redirect()->route('register.form');
      }
    }
}
