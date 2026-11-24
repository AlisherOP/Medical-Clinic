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
      <button
        type="button"
        onclick="fetchLiveQueue()"
        class="px-3.5 py-2 rounded-lg bg-white border border-[#E6E4DD] font-semibold text-[#1C1C1B] hover:bg-[#F9F8F4] transition flex items-center gap-1.5"
      >
        <span>🔄</span>
        <span>Refresh Now</span>
      </button>
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

  @if(session('success'))
    <div class="bg-[#EEF2EC] text-[#14241E] border border-[#D4DDD0] px-5 py-3.5 rounded-xl text-xs font-semibold flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
      <span>{{ session('success') }}</span>
    </div>
  @endif

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

  <!-- FILTERS & STATUS TABS -->
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

    <!-- Date & Doctor Filters -->
    <div class="flex flex-wrap items-center gap-3 text-xs">
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
            <th class="py-3.5 px-5">Schedule Time</th>
            <th class="py-3.5 px-5">Doctor &amp; Dept</th>
            <th class="py-3.5 px-5">Status</th>
            <th class="py-3.5 px-5">Cancellation Reason</th>
            <th class="py-3.5 px-5 text-right">Live Actions</th>
          </tr>
        </thead>
        <tbody id="queue-table-body" class="divide-y divide-[#F0EFEA]">
          @forelse($appointments as $appt)
            <tr id="appt-row-{{ $appt->id }}" data-status="{{ $appt->status }}" class="hover:bg-[#FAF9F5] transition">
              <td class="py-4 px-5">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#14241E] text-white font-mono font-bold text-xs">
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
                <p class="text-[11px] text-[#6E716A]">{{ $appt->duration_minutes ?? 15 }} mins</p>
              </td>
              <td class="py-4 px-5">
                <p class="font-bold text-[#1C1C1B]">Dr. {{ $appt->doctor->user->last_name ?? 'Doctor' }}</p>
                <p class="text-[11px] text-[#6E716A]">{{ $appt->doctor->department->name ?? 'General Medicine' }}</p>
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
                    <button
                      type="button"
                      onclick="updateStatus({{ $appt->id }}, 'start')"
                      class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#14241E] text-white hover:bg-[#1E352C] transition"
                    >
                      Call In
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
        <h3 class="text-lg font-bold text-[#1C1C1B]">Book New Appointment</h3>
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
              <option value="{{ $d->id }}">Dr. {{ $d->user->full_name }} — {{ $d->department->name ?? 'General' }}</option>
            @endforeach
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-[#1C1C1B] mb-1">Date &amp; Time *</label>
            <input type="datetime-local" name="scheduled_at" class="w-full px-3 py-2 rounded-lg bg-[#F9F8F4] border border-[#E6E4DD] text-xs text-[#1C1C1B] outline-none focus:border-[#14241E]" required>
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
@endsection

@section('scripts')
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  let currentFilterTab = 'all';

  function openBookingModal() {
    const now = new Date();
    now.setMinutes(now.getMinutes() + 15);
    now.setMinutes(Math.ceil(now.getMinutes() / 15) * 15);
    const iso = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.querySelector('#bookingForm input[name="scheduled_at"]').value = iso;
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

  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
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
    applyClientSideTabFilter();
  }

  function applyClientSideTabFilter() {
    const rows = document.querySelectorAll('#queue-table-body tr');
    rows.forEach(row => {
      if (row.id === 'empty-row') return;
      const rowStatus = row.getAttribute('data-status');
      if (currentFilterTab === 'all' || rowStatus === currentFilterTab) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
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
        await fetchLiveQueue();
      } else {
        alert(result.message || 'Error updating status');
      }
    } catch (err) {
      console.error(err);
      alert('Network error updating appointment status.');
    }
  }

  async function submitBooking(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('btn-submit-booking');
    const errorBox = document.getElementById('booking-error');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Booking...';
    errorBox.classList.add('hidden');

    const formData = new FormData(form);

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
        await fetchLiveQueue();
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
        await fetchLiveQueue();
      } else {
        alert(result.message || 'Error completing consultation.');
      }
    } catch (err) {
      console.error(err);
      alert('Network error.');
    }
  }

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
        await fetchLiveQueue();
      } else {
        alert(result.message || 'Error cancelling appointment.');
      }
    } catch (err) {
      console.error(err);
      alert('Network error.');
    }
  }

  function applyFilters() {
    fetchLiveQueue();
  }

  async function fetchLiveQueue() {
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

      renderQueueRows(data.appointments);
    } catch (e) {
      console.warn('Queue auto-sync paused.');
    }
  }

  function renderQueueRows(appts) {
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

      let actionsHtml = '';
      if (a.status === 'scheduled') {
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F3F1EA] text-[#1C1C1B] border border-[#D4DDD0] hover:bg-[#E6E4DD] transition" onclick="updateStatus(${a.id}, 'check-in')">Check In</button>`;
      }
      if (a.status === 'scheduled' || a.status === 'waiting') {
        actionsHtml += `<button type="button" class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#14241E] text-white hover:bg-[#1E352C] transition" onclick="updateStatus(${a.id}, 'start')">Call In</button>`;
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

      html += `
        <tr id="appt-row-${a.id}" data-status="${a.status}" class="hover:bg-[#FAF9F5] transition">
          <td class="py-4 px-5">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#14241E] text-white font-mono font-bold text-xs">
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
            <p class="text-[11px] text-[#6E716A]">${a.duration_minutes} mins</p>
          </td>
          <td class="py-4 px-5">
            <p class="font-bold text-[#1C1C1B]">Dr. ${escapeHtml(a.doctor_name)}</p>
            <p class="text-[11px] text-[#6E716A]">${escapeHtml(a.department)}</p>
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
    applyClientSideTabFilter();
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  setInterval(fetchLiveQueue, 6000);
</script>
@endsection
