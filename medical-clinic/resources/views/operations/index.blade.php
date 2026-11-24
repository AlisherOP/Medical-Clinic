@extends('layouts.app')

@section('title', 'Operation Schedule & Theatre Monitoring | QMS Clinical')

@section('header_title', 'Operation Schedule')

@section('content')
  <!-- OVERVIEW HEADER + DATE FILTER -->
  <div class="bg-white p-6 rounded-xl border border-[#E6E4DD] flex flex-wrap items-center justify-between gap-4">
    <div>
      <p class="text-[10px] font-bold tracking-widest uppercase text-[#6E716A] mb-1">Surgical Theatre &amp; Real-Time Scheduling</p>
      <h2 class="text-3xl font-serif-display font-bold text-[#1C1C1B]">Operation Schedule &amp; Theatre Monitoring</h2>
      <p class="text-xs text-[#6E716A] mt-0.5">
        Live surgical caseload tracking, operating theatre capacity, and procedural audit logs.
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2 text-xs">
      <div class="flex items-center gap-2">
        <label for="operation-date" class="text-xs font-semibold text-[#6E716A]">Schedule Date:</label>
        <input
          type="date"
          id="operation-date"
          value="{{ $selectedDate }}"
          onchange="applyDateFilter()"
          class="px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E] font-medium"
        >
      </div>
      <button
        type="button"
        onclick="fetchLiveMetrics()"
        class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold hover:bg-[#1E352C] transition flex items-center gap-1.5"
      >
        <span>🔄</span>
        <span>Refresh Now</span>
      </button>
    </div>
  </div>

  <!-- THEATRE STATUS BANNER -->
  <div class="bg-[#F3F1EA] p-4 rounded-xl border border-[#E2DFD5] flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-lg bg-[#14241E] text-white flex items-center justify-center text-sm">🏥</div>
      <div>
        <p class="text-xs font-bold text-[#1C1C1B]">Operating Suite Telemetry</p>
        <p class="text-[11px] text-[#6E716A]">Continuous sync across scheduled surgical procedures, live theatre blocks, and postoperative updates.</p>
      </div>
    </div>
    <div class="flex items-center gap-2 text-xs">
      <span class="px-3 py-1.5 rounded-lg bg-white border border-[#E6E4DD] text-[11px] font-semibold text-[#1C1C1B] flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
        Continuous OR Sync
      </span>
    </div>
  </div>

  <!-- THE 4 REAL-TIME TOP METRIC BARS -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- 1. Today's Caseload (Emergency Priority) -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">1. Today's Caseload</span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#F9EAEA] text-[#8B2626]">Emergency Priority</span>
        </div>
        <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]" id="kpi-caseload-total">
          {{ $metrics['caseload']['total'] }} <span class="text-sm font-sans font-medium text-[#6E716A]">Cases</span>
        </p>
      </div>
      <div class="mt-4 pt-3 border-t border-[#F0EFEA] flex items-center gap-2 flex-wrap text-xs">
        <span class="px-2 py-0.5 rounded font-semibold text-[11px] bg-[#F9EAEA] text-[#8B2626]" id="kpi-caseload-emergency">
          🚨 {{ $metrics['caseload']['emergency'] }} Emergency
        </span>
        <span class="text-[11px] text-[#6E716A]" id="kpi-caseload-elective">
          ({{ $metrics['caseload']['elective'] }} Elective)
        </span>
      </div>
    </div>

    <!-- 2. Today's Completed (Pending Active) -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">2. Today's Completed</span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Pending Active</span>
        </div>
        <p class="text-3xl font-serif-display font-bold text-[#14241E]" id="kpi-completed-count">
          {{ $metrics['completed']['completed'] }} <span class="text-sm font-sans font-medium text-[#6E716A]">Done</span>
        </p>
      </div>
      <div class="mt-4 pt-3 border-t border-[#F0EFEA] flex items-center gap-2 flex-wrap text-xs">
        <span class="px-2 py-0.5 rounded font-semibold text-[11px] bg-[#EEF2EC] text-[#14241E]" id="kpi-active-count">
          🔵 {{ $metrics['completed']['active'] }} Active in OR
        </span>
        <span class="px-2 py-0.5 rounded font-semibold text-[11px] bg-[#F3F1EA] text-[#1C1C1B]" id="kpi-pending-count">
          ⏳ {{ $metrics['completed']['pending'] }} Pending
        </span>
      </div>
    </div>

    <!-- 3. Today's Cancelled (Audit logs stored safely) -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">3. Today's Cancelled</span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#F9EAEA] text-[#8B2626]">Audit Logs</span>
        </div>
        <p class="text-3xl font-serif-display font-bold text-[#8B2626]" id="kpi-cancelled-count">
          {{ $metrics['cancelled']['cancelled'] }} <span class="text-sm font-sans font-medium text-[#6E716A]">Cancelled</span>
        </p>
      </div>
      <div class="mt-4 pt-3 border-t border-[#F0EFEA] flex items-center gap-2 text-xs">
        <span class="px-2.5 py-0.5 rounded font-semibold text-[11px] bg-[#F3F1EA] text-[#475569]" id="kpi-audit-status">
          🛡️ {{ $metrics['cancelled']['audit_status'] }}
        </span>
      </div>
    </div>

    <!-- 4. Theatre Utilization (Also Average Duration) -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">4. Theatre Utilization</span>
          <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Average Duration</span>
        </div>
        <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]" id="kpi-utilization-rate">
          {{ $metrics['theatre_utilization']['rate'] }}%
        </p>
        <!-- Progress bar -->
        <div class="w-full h-1.5 bg-[#F3F1EA] rounded-full overflow-hidden mt-3">
          <div
            id="kpi-progress-fill"
            class="h-full bg-[#14241E] rounded-full transition-all duration-500"
            style="width: {{ min(100, $metrics['theatre_utilization']['rate']) }}%;"
          ></div>
        </div>
      </div>
      <div class="mt-4 pt-3 border-t border-[#F0EFEA] flex items-center justify-between text-xs">
        <span class="px-2 py-0.5 rounded font-semibold text-[11px] bg-[#EEF2EC] text-[#14241E]" id="kpi-avg-duration">
          ⏱️ {{ $metrics['theatre_utilization']['avg_duration_minutes'] }} min avg duration
        </span>
        <span class="text-[11px] text-[#6E716A]" id="kpi-theatres-count">
          {{ $metrics['theatre_utilization']['active_theatres'] }} Active ORs
        </span>
      </div>
    </div>

  </div>

  <!-- THEATRE SCHEDULE OVERVIEW & AUDIT SUMMARY -->
  <div class="bg-white rounded-xl border border-[#E6E4DD] p-6 space-y-4">
    <div class="flex items-center justify-between pb-4 border-b border-[#F0EFEA]">
      <div>
        <h3 class="text-base font-bold text-[#1C1C1B]">Operating Room Utilization Protocol</h3>
        <p class="text-xs text-[#6E716A]">Live audit safeguards and operational constraints for OT suites.</p>
      </div>
      <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-[#EEF2EC] text-[#14241E]">
        QMS Compliant
      </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
      <div class="p-4 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD]">
        <p class="text-xs font-bold text-[#1C1C1B] mb-1">🚨 Emergency Priority Override</p>
        <p class="text-[11px] text-[#6E716A]">
          Immediate booking slot allocation with audit recording. Elective cases are securely deferred with reason capture.
        </p>
      </div>
      <div class="p-4 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD]">
        <p class="text-xs font-bold text-[#1C1C1B] mb-1">🛡️ Immutable Audit Preservation</p>
        <p class="text-[11px] text-[#6E716A]">
          Audit logs stored safely for every cancelled, modified, or delayed procedure with clinical timestamps.
        </p>
      </div>
      <div class="p-4 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD]">
        <p class="text-xs font-bold text-[#1C1C1B] mb-1">⚡ Theatre Utilization Telemetry</p>
        <p class="text-[11px] text-[#6E716A]">
          Automated block utilization calculations based on total booked surgical minutes over standard operating capacity.
        </p>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  function applyDateFilter() {
    fetchLiveMetrics();
  }

  async function fetchLiveMetrics() {
    const dateInput = document.getElementById('operation-date');
    const selectedDate = dateInput ? dateInput.value : '';

    try {
      const url = new URL('{{ route("operations.index") }}', window.location.origin);
      url.searchParams.set('feed', '1');
      if (selectedDate) {
        url.searchParams.set('date', selectedDate);
      }

      const response = await fetch(url.toString(), {
        headers: { 'Accept': 'application/json' }
      });

      if (!response.ok) return;
      const data = await response.json();

      if (data.success && data.metrics) {
        const m = data.metrics;

        // 1. Caseload
        const caseloadEl = document.getElementById('kpi-caseload-total');
        if (caseloadEl) {
          caseloadEl.innerHTML = `${m.caseload.total} <span class="text-sm font-sans font-medium text-[#6E716A]">Cases</span>`;
        }
        const emergencyEl = document.getElementById('kpi-caseload-emergency');
        if (emergencyEl) {
          emergencyEl.textContent = `🚨 ${m.caseload.emergency} Emergency`;
        }
        const electiveEl = document.getElementById('kpi-caseload-elective');
        if (electiveEl) {
          electiveEl.textContent = `(${m.caseload.elective} Elective)`;
        }

        // 2. Completed
        const completedEl = document.getElementById('kpi-completed-count');
        if (completedEl) {
          completedEl.innerHTML = `${m.completed.completed} <span class="text-sm font-sans font-medium text-[#6E716A]">Done</span>`;
        }
        const activeEl = document.getElementById('kpi-active-count');
        if (activeEl) {
          activeEl.textContent = `🔵 ${m.completed.active} Active in OR`;
        }
        const pendingEl = document.getElementById('kpi-pending-count');
        if (pendingEl) {
          pendingEl.textContent = `⏳ ${m.completed.pending} Pending`;
        }

        // 3. Cancelled
        const cancelledEl = document.getElementById('kpi-cancelled-count');
        if (cancelledEl) {
          cancelledEl.innerHTML = `${m.cancelled.cancelled} <span class="text-sm font-sans font-medium text-[#6E716A]">Cancelled</span>`;
        }
        const auditEl = document.getElementById('kpi-audit-status');
        if (auditEl) {
          auditEl.textContent = `🛡️ ${m.cancelled.audit_status}`;
        }

        // 4. Theatre Utilization
        const utilEl = document.getElementById('kpi-utilization-rate');
        if (utilEl) {
          utilEl.textContent = `${m.theatre_utilization.rate}%`;
        }
        const progressEl = document.getElementById('kpi-progress-fill');
        if (progressEl) {
          progressEl.style.width = `${Math.min(100, m.theatre_utilization.rate)}%`;
        }
        const durationEl = document.getElementById('kpi-avg-duration');
        if (durationEl) {
          durationEl.textContent = `⏱️ ${m.theatre_utilization.avg_duration_minutes} min avg duration`;
        }
        const theatresEl = document.getElementById('kpi-theatres-count');
        if (theatresEl) {
          theatresEl.textContent = `${m.theatre_utilization.active_theatres} Active ORs`;
        }
      }
    } catch (err) {
      console.warn('Live metrics auto-sync paused.');
    }
  }

  // Auto-poll every 6 seconds without page reload
  setInterval(fetchLiveMetrics, 6000);
</script>
@endsection
