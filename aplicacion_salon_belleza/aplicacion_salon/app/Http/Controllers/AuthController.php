<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'nombre'   => $request->nombre,
            'apellido' => $request->apellido,
            'email'    => $request->email,
            'password' => $request->password, // El modelo User tiene el cast 'hashed'
            'FK_rol'   => 2, // Cliente
        ]);

        Auth::login($user);

        return redirect('/cliente')->with('success', 'Cuenta creada con éxito. ¡Bienvenido ' . $user->nombre . '!');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            if ($user->FK_rol == 1) {
                return redirect('/admin')->with('success', 'Bienvenido administrador ' . $user->nombre);
            }

            if ($user->FK_rol == 2) {
                return redirect('/cliente')->with('success', 'Bienvenido ' . $user->nombre);
            }

            return redirect('/')->with('success', 'Bienvenido ' . $user->nombre);
        }

        return back()->with('error', 'Credenciales incorrectas');
    }
}