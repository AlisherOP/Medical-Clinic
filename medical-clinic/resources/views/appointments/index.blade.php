@extends('layouts.app')

@section('title', 'Live Appointment Queue Board | QMS Clinical')

@section('header_title', 'Appointments')

@section('styles')
<style>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(20, 36, 30, 0.45);
    backdrop-filter: blur(3px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 50;
  }
  @keyframes highlightRow {
    0% { background-color: #D4DDD0; }
    100% { background-color: transparent; }
  }
  .row-highlight {
    animation: highlightRow 2.5s ease-out;
  }
  @media print {
    body * {
      visibility: hidden;
    }
    #printableTicketSlip, #printableTicketSlip * {
      visibility: visible;
    }
    #printableTicketSlip {
      position: absolute;
      left: 50%;
      top: 20px;
      transform: translateX(-50%);
      width: 80mm !important;
      margin: 0 auto;
      padding: 12px;
      background: white !important;
      color: black !important;
      box-shadow: none !important;
      border: 1px dashed #999 !important;
    }
    .no-print {
      display: none !important;
    }
  }
</style>
@endsection

@section('content')
  <!-- OVERVIEW HEADER + ACTION CONTROLS -->
  <div class="bg-white p-6 rounded-xl border border-[#E6E4DD] flex flex-wrap items-center justify-between gap-4">
    <div>
      <p class="text-[10px] font-bold tracking-widest uppercase text-[#6E716A] mb-1">Outpatient Clinic &amp; Triage Queue</p>
      <h2 class="text-3xl font-serif-display font-bold text-[#1C1C1B]">Live Appointment Queue Board</h2>
      <p class="text-xs text-[#6E716A] mt-0.5">
        Manage real-time outpatient check-ins, doctor assignments, and consultation statuses.
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2 text-xs">
      <!-- Audio Chime Toggle -->
      <button
        type="button"
        id="btn-toggle-chime"
        onclick="toggleChimeAudio()"
        class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-[#1C1C1B] hover:bg-[#F9F8F4] transition flex items-center gap-1.5"
        title="Toggle Calling Chime Sound"
      >
        <span id="chimeIcon">🔊</span>
        <span id="chimeStatusText">Chime: On</span>
      </button>

      <!-- Lobby Display TV Mode Button -->
      <button
        type="button"
        onclick="openLobbyDisplay()"
        class="px-3.5 py-2 rounded-lg bg-[#F3F1EA] border border-[#E2DFD5] font-semibold text-[#14241E] hover:bg-[#EAE6DC] transition flex items-center gap-1.5"
      >
        <span>📺</span>
        <span>Lobby TV Display</span>
      </button>

      <!-- Refresh Now -->
      <button
        type="button"
        onclick="fetchLiveQueue()"
        class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-[#1C1C1B] hover:bg-[#F9F8F4] transition flex items-center gap-1.5"
      >
        <span>🔄</span>
        <span>Refresh Now</span>
      </button>

      <!-- Book Appointment -->
      <button
        type="button"
        onclick="openBookingModal()"
        class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold hover:bg-[#1E352C] transition flex items-center gap-1.5"
      >
        <span>+</span>
        <span>Book Appointment</span>
      </button>
    </div>
  </div>

  <!-- NOW CALLING AUDIO/VISUAL BROADCAST BANNER -->
  <div id="callingAlertBanner" class="hidden bg-[#14241E] text-white px-5 py-3.5 rounded-xl border border-white/10 flex items-center justify-between gap-4 shadow-lg transition-all">
    <div class="flex items-center gap-3.5">
      <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-base animate-pulse">
        🔔
      </div>
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500 text-[#14241E]">Now Calling</span>
          <span id="callingTicketNum" class="text-sm font-mono font-bold text-white"></span>
          <span class="text-xs text-[#A3A89E]">•</span>
          <span id="callingRoomText" class="text-xs font-semibold text-emerald-300"></span>
        </div>
        <p id="callingPatientText" class="text-xs font-medium text-[#E6E4DD] mt-0.5"></p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button type="button" onclick="playCallingChime()" class="text-xs text-[#A3A89E] hover:text-white" title="Replay Chime">Replay 🔔</button>
      <button type="button" onclick="dismissCallingNotice()" class="text-xs text-[#A3A89E] hover:text-white font-semibold">✕</button>
    </div>
  </div>

  <!-- SERVER & CLIENT SUCCESS NOTICES -->
  @if(session('success'))
    <div class="bg-[#EEF2EC] text-[#14241E] border border-[#D4DDD0] px-5 py-3.5 rounded-xl text-xs font-semibold flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  <div id="liveNoticeBanner" class="hidden bg-[#EEF2EC] text-[#14241E] border border-[#D4DDD0] px-5 py-3.5 rounded-xl text-xs font-semibold flex items-center justify-between gap-4">
    <div class="flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
      <span id="liveNoticeText"></span>
    </div>
    <button type="button" onclick="dismissLiveNotice()" class="text-xs text-[#6E716A] hover:text-[#1C1C1B]">✕</button>
  </div>

  <!-- LIVE KPI OVERVIEW (5 COLUMNS) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    <!-- 1. Total Today -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">Total Today</span>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Live</span>
      </div>
      <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]" id="kpi-total">{{ $kpis['total'] }}</p>
      <p class="text-[11px] text-[#6E716A] mt-1">Scheduled patients</p>
    </div>

    <!-- 2. Waiting Lobby -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">Waiting Lobby</span>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#F3F1EA] text-[#1C1C1B]">Queue</span>
      </div>
      <p class="text-3xl font-serif-display font-bold text-[#1C1C1B]" id="kpi-waiting">{{ $kpis['waiting'] }}</p>
      <p class="text-[11px] text-[#6E716A] mt-1">Checked-in &amp; ready</p>
    </div>

    <!-- 3. In Consultation -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">In Consultation</span>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Active</span>
      </div>
      <p class="text-3xl font-serif-display font-bold text-[#14241E]" id="kpi-progress">{{ $kpis['in_progress'] }}</p>
      <p class="text-[11px] text-[#6E716A] mt-1">Inside doctor room</p>
    </div>

    <!-- 4. Completed -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">Completed</span>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#EEF2EC] text-[#14241E]">Done</span>
      </div>
      <p class="text-3xl font-serif-display font-bold text-[#14241E]" id="kpi-completed">{{ $kpis['completed'] }}</p>
      <p class="text-[11px] text-[#6E716A] mt-1">Finished visits</p>
    </div>

    <!-- 5. Cancelled -->
    <div class="bg-white p-5 rounded-xl border border-[#E6E4DD] flex flex-col justify-between">
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold text-[#6E716A] uppercase tracking-wider">Cancelled</span>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#F9EAEA] text-[#8B2626]">Audit</span>
      </div>
      <p class="text-3xl font-serif-display font-bold text-[#8B2626]" id="kpi-cancelled">{{ $kpis['cancelled'] }}</p>
      <p class="text-[11px] text-[#6E716A] mt-1">Slot released safely</p>
    </div>
  </div>

  <!-- FILTERS, STATUS TABS & INSTANT SEARCH BAR -->
  <div class="bg-[#F3F1EA] p-4 rounded-xl border border-[#E2DFD5] flex flex-wrap items-center justify-between gap-4">
    <!-- Status Filter Buttons -->
    <div class="flex flex-wrap items-center gap-1.5 text-xs">
      <button
        type="button"
        class="tab-btn px-3 py-1.5 rounded-lg font-semibold bg-[#14241E] text-white transition active"
        data-filter="all"
        onclick="setFilterTab('all', this)"
      >
        All Visits
      </button>
      <button
        type="button"
        class="tab-btn px-3 py-1.5 rounded-lg font-semibold text-[#6E716A] hover:bg-white/60 transition"
        data-filter="waiting"
        onclick="setFilterTab('waiting', this)"
      >
        Waiting Lobby
      </button>
      <button
        type="button"
        class="tab-btn px-3 py-1.5 rounded-lg font-semibold text-[#6E716A] hover:bg-white/60 transition"
        data-filter="in_progress"
        onclick="setFilterTab('in_progress', this)"
      >
        In Consultation
      </button>
      <button
        type="button"
        class="tab-btn px-3 py-1.5 rounded-lg font-semibold text-[#6E716A] hover:bg-white/60 transition"
        data-filter="scheduled"
        onclick="setFilterTab('scheduled', this)"
      >
        Scheduled
      </button>
      <button
        type="button"
        class="tab-btn px-3 py-1.5 rounded-lg font-semibold text-[#6E716A] hover:bg-white/60 transition"
        data-filter="completed"
        onclick="setFilterTab('completed', this)"
      >
        Completed
      </button>
      <button
        type="button"
        class="tab-btn px-3 py-1.5 rounded-lg font-semibold text-[#6E716A] hover:bg-white/60 transition"
        data-filter="cancelled"
        onclick="setFilterTab('cancelled', this)"
      >
        Cancelled
      </button>
    </div>

    <!-- Instant Search Bar, Date & Doctor Select -->
    <div class="flex flex-wrap items-center gap-3 text-xs">
      <!-- Instant Search Input -->
      <div class="relative w-56">
        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#6E716A] text-xs">🔍</span>
        <input
          type="text"
          id="filter-search"
          placeholder="Search patient, MRN, ticket, doc..."
          oninput="applyClientSideFilters()"
          class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-white border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E] text-xs"
        >
      </div>

      <!-- Date Filter -->
      <div class="flex items-center gap-2">
        <label for="filter-date" class="font-semibold text-[#6E716A]">Date:</label>
        <input
          type="date"
          id="filter-date"
          value="{{ $selectedDate }}"
          onchange="applyFilters()"
          class="px-3 py-1.5 rounded-lg bg-white border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E]"
        >
      </div>

      <!-- Doctor Filter -->
      <div class="flex items-center gap-2">
        <label for="filter-doctor" class="font-semibold text-[#6E716A]">Doctor:</label>
        <select
          id="filter-doctor"
          onchange="applyFilters()"
          class="px-3 py-1.5 rounded-lg bg-white border border-[#E6E4DD] text-[#1C1C1B] outline-none focus:border-[#14241E]"
        >
          <option value="">All Doctors</option>
          @foreach($doctors as $doc)
            <option value="{{ $doc->id }}" {{ $doctorId == $doc->id ? 'selected' : '' }}>
              Dr. {{ $doc->user->last_name }} ({{ $doc->department->name ?? 'General' }})
            </option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  <!-- THE QUEUE BOARD TABLE -->
  <div class="bg-white rounded-xl border border-[#E6E4DD] overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-[#F9F8F4] border-b border-[#E6E4DD] text-[11px] font-bold uppercase tracking-wider text-[#6E716A]">
            <th class="py-3.5 px-5 w-20">Ticket #</th>
            <th class="py-3.5 px-5">Patient Name</th>
            <th class="py-3.5 px-5">MR Number</th>
            <th class="py-3.5 px-5">Schedule &amp; Wait</th>
            <th class="py-3.5 px-5">Doctor &amp; Station</th>
            <th class="py-3.5 px-5">Status</th>
            <th class="py-3.5 px-5">Cancellation Reason</th>
            <th class="py-3.5 px-5 text-right">Live Actions</th>
          </tr>
        </thead>
        <tbody id="queue-table-body" class="divide-y divide-[#F0EFEA]">
          @forelse($appointments as $appt)
            @php
              $telemetry = $appt->telemetry ?? ['wait_text' => 'Scheduled', 'patients_ahead' => null];
              $roomName = $appt->doctor?->consultation_room ?? 'Suite 101';
            @endphp
            <tr
              id="appt-row-{{ $appt->id }}"
              data-status="{{ $appt->status }}"
              data-search="{{ strtolower(($appt->patient->user->full_name ?? '') . ' ' . ($appt->patient->mrn ?? '') . ' ' . $appt->queue_number . ' ' . ($appt->doctor->user->last_name ?? '') . ' ' . ($appt->doctor->department->name ?? '') . ' ' . $roomName) }}"
              class="hover:bg-[#FAF9F5] transition"
            >
              <td class="py-4 px-5">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#14241E] text-white font-mono font-bold text-xs shadow-sm">
                  #{{ sprintf('%02d', $appt->queue_number) }}
                </span>
              </td>
              <td class="py-4 px-5">
                <div>
                  <p class="font-bold text-[#1C1C1B] text-sm">{{ $appt->patient->user->full_name ?? 'Unknown Patient' }}</p>
                  <p class="text-[11px] text-[#6E716A] capitalize">{{ $appt->type }}</p>
                </div>
              </td>
              <td class="py-4 px-5">
                <span class="inline-block px-2.5 py-1 rounded bg-[#F3F1EA] text-[#475569] font-mono text-[11px] font-semibold border border-[#E2DFD5]">
                  {{ $appt->patient->mrn ?? 'N/A' }}
                </span>
              </td>
              <td class="py-4 px-5">
                <p class="font-bold text-[#1C1C1B]">{{ \Carbon\Carbon::parse($appt->scheduled_at)->format('H:i') }}</p>
                <div class="mt-0.5">
                  @if($appt->status === 'waiting')
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#FDF6B2] text-[#723B13] text-[10px] font-semibold">
                      ⏱️ {{ $telemetry['wait_text'] }}
                    </span>
                  @elseif($appt->status === 'in_progress')
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#EEF2EC] text-[#14241E] text-[10px] font-semibold">
                      🟢 Calling / In OR
                    </span>
                  @else
                    <span class="text-[11px] text-[#6E716A]">{{ $appt->duration_minutes ?? 15 }} mins</span>
                  @endif
                </div>
              </td>
              <td class="py-4 px-5">
                <p class="font-bold text-[#1C1C1B]">Dr. {{ $appt->doctor->user->last_name ?? 'Doctor' }}</p>
                <div class="flex items-center gap-1.5 text-[11px] text-[#6E716A] mt-0.5">
                  <span>{{ $appt->doctor->department->name ?? 'General Medicine' }}</span>
                  <span>•</span>
                  <span class="font-semibold text-[#14241E] bg-[#EEF2EC] px-1.5 py-0.2 rounded text-[10px] border border-[#D4DDD0]">{{ $roomName }}</span>
                </div>
              </td>
              <td class="py-4 px-5">
                @php
                  $badgeClasses = match($appt->status) {
                    'in_progress' => 'bg-[#EEF2EC] text-[#14241E] border-[#D4DDD0]',
                    'completed' => 'bg-[#EEF2EC] text-[#14241E] border-[#D4DDD0]',
                    'cancelled' => 'bg-[#F9EAEA] text-[#8B2626] border-[#EFC7C7]',
                    'waiting' => 'bg-[#FDF6B2] text-[#723B13] border-[#FCE96A]',
                    default => 'bg-[#F9F8F4] text-[#6E716A] border-[#E6E4DD]',
                  };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $badgeClasses }} capitalize">
                  {{ str_replace('_', ' ', $appt->status) }}
                </span>
              </td>
              <td class="py-4 px-5">
                @if($appt->status === 'cancelled' && $appt->notes)
                  <div class="text-[11px] text-[#8B2626] bg-[#F9EAEA] px-2.5 py-1 rounded border border-[#EFC7C7] max-w-xs">
                    {{ $appt->notes }}
                  </div>
                @else
                  <span class="text-[#A3A89E]">—</span>
                @endif
              </td>
              <td class="py-4 px-5 text-right">
                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                  <!-- Printable Ticket Slip Action -->
                  <button
                    type="button"
                    onclick="openTicketSlip({{ $appt->id }}, '{{ sprintf('%02d', $appt->queue_number) }}', '{{ addslashes($appt->patient->user->full_name ?? 'Unknown') }}', '{{ $appt->patient->mrn ?? 'N/A' }}', '{{ \Carbon\Carbon::parse($appt->scheduled_at)->format('Y-m-d H:i') }}', 'Dr. {{ addslashes($appt->doctor->user->last_name ?? 'Doctor') }}', '{{ addslashes($appt->doctor->department->name ?? 'General') }}', '{{ $roomName }}', '{{ $telemetry['wait_text'] }}', '{{ ucfirst($appt->type) }}')"
                    class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white text-[#1C1C1B] border border-[#E6E4DD] hover:bg-[#F9F8F4] transition flex items-center gap-1"
                    title="Print Intake Queue Ticket"
                  >
                    <span>🖨️</span>
                    <span>Ticket</span>
                  </button>

                  @if($appt->status === 'scheduled')
                    <button
                      type="button"
                      onclick="updateStatus({{ $appt->id }}, 'check-in')"
                      class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F3F1EA] text-[#1C1C1B] border border-[#D4DDD0] hover:bg-[#E6E4DD] transition"
                    >
                      Check In
                    </button>
                  @endif

                  @if(in_array($appt->status, ['scheduled', 'waiting']))
                    <!-- Call In with Calling Sound & Banner -->
                    <button
                      type="button"
                      onclick="callPatientIn({{ $appt->id }}, '{{ sprintf('%02d', $appt->queue_number) }}', '{{ addslashes($appt->patient->user->full_name ?? 'Patient') }}', 'Dr. {{ addslashes($appt->doctor->user->last_name ?? 'Doctor') }}', '{{ $roomName }}')"
                      class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#14241E] text-white hover:bg-[#1E352C] transition flex items-center gap-1"
                    >
                      <span>🔔</span>
                      <span>Call In</span>
                    </button>
                    <button
                      type="button"
                      onclick="openRescheduleModal({{ $appt->id }}, '{{ addslashes($appt->patient->user->full_name ?? '') }}', {{ $appt->doctor_id }}, '{{ \Carbon\Carbon::parse($appt->scheduled_at)->format('Y-m-d\TH:i') }}', {{ $appt->duration_minutes ?? 15 }})"
                      class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F3F1EA] text-[#1C1C1B] border border-[#D4DDD0] hover:bg-[#E6E4DD] transition"
                    >
                      Reschedule
                    </button>
                  @endif

                  @if($appt->status === 'in_progress')
                    <button
                      type="button"
                      onclick="openCompleteModal({{ $appt->id }}, '{{ addslashes($appt->patient->user->full_name) }}')"
                      class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-700 text-white hover:bg-emerald-800 transition"
                    >
                      Complete
                    </button>
                  @endif

                  @if(!in_array($appt->status, ['completed', 'cancelled']))
                    <button
                      type="button"
                      onclick="openCancelModal({{ $appt->id }})"
                      class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F9EAEA] text-[#8B2626] border border-[#EFC7C7] hover:bg-[#F3D5D5] transition"
                    >
                      Cancel
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr id="empty-row">
              <td colspan="8" class="text-center py-12 px-4 text-[#6E716A]">
                <div class="text-3xl mb-2">📋</div>
                <p class="font-bold text-sm text-[#1C1C1B]">No appointments scheduled for this date.</p>
                <p class="text-xs mt-1">Click "+ Book Appointment" above to schedule a patient.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- MODAL 1: BOOK APPOINTMENT -->
  <div id="bookingModal" class="modal-overlay">
    <div class="bg-white rounded-xl border border-[#E6E4DD] max-w-lg w-full p-6 shadow-xl mx-4 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#F0EFEA]">
        <div>
          <h3 class="text-lg font-bold text-[#1C1C1B]">Book New Appointment</h3>
          <p class="text-xs text-[#6E716A]">Schedule patient consultation with automatic queue ticket assignment.</p>
        </div>
        <button type="button" class="text-lg text-[#6E716A] hover:text-[#1C1C1B]" onclick="closeModal('bookingModal')">&times;</button>
      </div>

      <form action="{{ route('appointments.store') }}" method="POST" id="bookingForm" onsubmit="submitBooking(event)" class="space-y-4">
        @csrf
        <div id="booking-error" class="hidden bg-[#F9EAEA] border border-[#EFC7C7] text-[#8B2626] p-3 rounded-lg text-xs font-semibold"></div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Patient *</label>
          <select name="patient_id" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
            <option value="">Select Patient</option>
            @foreach($patients as $p)
              <option value="{{ $p->id }}">{{ $p->user->full_name }} ({{ $p->mrn }})</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Doctor *</label>
          <select name="doctor_id" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
            <option value="">Select Doctor</option>
            @foreach($doctors as $d)
              <option value="{{ $d->id }}">Dr. {{ $d->user->full_name }} — {{ $d->department->name ?? 'General' }} ({{ $d->consultation_room }})</option>
            @endforeach
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Date &amp; Time *</label>
            <input type="datetime-local" name="scheduled_at" id="booking-scheduled-at" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
          </div>
          <div>
            <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Duration (mins)</label>
            <input type="number" name="duration_minutes" value="15" min="5" max="180" step="5" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Appointment Type *</label>
          <select name="type" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
            <option value="consultation" selected>General Consultation</option>
            <option value="checkup">Routine Checkup</option>
            <option value="emergency">Emergency Triage</option>
            <option value="operation">Surgical Operation</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Chief Complaint / Clinical Notes</label>
          <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" placeholder="e.g. Fever, routine follow-up..."></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0EFEA]">
          <button type="button" class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-xs text-[#1C1C1B] hover:bg-[#F9F8F4] transition" onclick="closeModal('bookingModal')">Cancel</button>
          <button type="submit" id="btn-submit-booking" class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold text-xs hover:bg-[#1E352C] transition">Book Ticket</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 2: COMPLETE CONSULTATION -->
  <div id="completeModal" class="modal-overlay">
    <div class="bg-white rounded-xl border border-[#E6E4DD] max-w-lg w-full p-6 shadow-xl mx-4 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#F0EFEA]">
        <h3 class="text-lg font-bold text-[#1C1C1B]">Complete Consultation</h3>
        <button type="button" class="text-lg text-[#6E716A] hover:text-[#1C1C1B]" onclick="closeModal('completeModal')">&times;</button>
      </div>

      <form id="completeForm" onsubmit="submitComplete(event)" class="space-y-4">
        <input type="hidden" id="complete-appt-id">

        <div class="text-xs font-semibold text-[#6E716A]">
          Patient: <span id="complete-patient-name" class="text-[#14241E] font-bold"></span>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Diagnosis *</label>
          <input type="text" id="complete-diagnosis" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" placeholder="e.g. Acute Viral Bronchitis" required>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Doctor's Advice &amp; Treatment Plan</label>
          <textarea id="complete-notes" rows="3" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" placeholder="Prescribed rest, follow-up in 7 days..."></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0EFEA]">
          <button type="button" class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-xs text-[#1C1C1B] hover:bg-[#F9F8F4] transition" onclick="closeModal('completeModal')">Back</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-700 text-white font-semibold text-xs hover:bg-emerald-800 transition">Save &amp; Complete</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 3: CANCEL APPOINTMENT -->
  <div id="cancelModal" class="modal-overlay">
    <div class="bg-white rounded-xl border border-[#E6E4DD] max-w-md w-full p-6 shadow-xl mx-4 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#F0EFEA]">
        <h3 class="text-lg font-bold text-[#8B2626]">Cancel Appointment</h3>
        <button type="button" class="text-lg text-[#6E716A] hover:text-[#1C1C1B]" onclick="closeModal('cancelModal')">&times;</button>
      </div>

      <form id="cancelForm" onsubmit="submitCancel(event)" class="space-y-4">
        <input type="hidden" id="cancel-appt-id">
        <p class="text-xs text-[#6E716A]">
          This will release the doctor's time slot for other patients. Please provide an audit cancellation reason:
        </p>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Reason *</label>
          <select id="cancel-reason-select" onchange="toggleCancelOther(this)" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]">
            <option value="Patient requested reschedule">Patient requested reschedule</option>
            <option value="Patient did not arrive (No Show)">Patient did not arrive (No Show)</option>
            <option value="Doctor emergency / unavailable">Doctor emergency / unavailable</option>
            <option value="Duplicate booking">Duplicate booking</option>
            <option value="Other">Other reason...</option>
          </select>
        </div>

        <div id="cancel-other-group" class="hidden">
          <input type="text" id="cancel-reason-other" placeholder="Specify cancellation reason..." class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]">
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0EFEA]">
          <button type="button" class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-xs text-[#1C1C1B] hover:bg-[#F9F8F4] transition" onclick="closeModal('cancelModal')">Close</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-[#8B2626] text-white font-semibold text-xs hover:bg-[#721F1F] transition">Confirm Cancellation</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 4: RESCHEDULE / REASSIGN APPOINTMENT -->
  <div id="rescheduleModal" class="modal-overlay">
    <div class="bg-white rounded-xl border border-[#E6E4DD] max-w-lg w-full p-6 shadow-xl mx-4 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#F0EFEA]">
        <div>
          <h3 class="text-lg font-bold text-[#1C1C1B]">Reschedule / Reassign Doctor</h3>
          <p class="text-xs text-[#6E716A]">Update appointment time slot or assign to an alternate physician.</p>
        </div>
        <button type="button" class="text-lg text-[#6E716A] hover:text-[#1C1C1B]" onclick="closeModal('rescheduleModal')">&times;</button>
      </div>

      <form id="rescheduleForm" onsubmit="submitReschedule(event)" class="space-y-4">
        <input type="hidden" id="reschedule-appt-id">
        <div id="reschedule-error" class="hidden bg-[#F9EAEA] border border-[#EFC7C7] text-[#8B2626] p-3 rounded-lg text-xs font-semibold"></div>

        <div class="p-3 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs">
          <p class="font-bold text-[#1C1C1B]">Patient: <span id="reschedule-patient-name" class="font-semibold text-[#14241E]"></span></p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Attending Doctor *</label>
          <select id="reschedule-doctor-id" name="doctor_id" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
            @foreach($doctors as $d)
              <option value="{{ $d->id }}">Dr. {{ $d->user->full_name }} — {{ $d->department->name ?? 'General' }} ({{ $d->consultation_room }})</option>
            @endforeach
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">New Date &amp; Time *</label>
            <input type="datetime-local" id="reschedule-scheduled-at" name="scheduled_at" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
          </div>
          <div>
            <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Duration (mins)</label>
            <input type="number" id="reschedule-duration" name="duration_minutes" value="15" min="5" max="180" step="5" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Reschedule Reason / Clinical Note</label>
          <textarea id="reschedule-notes" name="notes" rows="2" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" placeholder="e.g. Patient requested morning slot / doctor transferred case..."></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F0EFEA]">
          <button type="button" class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-xs text-[#1C1C1B] hover:bg-[#F9F8F4] transition" onclick="closeModal('rescheduleModal')">Cancel</button>
          <button type="submit" id="btn-submit-reschedule" class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold text-xs hover:bg-[#1E352C] transition">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL 5: PRINTABLE INTAKE QUEUE TICKET SLIP -->
  <div id="ticketModal" class="modal-overlay">
    <div class="bg-white rounded-xl border border-[#E6E4DD] max-w-sm w-full p-6 shadow-2xl mx-4 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#F0EFEA] no-print">
        <div>
          <h3 class="text-base font-bold text-[#1C1C1B]">Clinic Queue Ticket</h3>
          <p class="text-[11px] text-[#6E716A]">Intake slip for waiting room and patient verification</p>
        </div>
        <button type="button" class="text-lg text-[#6E716A] hover:text-[#1C1C1B]" onclick="closeModal('ticketModal')">&times;</button>
      </div>

      <!-- THERMAL TICKET SLIP BODY -->
      <div id="printableTicketSlip" class="bg-white p-5 rounded-lg border-2 border-dashed border-[#D4DDD0] font-sans text-center space-y-3.5">
        <!-- Clinic Branding Header -->
        <div class="border-b border-black/15 pb-2.5">
          <p class="text-[10px] font-bold tracking-widest uppercase text-gray-500">QMS Clinical Health System</p>
          <h4 class="text-sm font-bold uppercase tracking-wider text-black">Outpatient Consultation Ticket</h4>
          <p class="text-[10px] text-gray-600">Central Ambulatory Registry</p>
        </div>

        <!-- Huge Ticket Number -->
        <div class="py-1">
          <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Your Queue Number</p>
          <div id="ticket-number" class="text-5xl font-mono font-extrabold text-[#14241E] tracking-tight my-0.5">#01</div>
          <span id="ticket-type" class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#F3F1EA] text-[#14241E] border border-[#E2DFD5]">Consultation</span>
        </div>

        <!-- Patient & Doctor Information -->
        <div class="text-left text-xs bg-[#F9F8F4] p-3 rounded-lg border border-[#E6E4DD] space-y-1.5">
          <div class="flex justify-between items-center">
            <span class="text-gray-500 text-[11px]">Patient:</span>
            <span id="ticket-patient" class="font-bold text-black text-right"></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-500 text-[11px]">MRN:</span>
            <span id="ticket-mrn" class="font-mono font-semibold text-black"></span>
          </div>
          <div class="flex justify-between items-center border-t border-gray-200 pt-1.5">
            <span class="text-gray-500 text-[11px]">Doctor:</span>
            <span id="ticket-doctor" class="font-bold text-black text-right"></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-500 text-[11px]">Station / Room:</span>
            <span id="ticket-room" class="font-bold text-[#14241E] bg-[#EEF2EC] px-1.5 py-0.2 rounded border border-[#D4DDD0]"></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-500 text-[11px]">Scheduled At:</span>
            <span id="ticket-time" class="font-semibold text-black"></span>
          </div>
          <div class="flex justify-between items-center border-t border-gray-200 pt-1.5">
            <span class="text-gray-500 text-[11px]">Queue Position:</span>
            <span id="ticket-wait" class="font-bold text-emerald-800"></span>
          </div>
        </div>

        <!-- Simulated Barcode Graphic -->
        <div class="pt-1">
          <div class="font-mono text-[10px] tracking-widest text-gray-400 select-none">
            ||| | |||| | ||||| || |||| ||| ||| | |||
          </div>
          <p class="text-[9px] text-gray-500 uppercase tracking-wider mt-0.5">Please wait in the main lobby. Your number will chime overhead.</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#F0EFEA] no-print">
        <button type="button" class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-xs text-[#1C1C1B] hover:bg-[#F9F8F4] transition" onclick="closeModal('ticketModal')">Close</button>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-lg bg-[#14241E] text-white font-semibold text-xs hover:bg-[#1E352C] transition flex items-center gap-1.5">
          <span>🖨️</span>
          <span>Print Slip</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL 6: LOBBY TV DISPLAY (FULLSCREEN WAITING ROOM MONITOR) -->
  <div id="lobbyDisplayModal" class="fixed inset-0 z-50 bg-[#14241E] text-white hidden flex-col">
    <!-- Lobby Top Bar -->
    <header class="h-16 px-8 border-b border-white/10 flex items-center justify-between shrink-0 bg-[#0F1C17]">
      <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-white text-[#14241E] flex items-center justify-center font-serif-display font-bold text-2xl">
          Q
        </div>
        <div>
          <h1 class="text-base font-bold uppercase tracking-wider text-white">QMS Clinical — Waiting Lobby Monitor</h1>
          <p class="text-xs text-[#A3A89E]">Outpatient Clinic Queue &amp; Doctor Room Telemetry</p>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <div class="text-right">
          <p id="lobbyLiveClock" class="font-mono font-bold text-xl text-emerald-400">00:00:00</p>
          <p class="text-[10px] uppercase tracking-wider text-[#A3A89E]">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</p>
        </div>
        <button type="button" onclick="toggleLobbyFullscreen()" class="px-3.5 py-2 rounded-lg border border-white/20 text-xs font-semibold text-[#E6E4DD] hover:bg-white/10 transition flex items-center gap-1.5" title="Toggle Fullscreen">
          <span>⛶</span>
          <span>Fullscreen</span>
        </button>
        <button type="button" onclick="closeLobbyDisplay()" class="px-4 py-2 rounded-lg border border-white/20 text-xs font-semibold text-[#E6E4DD] hover:bg-white/10 transition">
          Exit Display ✕
        </button>
      </div>
    </header>

    <!-- Lobby Split View -->
    <div class="flex-1 p-8 grid grid-cols-1 md:grid-cols-2 gap-8 overflow-y-auto">
      <!-- Left: Now Calling / Active in Consultations -->
      <div class="bg-[#182C24] p-6 rounded-2xl border border-white/10 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-6">
            <div class="flex items-center gap-2.5">
              <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
              <h2 class="text-lg font-bold tracking-wider uppercase text-emerald-400">Now Calling / In Consultation</h2>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Active</span>
          </div>

          <div id="lobby-active-list" class="space-y-4">
            <!-- Dynamic active calling cards -->
          </div>
        </div>

        <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-xs text-[#A3A89E] flex items-center justify-between">
          <span>🔊 Audio chime sounds automatically upon ticket call</span>
          <span class="text-emerald-400 font-mono">Live Sync</span>
        </div>
      </div>

      <!-- Right: Next In Line (Waiting Queue & Upcoming) -->
      <div class="bg-[#182C24] p-6 rounded-2xl border border-white/10 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-6">
            <div class="flex items-center gap-2.5">
              <span class="w-3 h-3 rounded-full bg-amber-400"></span>
              <h2 class="text-lg font-bold tracking-wider uppercase text-white">Queue &amp; Upcoming Visits</h2>
            </div>
            <span id="lobby-queue-count-pill" class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Queue</span>
          </div>

          <div id="lobby-waiting-list" class="space-y-3">
            <!-- Dynamic waiting and scheduled cards -->
          </div>
        </div>

        <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-xs text-[#A3A89E] text-center">
          Please check in with reception or proceed to your assigned Consultation Suite when your ticket number is called.
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  let currentFilterTab = 'all';
  let chimeAudioEnabled = true;

  // Initialize Audio Chime Settings from localStorage
  if (localStorage.getItem('qms_chime_enabled') === '0') {
    chimeAudioEnabled = false;
    updateChimeUI();
  }

  function toggleChimeAudio() {
    chimeAudioEnabled = !chimeAudioEnabled;
    localStorage.setItem('qms_chime_enabled', chimeAudioEnabled ? '1' : '0');
    updateChimeUI();
    if (chimeAudioEnabled) {
      playCallingChime();
    }
  }

  function updateChimeUI() {
    const icon = document.getElementById('chimeIcon');
    const text = document.getElementById('chimeStatusText');
    if (icon && text) {
      icon.textContent = chimeAudioEnabled ? '🔊' : '🔇';
      text.textContent = chimeAudioEnabled ? 'Chime: On' : 'Chime: Off';
    }
  }

  // Web Audio Hospital Calling Chime (Pure Audio Oscillator - No network audio file needed!)
  function playCallingChime() {
    if (!chimeAudioEnabled) return;
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const now = ctx.currentTime;

      // Authentic two-tone hospital ding-dong (C5: 523Hz -> G5: 784Hz)
      const tones = [
        { freq: 523.25, time: 0, duration: 0.4 },
        { freq: 783.99, time: 0.28, duration: 0.7 }
      ];

      tones.forEach(tone => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(tone.freq, now + tone.time);
        gain.gain.setValueAtTime(0.18, now + tone.time);
        gain.gain.exponentialRampToValueAtTime(0.001, now + tone.time + tone.duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now + tone.time);
        osc.stop(now + tone.time + tone.duration);
      });
    } catch (err) {
      console.warn('Audio chime blocked or unsupported.');
    }
  }

  // Call In Action: Update Status + Play Chime + Show Overhead Calling Banner
  async function callPatientIn(id, ticketNum, patientName, doctorName, roomName) {
    try {
      playCallingChime();

      // Show Calling Banner
      const banner = document.getElementById('callingAlertBanner');
      document.getElementById('callingTicketNum').textContent = `#${ticketNum}`;
      document.getElementById('callingRoomText').textContent = roomName;
      document.getElementById('callingPatientText').textContent = `${patientName} — Please proceed to ${roomName} with ${doctorName}`;
      if (banner) banner.classList.remove('hidden');

      // Update backend status to start consultation
      await updateStatus(id, 'start');
    } catch (e) {
      console.error(e);
    }
  }

  function dismissCallingNotice() {
    const banner = document.getElementById('callingAlertBanner');
    if (banner) banner.classList.add('hidden');
  }

  // Open Ticket Slip Modal
  function openTicketSlip(id, queueNumber, patientName, mrn, scheduledAt, doctorName, dept, roomName, waitText, type) {
    document.getElementById('ticket-number').textContent = `#${queueNumber}`;
    document.getElementById('ticket-patient').textContent = patientName;
    document.getElementById('ticket-mrn').textContent = mrn;
    document.getElementById('ticket-time').textContent = scheduledAt;
    document.getElementById('ticket-doctor').textContent = `${doctorName} (${dept})`;
    document.getElementById('ticket-room').textContent = roomName;
    document.getElementById('ticket-wait').textContent = waitText || 'Scheduled';
    document.getElementById('ticket-type').textContent = type || 'Consultation';
    document.getElementById('ticketModal').style.display = 'flex';
  }

  // Lobby TV Display Modal Controls
  let clockInterval = null;
  function openLobbyDisplay() {
    const modal = document.getElementById('lobbyDisplayModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    updateLobbyClock();
    if (!clockInterval) {
      clockInterval = setInterval(updateLobbyClock, 1000);
    }
    fetchLiveQueue();
  }

  function closeLobbyDisplay() {
    const modal = document.getElementById('lobbyDisplayModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    if (document.fullscreenElement) {
      document.exitFullscreen?.().catch(() => {});
    }
    if (clockInterval) {
      clearInterval(clockInterval);
      clockInterval = null;
    }
  }

  function toggleLobbyFullscreen() {
    const modal = document.getElementById('lobbyDisplayModal');
    if (!document.fullscreenElement) {
      modal.requestFullscreen?.().catch(() => {});
    } else {
      document.exitFullscreen?.().catch(() => {});
    }
  }

  function updateLobbyClock() {
    const el = document.getElementById('lobbyLiveClock');
    if (el) {
      const now = new Date();
      el.textContent = now.toTimeString().slice(0, 8);
    }
  }

  // Live Notification Helpers
  function showLiveNotice(message) {
    const banner = document.getElementById('liveNoticeBanner');
    const textEl = document.getElementById('liveNoticeText');
    if (banner && textEl) {
      textEl.textContent = message;
      banner.classList.remove('hidden');
      setTimeout(() => {
        banner.classList.add('hidden');
      }, 7000);
    }
  }

  function dismissLiveNotice() {
    const banner = document.getElementById('liveNoticeBanner');
    if (banner) banner.classList.add('hidden');
  }

  // Open Booking Modal with Smart Date Default
  function openBookingModal() {
    const boardDateVal = document.getElementById('filter-date').value || new Date().toISOString().slice(0, 10);
    const now = new Date();
    const todayStr = now.toISOString().slice(0, 10);

    let defaultTime = "09:00";
    if (boardDateVal === todayStr) {
      now.setMinutes(now.getMinutes() + 15);
      now.setMinutes(Math.ceil(now.getMinutes() / 15) * 15);
      const hours = String(now.getHours()).padStart(2, '0');
      const mins = String(now.getMinutes()).padStart(2, '0');
      defaultTime = `${hours}:${mins}`;
    }

    const defaultDateTime = `${boardDateVal}T${defaultTime}`;
    const dateInput = document.getElementById('booking-scheduled-at');
    if (dateInput) {
      dateInput.value = defaultDateTime;
    }

    document.getElementById('booking-error').classList.add('hidden');
    document.getElementById('bookingModal').style.display = 'flex';
  }

  function openCompleteModal(id, patientName) {
    document.getElementById('complete-appt-id').value = id;
    document.getElementById('complete-patient-name').textContent = patientName;
    document.getElementById('complete-diagnosis').value = '';
    document.getElementById('complete-notes').value = '';
    document.getElementById('completeModal').style.display = 'flex';
  }

  function openCancelModal(id) {
    document.getElementById('cancel-appt-id').value = id;
    document.getElementById('cancel-reason-select').value = 'Patient requested reschedule';
    document.getElementById('cancel-other-group').classList.add('hidden');
    document.getElementById('cancelModal').style.display = 'flex';
  }

  function openRescheduleModal(id, patientName, doctorId, scheduledAt, duration) {
    document.getElementById('reschedule-appt-id').value = id;
    document.getElementById('reschedule-patient-name').textContent = patientName;
    document.getElementById('reschedule-doctor-id').value = doctorId;
    if (scheduledAt) {
      document.getElementById('reschedule-scheduled-at').value = scheduledAt.slice(0, 16);
    }
    document.getElementById('reschedule-duration').value = duration || 15;
    document.getElementById('reschedule-notes').value = '';
    document.getElementById('reschedule-error').classList.add('hidden');
    document.getElementById('rescheduleModal').style.display = 'flex';
  }

  function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  }

  function toggleCancelOther(select) {
    const group = document.getElementById('cancel-other-group');
    if (select.value === 'Other') {
      group.classList.remove('hidden');
    } else {
      group.classList.add('hidden');
    }
  }

  function setFilterTab(status, button) {
    currentFilterTab = status;
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('bg-[#14241E]', 'text-white', 'shadow-sm', 'active');
      b.classList.add('text-[#6E716A]', 'hover:bg-white/60');
    });
    button.classList.add('bg-[#14241E]', 'text-white', 'shadow-sm', 'active');
    button.classList.remove('text-[#6E716A]', 'hover:bg-white/60');
    applyClientSideFilters();
  }

  // Combined Client-Side Tab & Instant Search Filter
  function applyClientSideFilters() {
    const query = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#queue-table-body tr');
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.id === 'empty-row' || row.id === 'search-empty-row') return;
      const rowStatus = row.getAttribute('data-status');
      const rowSearchText = (row.getAttribute('data-search') || row.textContent).toLowerCase();

      const matchesTab = (currentFilterTab === 'all' || rowStatus === currentFilterTab);
      const matchesSearch = (!query || rowSearchText.includes(query));

      if (matchesTab && matchesSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    let searchEmptyRow = document.getElementById('search-empty-row');
    if (visibleCount === 0 && rows.length > 0 && query) {
      if (!searchEmptyRow) {
        searchEmptyRow = document.createElement('tr');
        searchEmptyRow.id = 'search-empty-row';
        searchEmptyRow.innerHTML = `
          <td colspan="8" class="text-center py-10 px-4 text-[#6E716A]">
            <p class="font-semibold text-xs text-[#1C1C1B]">No appointments matching "${escapeHtml(query)}" in this filter.</p>
            <p class="text-[11px] mt-1">Clear your search or switch to "All Visits".</p>
          </td>
        `;
        document.getElementById('queue-table-body').appendChild(searchEmptyRow);
      }
      searchEmptyRow.style.display = '';
    } else if (searchEmptyRow) {
      searchEmptyRow.style.display = 'none';
    }
  }

  async function updateStatus(appointmentId, action) {
    try {
      const response = await fetch(`/appointments/${appointmentId}/${action}`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });

      const result = await response.json();
      if (result.success) {
        showLiveNotice(`✓ Status updated: ${result.message || 'Action saved'}`);
        await fetchLiveQueue();
      } else {
        alert(result.message || 'Error updating status');
      }
    } catch (err) {
      console.error(err);
      alert('Network error updating appointment status.');
    }
  }

  // Submit Booking & Auto-Navigate to the Booked Date
  async function submitBooking(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('btn-submit-booking');
    const errorBox = document.getElementById('booking-error');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Booking...';
    errorBox.classList.add('hidden');

    const formData = new FormData(form);
    const scheduledAtVal = formData.get('scheduled_at');
    const bookedDate = scheduledAtVal ? scheduledAtVal.split('T')[0] : null;

    try {
      const response = await fetch('/appointments', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });

      const data = await response.json();

      if (response.ok && data.success) {
        closeModal('bookingModal');
        form.reset();

        if (bookedDate) {
          document.getElementById('filter-date').value = bookedDate;
        }

        setFilterTabToAll();
        const searchInput = document.getElementById('filter-search');
        if (searchInput) searchInput.value = '';

        await fetchLiveQueue(data.appointment?.id);

        const patName = data.appointment?.patient?.user?.full_name || 'Patient';
        const qNum = data.appointment?.queue_number || '1';
        showLiveNotice(`✓ Ticket #${qNum} booked successfully for ${patName} on ${bookedDate}!`);
      } else {
        const msg = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Booking failed';
        errorBox.textContent = msg;
        errorBox.classList.remove('hidden');
      }
    } catch (err) {
      console.error(err);
      errorBox.textContent = 'Server communication error.';
      errorBox.classList.remove('hidden');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Book Ticket';
    }
  }

  // Submit Reschedule & Reassign Doctor
  async function submitReschedule(event) {
    event.preventDefault();
    const id = document.getElementById('reschedule-appt-id').value;
    const doctorId = document.getElementById('reschedule-doctor-id').value;
    const scheduledAt = document.getElementById('reschedule-scheduled-at').value;
    const durationMinutes = document.getElementById('reschedule-duration').value;
    const notes = document.getElementById('reschedule-notes').value;
    const errorBox = document.getElementById('reschedule-error');
    const submitBtn = document.getElementById('btn-submit-reschedule');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';
    errorBox.classList.add('hidden');

    const newDate = scheduledAt ? scheduledAt.split('T')[0] : null;

    try {
      const response = await fetch(`/appointments/${id}/reschedule`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          doctor_id: doctorId,
          scheduled_at: scheduledAt,
          duration_minutes: durationMinutes,
          notes: notes
        })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        closeModal('rescheduleModal');
        if (newDate) {
          document.getElementById('filter-date').value = newDate;
        }
        setFilterTabToAll();
        await fetchLiveQueue(id);
        showLiveNotice(`✓ ${data.message || 'Appointment rescheduled successfully.'}`);
      } else {
        const msg = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Rescheduling failed';
        errorBox.textContent = msg;
        errorBox.classList.remove('hidden');
      }
    } catch (err) {
      console.error(err);
      errorBox.textContent = 'Server communication error.';
      errorBox.classList.remove('hidden');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Save Changes';
    }
  }

  // Submit Complete Consultation
  async function submitComplete(event) {
    event.preventDefault();
    const id = document.getElementById('complete-appt-id').value;
    const diagnosis = document.getElementById('complete-diagnosis').value;
    const notes = document.getElementById('complete-notes').value;

    try {
      const response = await fetch(`/appointments/${id}/complete`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ diagnosis, notes })
      });

      const result = await response.json();
      if (result.success) {
        closeModal('completeModal');
        showLiveNotice('✓ Consultation completed and recorded in registry.');
        await fetchLiveQueue();
      } else {
        alert(result.message || 'Error completing consultation.');
      }
    } catch (err) {
      console.error(err);
      alert('Network error.');
    }
  }

  // Submit Cancellation
  async function submitCancel(event) {
    event.preventDefault();
    const id = document.getElementById('cancel-appt-id').value;
    let reason = document.getElementById('cancel-reason-select').value;
    if (reason === 'Other') {
      reason = document.getElementById('cancel-reason-other').value || 'Other';
    }

    try {
      const response = await fetch(`/appointments/${id}/cancel`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason })
      });

      const result = await response.json();
      if (result.success) {
        closeModal('cancelModal');
        showLiveNotice('✓ Appointment cancelled and slot released safely.');
        await fetchLiveQueue();
      } else {
        alert(result.message || 'Error cancelling appointment.');
      }
    } catch (err) {
      console.error(err);
      alert('Network error.');
    }
  }

  function setFilterTabToAll() {
    currentFilterTab = 'all';
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('bg-[#14241E]', 'text-white', 'shadow-sm', 'active');
      b.classList.add('text-[#6E716A]', 'hover:bg-white/60');
      if (b.getAttribute('data-filter') === 'all') {
        b.classList.add('bg-[#14241E]', 'text-white', 'shadow-sm', 'active');
        b.classList.remove('text-[#6E716A]', 'hover:bg-white/60');
      }
    });
  }

  function applyFilters() {
    fetchLiveQueue();
  }

  async function fetchLiveQueue(highlightApptId = null) {
    const date = document.getElementById('filter-date').value;
    const doctorId = document.getElementById('filter-doctor').value;

    try {
      const url = new URL('{{ route("appointments.index") }}', window.location.origin);
      url.searchParams.set('feed', '1');
      if (date) url.searchParams.set('date', date);
      if (doctorId) url.searchParams.set('doctor_id', doctorId);

      const res = await fetch(url.toString(), {
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) return;
      const data = await res.json();

      if (data.kpis) {
        document.getElementById('kpi-total').textContent = data.kpis.total;
        document.getElementById('kpi-waiting').textContent = data.kpis.waiting;
        document.getElementById('kpi-progress').textContent = data.kpis.in_progress;
        document.getElementById('kpi-completed').textContent = data.kpis.completed;
        document.getElementById('kpi-cancelled').textContent = data.kpis.cancelled;
      }

      renderQueueRows(data.appointments, highlightApptId);
      updateLobbyDisplayPanels(data.appointments);
    } catch (e) {
      console.warn('Queue auto-sync paused.');
    }
  }

  function renderQueueRows(appts, highlightApptId = null) {
    const tbody = document.getElementById('queue-table-body');

    if (!appts || appts.length === 0) {
      tbody.innerHTML = `
        <tr id="empty-row">
          <td colspan="8" class="text-center py-12 px-4 text-[#6E716A]">
            <div class="text-3xl mb-2">📋</div>
            <p class="font-bold text-sm text-[#1C1C1B]">No appointments scheduled for this date.</p>
            <p class="text-xs mt-1">Click "+ Book Appointment" above to schedule a patient.</p>
          </td>
        </tr>
      `;
      return;
    }

    let html = '';
    appts.forEach(a => {
      let reasonHtml = '<span class="text-[#A3A89E]">—</span>';
      if (a.status === 'cancelled' && a.cancellation_reason) {
        reasonHtml = `<div class="text-[11px] text-[#8B2626] bg-[#F9EAEA] px-2.5 py-1 rounded border border-[#EFC7C7] max-w-xs">${escapeHtml(a.cancellation_reason)}</div>`;
      }

      const roomName = a.consultation_room || 'Suite 101';
      const waitTelemetry = a.wait_text || 'Scheduled';

      let actionsHtml = `
        <button
          type="button"
          onclick="openTicketSlip(${a.id}, '${a.queue_number}', '${escapeHtml(a.patient_name)}', '${escapeHtml(a.mrn)}', '${a.scheduled_date} ${a.scheduled_time}', '${escapeHtml(a.doctor_name)}', '${escapeHtml(a.department)}', '${escapeHtml(roomName)}', '${escapeHtml(waitTelemetry)}', '${escapeHtml(a.type)}')"
          class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white text-[#1C1C1B] border border-[#E6E4DD] hover:bg-[#F9F8F4] transition flex items-center gap-1"
          title="Print Intake Queue Ticket"
        >
          <span>🖨️</span>
          <span>Ticket</span>
        </button>
      `;

      if (a.status === 'scheduled') {
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F3F1EA] text-[#1C1C1B] border border-[#D4DDD0] hover:bg-[#E6E4DD] transition" onclick="updateStatus(${a.id}, 'check-in')">Check In</button>`;
      }
      if (a.status === 'scheduled' || a.status === 'waiting') {
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#14241E] text-white hover:bg-[#1E352C] transition flex items-center gap-1" onclick="callPatientIn(${a.id}, '${a.queue_number}', '${escapeHtml(a.patient_name)}', '${escapeHtml(a.doctor_name)}', '${escapeHtml(roomName)}')"><span>🔔</span><span>Call In</span></button>`;
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F3F1EA] text-[#1C1C1B] border border-[#D4DDD0] hover:bg-[#E6E4DD] transition" onclick="openRescheduleModal(${a.id}, '${escapeHtml(a.patient_name)}', ${a.doctor_id}, '${a.scheduled_datetime}', ${a.duration_minutes || 15})">Reschedule</button>`;
      }
      if (a.status === 'in_progress') {
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-700 text-white hover:bg-emerald-800 transition" onclick="openCompleteModal(${a.id}, '${escapeHtml(a.patient_name)}')">Complete</button>`;
      }
      if (a.status !== 'completed' && a.status !== 'cancelled') {
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F9EAEA] text-[#8B2626] border border-[#EFC7C7] hover:bg-[#F3D5D5] transition" onclick="openCancelModal(${a.id})">Cancel</button>`;
      }

      let badgeClasses = 'bg-[#F9F8F4] text-[#6E716A] border-[#E6E4DD]';
      if (a.status === 'in_progress' || a.status === 'completed') {
        badgeClasses = 'bg-[#EEF2EC] text-[#14241E] border-[#D4DDD0]';
      } else if (a.status === 'cancelled') {
        badgeClasses = 'bg-[#F9EAEA] text-[#8B2626] border-[#EFC7C7]';
      } else if (a.status === 'waiting') {
        badgeClasses = 'bg-[#FDF6B2] text-[#723B13] border-[#FCE96A]';
      }

      let waitBadgeHtml = '';
      if (a.status === 'waiting') {
        waitBadgeHtml = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#FDF6B2] text-[#723B13] text-[10px] font-semibold">⏱️ ${escapeHtml(waitTelemetry)}</span>`;
      } else if (a.status === 'in_progress') {
        waitBadgeHtml = `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#EEF2EC] text-[#14241E] text-[10px] font-semibold">🟢 In Consultation</span>`;
      } else {
        waitBadgeHtml = `<span class="text-[11px] text-[#6E716A]">${a.duration_minutes || 15} mins</span>`;
      }

      const isHighlighted = highlightApptId && String(highlightApptId) === String(a.id);
      const highlightClass = isHighlighted ? 'row-highlight' : '';
      const searchTerms = `${a.patient_name} ${a.mrn} ${a.queue_number} ${a.doctor_name} ${a.department} ${roomName} ${a.type}`.toLowerCase();

      html += `
        <tr
          id="appt-row-${a.id}"
          data-status="${a.status}"
          data-search="${escapeHtml(searchTerms)}"
          class="hover:bg-[#FAF9F5] transition ${highlightClass}"
        >
          <td class="py-4 px-5">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#14241E] text-white font-mono font-bold text-xs shadow-sm">
              #${a.queue_number}
            </span>
          </td>
          <td class="py-4 px-5">
            <div>
              <p class="font-bold text-[#1C1C1B] text-sm">${escapeHtml(a.patient_name)}</p>
              <p class="text-[11px] text-[#6E716A] capitalize">${escapeHtml(a.type)}</p>
            </div>
          </td>
          <td class="py-4 px-5">
            <span class="inline-block px-2.5 py-1 rounded bg-[#F3F1EA] text-[#475569] font-mono text-[11px] font-semibold border border-[#E2DFD5]">
              ${escapeHtml(a.mrn)}
            </span>
          </td>
          <td class="py-4 px-5">
            <p class="font-bold text-[#1C1C1B]">${a.scheduled_time}</p>
            <div class="mt-0.5">${waitBadgeHtml}</div>
          </td>
          <td class="py-4 px-5">
            <p class="font-bold text-[#1C1C1B]">${escapeHtml(a.doctor_name)}</p>
            <div class="flex items-center gap-1.5 text-[11px] text-[#6E716A] mt-0.5">
              <span>${escapeHtml(a.department)}</span>
              <span>•</span>
              <span class="font-semibold text-[#14241E] bg-[#EEF2EC] px-1.5 py-0.2 rounded text-[10px] border border-[#D4DDD0]">${escapeHtml(roomName)}</span>
            </div>
          </td>
          <td class="py-4 px-5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border ${badgeClasses} capitalize">
              ${a.status.replace('_', ' ')}
            </span>
          </td>
          <td class="py-4 px-5">${reasonHtml}</td>
          <td class="py-4 px-5 text-right">
            <div class="flex items-center justify-end gap-1.5 flex-wrap">${actionsHtml}</div>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
    applyClientSideFilters();
  }

  // Update Public Waiting Lobby Display Panels
  function updateLobbyDisplayPanels(appts) {
    const activeContainer = document.getElementById('lobby-active-list');
    const waitingContainer = document.getElementById('lobby-waiting-list');
    if (!activeContainer || !waitingContainer) return;

    if (!appts || appts.length === 0) {
      activeContainer.innerHTML = '<div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center text-[#A3A89E] text-sm">Station idle. Preparing next consultation.</div>';
      waitingContainer.innerHTML = '<div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center text-[#A3A89E] text-sm">No scheduled or waiting visits for this date.</div>';
      return;
    }

    const inProgress = appts.filter(a => a.status === 'in_progress');
    const waiting = appts.filter(a => a.status === 'waiting');
    const scheduled = appts.filter(a => a.status === 'scheduled');
    const upcomingQueue = [...waiting, ...scheduled];

    const countPill = document.getElementById('lobby-queue-count-pill');
    if (countPill) {
      countPill.textContent = `${waiting.length} In Lobby • ${scheduled.length} Scheduled`;
    }

    // Render Active (Now Calling / In Consultation)
    if (inProgress.length === 0) {
      activeContainer.innerHTML = '<div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center text-[#A3A89E] text-sm">Station idle. All doctors ready for next patient.</div>';
    } else {
      activeContainer.innerHTML = inProgress.map(a => `
        <div class="p-5 rounded-xl bg-emerald-950/40 border-2 border-emerald-500/50 flex items-center justify-between gap-4 shadow-lg animate-pulse-slow">
          <div class="flex items-center gap-4">
            <span class="text-4xl font-mono font-black text-emerald-400">#${a.queue_number}</span>
            <div>
              <p class="text-base font-bold text-white">${escapeHtml(a.patient_name)}</p>
              <p class="text-xs text-[#A3A89E]">${escapeHtml(a.doctor_name)} • ${escapeHtml(a.department)}</p>
            </div>
          </div>
          <div class="text-right">
            <span class="px-3 py-1.5 rounded-lg bg-emerald-500 text-[#14241E] font-bold text-sm uppercase tracking-wide">
              ${escapeHtml(a.consultation_room || 'Suite 101')}
            </span>
          </div>
        </div>
      `).join('');
    }

    // Render Upcoming Queue (Waiting in Lobby + Scheduled Today)
    if (upcomingQueue.length === 0) {
      waitingContainer.innerHTML = '<div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center text-[#A3A89E] text-sm">No visits currently in waiting lobby or scheduled.</div>';
    } else {
      waitingContainer.innerHTML = upcomingQueue.slice(0, 10).map(a => {
        const isWaiting = a.status === 'waiting';
        const numColor = isWaiting ? 'text-amber-300' : 'text-emerald-300';
        const badgeClasses = isWaiting
          ? 'bg-amber-500/20 text-amber-300 border-amber-500/30'
          : 'bg-white/10 text-[#C4C7BE] border-white/10';
        const statusLabel = isWaiting
          ? (a.wait_text ? `⏱️ ${a.wait_text}` : 'In Lobby')
          : `📅 Scheduled ${a.scheduled_time}`;

        return `
          <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-4 hover:bg-white/10 transition">
            <div class="flex items-center gap-3.5">
              <span class="text-xl font-mono font-bold ${numColor}">#${a.queue_number}</span>
              <div>
                <div class="flex items-center gap-2">
                  <p class="text-xs font-semibold text-white">${escapeHtml(a.patient_name)}</p>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border ${badgeClasses}">
                    ${isWaiting ? 'In Lobby' : 'Scheduled'}
                  </span>
                </div>
                <p class="text-[11px] text-[#A3A89E] mt-0.5">${escapeHtml(a.doctor_name)} • ${escapeHtml(a.consultation_room || 'Suite 101')}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="text-[11px] font-semibold text-white/90">${escapeHtml(statusLabel)}</span>
            </div>
          </div>
        `;
      }).join('');
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  document.addEventListener('DOMContentLoaded', () => {
    fetchLiveQueue();
  });

  setInterval(() => {
    const searchVal = document.getElementById('filter-search')?.value;
    if (!searchVal) {
      fetchLiveQueue();
    }
  }, 6000);
</script>
@endsection
