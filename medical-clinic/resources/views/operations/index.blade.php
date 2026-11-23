@extends('layouts.app')

@section('title', 'Operation Schedule | Medical Clinic')

@section('content')
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="topbar-title">Operation Schedule & Theatre Monitoring</h1>
            <div class="live-pill">
                <span class="live-dot"></span>
                <span>Live Sync Active</span>
            </div>
        </div>

        <div class="topbar-actions">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Schedule Date:</label>
                <input type="date" id="operation-date" class="form-input" style="padding: 6px 12px; border: 1px solid var(--border); border-radius: 6px;" value="{{ $selectedDate }}" onchange="applyDateFilter()">
            </div>
            <button class="btn btn-secondary" onclick="fetchLiveMetrics()">
                <span>🔄</span> Refresh Now
            </button>
        </div>
    </header>

    <!-- Content Area: The 4 Real-Time Top Metric Bars -->
    <div class="content-area">
        <div class="kpi-grid">
            <!-- 1. Today's Caseload (Emergency Priority) -->
            <div class="kpi-card border-primary">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">1. Today's Caseload</span>
                    <span class="kpi-card-icon">📋</span>
                </div>
                <div class="kpi-card-value" id="kpi-caseload-total">{{ $metrics['caseload']['total'] }} <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">Cases</span></div>
                <div>
                    <span class="kpi-card-sub kpi-sub-emergency" id="kpi-caseload-emergency">
                        🚨 {{ $metrics['caseload']['emergency'] }} Emergency
                    </span>
                    <span style="font-size: 12px; color: var(--text-muted); margin-left: 6px;" id="kpi-caseload-elective">
                        ({{ $metrics['caseload']['elective'] }} Elective)
                    </span>
                </div>
            </div>

            <!-- 2. Today's Completed (Pending Active) -->
            <div class="kpi-card border-success">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">2. Today's Completed</span>
                    <span class="kpi-card-icon">✅</span>
                </div>
                <div class="kpi-card-value" style="color: var(--success);" id="kpi-completed-count">{{ $metrics['completed']['completed'] }} <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">Done</span></div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <span class="kpi-card-sub kpi-sub-active" id="kpi-active-count">
                        🔵 {{ $metrics['completed']['active'] }} Active in OR
                    </span>
                    <span class="kpi-card-sub" style="background: #f1f5f9; color: #475569;" id="kpi-pending-count">
                        ⏳ {{ $metrics['completed']['pending'] }} Pending
                    </span>
                </div>
            </div>

            <!-- 3. Today's Cancelled (Audit logs stored safely) -->
            <div class="kpi-card border-danger">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">3. Today's Cancelled</span>
                    <span class="kpi-card-icon">🚫</span>
                </div>
                <div class="kpi-card-value" style="color: var(--danger);" id="kpi-cancelled-count">{{ $metrics['cancelled']['cancelled'] }} <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">Cancelled</span></div>
                <div>
                    <span class="kpi-card-sub kpi-sub-audit" id="kpi-audit-status">
                        🛡️ {{ $metrics['cancelled']['audit_status'] }}
                    </span>
                </div>
            </div>

            <!-- 4. Theatre Utilization (Also Average Duration) -->
            <div class="kpi-card border-purple">
                <div class="kpi-card-header">
                    <span class="kpi-card-title">4. Theatre Utilization</span>
                    <span class="kpi-card-icon">⚡</span>
                </div>
                <div class="kpi-card-value" style="color: var(--purple);" id="kpi-utilization-rate">{{ $metrics['theatre_utilization']['rate'] }}%</div>
                <div class="kpi-progress-bar">
                    <div class="kpi-progress-fill fill-purple" id="kpi-progress-fill" style="width: {{ min(100, $metrics['theatre_utilization']['rate']) }}%;"></div>
                </div>
                <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="kpi-card-sub kpi-sub-duration" id="kpi-avg-duration">
                        ⏱️ {{ $metrics['theatre_utilization']['avg_duration_minutes'] }} min avg duration
                    </span>
                    <span style="font-size: 11px; color: var(--text-muted);" id="kpi-theatres-count">
                        {{ $metrics['theatre_utilization']['active_theatres'] }} Active ORs
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Real-Time (No-Refresh) Metrics Sync
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
                document.getElementById('kpi-caseload-total').innerHTML = `${m.caseload.total} <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">Cases</span>`;
                document.getElementById('kpi-caseload-emergency').textContent = `🚨 ${m.caseload.emergency} Emergency`;
                document.getElementById('kpi-caseload-elective').textContent = `(${m.caseload.elective} Elective)`;

                // 2. Completed
                document.getElementById('kpi-completed-count').innerHTML = `${m.completed.completed} <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">Done</span>`;
                document.getElementById('kpi-active-count').textContent = `🔵 ${m.completed.active} Active in OR`;
                document.getElementById('kpi-pending-count').textContent = `⏳ ${m.completed.pending} Pending`;

                // 3. Cancelled
                document.getElementById('kpi-cancelled-count').innerHTML = `${m.cancelled.cancelled} <span style="font-size: 16px; font-weight: 600; color: var(--text-muted);">Cancelled</span>`;
                document.getElementById('kpi-audit-status').textContent = `🛡️ ${m.cancelled.audit_status}`;

                // 4. Theatre Utilization
                document.getElementById('kpi-utilization-rate').textContent = `${m.theatre_utilization.rate}%`;
                document.getElementById('kpi-progress-fill').style.width = `${Math.min(100, m.theatre_utilization.rate)}%`;
                document.getElementById('kpi-avg-duration').textContent = `⏱️ ${m.theatre_utilization.avg_duration_minutes} min avg duration`;
                document.getElementById('kpi-theatres-count').textContent = `${m.theatre_utilization.active_theatres} Active ORs`;
            }
        } catch (err) {
            console.warn('Live metrics auto-sync paused.');
        }
    }

    // Auto-poll every 6 seconds without page reload
    setInterval(fetchLiveMetrics, 6000);
</script>
@endsection
