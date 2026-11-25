@extends('layouts.auth')

@section('title', 'Staff Register | QMS Clinical')

@section('header_title', 'Staff Registration')

@section('content')
  <!-- HEADING -->
  <div class="mb-6">
    <p class="text-[10px] font-bold tracking-widest uppercase text-[#6E716A] mb-1">New Staff Member</p>
    <h2 class="text-3xl font-serif-display font-bold text-[#1C1C1B]">Create your staff account</h2>
    <p class="text-xs text-[#6E716A] mt-1.5">
      Register a staff account to access the clinic workspace.
    </p>
  </div>

  <!-- VALIDATION ALERTS -->
  @if($errors->any())
    <div class="mb-5 rounded-lg border border-[#F3D6D6] bg-[#F9EAEA] px-4 py-3 text-xs font-semibold text-[#8B2626]">
      {{ $errors->first() }}
    </div>
  @endif

  <!-- REGISTER FORM -->
  <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="first_name" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">First Name</label>
        <input
          type="text"
          id="first_name"
          name="first_name"
          value="{{ old('first_name') }}"
          placeholder="Jane"
          autocomplete="given-name"
          required
          autofocus
          class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
        >
        @error('first_name')
          <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
        @enderror
      </div>

      <div>
        <label for="last_name" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">Last Name</label>
        <input
          type="text"
          id="last_name"
          name="last_name"
          value="{{ old('last_name') }}"
          placeholder="Doe"
          autocomplete="family-name"
          required
          class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
        >
        @error('last_name')
          <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
        @enderror
      </div>
    </div>

    <div>
      <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">Email Address</label>
      <input
        type="email"
        id="email"
        name="email"
        value="{{ old('email') }}"
        placeholder="you@medicalclinic.com"
        autocomplete="email"
        required
        class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
      >
      @error('email')
        <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
      @enderror
    </div>

    <div>
      <label for="role" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">Staff Role</label>
      <select
        id="role"
        name="role"
        required
        class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] outline-none cursor-pointer transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
      >
        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select your role</option>
        @foreach($roles as $role)
          <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
            {{ \Illuminate\Support\Str::headline($role) }}
          </option>
        @endforeach
      </select>
      @error('role')
        <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
      @enderror
    </div>

    <div>
      <label for="password" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">Password</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="At least 8 characters"
        autocomplete="new-password"
        required
        class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
      >
      @error('password')
        <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
      @enderror
    </div>

    <div>
      <label for="password_confirmation" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">Confirm Password</label>
      <input
        type="password"
        id="password_confirmation"
        name="password_confirmation"
        placeholder="Re-enter your password"
        autocomplete="new-password"
        required
        class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
      >
    </div>

    <button
      type="submit"
      class="w-full px-4 py-2.5 rounded-lg bg-[#14241E] text-white text-sm font-semibold hover:bg-[#1E352C] transition"
    >
      Create Account
    </button>
  </form>

  <p class="mt-6 text-center text-xs text-[#6E716A]">
    Already have an account?
    <a href="{{ route('login') }}" class="font-semibold text-[#14241E] hover:underline">Log In</a>
  </p>
@endsection
