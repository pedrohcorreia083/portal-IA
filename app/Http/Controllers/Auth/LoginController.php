<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // Para onde redirecionar após o login
    protected $redirectTo = '/dashboard';

    // Para onde redirecionar após logout
    protected $redirectAfterLogout = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->redirectTo = route('dashboard');
    }

    // Se quiser personalizar a view de login
    public function showLoginForm()
    {
        return view('auth.login');
    }
}
