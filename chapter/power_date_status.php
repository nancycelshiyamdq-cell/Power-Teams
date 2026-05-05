<?php
include '../config.php';
session_start();
$success_msg = '';
$error_msg = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'] ?? null;
    if (!in_array($status, ['Completed', 'Not Completed'], true)) {
        $status = null;
    }
    $remarks = trim($_POST['remarks']);

    $stmt = $conn->prepare("UPDATE power_dates SET status = ?, remarks = ? WHERE id = ?");
    if ($stmt->execute([$status, $remarks, $id])) {
        $success_msg = "Power Date updated successfully.";
    } else {
        $error_msg = "Failed to update.";
    }
}

// Date settings
$today = date('Y-m-d');
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$current_month = date('Y-m');
$powerteamid = (int)($_SESSION['powerteam'] ?? 0);

// Dashboard queries
$metric_query = "
    SELECT
        SUM(CASE WHEN pd.date < :today AND pd.status = 'Not Completed' THEN 1 ELSE 0 END) AS overdue_count,
        SUM(CASE WHEN pd.date BETWEEN :month_start AND :month_end THEN 1 ELSE 0 END) AS month_planned,
        SUM(CASE WHEN pd.status = 'Completed' THEN 1 ELSE 0 END) AS completed_count,
        SUM(CASE WHEN pd.status = 'Not Completed' THEN 1 ELSE 0 END) AS not_completed_count
    FROM power_dates pd
    JOIN members m ON m.id = pd.organiser_id
    WHERE m.powerteam = :powerteamid
";
$metric_stmt = $conn->prepare($metric_query);
$metric_stmt->execute([
    ':today' => $today,
    ':month_start' => $month_start,
    ':month_end' => $month_end,
    ':powerteamid' => $powerteamid,
]);
$metrics = $metric_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$overdue_count = (int)($metrics['overdue_count'] ?? 0);
$month_planned = (int)($metrics['month_planned'] ?? 0);
$completed_count = (int)($metrics['completed_count'] ?? 0);
$not_completed_count = (int)($metrics['not_completed_count'] ?? 0);

// Daily trend for this month
$trend_stmt = $conn->prepare("
    SELECT DATE(pd.date) as d, COUNT(*) as cnt
    FROM power_dates pd
    JOIN members m ON m.id = pd.organiser_id
    WHERE m.powerteam = :powerteamid
      AND pd.date BETWEEN :month_start AND :month_end
    GROUP BY d
");
$trend_stmt->execute([
    ':powerteamid' => $powerteamid,
    ':month_start' => $month_start,
    ':month_end' => $month_end,
]);
$trend = [];
for ($d = 1; $d <= date('t'); $d++) {
    $day_str = date('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT);
    $trend[$day_str] = 0;
}
foreach ($trend_stmt as $row) {
    $trend[$row['d']] = (int)$row['cnt'];
}
// Fetch Power Dates with organiser
$query = "SELECT pd.*, 
                 m.name AS organiser_name,
                 m.powerteam
          FROM power_dates pd 
          JOIN members m ON m.id = pd.organiser_id
          
          ORDER BY 
              CASE 
                  WHEN m.powerteam = :powerteamid THEN 0
                  ELSE 1
              END,
              pd.date DESC";
$power_dates_stmt = $conn->prepare($query);
$power_dates_stmt->execute([':powerteamid' => $powerteamid]);
$power_dates = $power_dates_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Power Date Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root { --bni-red: #a6192e; }
        .bni-button { background-color: var(--bni-red); }
        .bni-button:hover { background-color: #851423; }
        .status-toggle { display: flex; gap: 6px; align-items: center; }
.status-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 2px solid #ddd;
    background: #f9f9f9;
    cursor: pointer;
    font-size: 15px;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.status-btn.active-tick {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}
.status-btn.active-cross {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
}
.status-btn:not(.active-tick):not(.active-cross):hover {
    background: #e5e7eb;
    border-color: #9ca3af;
}
.active-filter { box-shadow: 0 0 0 3px rgba(166,25,46,0.18); border-color: var(--bni-red) !important; color: var(--bni-red) !important; background: #fff5f6 !important; }
    </style>
</head>
<body class="bg-gray-100">
<?php include './partials/header.php'; ?>

<main class="max-w-7xl mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-[var(--bni-red)] mb-6 text-center">Power Date Dashboard</h2>

    <?php if ($success_msg): ?><div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= $success_msg ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= $error_msg ?></div><?php endif; ?>

    <!-- Dashboard Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white shadow p-4 rounded border-l-4 border-yellow-400">
            <div class="text-gray-600 text-sm">Overdue Power Dates</div>
            <div class="text-2xl font-bold text-yellow-600"><?= $overdue_count ?></div>
        </div>
        <div class="bg-white shadow p-4 rounded border-l-4 border-blue-500">
            <div class="text-gray-600 text-sm">Planned This Month</div>
            <div class="text-2xl font-bold text-blue-600"><?= $month_planned ?></div>
        </div>
        <div class="bg-white shadow p-4 rounded border-l-4 border-green-500">
            <div class="text-gray-600 text-sm">Completed</div>
            <div class="text-2xl font-bold text-green-600"><?= $completed_count ?></div>
        </div>
        <div class="bg-white shadow p-4 rounded border-l-4 border-red-500">
            <div class="text-gray-600 text-sm">Not Completed</div>
            <div class="text-2xl font-bold text-red-600"><?= $not_completed_count ?></div>
        </div>
    </div>

    <!-- Trend Graph -->
    <!-- <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h3 class="text-xl font-semibold mb-4 text-[var(--bni-red)]">Power Dates Trend - <?= date('F Y') ?></h3>
        <canvas id="trendChart" height="100"></canvas>
    </div> -->

    <!-- Table -->
<!-- Your Power Team Table -->
<!-- Your Power Team Table -->
<?php
$has_pending = false;
foreach ($power_dates as $pd) {
    if ($pd['powerteam'] == $powerteamid && ($pd['status'] ?? '') === '') {
        $has_pending = true;
        break;
    }
}
?>
<?php if ($has_pending): ?>
<div class="overflow-x-auto bg-white shadow-md rounded-lg mb-8">
    <h3 class="text-xl font-semibold px-6 pt-4 pb-2 text-[var(--bni-red)]">Your Power Team</h3>
    <!-- <div class="flex flex-wrap items-center gap-3 px-6 py-3 border-b border-gray-200">
    <span class="text-sm font-medium text-gray-600">Filter by Status:</span>
    <button onclick="filterTable('all')" id="filter-all"
        class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border-2 border-gray-300 bg-white text-gray-600 hover:border-gray-400 transition active-filter">
        All
    </button>
    <button onclick="filterTable('Completed')" id="filter-completed"
        class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border-2 border-green-400 bg-white text-green-700 hover:bg-green-50 transition">
        ✓ Completed
    </button>
    <button onclick="filterTable('Not Completed')" id="filter-not"
        class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border-2 border-red-400 bg-white text-red-700 hover:bg-red-50 transition">
        ✗ Not Completed
    </button>
    <span id="filter-count" class="ml-auto text-xs text-gray-400"></span>
</div> -->
    <table class="min-w-full table-auto text-sm">
        <thead class="bg-[var(--bni-red)] text-white">
            <tr>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Organiser</th>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Industry</th>
                <th class="px-4 py-2">Location</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Remarks</th>
                <th class="px-4 py-2">Update</th>
            </tr>
        </thead>
        <tbody class="divide-y" id="your-powerteam-tbody">
            <?php foreach ($power_dates as $pd): ?>
                <?php if ($pd['powerteam'] != $powerteamid) continue; ?>
                <?php if (($pd['status'] ?? '') !== '') continue; ?>
                <tr>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $pd['id'] ?>">
                        <td class="px-4 py-2"><?= htmlspecialchars($pd['date']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($pd['organiser_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($pd['company_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($pd['industry']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($pd['location']) ?></td>
                       <td class="px-4 py-2">
                       <input type="hidden" name="status" value="<?= htmlspecialchars($pd['status'] ?? '') ?>" class="status-input">
                      <div class="status-toggle">
                      <button type="button" onclick="setStatus(this, 'Completed')"
                       class="status-btn <?= $pd['status'] === 'Completed' ? 'active-tick' : '' ?>">&#10003;</button>
                     <button type="button" onclick="setStatus(this, 'Not Completed')"
                     class="status-btn <?= $pd['status'] === 'Not Completed' ? 'active-cross' : '' ?>">&#10007;</button>
               </div>
               </td>
                        <td class="px-4 py-2">
                            <textarea name="remarks" class="rounded border px-2 py-1 w-full" rows="3" style="min-width:200px; resize:vertical;"><?= htmlspecialchars($pd['remarks']) ?></textarea>
                        </td>
                        <td class="px-4 py-2">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-lg"> ✓</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
   </table>
</div>
<?php endif; ?>





<!-- Filter Bar -->
<div class="bg-white shadow-md rounded-lg px-6 py-4 mb-4 flex flex-wrap items-end gap-4 justify-center">
    <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="filter-status" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 min-w-[150px]">
            <option value="all">All</option>
            <option value="Completed">Completed</option>
            <option value="Not Completed">Not Completed</option>
        </select>
    </div>
    <button onclick="applyFilter()"
        class="bni-button text-white px-6 py-2 rounded-md text-sm font-semibold hover:opacity-90 transition">
        Filter
    </button>
    <span id="filter-count" class="text-xs text-gray-400 self-center"></span>
</div>

<div id="status-details-section" class="overflow-x-auto bg-white shadow-md rounded-lg mb-8 hidden">
    <h3 class="text-xl font-semibold px-6 pt-4 pb-2 text-[var(--bni-red)]">Power Date Details</h3>
    <table class="min-w-full table-auto text-sm">
        <thead class="bg-[var(--bni-red)] text-white">
            <tr>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Organiser</th>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Industry</th>
                <th class="px-4 py-2">Location</th>
                <!-- <th class="px-4 py-2">Status</th> -->
                <th class="px-4 py-2">Remarks</th>
            </tr>
        </thead>
        <tbody class="divide-y" id="status-details-tbody">
            <?php foreach ($power_dates as $pd): ?>
                <?php if ($pd['powerteam'] != $powerteamid) continue; ?>
                <?php if (!in_array($pd['status'] ?? '', ['Completed', 'Not Completed'], true)) continue; ?>
                <tr data-status="<?= htmlspecialchars($pd['status']) ?>">
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['date']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['organiser_name']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['company_name']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['industry']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['location']) ?></td>
                    <!-- <td class="px-4 py-2">
                        <?php if ($pd['status'] === 'Completed'): ?>
                            <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Completed</span>
                        <?php else: ?>
                            <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">Not Completed</span>
                        <?php endif; ?>
                    </td> -->
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['remarks'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="overflow-x-auto bg-white shadow-md rounded-lg mt-8">
    <h3 class="text-xl font-semibold px-6 pt-4 pb-2 text-[var(--bni-red)]">Other Power Teams</h3>
    <table class="min-w-full table-auto text-sm">
        <thead class="bg-[var(--bni-red)] text-white">
            <tr>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Organiser</th>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Industry</th>
                <th class="px-4 py-2">Location</th>
                <!-- <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Remarks</th> -->
            </tr>
        </thead>
            
        <tbody class="divide-y">
            <?php foreach ($power_dates as $pd): ?>
                <?php if ($pd['powerteam'] == $powerteamid) continue; ?>
                <tr>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['date']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['organiser_name']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['company_name']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['industry']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pd['location']) ?></td>
                    <!-- <td class="px-4 py-2"> -->
                        <!-- <div class="status-toggle">
                            <button type="button" disabled
                                class="status-btn cursor-not-allowed <?= $pd['status'] === 'Completed' ? 'active-tick' : '' ?>">&#10003;</button>
                            <button type="button" disabled
                                class="status-btn cursor-not-allowed <?= $pd['status'] === 'Not Completed' ? 'active-cross' : '' ?>">&#10007;</button>
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <textarea disabled rows="1"
                            class="rounded border px-2 py-1 w-full bg-gray-100 cursor-not-allowed text-gray-500"><?= htmlspecialchars($pd['remarks']) ?></textarea> -->
                   
                </tr>
            <?php endforeach; ?>
            </div>
        </tbody>
    </table>
    


</main>

<script>
    const trendCanvas = document.getElementById('trendChart');
    if (trendCanvas) {
        const ctx = trendCanvas.getContext('2d');
        const trendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($trend)) ?>,
                datasets: [{
                    label: 'Power Dates per Day',
                    data: <?= json_encode(array_values($trend)) ?>,
                    backgroundColor: 'rgba(166, 25, 46, 0.7)',
                    borderRadius: 5
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });
    }
</script>
<script>
function setStatus(btn, value) {
    const toggle = btn.closest('.status-toggle');
    const input = toggle.closest('td').querySelector('.status-input') 
               || toggle.parentElement.querySelector('.status-input');
    
    // Clear all active classes in this toggle
    toggle.querySelectorAll('.status-btn').forEach(b => {
        b.classList.remove('active-tick', 'active-cross');
    });

    // Set active class on clicked button
    if (value === 'Completed') {
        btn.classList.add('active-tick');
    } else {
        btn.classList.add('active-cross');
    }

    // Update hidden input
    const hiddenInput = btn.closest('tr').querySelector('.status-input');
    if (hiddenInput) hiddenInput.value = value;
}
</script>
<script>
function applyFilter() {
    const status = document.getElementById('filter-status').value;
    const detailsSection = document.getElementById('status-details-section');
    const rows = document.querySelectorAll('#status-details-tbody tr');
    let visible = 0;

    detailsSection.classList.remove('hidden');

    rows.forEach(row => {
        const show = status === 'all' || row.dataset.status === status;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // document.getElementById('filter-count').textContent = `${visible} of ${rows.length} shown`;
}
</script>
</body>
</html>
