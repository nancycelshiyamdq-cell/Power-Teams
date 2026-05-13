<?php
include '../config.php';
include './partials/header.php';
// session_start();

// Check login
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

// --- Total Meetings ---
$where_attendance = "WHERE m.region = :regionId AND m.chapter = :chapterId AND m.powerteam = :powerteamId";
$params_attendance = [
    ':regionId' => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
];
if ($month) {
    $where_attendance .= " AND MONTH(a.meeting_date) = :month";
    $params_attendance[':month'] = $month;
}
if ($year) {
    $where_attendance .= " AND YEAR(a.meeting_date) = :year";
    $params_attendance[':year'] = $year;
}

// $sql = "SELECT COUNT(DISTINCT a.meeting_date) as total
//         FROM attendance a 
//         JOIN members m ON a.member_id = m.id
//         $where_attendance";
$sql = "SELECT COUNT( a.member_id) as total
        FROM attendance a 
        JOIN members m ON a.member_id = m.id
        $where_attendance";

$stmt = $conn->prepare($sql); 
$stmt->execute($params_attendance);
$total_meetings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// --- Attendance Ratio ---
$sql_present = "SELECT COUNT(*) as present
                FROM attendance a
                JOIN members m ON a.member_id = m.id
                $where_attendance AND a.present = 'Present'";
$stmt = $conn->prepare($sql_present);
$stmt->execute($params_attendance);
$present_count = $stmt->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;

$sql_total = "SELECT COUNT(*) as total
              FROM attendance a
              JOIN members m ON a.member_id = m.id
              $where_attendance";
$stmt = $conn->prepare($sql_total);
$stmt->execute($params_attendance);
$total_attendance_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$attendance_ratio = $total_attendance_records > 0 
    ? round(($present_count / $total_attendance_records) * 100, 2) 
    : 0;

// --- Referrals ---
$where_ref = "WHERE m.region = :regionId AND m.chapter = :chapterId AND m.powerteam = :powerteamId";
$params_ref = [
    ':regionId' => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
];
if ($month) {
    $where_ref .= " AND MONTH(r.referred_on) = :month";
    $params_ref[':month'] = $month;
}
if ($year) {
    $where_ref .= " AND YEAR(r.referred_on) = :year";
    $params_ref[':year'] = $year;
}

// Total Referrals Given
$sql = "SELECT COUNT(*) as count FROM referrals r
        JOIN members m ON r.member_id = m.id
        $where_ref AND r.assigned_member IS NOT NULL";
$stmt = $conn->prepare($sql);
$stmt->execute($params_ref);
$total_referrals_given = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Specific Asks
$sql = "SELECT COUNT(*) as count FROM referrals r
        JOIN members m ON r.member_id = m.id
        $where_ref AND r.referral_type = 'Specific Ask'";
$stmt = $conn->prepare($sql);
$stmt->execute($params_ref);
$total_referrals_received = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Specific Gives
$sql = "SELECT COUNT(*) as count FROM referrals r
        JOIN members m ON r.member_id = m.id
        $where_ref AND r.referral_type = 'Specific Give'";
$stmt = $conn->prepare($sql);
$stmt->execute($params_ref);
$total_specific_gives = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// --- Referrals per Member ---
$sql = "
SELECT m.id, m.name,
       COALESCE(SUM(CASE WHEN r.member_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS referrals_given,
       COALESCE(SUM(CASE WHEN r.assigned_member IS NOT NULL AND r.assigned_member <> '' THEN 1 ELSE 0 END), 0) AS referrals_received
FROM members m
LEFT JOIN referrals r ON m.id = r.member_id
    AND r.referral_type = 'Specific Ask'
    " . ($month ? " AND MONTH(r.referred_on) = :month" : "") . "
    " . ($year ? " AND YEAR(r.referred_on) = :year" : "") . "
WHERE m.region = :regionId AND m.chapter = :chapterId AND m.powerteam = :powerteamId
GROUP BY m.id, m.name
ORDER BY m.name
";

$stmt = $conn->prepare($sql);
$stmt->execute($params_ref);
$referrals_per_member = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="p-6 max-w-7xl mx-auto">
    <h2 class="text-3xl font-semibold mb-6 text-gray-800">
      Dashboard - <?= htmlspecialchars($regionName) ?> / <?= htmlspecialchars($chapterName) ?> / <?= htmlspecialchars($powerteamName) ?>
    </h2>

    <!-- 🔽 Filter Form -->
    <form method="GET" class="mb-6 flex flex-wrap gap-3 bg-white p-4 rounded shadow">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
            <select name="month" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">All Months</option>
                <?php for ($m=1; $m<=12; $m++): ?>
                  <option value="<?= $m ?>" <?= ($month==$m ? 'selected' : '') ?>>
                    <?= date("F", mktime(0,0,0,$m,1)) ?>
                  </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
            <select name="year" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">All Years</option>
                <?php for ($y=date("Y"); $y>=2020; $y--): ?>
                  <option value="<?= $y ?>" <?= ($year==$y ? 'selected' : '') ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full sm:w-auto">Filter</button>
        </div>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <!-- <div class="bg-white shadow rounded p-6">
        <h4 class="text-lg font-medium text-gray-700">Total Meetings</h4>
        <p class="text-2xl font-bold text-blue-600 mt-2"><?= $total_meetings; ?></p>
      </div> -->
      <!-- <div class="grid grid-cols-5 gap-4"> -->
        <div class="bg-white shadow rounded p-6 cursor-pointer"
     onclick="window.location.href='meetings_list.php?total=<?= $total_meetings; ?>'">
  <h4 class="text-lg font-medium text-gray-700">Total Meetings</h4>
  <p class="text-2xl font-bold text-blue-600 mt-2"><?= $total_meetings; ?></p>
</div>

      <!-- </div> -->
       
      <div class="bg-white shadow rounded p-6">
        <h4 class="text-lg font-medium text-gray-700">Attendance Ratio</h4>
        <p class="text-2xl font-bold text-green-600 mt-2"><?= $attendance_ratio; ?>%</p>
      </div>
      <!-- <div class="bg-white shadow rounded p-6">
        <h4 class="text-lg font-medium text-gray-700">Referrals Given</h4>
        <p class="text-2xl font-bold text-indigo-600 mt-2"><?= $total_referrals_given; ?></p>
      </div> -->
      <div class="bg-white shadow rounded p-6 cursor-pointer"
     onclick="window.location.href='referrals_given.php?month=<?= $month ?? '' ?>&year=<?= $year ?? '' ?>'">
    <h4 class="text-lg font-medium text-gray-700">Referrals Given</h4>
    <p class="text-2xl font-bold text-indigo-600 mt-2"><?= $total_referrals_given; ?></p>
</div>

      <div class="bg-white shadow rounded p-6 cursor-pointer"
          onclick="window.location.href='specific_asks.php?month=<?= $month ?? '' ?>&year=<?= $year ?? '' ?>'">
          <h4 class="text-lg font-medium text-gray-700">Specific Asks</h4>
          <p class="text-2xl font-bold text-pink-600 mt-2"><?= $total_referrals_received; ?></p>
      </div>
      <!-- <div class="bg-white shadow rounded p-6">
        <h4 class="text-lg font-medium text-gray-700">Specific Gives</h4>
        <p class="text-2xl font-bold text-purple-600 mt-2"><?= $total_specific_gives; ?></p>
      </div>
    </div> -->

<div class="bg-white shadow rounded p-6 cursor-pointer"
     onclick="window.location.href='specific_gives.php?month=<?= $month ?? '' ?>&year=<?= $year ?? '' ?>'">
    <h4 class="text-lg font-medium text-gray-700">Specific Gives</h4>
    <p class="text-2xl font-bold text-purple-600 mt-2"><?= $total_specific_gives; ?></p>
</div>
</div>

    <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Referrals by Members</h3>
    <ul class="bg-white shadow rounded divide-y divide-gray-200">
      <?php foreach ($referrals_per_member as $row): ?>
        <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-4 gap-1">
          <span class="text-gray-700 font-medium"><a href="member_referrals.php?member_id=<?= $row['id']; ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($row['name']); ?></a></span>
          <span class="text-sm font-medium text-green-600"><?= $row['referrals_given']; ?> Given</span>
          <span class="text-sm font-medium text-indigo-600"><?= $row['referrals_received']; ?> Received</span>
        </li>
      <?php endforeach; ?>
    </ul>
</main>

<?php include './partials/footer.php'; ?>
