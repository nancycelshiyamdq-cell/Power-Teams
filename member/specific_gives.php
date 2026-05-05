<?php
include '../config.php';
include './partials/header.php';

// ✅ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

// Get session values
$regionId = $_SESSION['region'];
$chapterId = $_SESSION['chapter'];
$powerteamId = $_SESSION['powerteam'];

// Optional: Get names for display
$regionName = $conn->query("SELECT rvalue FROM region WHERE id = $regionId")->fetchColumn();
$chapterName = $conn->query("SELECT svalue FROM chapters WHERE id = $chapterId")->fetchColumn();
$powerteamName = $conn->query("SELECT pvalue FROM powerteam WHERE id = $powerteamId")->fetchColumn();

// Filters
$month = $_GET['month'] ?? null;
$year  = $_GET['year'] ?? null;

// Build WHERE clause
$where = "WHERE m1.region = :regionId AND m1.chapter = :chapterId AND m1.powerteam = :powerteamId AND r.referral_type = 'Specific Give'";
$params = [
    ':regionId' => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
];

if ($month) {
    $where .= " AND MONTH(r.referred_on) = :month";
    $params[':month'] = $month;
}
if ($year) {
    $where .= " AND YEAR(r.referred_on) = :year";
    $params[':year'] = $year;
}

// Fetch Specific Gives referrals with assigned member name
$sql = "
SELECT 
    r.id, 
    r.member_id, 
    m1.name AS member_name,         -- Referral giver
    r.assigned_member, 
    m2.name AS assigned_name,       -- Assigned member
    r.referral_type, 
    r.referred_on
FROM referrals r
JOIN members m1 ON r.member_id = m1.id
LEFT JOIN members m2 ON r.assigned_member = m2.id
$where
ORDER BY r.referred_on DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$specific_gives = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Specific Gives</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
  <main class="p-6 max-w-7xl mx-auto">
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">
      Specific Gives - <?= htmlspecialchars($regionName) ?> / <?= htmlspecialchars($chapterName) ?> / <?= htmlspecialchars($powerteamName) ?>
    </h2>

    <table class="min-w-full border border-gray-300">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 border">#</th>
          <th class="px-4 py-2 border">Member Name</th>
          <th class="px-4 py-2 border">Assigned Member</th>
          <th class="px-4 py-2 border">Referral Type</th>
          <th class="px-4 py-2 border">Referred On</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($specific_gives)): ?>
          <?php foreach ($specific_gives as $index => $r): ?>
            <tr>
              <td class="px-4 py-2 border"><?= $index + 1 ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($r['member_name']) ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($r['assigned_name'] ?? '--') ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($r['referral_type']) ?></td>
              <td class="px-4 py-2 border"><?= htmlspecialchars($r['referred_on']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="px-4 py-3 text-center text-gray-500">No Specific Gives found</td>
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
