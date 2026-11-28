<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QMS — Clinical Operations Dashboard</title>
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
  </style>
</head>
<body class="min-h-screen flex antialiased">

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
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-white/15 text-white font-semibold">
          <span>⊞</span>
          <span>Dashboard</span>
        </a>
        <a href="{{ Route::has('doctors.index') ? route('doctors.index') : '#doctors' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>🩺</span>
          <span>Doctors</span>
        </a>
        <a href="{{ Route::has('services.index') ? route('services.index') : '#services' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📋</span>
          <span>Services</span>
        </a>
        <a href="{{ Route::has('appointments.index') ? route('appointments.index') : '#appointments' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📅</span>
          <span>Appointments</span>
        </a>
        <a href="{{ Route::has('operations.index') ? route('operations.index') : '#operations' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>⏱</span>
          <span>Operation Schedule</span>
        </a>
        <a href="#analytics" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📊</span>
          <span>Analytics</span>
        </a>
        <a href="#reports" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>📄</span>
          <span>Reports</span>
        </a>
        <a href="#entry-log" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>👥</span>
          <span>Users &amp; Entries</span>
        </a>
        <a href="#audit-logs" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#C4C7BE] hover:bg-white/10 hover:text-white transition">
          <span>🛡</span>
          <span>Audit Logs</span>
        </a>
      </nav>
    </div>

    <!-- Signed-In User Footer -->
    <div class="pt-4 border-t border-white/10 space-y-3">
      <div class="px-2">
        <p class="text-[10px] uppercase tracking-wider text-[#A3A89E]">Signed in as</p>
        <p class="text-xs font-bold text-white mt-0.5">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'Aslan Rahmatulloev' }}</p>
        <p class="text-[11px] text-[#A3A89E] capitalize">{{ auth()->user()->role ?? 'admin' }}</p>
      </div>
      @if(Route::has('logout'))
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="flex items-center justify-center gap-2 w-full py-2 rounded-lg border border-white/15 text-xs text-[#E6E4DD] hover:bg-white/10 transition">
            <span>↪</span>
            <span>Sign Out</span>
          </button>
        </form>
      @else
        <a href="#" class="flex items-center justify-center gap-2 w-full py-2 rounded-lg border border-white/15 text-xs text-[#E6E4DD] hover:bg-white/10 transition">
          <span>↪</span>
          <span>Sign Out</span>
        </a>
      @endif
    </div>
  </aside>

  <!-- MAIN CONTENT SHELL -->
  <div class="flex-1 flex flex-col min-w-0">

    <!-- TOP BAR -->
    <header class="h-14 bg-white border-b border-[#E6E4DD] px-8 flex items-center justify-between shrink-0">
      <span class="text-xs font-semibold text-[#1C1C1B]">Dashboard</span>
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#EEF2EC] text-[#14241E] border border-[#D4DDD0]">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
          Live
        </span>
        <span class="px-3 py-1 rounded-md text-[11px] font-semibold bg-[#14241E] text-white">
          English
        </span>
      </div>
    </header>

    <!-- SCROLLABLE DASHBOARD BODY -->
    <main class="flex-1 p-8 space-y-6 overflow-y-auto">

      @php
        $entryCount = $totalEntriesCount ?? (isset($entries) ? $entries->count() : 0);
        $patientCount = $totalPatients ?? 0;
        $appointmentCount = $activeAppointments ?? 0;
        $completedCount = $completedAppointments ?? 0;
        $cancelledCount = $cancelledAppointments ?? 0;
        $pendingCount = $pendingAppointments ?? 0;
        $bedCount = $activeBeds ?? 0;
        $sessionsMap = $latestSessions ?? collect();
        $fromVal = $filters['from'] ?? request('from', '');
        $toVal = $filters['to'] ?? request('to', '');
        $searchVal = $filters['search'] ?? request('search', '');
        $roleVal = $filters['role'] ?? request('role', 'all');
        $isFiltered = $filters['active'] ?? ($fromVal !== '' || $toVal !== '' || $searchVal !== '' || $roleVal !== 'all');
      @endphp

      <!-- LIVE BROADCAST NOTICE BANNER -->
      @if(!empty($activeBroadcast))
        <div id="liveAlertBanner" class="bg-[#14241E] text-white px-5 py-3.5 rounded-xl flex items-center justify-between gap-4">
          <div class="flex items-center gap-3 text-xs">
            <span class="px-2 py-0.5 rounded bg-white/15 font-bold uppercase tracking-wider text-[10px]">Broadcast</span>
            <div>
              <p class="font-medium">{{ $activeBroadcast['message'] }}</p>
              <p class="text-[10px] text-[#A3A89E] mt-0.5">
                Sent by {{ $activeBroadcast['sender'] ?? 'Station' }} at {{ $activeBroadcast['sent_at'] ?? '' }}
                @if(!empty($activeBroadcast['expires_at']))
                  · Auto-clears at {{ $activeBroadcast['expires_at'] }}
                @endif
              </p>
            </div>
          </div>
          <form method="POST" action="{{ route('dashboard.broadcast.dismiss') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-[#C4C7BE] hover:text-white font-semibold">
              Dismiss ✕
            </button>
          </form>
        </div>
      @endif

      <!-- OVERVIEW HEADER + DATE RANGE FILTER -->
      <div class="bg-white p-6 rounded-xl border border-[#E6E4DD] flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-[10px] font-bold tracking-widest uppercase text-[#6E716A] mb-1">Clinical Precision &amp; Operations</p>
          <h2 class="text-3xl font-serif-display font-bold text-[#1C1C1B]">Dashboard Overview</h2>
          <p class="text-xs text-[#6E716A] mt-0.5">
            @if($fromVal !== '' || $toVal !== '')
              Filtered window:
              <strong class="text-[#1C1C1B]">{{ $fromVal ?: 'Beginning' }}</strong>
              <span>→</span>
              <strong class="text-[#1C1C1B]">{{ $toVal ?: 'Today' }}</strong>
            @else
              Real-time clinic performance metrics and system entry activity (All dates)
            @endif
          </p>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2 text-xs">
          @if($searchVal !== '')
            <input type="hidden" name="search" value="{{ $searchVal }}">
          @endif
          @if($roleVal !== 'all')
            <input type="hidden" name="role" value="{{ $roleVal }}">
          @endif
          <input
            type="date"
            name="from"
            value="{{ $fromVal }}"
            aria-label="From date"
            class="px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E]"
          >
          <span class="text-[#6E716A]">→</span>
          <input
            type="date"
            name="to"
            value="{{ $toVal }}"
            aria-label="To date"
            class="px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E]"
          >
          <button
            type="submit"
            class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold hover:bg-[#1E352C] transition"
          >
            Apply Filter
          </button>
        </form>
      </div>

      <!-- SYSTEM / QUEUE ACTION BANNER -->
      <div class="bg-[#F3F1EA] p-4 rounded-xl border border-[#E2DFD5] flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-[#14241E] text-white flex items-center justify-center text-sm">🔔</div>
          <div>
            <p class="text-xs font-bold text-[#1C1C1B]">
              @if($isFiltered)
                Active Filters Applied
              @else
                Operational &amp; Queue Controls
              @endif
            </p>
            <p class="text-[11px] text-[#6E716A]">
              @if($isFiltered)
                Showing metrics and web entries matching your active filter criteria.
              @else
                Review live appointments, bed assignments, and web entry activity across the clinic.
              @endif
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2 text-xs">
          <a
            href="{{ route('dashboard') }}"
            class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-[#1C1C1B] hover:bg-[#F9F8F4] transition"
          >
            Reset Filter
          </a>
          <a
            href="{{ route('dashboard.export', request()->only(['from', 'to', 'search', 'role'])) }}"
            class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-[#1C1C1B] hover:bg-[#F9F8F4] transition"
          >
            Export CSV
          </a>
          <button
            type="button"
            onclick="openBroadcastModal()"
            class="px-3.5 py-2 rounded-lg bg-[#14241E] text-white font-semibold hover:bg-[#1E352C] transition"
          >
            Broadcast Alert
          </button>
        </div>
      </div>

      <!-- PRIMARY KPI METRIC CARDS (4 COLUMNS) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Web Entries / Users -->
        <div class="bg-white p-5 rounded-xl border border-[#E6E4DD]">
          <div class="flex items-center justify-between mb-3">
            <span class="text-[11px] font-semibold text-[#6E716A]">Web Entries / Users</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Live</span>
          </div>
          <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]">{{ $entryCount }}</p>
          <p class="text-[11px] text-[#6E716A] mt-1">Matching accounts &amp; portal entries</p>
        </div>

        <!-- Card 2: Total Patients -->
        <div class="bg-white p-5 rounded-xl border border-[#E6E4DD]">
          <div class="flex items-center justify-between mb-3">
            <span class="text-[11px] font-semibold text-[#6E716A]">Total Patients</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Registry</span>
          </div>
          <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]">{{ $patientCount }}</p>
          <p class="text-[11px] text-[#6E716A] mt-1">Registered patient records</p>
        </div>

        <!-- Card 3: Active Appointments -->
        <div class="bg-white p-5 rounded-xl border border-[#E6E4DD]">
          <div class="flex items-center justify-between mb-3">
            <span class="text-[11px] font-semibold text-[#6E716A]">Active Appointments</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Queue</span>
          </div>
          <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]">{{ $appointmentCount }}</p>
          <p class="text-[11px] text-[#6E716A] mt-1">Scheduled clinic appointments</p>
        </div>

        <!-- Card 4: Active Bed Assignments -->
        <div class="bg-white p-5 rounded-xl border border-[#E6E4DD]">
          <div class="flex items-center justify-between mb-3">
            <span class="text-[11px] font-semibold text-[#6E716A]">Active Bed Assignments</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Inpatient</span>
          </div>
          <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]">{{ $bedCount }}</p>
          <p class="text-[11px] text-[#6E716A] mt-1">Currently occupied beds</p>
        </div>
      </div>

      <!-- OPERATIONAL APPOINTMENT STATUS ROW (3 COLUMNS) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-[#E6E4DD] flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold text-[#6E716A]">Completed Visits</p>
            <p class="text-2xl font-serif-display font-bold text-[#1C1C1B] mt-0.5">{{ $completedCount }}</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#EEF2EC] text-[#14241E]">Completed</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-[#E6E4DD] flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold text-[#6E716A]">Pending / Scheduled</p>
            <p class="text-2xl font-serif-display font-bold text-[#1C1C1B] mt-0.5">{{ $pendingCount }}</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#F3F1EA] text-[#1C1C1B]">In Queue</span>
        </div>

        <div class="bg-white p-4 rounded-xl border border-[#E6E4DD] flex items-center justify-between">
          <div>
            <p class="text-[11px] font-semibold text-[#6E716A]">Cancelled Visits</p>
            <p class="text-2xl font-serif-display font-bold text-[#1C1C1B] mt-0.5">{{ $cancelledCount }}</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#F9EAEA] text-[#8B2626]">Cancelled</span>
        </div>
      </div>

      <!-- BOTTOM WORKFLOW SPLIT: WEB ENTRY LOG (2/3) + STATUS SUMMARY (1/3) -->
      <div id="entry-log" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Left 2 Columns: Web Entry & Access Log -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-[#E6E4DD] overflow-hidden">
          <div class="p-4 border-b border-[#E6E4DD] flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 class="text-sm font-bold text-[#1C1C1B]">Traffic &amp; Web Entry Log</h3>
              <p class="text-[11px] text-[#6E716A]">Users who registered or entered the web system</p>
            </div>

            <!-- Search Input Form -->
            <form method="GET" action="{{ route('dashboard') }}#entry-log" class="flex items-center gap-2 text-xs">
              @if($fromVal !== '')
                <input type="hidden" name="from" value="{{ $fromVal }}">
              @endif
              @if($toVal !== '')
                <input type="hidden" name="to" value="{{ $toVal }}">
              @endif
              @if($roleVal !== 'all')
                <input type="hidden" name="role" value="{{ $roleVal }}">
              @endif
              <input
                type="text"
                name="search"
                value="{{ $searchVal }}"
                placeholder="Search name or email..."
                class="px-3 py-1.5 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E]"
              >
              <button
                type="submit"
                class="px-3 py-1.5 rounded-lg bg-[#14241E] text-white font-semibold hover:bg-[#1E352C] transition"
              >
                Search
              </button>
            </form>
          </div>

          <!-- Role Filter Tabs -->
          <div class="px-4 py-2.5 bg-[#F9F8F4] border-b border-[#E6E4DD] flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-1.5">
              @php
                $baseQuery = array_filter([
                  'from' => $fromVal ?: null,
                  'to' => $toVal ?: null,
                  'search' => $searchVal ?: null,
                ]);
              @endphp
              <a
                href="{{ route('dashboard') . ($baseQuery ? '?' . http_build_query($baseQuery) : '') }}#entry-log"
                class="px-3 py-1 rounded-md font-semibold transition {{ $roleVal === 'all' ? 'bg-[#14241E] text-white' : 'text-[#6E716A] hover:bg-white' }}"
              >
                All Entries
              </a>
              <a
                href="{{ route('dashboard') . '?' . http_build_query(array_merge($baseQuery, ['role' => 'staff'])) }}#entry-log"
                class="px-3 py-1 rounded-md font-semibold transition {{ $roleVal === 'staff' ? 'bg-[#14241E] text-white' : 'text-[#6E716A] hover:bg-white' }}"
              >
                Clinical Staff
              </a>
              <a
                href="{{ route('dashboard') . '?' . http_build_query(array_merge($baseQuery, ['role' => 'patient'])) }}#entry-log"
                class="px-3 py-1 rounded-md font-semibold transition {{ $roleVal === 'patient' ? 'bg-[#14241E] text-white' : 'text-[#6E716A] hover:bg-white' }}"
              >
                Patients
              </a>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#EEF2EC] text-[#14241E]">
              {{ $entryCount }} Total
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-[#F9F8F4] text-[#6E716A] font-semibold border-b border-[#E6E4DD]">
                <tr>
                  <th class="p-3.5">User &amp; Email</th>
                  <th class="p-3.5">Role &amp; Entry Time</th>
                  <th class="p-3.5">Source / IP</th>
                  <th class="p-3.5">Initials</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E6E4DD]">
                @forelse($entries ?? [] as $entryUser)
                  @php
                    $session = $sessionsMap[$entryUser->id] ?? null;
                    $enteredAt = ($session && !empty($session->last_activity))
                        ? \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans()
                        : ($entryUser->created_at?->diffForHumans() ?? 'Recently');
                    $displayName = $entryUser->full_name
                        ?? trim(($entryUser->first_name ?? '') . ' ' . ($entryUser->last_name ?? ''))
                        ?: ($entryUser->name ?? 'User #' . $entryUser->id);
                    $initials = strtoupper(
                        mb_substr($entryUser->first_name ?? $displayName, 0, 1) .
                        mb_substr($entryUser->last_name ?? '', 0, 1)
                    ) ?: 'U';
                  @endphp
                  <tr class="hover:bg-[#F9F8F4] transition">
                    <td class="p-3.5">
                      <p class="font-bold text-[#1C1C1B]">{{ $displayName }}</p>
                      <p class="text-[11px] text-[#6E716A]">{{ $entryUser->email }}</p>
                    </td>
                    <td class="p-3.5">
                      <p class="font-medium text-[#1C1C1B] capitalize">{{ str_replace('_', ' ', $entryUser->role ?? 'user') }}</p>
                      <p class="text-[11px] text-[#6E716A]">Entered {{ $enteredAt }}</p>
                    </td>
                    <td class="p-3.5">
                      <span class="px-2.5 py-1 rounded bg-[#EEF2EC] text-[#14241E] font-semibold text-[11px]">
                        {{ $session->ip_address ?? 'Web Portal' }}
                      </span>
                    </td>
                    <td class="p-3.5">
                      <div class="w-7 h-7 rounded-full bg-[#14241E] text-white flex items-center justify-center font-bold text-[10px]">
                        {{ $initials }}
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="p-6 text-center text-[#6E716A]">No web entry records found for the selected criteria.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <!-- Right Column: Clinic & System Status -->
        <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] space-y-4">
          <div class="border-b border-[#E6E4DD] pb-3">
            <h3 class="text-sm font-bold text-[#1C1C1B]">System &amp; Clinic Status</h3>
            <p class="text-[11px] text-[#6E716A]">Live operational summary</p>
          </div>

          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between p-3 rounded-lg bg-[#F9F8F4]">
              <span class="font-medium text-[#1C1C1B]">Database Status</span>
              <span class="px-2 py-0.5 rounded bg-[#EEF2EC] text-[#14241E] font-semibold text-[11px]">Connected</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-[#F9F8F4]">
              <span class="font-medium text-[#1C1C1B]">Active Appointments</span>
              <span class="font-bold text-[#1C1C1B]">{{ $appointmentCount }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-[#F9F8F4]">
              <span class="font-medium text-[#1C1C1B]">Assigned Beds</span>
              <span class="font-bold text-[#1C1C1B]">{{ $bedCount }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-[#F9F8F4]">
              <span class="font-medium text-[#1C1C1B]">Completion Rate</span>
              <span class="font-bold text-[#14241E]">
                {{ $appointmentCount > 0 ? round(($completedCount / $appointmentCount) * 100) . '%' : '0%' }}
              </span>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>

  <!-- BROADCAST ALERT MODAL -->
  <div id="broadcastModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white w-full max-w-md rounded-2xl border border-[#E6E4DD] p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-[#1C1C1B]">Broadcast Clinic Alert</h3>
          <p class="text-[11px] text-[#6E716A]">Pick a quick preset or type a custom notice (up to 12 hrs)</p>
        </div>
        <button type="button" onclick="closeBroadcastModal()" class="text-xs text-[#6E716A] hover:text-[#1C1C1B] font-semibold">
          ✕
        </button>
      </div>

      <!-- Quick Presets -->
      <div class="space-y-1.5">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#6E716A]">Quick Presets</p>
        <div class="flex flex-wrap gap-1.5 text-[11px]">
          <button 
            type ="button"
            onclick="useBroadcastPreset('Outpatient queue running ~15 mins behind schedule.')"
            class="px-2.5 py-1 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] hover:bg-[#EEF2EC] transition"
          >
            +15m Queue Delay
          </button>
          <button
            type="button"
            onclick="useBroadcastPreset('Laboratory results processing slightly delayed; urgent orders prioritized.')"
            class="px-2.5 py-1 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] hover:bg-[#EEF2EC] transition"
          >
            Lab Delay
          </button>
          <button
            type="button"
            onclick="useBroadcastPreset('Shift handover in progress across nursing and front-desk stations.')"
            class="px-2.5 py-1 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] hover:bg-[#EEF2EC] transition"
          >
            Shift Handover
          </button>
        </div>
      </div>

    <form method="POST" action="{{ route('dashboard.broadcast') }}" class="space-y-4">
      @csrf
      <div>
        <label for="broadcastMessageInput" class="block text-xs font-semibold text-[#1C1C1B] mb-1">
          Alert Message
        </label>
        <textarea
          id="broadcastMessageInput"
          name="message"
          rows="3"
          maxlength="280"
          required
          placeholder="Type a clinic-wide notice..."
          class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]"
        ></textarea>
      </div>
      <div class="flex items-center justify-end gap-2 text-xs">
        <button
          type="button"
          onclick="closeBroadcastModal()"
          class="px-4 py-2 rounded-lg border border-[#E6E4DD] text-[#1C1C1B] font-semibold hover:bg-[#F9F8F4] transition"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold hover:bg-[#1E352C] transition"
        >
          Send Notice
        </button>
      </div>
    </form>
  </div>

  <script>
    function openBroadcastModal() {
      const modal = document.getElementById('broadcastModal');
      if (!modal) return;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.getElementById('broadcastMessageInput')?.focus();
    }

    function closeBroadcastModal() {
      const modal = document.getElementById('broadcastModal');
      if (!modal) return;
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function useBroadcastPreset(text) {
      const input = document.getElementById('broadcastMessageInput');
      if (!input) return;
      input.value = text;
      input.focus();
    } 
  </script>

</body>
</html>