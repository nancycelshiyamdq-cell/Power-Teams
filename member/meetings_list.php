<?php
include '../config.php';
include './partials/header.php';

$total = $_GET['total'] ?? 0;

// ✅ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

// ✅ Get session values
$regionId = $_SESSION['region'];
$chapterId = $_SESSION['chapter'];
$powerteamId = $_SESSION['powerteam'];

// ✅ Optional: Get names for display
$regionName = $conn->query("SELECT rvalue FROM region WHERE id = $regionId")->fetchColumn();
$chapterName = $conn->query("SELECT svalue FROM chapters WHERE id = $chapterId")->fetchColumn();
$powerteamName = $conn->query("SELECT pvalue FROM powerteam WHERE id = $powerteamId")->fetchColumn();

// ✅ Correct query with bound parameters
$sql = "
    SELECT a.id, a.meeting_date, m.name AS member_name
    FROM attendance a
    LEFT JOIN members m ON a.member_id = m.id     
    WHERE m.region = :regionId
      AND m.chapter = :chapterId
      AND m.powerteam = :powerteamId
    ORDER BY a.meeting_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':regionId' => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
]);
$meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Total Meetings</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <main class="max-w-6xl mx-auto mt-8 p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold text-red-700 mb-4">
      📅 All Meeting Details (<?= htmlspecialchars($regionName) ?> / <?= htmlspecialchars($chapterName) ?> / <?= htmlspecialchars($powerteamName) ?>)
    </h1>

    <table class="min-w-full border border-gray-300">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 border">#</th>
          <th class="px-4 py-2 border">Member Name</th>
          <th class="px-4 py-2 border">Meeting Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($meetings)): ?>
          <?php foreach ($meetings as $index => $m): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2 border"><?= $index + 1 ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($m['member_name']) ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($m['meeting_date']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="px-4 py-3 text-center text-gray-500">No meetings found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="mt-4">
      <a href="dashboard.php" class="text-blue-600 underline hover:text-blue-800">← Back to Dashboard</a>
    </div>
  </main>
</body>
</html>
