<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Staff roles that can be created through public registration.
     * Patient accounts and administrator accounts are intentionally excluded.
     */
    private const REGISTRABLE_ROLES = [
        'doctor',
        'receptionist',
        'nurse',
        'lab_technician',
        'pharmacist',
        'cashier',
    ];

    /**
     * Show the staff registration page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('appointments.index');
        }

        return view('auth.register', [
            'roles' => self::REGISTRABLE_ROLES,
        ]);
    }

    /**
     * Register a new staff account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(self::REGISTRABLE_ROLES)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = new User($validated);
            // 'role' is not mass-assignable, so it must be set explicitly.
            $user->role = $validated['role'];
            $user->save();

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('appointments.index')
            ->with('success', "Welcome, {$user->first_name}! Your staff account has been created.");
    }
}
