<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check() || session()->has('customer_id')) {
            return redirect()->route('account');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::guard('web')->check() || session()->has('customer_id')) {
            return redirect()->route('account');
        }
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])
            ->orWhere('phone', $credentials['email'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            session()->put('customer_id', $user->id);
            session()->put('customer_name', $user->name);
            session()->put('customer_phone', $user->phone);
            session()->put('customer_email', $user->email);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'redirect' => route('account')]);
            }
            return redirect()->route('account')->with('success', 'Welcome back, ' . $user->name . '!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid email/phone or password.'], 422);
        }

        return back()->withErrors(['email' => 'Invalid login details.'])->withInput($request->only('email'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        session()->put('customer_id', $user->id);
        session()->put('customer_name', $user->name);
        session()->put('customer_phone', $user->phone);
        session()->put('customer_email', $user->email);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'redirect' => route('account')]);
        }

        return redirect()->route('account')->with('success', 'Account created successfully!');
    }

    public function account()
    {
        $customerId = session('customer_id');
        $phone = session('customer_phone');
        
        $orders = [];
        if ($phone) {
            $orders = Order::where('phone', 'like', "%{$phone}%")->latest()->get();
        }

        return view('auth.account', compact('orders'));
    }

    public function logout()
    {
        session()->forget(['customer_id', 'customer_name', 'customer_phone', 'customer_email']);
        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}
