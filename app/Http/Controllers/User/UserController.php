<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function registerForm()
    {
        return view('page.register');
    }

    public function register(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = User::add($request->all());
        $user->generatePassword($request->get('password'));
        return redirect('/login');
    }

    public function loginForm()
    {
        return view('page.login');
    }

    public function login(Request $request)
    {
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt([
            'email' => $request->get('email'),
            'password' => $request->get('password'),
        ]))
        {
            return redirect('/');
        }
        else
        {
            return redirect()->back()->with(
                'message', 'Вы ввели не правильный логин или пароль!'
            );
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('page.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'name' => 'nullable|required',
            'email' => [
                'nullable',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => 'nullable|image',
        ]);

        $user->edit($request->all());
        $user->uploadAvatar($request->file('avatar'));

        return redirect('/profile');
    }

    public function viewProfile($id)
    {
        $user = User::where('id', $id)->firstOrFail();
        return view('page.user-profile', compact('user'));
    }
}
