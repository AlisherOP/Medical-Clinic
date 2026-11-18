<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f4f6f9; display: flex; height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #ddd; display: flex; flex-direction: column; padding: 20px; }
        .profile-box { text-align: center; margin-bottom: 30px; }
        .sidebar nav a { display: block; padding: 12px 15px; color: #333; text-decoration: none; margin-bottom: 5px; border-radius: 4px; }
        .sidebar nav a:hover, .sidebar nav a.active { background: #007bff; color: #fff; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .header { background: #343a40; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .content-body { padding: 30px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card h4 { color: #666; font-size: 13px; text-transform: uppercase; margin-bottom: 8px; }
        .stat-card p { font-size: 26px; font-weight: bold; color: #333; }

        /* Form Card */
        .card { background: #fff; padding: 20px; border-radius: 5px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card h3 { margin-bottom: 15px; color: #333; font-size: 16px; }
        .form-row { display: flex; gap: 10px; flex-wrap: wrap; }
        .form-row input, .form-row select { padding: 8px 12px; flex: 1; min-width: 150px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .btn-submit { background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background: #218838; }

        /* Tabs */
        .tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; font-weight: bold; color: #666; border-bottom: 3px solid transparent; }
        .tab.active { color: #007bff; border-bottom-color: #007bff; }

        /* Table */
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 5px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #333; font-size: 14px; }
        td { font-size: 14px; color: #555; }
        .btn-action { background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-action:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>

    <!-- Sidebar Section -->
    <div class="sidebar">
        <div class="profile-box">
            <h3>Dr. Aslan</h3>
            <p style="font-size: 12px; color: #777;">Emergency Specialist</p>
        </div>
        <nav>
            <a href="#" class="active">Dashboard</a>
            <a href="#">My Queue</a>
            <a href="#">Critical Cases</a>
            <a href="#">Schedule</a>
        </nav>
    </div>

    <!-- Main Container -->
    <div class="main-content">
        <div class="header">
            <h2>Hospital Management System</h2>
            <span>Welcome, Dr. Aslan</span>
        </div>

        <div class="content-body">
            
            <!-- Live Database Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Patients</h4>
                    <p style="color: #007bff;">{{ $totalPatients }}</p>
                </div>
                <div class="stat-card">
                    <h4>Active Appointments</h4>
                    <p style="color: #28a745;">{{ $activeAppointments }}</p>
                </div>
                <div class="stat-card">
                    <h4>Active Bed Assignments</h4>
                    <p style="color: #6f42c1;">{{ $activeBeds }}</p>
                </div>
            </div>

            <!-- Add Emergency / Appointment Form Card -->
            <div class="card">
                <h3>Add New Operation / Patient Queue</h3>
                <form id="patientForm" class="form-row">
                    <input type="text" id="deptInput" placeholder="Department (e.g. Surgery)" required>
                    <input type="text" id="nameInput" placeholder="Patient Name" required>
                    <input type="datetime-local" id="timeInput" required>
                    <select id="typeInput">
                        <option value="queue">Active Queue</option>
                        <option value="appointments">Appointment</option>
                        <option value="critical">Critical Emergency</option>
                    </select>
                    <button type="submit" class="btn-submit">Add Record</button>
                </form>
            </div>

            <!-- Tabs Navigation -->
            <div class="tabs">
                <div class="tab active" data-category="appointments">Appointments</div>
                <div class="tab" data-category="queue">Active Queue</div>
                <div class="tab" data-category="critical">Critical Emergencies</div>
            </div>

            <!-- Data Table -->
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Patient Name</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr data-category="appointments">
                        <td>Cardiology</td>
                        <td>John Doe</td>
                        <td>2026-11-09 20:00</td>
                        <td>Checked In</td>
                        <td>Room 101</td>
                        <td><button class="btn-action">Manage</button></td>
                    </tr>
                    <tr data-category="critical">
                        <td>Neurology</td>
                        <td>Sarah Connor</td>
                        <td>2026-11-09 21:00</td>
                        <td>In Surgery</td>
                        <td>OR 3</td>
                        <td><button class="btn-action btn-danger">Critical</button></td>
                    </tr>
                    <tr data-category="queue">
                        <td>Pediatrics</td>
                        <td>Emma Watson</td>
                        <td>2026-11-10 09:30</td>
                        <td>Waiting Queue</td>
                        <td>Room 204</td>
                        <td><button class="btn-action">Manage</button></td>
                    </tr>
                    <tr data-category="critical">
                        <td>Emergency</td>
                        <td>Michael Scott</td>
                        <td>2026-11-10 10:15</td>
                        <td>Active Triage</td>
                        <td>ER Bay 2</td>
                        <td><button class="btn-action btn-danger">Critical</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Tab Filtering Logic
        const tabs = document.querySelectorAll('.tab');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const category = tab.getAttribute('data-category');
                const rows = document.querySelectorAll('#tableBody tr');

                rows.forEach(row => {
                    if (category === 'all' || row.getAttribute('data-category') === category) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Add Patient / Emergency Form Submission Logic
        const form = document.getElementById('patientForm');
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const dept = document.getElementById('deptInput').value;
            const name = document.getElementById('nameInput').value;
            const time = document.getElementById('timeInput').value;
            const type = document.getElementById('typeInput').value;

            const tbody = document.getElementById('tableBody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-category', type);

            let statusText = 'Checked In';
            let locationText = 'Room 102';
            let btnClass = 'btn-action';
            let btnText = 'Manage';

            if (type === 'critical') {
                statusText = 'In Surgery / ER';
                locationText = 'OR Bay 1';
                btnClass = 'btn-action btn-danger';
                btnText = 'Critical';
            } else if (type === 'queue') {
                statusText = 'Waiting Queue';
                locationText = 'Waiting Area';
            }

            newRow.innerHTML = `
                <td>${dept}</td>
                <td>${name}</td>
                <td>${time.replace('T', ' ')}</td>
                <td>${statusText}</td>
                <td>${locationText}</td>
                <td><button class="${btnClass}">${btnText}</button></td>
            `;

            tbody.appendChild(newRow);
            form.reset();
        });
    </script>
</body>
</html>