<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            session()->put('admin_id', $admin->id);
            session()->put('admin_username', $admin->username);
            session()->put('admin_email', $admin->email);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'redirect' => route('admin.dashboard')]);
            }
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, ' . $admin->username . '!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid username or password.'], 422);
        }

        return back()->withErrors(['username' => 'Invalid credentials.'])->withInput($request->only('username'));
    }

    public function logout()
    {
        session()->forget(['admin_id', 'admin_username', 'admin_email']);
        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }
}
