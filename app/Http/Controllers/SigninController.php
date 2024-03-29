<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Hash;
use Session;

class SigninController extends Controller
{
    protected $redirectTo = '/home';
    
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index(){
        return view('auth.login');
    }
    public function form()
    {
        return view('auth.login');
    }

    public function attempt(Request $request)
    {
        /*$this->validate($request, [
            'email' => 'email|exists:users,email',
            'password' => 'required',
        ]);*/

        $user = $request->input('user_email');
        $pass = $request->input('user_password');
        // var_dump(Hash::make('sehati'));die;


        $users = DB::table('kka_dab.mst_user')->where(['user_email'=> $user])->first();

        if($users==''){

            return redirect('/')->with('failed','Login gagal');

        } else
            // var_dump(Hash::check($pass, $users->user_password));die;
            if($users->user_email == $user AND Hash::check($pass, $users->user_password) ){

                Session::put('login', 'Selamat anda berhasil login');
                return redirect('/home');

            } else {

                return redirect('/')->with('failed','Login gagal');

            }

    }
}
