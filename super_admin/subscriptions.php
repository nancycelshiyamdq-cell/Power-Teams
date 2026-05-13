<?php
include '../config.php';

// ✅ Update sub_date if form is submitted via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_sub_date') {
    $memberId = (int)$_POST['member_id'];
    $type = $_POST['type'];
    $value = (int)$_POST['value'];

    $currentDate = new DateTime();

    if ($type === 'days') {
        $currentDate->modify("+{$value} days");
    } elseif ($type === 'month') {
        $currentDate->modify("+{$value} months");
    }

    $newDate = $currentDate->format('Y-m-d');

    $stmt = $conn->prepare("UPDATE members SET sub_date = ? WHERE id = ?");
    $stmt->execute([$newDate, $memberId]);

    echo "Subscription updated to {$newDate}";
    exit;
}

// ✅ Fetch Members with their region, chapter, powerteam names
$stmt = $conn->prepare("
    SELECT 
        m.*, 
        r.rvalue AS region_name, 
        c.svalue AS chapter_name, 
        p.pvalue AS powerteam_name 
    FROM members m
    LEFT JOIN region r ON m.region = r.id
    LEFT JOIN chapters c ON m.chapter = c.id
    LEFT JOIN powerteam p ON m.powerteam = p.id
    WHERE m.status = 1
    ORDER BY m.id DESC
");
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Members - Subscription Update</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-900">
    <?php include './partials/header.php'; ?>

<main class="max-w-7xl mx-auto py-8 px-4">
    <div class="bg-white shadow-xl rounded-xl p-6 overflow-x-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Members List</h2>

        <div class="min-w-full inline-block align-middle">
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                    <thead class="bg-gray-100 text-gray-800">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">Mobile</th>
                            <th class="px-4 py-3 font-semibold">Region</th>
                            <th class="px-4 py-3 font-semibold">Chapter</th>
                            <th class="px-4 py-3 font-semibold">Powerteam</th>
                            <th class="px-4 py-3 font-semibold">subscriptions Date</th>
                            <th class="px-4 py-3 font-semibold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($members as $index => $row): ?>
                            <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-green-50 transition">
                                <td class="px-4 py-3"><?= htmlspecialchars($row['name']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['email']); ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['mobile']); ?></td>
                                <td class="px-4 py-3"><?= $row['region_name'] ?? '-'; ?></td>
                                <td class="px-4 py-3"><?= $row['chapter_name'] ?? '-'; ?></td>
                                <td class="px-4 py-3"><?= $row['powerteam_name'] ?? '-'; ?></td>
                                <td class="px-4 py-3"><?= $row['sub_date'] ?? '-'; ?></td>
                                <td class="px-4 py-3 text-center">
                                    <button 
                                        onclick="openModal(<?= $row['id']; ?>)" 
                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                        Update Sub Date
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- 🪟 Modal -->
<div id="subDateModal" class="fixed inset-0 bg-black bg-opacity-40 hidden justify-center items-center">
  <div class="bg-white rounded-lg shadow-lg p-6 w-96">
    <h3 class="text-lg font-semibold mb-4">Update Subscription</h3>
    <input type="hidden" id="member_id">
    <label class="block mb-2 font-medium">Select Type</label>
    <select id="sub_type" class="w-full border px-3 py-2 rounded mb-4">
        <option value="">-- Select --</option>
        <option value="days">Days</option>
        <option value="month">Month</option>
    </select>

    <label class="block mb-2 font-medium">Enter Value</label>
    <input type="number" id="sub_value" class="w-full border px-3 py-2 rounded mb-4" placeholder="Enter days or months">

    <div class="flex justify-end gap-2">
        <button onclick="closeModal()" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
        <button onclick="updateSubDate()" class="px-3 py-1 bg-green-600 text-white rounded">Update</button>
    </div>
  </div>
</div>

<script>
function openModal(id) {
    document.getElementById('member_id').value = id;
    document.getElementById('sub_type').value = '';
    document.getElementById('sub_value').value = '';
    document.getElementById('subDateModal').classList.remove('hidden');
    document.getElementById('subDateModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('subDateModal').classList.add('hidden');
    document.getElementById('subDateModal').classList.remove('flex');
}

function updateSubDate() {
    const member_id = document.getElementById('member_id').value;
    const type = document.getElementById('sub_type').value;
    const value = document.getElementById('sub_value').value;

    if (!type || !value) {
        alert('Please select type and enter value.');
        return;
    }

    const formData = new URLSearchParams();
    formData.append('action', 'update_sub_date');
    formData.append('member_id', member_id);
    formData.append('type', type);
    formData.append('value', value);

    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        closeModal();
        location.reload();
    });
}
</script>

</body>
</html>
