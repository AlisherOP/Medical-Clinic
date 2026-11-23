<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the public patient registration page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('appointments.index');
        }

        return view('auth.register');
    }

    /**
     * Register a new patient account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = new User($validated);
            // Public registration always creates a patient account.
            $user->role = 'patient';
            $user->save();

            $user->patient()->create(['mrn' => $this->generateMrn()]);

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('appointments.index')
            ->with('success', "Welcome, {$user->first_name}! Your patient account has been created.");
    }

    /**
     * Generate a unique medical record number for the new patient.
     */
    private function generateMrn(): string
    {
        do {
            $mrn = 'MRN-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Patient::where('mrn', $mrn)->exists());

        return $mrn;
    }
}
