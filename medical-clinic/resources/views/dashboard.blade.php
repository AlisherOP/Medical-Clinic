<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PulseHealth - Clinical Operations Dashboard</title>
  <!-- Tailwind CSS & Plus Jakarta Sans Font -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
  </style>
</head>
<body class="bg-[#f8f9ff] text-[#081c3b] min-h-screen flex antialiased">

  <!-- LEFT PERSISTENT SIDEBAR -->
  <aside class="w-64 border-r border-[#dae8ff] bg-white flex flex-col justify-between p-5 min-h-screen shrink-0">
    <div class="space-y-6">
      <!-- Brand Logo -->
      <div class="flex items-center gap-3 px-2">
        <div class="w-9 h-9 rounded-lg bg-[#0f766e] flex items-center justify-center text-white font-bold text-lg">P</div>
        <div>
          <h1 class="text-base font-bold leading-tight text-[#0f766e]">PulseHealth</h1>
          <p class="text-[11px] font-semibold tracking-wider text-[#6e7a8e] uppercase">Clinical OS</p>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="space-y-1">
        <p class="px-3 text-[11px] font-bold text-[#6e7a8e] uppercase tracking-wider mb-2">Clinical Workflow</p>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#0f766e] text-white font-medium text-sm transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
          Dashboard
        </a>
        <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-[#3e4b5d] hover:bg-[#eff4ff] font-medium text-sm transition">
          <span class="flex items-center gap-3">
            <svg class="w-4 h-4 text-[#6e7a8e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            My Queue
          </span>
          <span class="text-xs bg-[#eff4ff] text-[#0f766e] font-semibold px-2 py-0.5 rounded-full">12</span>
        </a>
        <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-[#3e4b5d] hover:bg-[#eff4ff] font-medium text-sm transition">
          <span class="flex items-center gap-3">
            <svg class="w-4 h-4 text-[#ba1a1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Critical Cases
          </span>
          <span class="text-xs bg-[#ffdad6] text-[#ba1a1a] font-semibold px-2 py-0.5 rounded-full">{{ $criticalPatientsCount }}</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#3e4b5d] hover:bg-[#eff4ff] font-medium text-sm transition">
          <svg class="w-4 h-4 text-[#6e7a8e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Schedule
        </a>
      </nav>
    </div>

    <!-- User Profile Badge Footer -->
    <div class="pt-4 border-t border-[#dae8ff] flex items-center gap-3 bg-[#eff4ff] p-3 rounded-lg">
      <div class="w-8 h-8 rounded-full bg-[#0f766e] text-white flex items-center justify-center font-bold text-xs">AS</div>
      <div>
        <p class="text-xs font-semibold text-[#081c3b]">Dr. Aslan Rahmatulloev</p>
        <p class="text-[11px] text-[#0f766e] flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> On Shift • Ward 4B</p>
      </div>
    </div>
  </aside>

  <!-- MAIN APP CONTAINER -->
  <div class="flex-1 flex flex-col min-w-0">
    <!-- TOP HEADER BAR -->
    <header class="h-16 bg-white border-b border-[#dae8ff] px-8 flex items-center justify-between shrink-0">
      <!-- Search Input -->
      <div class="relative w-96">
        <input type="text" placeholder="Search patient records, MRN, beds, or staff... ⌘K" class="w-full text-xs pl-9 pr-4 py-2 rounded-lg bg-[#eff4ff] border border-transparent focus:border-[#0f766e] focus:bg-white outline-none">
        <svg class="w-4 h-4 text-[#6e7a8e] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>

      <!-- Header Status & Profile -->
      <div class="flex items-center gap-5">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-[#eff4ff] text-[#0f766e] border border-[#a7f3d0]">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ER Capacity: 84% Nominal
        </span>
        <span class="text-xs text-[#6e7a8e] font-mono">{{ now()->format('H:i:s') }}</span>
        <div class="flex items-center gap-3 pl-4 border-l border-[#dae8ff]">
          <div class="text-right">
            <p class="text-xs font-bold text-[#081c3b]">Dr. Aslan</p>
            <p class="text-[10px] text-[#6e7a8e]">Emergency Specialist</p>
          </div>
          <div class="w-9 h-9 rounded-full bg-[#0284c7] text-white flex items-center justify-center font-bold text-xs">AS</div>
        </div>
      </div>
    </header>

    <!-- CONTENT BODY (SCROLLABLE) -->
    <main class="flex-1 p-8 space-y-6 overflow-y-auto">
      
      <!-- DASHBOARD TITLE & ACTIONS -->
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-[#0f766e] uppercase">● Live Inpatient Stream</span>
            <span class="text-xs text-[#6e7a8e]">Station ID: W4B-CLIN-09</span>
          </div>
          <h2 class="text-2xl font-bold font-serif text-[#081c3b]">Clinical Operations Dashboard</h2>
          <p class="text-xs text-[#6e7a8e] mt-0.5">{{ now()->format('l, M d') }} • Shift 07:00 – 19:00 • Dr. Aslan (Charge Physician)</p>
        </div>
        <div class="flex items-center gap-3">
          <button class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-[#dae8ff] bg-white text-[#3e4b5d] hover:bg-[#eff4ff] flex items-center gap-1.5">
            Sync Telemetry
          </button>
          <button class="px-4 py-2 text-xs font-semibold rounded-lg bg-[#0f766e] text-white hover:bg-[#0c5f59] shadow-sm flex items-center gap-1.5">
            Export Shift Report
          </button>
        </div>
      </div>

      <!-- TOP METRIC CARDS (4 COLUMNS) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Admitted -->
        <div class="bg-white p-5 rounded-xl border border-[#dae8ff] shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold tracking-wider text-[#6e7a8e] uppercase">Total Patients</span>
            <span class="p-2 rounded-lg bg-[#eff4ff] text-[#0f766e]">🗂</span>
          </div>
          <div class="my-2">
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-bold text-[#081c3b]">{{ $totalPatients }}</span>
              <span class="text-xs font-bold text-emerald-600">Live DB</span>
            </div>
            <p class="text-[11px] text-[#6e7a8e] mt-1">Active hospital registry records</p>
          </div>
        </div>

        <!-- Card 2: Active Appointments -->
        <div class="bg-white p-5 rounded-xl border border-[#dae8ff] shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold tracking-wider text-[#6e7a8e] uppercase">Active Appointments</span>
            <span class="p-2 rounded-lg bg-[#eff4ff] text-[#0284c7]">📋</span>
          </div>
          <div class="my-2">
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-bold text-[#081c3b]">{{ $activeAppointments }}</span>
              <span class="text-xs text-[#6e7a8e]">Scheduled</span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-3 flex">
              <div class="bg-[#0f766e] w-1/3"></div>
              <div class="bg-[#0284c7] w-1/4"></div>
              <div class="bg-slate-300 w-5/12"></div>
            </div>
            <p class="text-[10px] text-[#6e7a8e] mt-2">Queue synchronized successfully</p>
          </div>
        </div>

        <!-- Card 3: Bed Occupancy -->
        <div class="bg-white p-5 rounded-xl border border-[#dae8ff] shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold tracking-wider text-[#6e7a8e] uppercase">Active Bed Assignments</span>
            <span class="p-2 rounded-lg bg-[#eff4ff] text-[#0e7490]">🛏</span>
          </div>
          <div class="my-2">
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-bold text-[#081c3b]">{{ $activeBeds }}</span>
              <span class="text-xs text-[#6e7a8e]">Assigned Beds</span>
            </div>
            <div class="flex justify-between text-[11px] text-[#6e7a8e] mt-2 border-t border-[#dae8ff] pt-2">
              <span>Status: <strong>Active</strong></span>
              <span>Ward: <strong>Connected</strong></span>
            </div>
          </div>
        </div>

        <!-- Card 4: Critical & ER Priority -->
        <div class="bg-white p-5 rounded-xl border-l-4 border-l-[#ba1a1a] border border-[#dae8ff] shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold tracking-wider text-[#ba1a1a] uppercase">Critical & ER Priority</span>
            <span class="p-1.5 rounded-full bg-red-100 text-[#ba1a1a] text-xs">⚠️</span>
          </div>
          <div class="my-2">
            <div class="text-3xl font-serif font-bold text-[#ba1a1a]">{{ $criticalPatientsCount }} Patients</div>
            <div class="flex items-center gap-2 mt-2">
              <span class="text-[11px] text-[#3e4b5d]">Avg Wait: <strong>8 min</strong></span>
              <span class="px-2 py-0.5 rounded text-[10px] bg-[#ba1a1a] text-white font-bold">Priority Triage</span>
            </div>
          </div>
        </div>
      </div>

      <!-- MAIN WORKFLOW SPLIT: DATA TABLE (2/3) + INTAKE PANEL (1/3) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- LEFT 2 COLUMNS: PATIENT CARE REGISTRY TABLE -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-xl border border-[#dae8ff] shadow-sm overflow-hidden">
            <!-- Filter & Tabs Header -->
            <div class="p-4 border-b border-[#dae8ff] flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-[#081c3b]">Live Patient Care Registry</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-[#eff4ff] text-[#0f766e] font-bold">{{ $totalPatients }} Active</span>
              </div>
              <div class="flex items-center gap-2">
                <input type="text" placeholder="Filter patient or MRN..." class="text-xs px-3 py-1.5 bg-[#eff4ff] rounded-lg border-none outline-none">
                <select class="text-xs px-3 py-1.5 bg-[#eff4ff] rounded-lg border-none outline-none text-[#3e4b5d]">
                  <option>All Triage Levels</option>
                  <option>L1 Resus</option>
                  <option>L2 Emergent</option>
                </select>
              </div>
            </div>

            <!-- Triage Tabs -->
            <div class="px-4 py-2.5 bg-[#f8f9ff] border-b border-[#dae8ff] flex items-center gap-2">
              <button class="px-3 py-1 text-xs font-semibold rounded-md bg-[#0f766e] text-white">All Patients ({{ $totalPatients }})</button>
              <button class="px-3 py-1 text-xs font-semibold rounded-md text-[#3e4b5d] hover:bg-white">Active Queue</button>
              <button class="px-3 py-1 text-xs font-semibold rounded-md text-[#ba1a1a] hover:bg-white">Critical Emergencies ({{ $criticalPatientsCount }})</button>
            </div>

            <!-- Table -->
            <table class="w-full text-left text-xs">
              <thead class="bg-[#eff4ff] text-[#6e7a8e] font-semibold border-b border-[#dae8ff]">
                <tr>
                  <th class="p-3.5">Patient & MRN</th>
                  <th class="p-3.5">Diagnosis / Complaint</th>
                  <th class="p-3.5">Location</th>
                  <th class="p-3.5">Care Team</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#dae8ff]">
                @forelse($patients as $patient)
                  <tr class="hover:bg-[#eff4ff]/50 transition">
                    <td class="p-3.5">
                      <p class="font-bold text-[#081c3b]">{{ $patient->name ?? 'Patient #' . $patient->id }}</p>
                      <p class="text-[11px] text-[#6e7a8e]">{{ $patient->age ?? '35' }}y • #MRN-{{ 80000 + $patient->id }}</p>
                    </td>
                    <td class="p-3.5">
                      <p class="font-medium text-[#081c3b]">{{ $patient->diagnosis ?? 'General Consultation' }}</p>
                      <p class="text-[11px] text-[#0f766e] font-semibold">Vitals Stable • Monitored</p>
                    </td>
                    <td class="p-3.5"><span class="px-2 py-1 rounded bg-[#bae6fd] text-[#082f49] font-semibold">● Ward 4B</span></td>
                    <td class="p-3.5"><div class="w-6 h-6 rounded-full bg-[#0f766e] text-white flex items-center justify-center font-bold text-[10px]">DR</div></td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="p-6 text-center text-[#6e7a8e]">No patient records found in the database. Run your seeders or add a patient!</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <!-- RIGHT COLUMN: RAPID PATIENT INTAKE FORM & ALERTS -->
        <div class="space-y-6">
          <!-- Intake Card -->
          <div class="bg-white p-5 rounded-xl border border-[#dae8ff] shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#dae8ff]">
              <div>
                <h3 class="text-sm font-bold text-[#081c3b]">Rapid Patient Intake</h3>
                <p class="text-[11px] text-[#6e7a8e]">Enter telemetry & admit to queue</p>
              </div>
              <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#eff4ff] text-[#0f766e]">Fast-Track</span>
            </div>

            <form class="space-y-3 text-xs">
              <div>
                <label class="block text-[11px] font-bold text-[#3e4b5d] mb-1">Patient Legal Full Name</label>
                <input type="text" placeholder="e.g. Jonathan R. Miller" class="w-full p-2 rounded-lg bg-[#eff4ff] border-none outline-none">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-[#3e4b5d] mb-1">MRN or National Identifier</label>
                <input type="text" placeholder="#MRN-000000" class="w-full p-2 rounded-lg bg-[#eff4ff] border-none outline-none">
              </div>
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <label class="block text-[11px] font-bold text-[#3e4b5d] mb-1">Age</label>
                  <input type="number" placeholder="45" class="w-full p-2 rounded-lg bg-[#eff4ff] border-none outline-none">
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-[#3e4b5d] mb-1">Sex</label>
                  <select class="w-full p-2 rounded-lg bg-[#eff4ff] border-none outline-none text-[#3e4b5d]">
                    <option>Male</option><option>Female</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-[#3e4b5d] mb-1">Blood</label>
                  <select class="w-full p-2 rounded-lg bg-[#eff4ff] border-none outline-none text-[#3e4b5d]">
                    <option>O+</option><option>A+</option><option>B+</option><option>AB+</option>
                  </select>
                </div>
              </div>
              <button type="button" class="w-full py-2.5 rounded-lg bg-[#0f766e] text-white font-bold text-xs hover:bg-[#0c5f59] shadow-sm">
                Queue & Admit Patient
              </button>
            </form>
          </div>

          <!-- Direct Alerts Feed -->
          <div class="bg-white p-5 rounded-xl border border-[#dae8ff] shadow-sm space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-[#081c3b]">Critical Direct Alerts</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#ffdad6] text-[#ba1a1a] font-bold">Live Feed</span>
            </div>
            <div class="p-3 rounded-lg border-l-4 border-l-[#ba1a1a] bg-[#ffdad6]/20">
              <p class="text-xs font-bold text-[#ba1a1a]">System Telemetry Active</p>
              <p class="text-[11px] text-[#6e7a8e]">Connected to Laragon MySQL Database • All nodes operational.</p>
            </div>
          </div>

        </div>

      </div>

    </main>
  </div>
</body>
</html>