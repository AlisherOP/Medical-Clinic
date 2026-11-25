@extends('layouts.auth')

@section('title', 'Staff Log In | QMS Clinical')

@section('header_title', 'Staff Log In')

@section('content')
  <!-- HEADING -->
  <div class="mb-6">
    <p class="text-[10px] font-bold tracking-widest uppercase text-[#6E716A] mb-1">Clinic Staff Access</p>
    <h2 class="text-3xl font-serif-display font-bold text-[#1C1C1B]">Welcome back</h2>
    <p class="text-xs text-[#6E716A] mt-1.5">
      Log in with your clinic account to continue into the workspace.
    </p>
  </div>

  <!-- FLASH / VALIDATION ALERTS -->
  @if(session('status'))
    <div class="mb-5 rounded-lg border border-[#D4DDD0] bg-[#EEF2EC] px-4 py-3 text-xs font-semibold text-[#14241E]">
      {{ session('status') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-5 rounded-lg border border-[#F3D6D6] bg-[#F9EAEA] px-4 py-3 text-xs font-semibold text-[#8B2626]">
      {{ $errors->first() }}
    </div>
  @endif

  <!-- LOGIN FORM -->
  <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
    @csrf

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
        autofocus
        class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
      >
      @error('email')
        <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
      @enderror
    </div>

    <div>
      <label for="password" class="block text-[11px] font-semibold uppercase tracking-wider text-[#6E716A] mb-1.5">Password</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="Enter your password"
        autocomplete="current-password"
        required
        class="w-full px-3.5 py-2.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-sm text-[#1C1C1B] placeholder-[#A3A89E] outline-none transition focus:border-[#14241E] focus:ring-2 focus:ring-[#14241E]/10"
      >
      @error('password')
        <span class="block mt-1.5 text-[11px] font-medium text-[#8B2626]">{{ $message }}</span>
      @enderror
    </div>

    <button
      type="submit"
      class="w-full px-4 py-2.5 rounded-lg bg-[#14241E] text-white text-sm font-semibold hover:bg-[#1E352C] transition"
    >
      Log In
    </button>
  </form>

  <p class="mt-6 text-center text-xs text-[#6E716A]">
    New staff member?
    <a href="{{ route('register') }}" class="font-semibold text-[#14241E] hover:underline">Register</a>
  </p>
@endsection
