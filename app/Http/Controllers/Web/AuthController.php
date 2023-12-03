<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    protected $redirectPath = 'route("dashboard")';
    public function login(Request $request){
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|exists:users,email',
                'password' => 'required',
            ]);
            if($validator->fails()){
                return redirect()->back()->withErrors($validator)->withInput(); 
            }
            $validated = $validator->validate();
            if(Auth::attempt($validated, $request['remember-me'])){
               return redirect(route('dashboard'));
            }else{
                return redirect()->back()->withErrors(['message' => 'Wrong email or password! Please try again']);
            }
        }
        return view('auth.login');
    }

    public function register(Request $request){
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'name'      => 'required|string|unique:users,name',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|confirmed',
            ]);
            if($validator->fails()){
                return redirect()->back()->withErrors($validator)->withInput(); 
            }
            $validated = $validator->validate();
            $user = User::create($validated);

            Auth::login($user);

            return redirect(route('dashboard'));
        }
        return view('auth.register');
    }

    public function logout(){
        Auth::logout();
        return redirect(route('login'));
    }
}
