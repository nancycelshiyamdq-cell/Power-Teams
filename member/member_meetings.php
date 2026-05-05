<?php
include '../config.php';
include './partials/header.php';

if (!isset($_GET['id'])) {
    die("Member ID is required");
}

$memberId = intval($_GET['id']);

// ✅ Fetch member details
$stmt = $conn->prepare("SELECT name FROM members WHERE id = ?");
$stmt->execute([$memberId]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$member) {
    die("Member not found");
}

// ✅ Fetch attendance / meeting data
$attendanceStmt = $conn->prepare("
    SELECT id, meeting_date, check_in, check_out, status
    FROM attendance 
    WHERE member_id = ?
    ORDER BY meeting_date DESC
");
$attendanceStmt->execute([$memberId]);
$meetings = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Calculate total counts
$totalMeetings = count($meetings);
$presentCount = 0;
$absentCount = 0;

foreach ($meetings as $m) {
    if ($m['status'] == 'Present') $presentCount++;
    if ($m['status'] == 'Absent') $absentCount++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Meetings</title>
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/create_admin.css">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <main class="max-w-5xl mx-auto mt-8 p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4 text-red-700">
            Meeting Details for <?= htmlspecialchars($member['name']) ?>
        </h1>

        <div class="flex justify-between mb-4">
            <p><strong>Total Meetings:</strong> <?= $totalMeetings ?></p>
            <p><strong>Present:</strong> <?= $presentCount ?></p>
            <p><strong>Absent:</strong> <?= $absentCount ?></p>
        </div>

        <table class="min-w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border">Meeting Date</th>
                    <th class="px-4 py-2 border">Check In</th>
                    <th class="px-4 py-2 border">Check Out</th>
                    <th class="px-4 py-2 border">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meetings as $m): ?>
                    <tr>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($m['meeting_date']) ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($m['check_in'] ?? '--') ?></td>
                        <td class="px-4 py-2 border"><?= htmlspecialchars($m['check_out'] ?? '--') ?></td>
                        <td class="px-4 py-2 border">
                            <?php if ($m['status'] == 'Present'): ?>
                                <span class="text-green-600 font-semibold"><?= $m['status'] ?></span>
                            <?php else: ?>
                                <span class="text-red-600 font-semibold"><?= $m['status'] ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($meetings)): ?>
                    <tr>
                        <td colspan="4" class="text-center p-4 text-gray-500">No meeting records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-4">
            <a href="create_member.php" class="text-blue-600 underline hover:text-blue-800">← Back to Member List</a>
        </div>
    </main>
</body>
</html>
                    