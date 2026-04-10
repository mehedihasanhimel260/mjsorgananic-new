<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private function generateAffiliateCode(): string
    {
        do {
            $code = 'AFF-'.strtoupper(substr(md5((string) microtime(true).random_int(1000, 9999)), 0, 8));
        } while (Affiliate::where('affiliate_code', $code)->exists());

        return $code;
    }

    public function showLoginForm()
    {
        if (Auth::guard('affiliate')->check()) {
            return redirect()->route('affiliates.dashboard');
        }

        return view('affiliates.auth.login');
    }

    public function showRegisterForm()
    {
        return view('affiliates.auth.register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [
            $field => $request->login,
            'password' => $request->password,
            'status' => 'active',
        ];

        if (! Auth::guard('affiliate')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => 'Invalid email/phone or password'])
                ->withInput();
        }

        $request->session()->regenerate();
        $request->session()->forget('impersonated_by_admin_id');
        Auth::guard('affiliate')->user()?->update(['last_login_at' => now()]);

        return redirect()->route('affiliates.dashboard');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:affiliates,phone',
            'email' => 'required|email|max:255|unique:affiliates,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $affiliate = Affiliate::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'affiliate_code' => $this->generateAffiliateCode(),
            'balance' => 0,
            'status' => 'active',
        ]);

        Auth::guard('affiliate')->login($affiliate);
        $request->session()->regenerate();
        $request->session()->forget('impersonated_by_admin_id');

        return redirect()->route('affiliates.dashboard')->with('success', 'Affiliate account created successfully.');
    }

    public function leaveImpersonation(Request $request)
    {
        if (! $request->session()->has('impersonated_by_admin_id')) {
            return redirect()->route('affiliates.dashboard');
        }

        Auth::guard('affiliate')->logout();
        $request->session()->forget('impersonated_by_admin_id');
        $request->session()->regenerateToken();

        return redirect()->route('admin.dashboard')->with('success', 'Returned to admin panel successfully.');
    }

    public function logout(Request $request)
    {
        if ($request->session()->has('impersonated_by_admin_id')) {
            return $this->leaveImpersonation($request);
        }

        Auth::guard('affiliate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('affiliates.login');
    }
}
