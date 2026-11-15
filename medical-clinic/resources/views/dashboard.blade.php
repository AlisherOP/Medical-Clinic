<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Clinic Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Clinic Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-gray-500 text-sm font-medium">Total Patients</h2>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalPatients }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-gray-500 text-sm font-medium">Active Appointments</h2>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $activeAppointments }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-gray-500 text-sm font-medium">Active Bed Assignments</h2>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ $activeBeds }}</p>
            </div>
        </div>
    </div>
</body>
</html>