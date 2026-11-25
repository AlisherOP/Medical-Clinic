<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'QMS Clinical — Hospital Operations')</title>

  <!-- Tailwind CSS & Aura Design System Typography -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background-color: #F9F8F4;
      color: #1C1C1B;
    }
    .font-serif-display {
      font-family: 'Cormorant Garamond', Georgia, serif;
    }
    /* Subtle custom scrollbars */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F9F8F4;
    }
    ::-webkit-scrollbar-thumb {
      background: #D4DDD0;
      border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #A3A89E;
    }
  </style>

  @yield('styles')
</head>
<body class="min-h-screen flex antialiased bg-[#F9F8F4] text-[#1C1C1B]">

  <!-- LEFT PERSISTENT SIDEBAR (Deep Forest #14241E + QMS Navigation) -->
  <aside class="w-64 bg-[#14241E] text-[#E6E4DD] flex flex-col justify-between p-5 min-h-screen shrink-0">
    <div class="space-y-6">
      <!-- Brand Header -->
      <div class="flex items-center gap-3 px-2 pb-4 border-b border-white/10">
        <div class="w-9 h-9 rounded-lg bg-[#E6E4DD] text-[#14241E] flex items-center justify-center font-serif-display font-bold text-xl">
          Q
        </div>
        <div>
          <h1 class="text-sm font-bold tracking-wide text-white uppercase">QMS Clinical</h1>
          <p class="text-[11px] text-[#A3A89E]">Operations &amp; Registry</p>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="space-y-1 text-xs font-medium">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard*') ? 'bg-white/15 text-white font-semibold' : 'text-[#C4C7BE] hover:bg-white/10 hover:text-white transition' }}">
          <span>⊞</span>
          <span>Dashboard</span>
        </a>
        <a href="{{ route('dashboard') }}#doctors" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>🩺</span>
          <span>Doctors</span>
        </a>
        <a href="{{ route('dashboard') }}#services" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📋</span>
          <span>Services</span>
        </a>
        <a href="{{ route('appointments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('appointments.*') ? 'bg-white/15 text-white font-semibold' : 'text-[#C4C7BE] hover:bg-white/10 hover:text-white transition' }}">
          <span>📅</span>
          <span>Appointments</span>
        </a>
        <a href="{{ route('operations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('operations.*') ? 'bg-white/15 text-white font-semibold' : 'text-[#C4C7BE] hover:bg-white/10 hover:text-white transition' }}">
          <span>⏱</span>
          <span>Operation Schedule</span>
        </a>
        <a href="{{ route('dashboard') }}#analytics" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📊</span>
          <span>Analytics</span>
        </a>
        <a href="{{ route('dashboard') }}#reports" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📄</span>
          <span>Reports</span>
        </a>
        <a href="{{ route('dashboard') }}#entry-log" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>👥</span>
          <span>Users &amp; Entries</span>
        </a>
        <a href="{{ route('dashboard') }}#audit-logs" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>🛡</span>
          <span>Audit Logs</span>
        </a>
      </nav>
    </div>

    <!-- Signed-In User Footer -->
    <div class="pt-4 border-t border-white/10 space-y-3">
      <div class="px-2">
        <p class="text-[10px] uppercase tracking-wider text-[#A3A89E]">Signed in as</p>
        <p class="text-xs font-bold text-white mt-0.5">{{ auth()->user()->full_name ?? 'Alisher / Lead' }}</p>
        <p class="text-[11px] text-[#A3A89E] capitalize">{{ auth()->user()->role ?? 'admin' }}</p>
      </div>
      <a href="#" class="flex items-center justify-center gap-2 w-full py-2 rounded-lg border border-white/15 text-xs text-[#E6E4DD] hover:bg-white/10 transition">
        <span>↪</span>
        <span>Sign Out</span>
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT SHELL -->
  <div class="flex-1 flex flex-col min-w-0">

    <!-- TOP BAR -->
    <header class="h-14 bg-white border-b border-[#E6E4DD] px-8 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-[#1C1C1B]">@yield('header_title', 'Clinical Workspace')</span>
        @hasSection('header_badge')
          @yield('header_badge')
        @endif
      </div>
      <div class="flex items-center gap-3">
        @yield('header_actions')
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#EEF2EC] text-[#14241E] border border-[#D4DDD0]">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
          Live Sync Active
        </span>
        <span class="px-3 py-1 rounded-md text-[11px] font-semibold bg-[#14241E] text-white">
          English
        </span>
      </div>
    </header>

    <!-- SCROLLABLE PAGE BODY -->
    <main class="flex-1 p-8 space-y-6 overflow-y-auto">
      @yield('content')
    </main>
  </div>

  @yield('scripts')
</body>
</html>