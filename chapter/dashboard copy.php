<?php
include '../config.php';
include './partials/header.php';
// session_start();

// 1️⃣ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

// ✅ Get IDs directly from session
$regionId    = $_SESSION['region'];    // region_id
$chapterId   = $_SESSION['chapter'];   // chapter_id
$powerteamId = $_SESSION['powerteam']; // powerteam_id

// Read filters safely
$month = isset($_GET['month']) && $_GET['month'] !== '' ? (int)$_GET['month'] : null;
$year  = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : null;

// ------------------ Attendance Filters ------------------
$whereAttendance = "WHERE member_id IN (SELECT id FROM members WHERE region = :regionId AND chapter = :chapterId AND powerteam = :powerteamId)";
$paramsAttendance = [
    ':regionId' => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
];

if ($month) {
    $whereAttendance .= " AND MONTH(meeting_date) = :month";
    $paramsAttendance[':month'] = $month;
}
if ($year) {
    $whereAttendance .= " AND YEAR(meeting_date) = :year";
    $paramsAttendance[':year'] = $year;
}

// Total Meetings
$stmt = $conn->prepare("SELECT COUNT(DISTINCT meeting_date) as total FROM attendance $whereAttendance");
$stmt->execute($paramsAttendance);
$total_meetings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Attendance Ratio
$stmt = $conn->prepare("SELECT COUNT(*) as present FROM attendance $whereAttendance AND present = 'Present'");
$stmt->execute($paramsAttendance);
$present_count = $stmt->fetch(PDO::FETCH_ASSOC)['present'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance $whereAttendance");
$stmt->execute($paramsAttendance);
$total_attendance_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$attendance_ratio = $total_attendance_records > 0 ? round(($present_count / $total_attendance_records) * 100, 2) : 0;

// ------------------ Referral Filters ------------------
$whereReferrals = "
    WHERE member_id IN (
        SELECT id FROM members 
        WHERE region = :regionId 
        AND chapter = :chapterId 
        AND powerteam = :powerteamId 
        AND status = 1
    )
";

$paramsReferrals = [
    ':regionId' => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
];

if ($month) {
    $whereReferrals .= " AND MONTH(referred_on) = :month";
    $paramsReferrals[':month'] = $month;
}
if ($year) {
    $whereReferrals .= " AND YEAR(referred_on) = :year";
    $paramsReferrals[':year'] = $year;
}

// ✅ Total Referrals Given
$sql = "SELECT COUNT(*) as count FROM referrals $whereReferrals AND assigned_member IS NOT NULL";
$stmt = $conn->prepare($sql);
$stmt->execute($paramsReferrals);
$total_referrals_given = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// ✅ Total Referrals Received (Specific Ask)
$sql = "SELECT COUNT(*) as count FROM referrals $whereReferrals AND referral_type = 'Specific Ask'";
$stmt = $conn->prepare($sql);
$stmt->execute($paramsReferrals);
$total_referrals_received = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// ✅ Total Specific Gives
$sql = "SELECT COUNT(*) as count FROM referrals $whereReferrals AND referral_type = 'Specific Give'";
$stmt = $conn->prepare($sql);
$stmt->execute($paramsReferrals);
$total_specific_gives = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Referrals per Member
$sql = "
    SELECT 
        m.id,
        m.name,
        COALESCE(given.count, 0) AS referrals_given,
        COALESCE(received.referrals_received, 0) AS referrals_received
    FROM members m
    LEFT JOIN (
        SELECT member_id, COUNT(*) AS count
        FROM referrals
        WHERE referral_type = 'Specific Ask' 
        " . ($month ? " AND MONTH(referred_on) = :month1" : "") . "
        " . ($year ? " AND YEAR(referred_on) = :year1" : "") . "
        GROUP BY member_id
    ) AS given ON m.id = given.member_id
    LEFT JOIN (
        SELECT member_id, COUNT(*) AS referrals_received
        FROM referrals
        WHERE referral_type = 'Specific Ask' 
        " . ($month ? " AND MONTH(referred_on) = :month2" : "") . "
        " . ($year ? " AND YEAR(referred_on) = :year2" : "") . "
        GROUP BY member_id
    ) AS received ON m.id = received.member_id
    WHERE m.region = :regionId AND m.chapter = :chapterId AND m.powerteam = :powerteamId AND m.status = 1
";

$stmt = $conn->prepare($sql);
if ($month) {
    $stmt->bindValue(':month1', $month);
    $stmt->bindValue(':month2', $month);
}
if ($year) {
    $stmt->bindValue(':year1', $year);
    $stmt->bindValue(':year2', $year);
}
$stmt->bindValue(':regionId', $regionId);
$stmt->bindValue(':chapterId', $chapterId);
$stmt->bindValue(':powerteamId', $powerteamId);

$stmt->execute();
$referrals_per_member = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

  <main class="p-4 md:p-6 lg:p-8 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <h2 class="text-2xl md:text-3xl font-semibold mb-6 text-gray-800">Dashboard</h2>

      <!-- Filter Form -->
      <form method="GET" class="mb-6 flex flex-wrap gap-4 bg-white shadow p-4 rounded">
        <div>
          <label class="block text-sm font-medium text-gray-700">Month</label>
          <select name="month" class="mt-1 border rounded px-3 py-2">
            <option value="">All</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= date("F", mktime(0, 0, 0, $m, 1)) ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Year</label>
          <select name="year" class="mt-1 border rounded px-3 py-2">
            <option value="">All</option>
            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
              <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="flex items-end">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
        </div>
      </form>

      <!-- Stats Grid -->
      <div class="overflow-x-auto">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="bg-white shadow rounded p-6">
            <h4 class="text-lg font-medium text-gray-700">Total Meetings</h4>
            <p class="text-2xl font-bold text-blue-600 mt-2"><?= $total_meetings; ?></p>
          </div>
          <div class="bg-white shadow rounded p-6">
            <h4 class="text-lg font-medium text-gray-700">Attendance Ratio</h4>
            <p class="text-2xl font-bold text-green-600 mt-2"><?= $attendance_ratio; ?>%</p>
          </div>
          <div class="bg-white shadow rounded p-6">
            <h4 class="text-lg font-medium text-gray-700">Referrals Given</h4>
            <p class="text-2xl font-bold text-indigo-600 mt-2"><?= $total_referrals_given; ?></p>
          </div>
          <div class="bg-white shadow rounded p-6">
            <h4 class="text-lg font-medium text-gray-700">Specific Asks</h4>
            <p class="text-2xl font-bold text-pink-600 mt-2"><?= $total_referrals_received; ?></p>
          </div>
          <div class="bg-white shadow rounded p-6">
            <h4 class="text-lg font-medium text-gray-700">Specific Gives</h4>
            <p class="text-2xl font-bold text-purple-600 mt-2"><?= $total_specific_gives; ?></p>
          </div>
        </div>
      </div>

      <!-- Referrals by Members -->
      <h3 class="text-xl font-semibold text-gray-800 my-6">Referrals by Members</h3>
      <ul class="bg-white shadow rounded divide-y divide-gray-200">
        <?php foreach ($referrals_per_member as $row): ?>
          <li class="flex justify-between items-center p-4">
            <span class="text-gray-700 w-1/3"><a href="member_referrals.php?member_id=<?= $row['id']; ?>" 
                                           class="text-blue-600 hover:underline w-full sm:w-1/3 mb-2 sm:mb-0">
                                            <?= htmlspecialchars($row['name']); ?>
                                        </a></span>
            <span class="text-sm font-medium text-green-600 w-1/3 text-center">
              <?= $row['referrals_given']; ?> Given
            </span>
            <span class="text-sm font-medium text-indigo-600 w-1/3 text-right">
              <?= $row['referrals_received']; ?> Received
            </span>
          </li>
        <?php endforeach; ?>
      </ul>

    </div>
  </main>
</body>
</html>
