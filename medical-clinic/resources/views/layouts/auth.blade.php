<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Staff Access | QMS Clinical')</title>

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
<body class="min-h-screen antialiased bg-[#F9F8F4] text-[#1C1C1B]">

  <div class="min-h-screen flex">

    <!-- LEFT BRAND PANEL (Deep Forest #14241E) -->
    <aside class="hidden lg:flex w-[42%] max-w-xl shrink-0 flex-col justify-between bg-[#14241E] text-[#E6E4DD] p-10">
      <div class="space-y-10">
        <!-- Brand Header -->
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-[#E6E4DD] text-[#14241E] flex items-center justify-center font-serif-display font-bold text-2xl">
            Q
          </div>
          <div>
            <h1 class="text-sm font-bold tracking-wide text-white uppercase">QMS Clinical</h1>
            <p class="text-[11px] text-[#A3A89E]">Operations &amp; Registry</p>
          </div>
        </div>

        <!-- Positioning Copy -->
        <div class="space-y-3">
          <p class="text-[10px] font-bold tracking-widest uppercase text-[#A3A89E]">Medical Clinic Hospital System</p>
          <h2 class="text-4xl font-serif-display font-bold leading-tight text-white">Precision care,<br>quietly orchestrated.</h2>
          <p class="text-xs text-[#C4C7BE] leading-relaxed max-w-sm">
            Live outpatient queues, surgical theatre telemetry, and clinical audit trails — unified in one calm workspace.
          </p>
        </div>

        <!-- Feature Highlights -->
        <ul class="space-y-3 text-xs text-[#E6E4DD]">
          <li class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center">📅</span>
            <span>Real-time appointment queue &amp; triage</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center">⏱</span>
            <span>Surgical theatre monitoring &amp; capacity</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center">🛡</span>
            <span>Immutable audit logs for every entry</span>
          </li>
        </ul>
      </div>

      <div class="pt-4 border-t border-white/10 text-[11px] text-[#A3A89E]">
        Authorized clinical staff only &middot; Protected by QMS audit logging
      </div>
    </aside>

    <!-- RIGHT FORM PANEL -->
    <main class="flex-1 flex flex-col min-w-0">

      <!-- TOP BAR -->
      <header class="h-14 bg-white border-b border-[#E6E4DD] px-6 sm:px-8 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2.5 text-xs font-semibold text-[#1C1C1B]">
          <span class="lg:hidden w-6 h-6 rounded bg-[#14241E] text-white flex items-center justify-center font-serif-display text-sm">Q</span>
          <span>@yield('header_title', 'Staff Access')</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#EEF2EC] text-[#14241E] border border-[#D4DDD0]">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
            Live Sync Active
          </span>
          <button
            type="button"
            onclick="history.back()"
            class="px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-white text-[#1C1C1B] border border-[#E6E4DD] hover:bg-[#F9F8F4] transition"
          >
            ← Back
          </button>
        </div>
      </header>

      <!-- FORM AREA -->
      <div class="flex-1 flex items-center justify-center p-6 sm:p-10 overflow-y-auto">
        <div class="w-full max-w-md">
          <div class="bg-white rounded-xl border border-[#E6E4DD] shadow-[0_24px_60px_-40px_rgba(20,36,30,0.45)] p-7 sm:p-9">
            @yield('content')
          </div>

          <p class="text-center text-[11px] text-[#A3A89E] mt-5">
            QMS Clinical &middot; Medical Clinic Hospital System &middot; Authorized staff only
          </p>
        </div>
      </div>
    </main>
  </div>

  @yield('scripts')
</body>
</html>
