@extends('layouts.app')

@section('title', 'Live Appointment Board | Medical Clinic')

@section('styles')
<style>
    /* Table & Card */
    .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: #f8fafc; color: var(--text-muted); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 12px 18px; border-bottom: 1px solid var(--border); }
    td { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }
    tr:hover { background: #fbfdff; }

    /* Controls / Filter Bar */
    .filter-card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; }
    .filter-group { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .filter-label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .form-select, .form-input { padding: 7px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; color: var(--text-main); background: #ffffff; outline: none; }
    .form-select:focus, .form-input:focus { border-color: var(--primary); }

    /* Tabs */
    .status-tabs { display: flex; gap: 6px; }
    .tab-btn { padding: 6px 14px; border-radius: 6px; border: 1px solid transparent; background: transparent; color: var(--text-muted); font-size: 13px; font-weight: 600; cursor: pointer; }
    .tab-btn:hover { background: #f1f5f9; color: var(--text-main); }
    .tab-btn.active { background: var(--primary-light); color: var(--primary-dark); border-color: #bae6fd; }

    /* Column Specific Styles */
    .ticket-badge { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: #0f172a; color: #ffffff; border-radius: 8px; font-weight: 800; font-size: 15px; font-mono; }
    .patient-cell { display: flex; flex-direction: column; }
    .patient-name { font-weight: 700; color: var(--text-main); }
    .patient-sub { font-size: 12px; color: var(--text-muted); }
    .mrn-badge { font-family: monospace; font-size: 12px; font-weight: 600; background: #f1f5f9; padding: 3px 7px; border-radius: 4px; color: #334155; }
    .time-cell { font-weight: 600; color: var(--text-main); }
    .time-duration { font-size: 12px; color: var(--text-muted); font-weight: normal; }

    /* Status Badges */
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
    .status-scheduled { background: #f1f5f9; color: #475569; }
    .status-waiting { background: var(--warning-light); color: var(--warning); border: 1px solid #fde68a; }
    .status-in_progress { background: var(--info-light); color: var(--info); border: 1px solid #bfdbfe; }
    .status-completed { background: var(--success-light); color: var(--success); border: 1px solid #bbf7d0; }
    .status-cancelled { background: var(--danger-light); color: var(--danger); border: 1px solid #fecaca; }
    .status-no_show { background: #f3f4f6; color: #6b7280; text-decoration: line-through; }

    .reason-box { font-size: 12px; color: #ef4444; background: #fff5f5; padding: 4px 8px; border-radius: 4px; border: 1px dashed #fca5a5; max-width: 200px; }

    /* Action Buttons */
    .actions-cell { display: flex; gap: 6px; align-items: center; }
    .btn-sm { padding: 5px 10px; font-size: 12px; font-weight: 600; border-radius: 5px; cursor: pointer; border: none; transition: opacity 0.15s; }
    .btn-checkin { background: #eab308; color: #ffffff; }
    .btn-start { background: #2563eb; color: #ffffff; }
    .btn-complete { background: #16a34a; color: #ffffff; }
    .btn-cancel { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .btn-sm:hover { opacity: 0.85; }

    /* Modals */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.5); display: none; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(2px); }
    .modal-card { background: #ffffff; border-radius: 10px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden; }
    .modal-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 17px; font-weight: 700; }
    .modal-close { font-size: 20px; background: none; border: none; color: var(--text-muted); cursor: pointer; }
    .modal-body { padding: 24px; max-height: 80vh; overflow-y: auto; }
    .modal-footer { padding: 14px 24px; border-top: 1px solid var(--border); background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; }

    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; }
    .form-row { display: flex; gap: 12px; }

    .empty-state { text-align: center; padding: 48px 20px; color: var(--text-muted); }
</style>
@endsection

@section('content')
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="topbar-title">Live Appointment Queue Board</h1>
            <div class="live-pill">
                <span class="live-dot"></span>
                <span>Live Sync Active</span>
            </div>
        </div>

        <div class="topbar-actions">
            <button class="btn btn-secondary" onclick="fetchLiveQueue()">
                <span>🔄</span> Refresh Now
            </button>
            <button class="btn btn-primary" onclick="openBookingModal()">
                <span>+</span> Book Appointment
            </button>
        </div>
    </header>

    <!-- Content Area -->
    <div class="content-area">
        @if(session('success'))
            <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
                ✓ {{ session('success') }}
            </div>
        @endif

        <!-- Live KPI Overview -->
        <div class="kpi-grid">
            <div class="kpi-card border-primary">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">Total Today</span>
                    <span class="kpi-card-icon">📋</span>
                </div>
                <div class="kpi-card-value kpi-total" id="kpi-total">{{ $kpis['total'] }}</div>
                <span style="font-size: 12px; color: var(--text-muted);">Scheduled patients</span>
            </div>
            <div class="kpi-card border-purple">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">In Waiting Lobby</span>
                    <span class="kpi-card-icon">⏳</span>
                </div>
                <div class="kpi-card-value kpi-waiting" id="kpi-waiting">{{ $kpis['waiting'] }}</div>
                <span class="kpi-card-sub" style="background: var(--warning-light); color: var(--warning);">Checked-in</span>
            </div>
            <div class="kpi-card border-primary">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">In Consultation</span>
                    <span class="kpi-card-icon">👨‍⚕️</span>
                </div>
                <div class="kpi-card-value kpi-progress" id="kpi-progress">{{ $kpis['in_progress'] }}</div>
                <span class="kpi-card-sub kpi-sub-active">Inside doctor room</span>
            </div>
            <div class="kpi-card border-success">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">Completed</span>
                    <span class="kpi-card-icon">✅</span>
                </div>
                <div class="kpi-card-value kpi-completed" id="kpi-completed">{{ $kpis['completed'] }}</div>
                <span class="kpi-card-sub" style="background: var(--success-light); color: var(--success);">Finished visit</span>
            </div>
            <div class="kpi-card border-danger">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">Cancelled / No Show</span>
                    <span class="kpi-card-icon">🚫</span>
                </div>
                <div class="kpi-card-value kpi-cancelled" id="kpi-cancelled">{{ $kpis['cancelled'] }}</div>
                <span class="kpi-card-sub kpi-sub-audit">Slot released</span>
            </div>
        </div>

        <!-- Filters & Status Tabs -->
        <div class="filter-card">
            <div class="status-tabs">
                <button class="tab-btn active" data-filter="all" onclick="setFilterTab('all', this)">All</button>
                <button class="tab-btn" data-filter="waiting" onclick="setFilterTab('waiting', this)">Waiting Line</button>
                <button class="tab-btn" data-filter="in_progress" onclick="setFilterTab('in_progress', this)">In Consultation</button>
                <button class="tab-btn" data-filter="scheduled" onclick="setFilterTab('scheduled', this)">Scheduled</button>
                <button class="tab-btn" data-filter="completed" onclick="setFilterTab('completed', this)">Completed</button>
                <button class="tab-btn" data-filter="cancelled" onclick="setFilterTab('cancelled', this)">Cancelled</button>
            </div>

            <div class="filter-group">
                <label class="filter-label">Date:</label>
                <input type="date" id="filter-date" class="form-input" value="{{ $selectedDate }}" onchange="applyFilters()">

                <label class="filter-label" style="margin-left: 8px;">Doctor:</label>
                <select id="filter-doctor" class="form-select" onchange="applyFilters()">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ $doctorId == $doc->id ? 'selected' : '' }}>
                            Dr. {{ $doc->user->last_name }} ({{ $doc->department->name ?? 'General' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- The Queue Board Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Ticket #</th>
                        <th>Patient Name</th>
                        <th>MR Number</th>
                        <th>Schedule Time</th>
                        <th>Doctor & Dept</th>
                        <th>Status</th>
                        <th>Cancellation Reason</th>
                        <th style="text-align: right;">Live Actions</th>
                    </tr>
                </thead>
                <tbody id="queue-table-body">
                    @forelse($appointments as $appt)
                        <tr id="appt-row-{{ $appt->id }}" data-status="{{ $appt->status }}">
                            <td>
                                <span class="ticket-badge">#{{ sprintf('%02d', $appt->queue_number) }}</span>
                            </td>
                            <td>
                                <div class="patient-cell">
                                    <span class="patient-name">{{ $appt->patient->user->full_name ?? 'Unknown Patient' }}</span>
                                    <span class="patient-sub">{{ ucfirst($appt->type) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="mrn-badge">{{ $appt->patient->mrn ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="time-cell">{{ \Carbon\Carbon::parse($appt->scheduled_at)->format('H:i') }}</div>
                                <div class="time-duration">{{ $appt->duration_minutes ?? 15 }} mins</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">Dr. {{ $appt->doctor->user->last_name ?? 'Doctor' }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $appt->doctor->department->name ?? 'General Medicine' }}</div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $appt->status }}">
                                    {{ str_replace('_', ' ', $appt->status) }}
                                </span>
                            </td>
                            <td>
                                @if($appt->status === 'cancelled' && $appt->notes)
                                    <div class="reason-box">{{ $appt->notes }}</div>
                                @else
                                    <span style="color: #94a3b8; font-size: 13px;">—</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div class="actions-cell" style="justify-content: flex-end;">
                                    @if($appt->status === 'scheduled')
                                        <button class="btn-sm btn-checkin" onclick="updateStatus({{ $appt->id }}, 'check-in')">Check In</button>
                                    @endif

                                    @if(in_array($appt->status, ['scheduled', 'waiting']))
                                        <button class="btn-sm btn-start" onclick="updateStatus({{ $appt->id }}, 'start')">Call In</button>
                                    @endif

                                    @if($appt->status === 'in_progress')
                                        <button class="btn-sm btn-complete" onclick="openCompleteModal({{ $appt->id }}, '{{ addslashes($appt->patient->user->full_name) }}')">Complete</button>
                                    @endif

                                    @if(!in_array($appt->status, ['completed', 'cancelled']))
                                        <button class="btn-sm btn-cancel" onclick="openCancelModal({{ $appt->id }})">Cancel</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="8" class="empty-state">
                                <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                                <div style="font-size: 15px; font-weight: 600;">No appointments scheduled for this date.</div>
                                <p style="font-size: 13px; margin-top: 4px;">Click "+ Book Appointment" above to schedule a patient.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Book Appointment -->
    <div id="bookingModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">Book New Appointment</h3>
                <button class="modal-close" onclick="closeModal('bookingModal')">&times;</button>
            </div>
            <form action="{{ route('appointments.store') }}" method="POST" id="bookingForm" onsubmit="submitBooking(event)">
                @csrf
                <div class="modal-body">
                    <div id="booking-error" style="display: none; background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 14px;"></div>

                    <div class="form-group">
                        <label>Patient *</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select Patient</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->user->full_name }} ({{ $p->mrn }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Doctor *</label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">Select Doctor</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">Dr. {{ $d->user->full_name }} — {{ $d->department->name ?? 'General' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label>Appointment Date & Time *</label>
                            <input type="datetime-local" name="scheduled_at" class="form-input" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Duration (mins)</label>
                            <input type="number" name="duration_minutes" class="form-input" value="15" min="5" max="180" step="5">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Appointment Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="consultation" selected>General Consultation</option>
                            <option value="checkup">Routine Checkup</option>
                            <option value="emergency">Emergency Triage</option>
                            <option value="operation">Surgical Operation</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Chief Complaint / Clinical Notes</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="e.g. Fever, routine follow-up..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('bookingModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-booking">Book Ticket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Complete Consultation -->
    <div id="completeModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">Complete Consultation</h3>
                <button class="modal-close" onclick="closeModal('completeModal')">&times;</button>
            </div>
            <form id="completeForm" onsubmit="submitComplete(event)">
                <input type="hidden" id="complete-appt-id">
                <div class="modal-body">
                    <div style="font-size: 14px; margin-bottom: 14px; font-weight: 600; color: #334155;">
                        Patient: <span id="complete-patient-name" style="color: var(--primary);"></span>
                    </div>

                    <div class="form-group">
                        <label>Diagnosis *</label>
                        <input type="text" id="complete-diagnosis" class="form-input" placeholder="e.g. Acute Viral Bronchitis" required>
                    </div>

                    <div class="form-group">
                        <label>Doctor's Advice & Treatment Plan</label>
                        <textarea id="complete-notes" rows="3" class="form-input" placeholder="Prescribed rest, follow-up in 7 days..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('completeModal')">Back</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--success);">Save & Complete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Cancel Appointment -->
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-card" style="max-width: 440px;">
            <div class="modal-header">
                <h3 class="modal-title" style="color: var(--danger);">Cancel Appointment</h3>
                <button class="modal-close" onclick="closeModal('cancelModal')">&times;</button>
            </div>
            <form id="cancelForm" onsubmit="submitCancel(event)">
                <input type="hidden" id="cancel-appt-id">
                <div class="modal-body">
                    <p style="font-size: 14px; color: #475569; margin-bottom: 16px;">
                        This will release the doctor's time slot for other patients. Please provide a cancellation reason:
                    </p>

                    <div class="form-group">
                        <label>Reason *</label>
                        <select id="cancel-reason-select" class="form-select" onchange="toggleCancelOther(this)">
                            <option value="Patient requested reschedule">Patient requested reschedule</option>
                            <option value="Patient did not arrive (No Show)">Patient did not arrive (No Show)</option>
                            <option value="Doctor emergency / unavailable">Doctor emergency / unavailable</option>
                            <option value="Duplicate booking">Duplicate booking</option>
                            <option value="Other">Other reason...</option>
                        </select>
                    </div>

                    <div class="form-group" id="cancel-other-group" style="display: none;">
                        <input type="text" id="cancel-reason-other" class="form-input" placeholder="Specify cancellation reason...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('cancelModal')">Close</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--danger);">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentFilterTab = 'all';

    // Modal Helpers
    function openBookingModal() {
        const now = new Date();
        now.setMinutes(now.getMinutes() + 15);
        now.setMinutes(Math.ceil(now.getMinutes() / 15) * 15);
        const iso = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.querySelector('#bookingForm input[name="scheduled_at"]').value = iso;
        document.getElementById('booking-error').style.display = 'none';
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
        document.getElementById('cancel-other-group').style.display = 'none';
        document.getElementById('cancelModal').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function toggleCancelOther(select) {
        document.getElementById('cancel-other-group').style.display = select.value === 'Other' ? 'block' : 'none';
    }

    // Status filter tabs
    function setFilterTab(status, button) {
        currentFilterTab = status;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        button.classList.add('active');
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

    // Live Actions without Page Refresh
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

    // Submit Booking via AJAX without Full Page Reload
    async function submitBooking(event) {
        event.preventDefault();
        const form = event.target;
        const submitBtn = document.getElementById('btn-submit-booking');
        const errorBox = document.getElementById('booking-error');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Booking...';
        errorBox.style.display = 'none';

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
                errorBox.style.display = 'block';
            }
        } catch (err) {
            console.error(err);
            errorBox.textContent = 'Server communication error.';
            errorBox.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Book Ticket';
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

    // Periodic Background Auto-Poll (Every 6 seconds)
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

            // Update KPIs
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
                    <td colspan="8" class="empty-state">
                        <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                        <div style="font-size: 15px; font-weight: 600;">No appointments scheduled for this date.</div>
                        <p style="font-size: 13px; margin-top: 4px;">Click "+ Book Appointment" above to schedule a patient.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        appts.forEach(a => {
            let reasonHtml = '—';
            if (a.status === 'cancelled' && a.cancellation_reason) {
                reasonHtml = `<div class="reason-box">${escapeHtml(a.cancellation_reason)}</div>`;
            }

            let actionsHtml = '';
            if (a.status === 'scheduled') {
                actionsHtml += `<button class="btn-sm btn-checkin" onclick="updateStatus(${a.id}, 'check-in')">Check In</button>`;
            }
            if (a.status === 'scheduled' || a.status === 'waiting') {
                actionsHtml += `<button class="btn-sm btn-start" onclick="updateStatus(${a.id}, 'start')">Call In</button>`;
            }
            if (a.status === 'in_progress') {
                actionsHtml += `<button class="btn-sm btn-complete" onclick="openCompleteModal(${a.id}, '${escapeHtml(a.patient_name)}')">Complete</button>`;
            }
            if (a.status !== 'completed' && a.status !== 'cancelled') {
                actionsHtml += `<button class="btn-sm btn-cancel" onclick="openCancelModal(${a.id})">Cancel</button>`;
            }

            html += `
                <tr id="appt-row-${a.id}" data-status="${a.status}">
                    <td><span class="ticket-badge">#${a.queue_number}</span></td>
                    <td>
                        <div class="patient-cell">
                            <span class="patient-name">${escapeHtml(a.patient_name)}</span>
                            <span class="patient-sub">${escapeHtml(a.type)}</span>
                        </div>
                    </td>
                    <td><span class="mrn-badge">${escapeHtml(a.mrn)}</span></td>
                    <td>
                        <div class="time-cell">${a.scheduled_time}</div>
                        <div class="time-duration">${a.duration_minutes} mins</div>
                    </td>
                    <td>
                        <div style="font-weight: 600;">${escapeHtml(a.doctor_name)}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${escapeHtml(a.department)}</div>
                    </td>
                    <td>
                        <span class="status-badge status-${a.status}">
                            ${a.status.replace('_', ' ')}
                        </span>
                    </td>
                    <td>${reasonHtml}</td>
                    <td style="text-align: right;">
                        <div class="actions-cell" style="justify-content: flex-end;">${actionsHtml}</div>
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
