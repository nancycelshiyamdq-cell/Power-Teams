<?php
include '../config.php';
include './partials/header.php';
// session_start(); 

// Check login and role
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Get session values
$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];

// Fetch IDs from database
$stmtChapter = $conn->prepare("SELECT id FROM chapters WHERE svalue = ?");
$stmtChapter->execute([$chapterName]);
$chapterId = $stmtChapter->fetchColumn(); 

$stmtPower = $conn->prepare("SELECT id FROM powerteam WHERE pvalue = ?");
$stmtPower->execute([$powerteamName]);
$powerteamId = $stmtPower->fetchColumn();

$stmtRegion = $conn->prepare("SELECT id FROM region WHERE rvalue = ?");
$stmtRegion->execute([$regionName]);
$regionId = $stmtRegion->fetchColumn();
// echo "regionId". $regionId;
// echo "regionName". $regionName;
// Get filter values
$selected_month = $_GET['month'] ?? '';
$selected_year  = $_GET['year'] ?? '';
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll(PDO::FETCH_ASSOC);
$selected_powerteam = $_GET['powerteam'] ?? '';
// echo "test".$selected_powerteam;
if (!empty($selected_powerteam)) {
    $whereClauses[] = "m.powerteam = :powerteam";
    $params[':powerteam'] = $selected_powerteam;
}

if (!empty($selected_powerteam)) {
    $whereRef[] = "m.powerteam = :powerteam";
    $paramsRef[':powerteam'] = $selected_powerteam;
}

// ================= Attendance =================
// ================= Attendance =================
$whereClauses = [
    "m.region = :regionId",
    "m.chapter = :chapterId"
];
$params = [
    ':regionId' => $regionName,
    ':chapterId' => $chapterName,
];

if (!empty($selected_powerteam)) {
    $whereClauses[] = "a.powerteam_id = :powerteamId";
    $params[':powerteamId'] = (int)$selected_powerteam;
}

$whereSQL = "WHERE " . implode(" AND ", $whereClauses);


// Total Meetings
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT a.meeting_date) as total 
    FROM attendance a
    JOIN members m ON a.member_id = m.id
    $whereSQL
");
$stmt->execute($params);
$total_meetings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Attendance Ratio
$stmt = $conn->prepare("
    SELECT COUNT(*) as present 
    FROM attendance a
    JOIN members m ON a.member_id = m.id
    $whereSQL AND a.present = 'Present'
");
$stmt->execute($params);
$present_count = $stmt->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM attendance a
    JOIN members m ON a.member_id = m.id
    $whereSQL
");

$stmt->execute($params);
$total_attendance_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$attendance_ratio = $total_attendance_records > 0 ? round(($present_count / $total_attendance_records) * 100, 2) : 0;

// ================= Referrals =================
$whereRef = [
    "m.region = :regionName",
    "m.chapter = :chapterName"
];
// $paramsRef = [
//     ':regionName' => $regionName,
//     ':chapterName' => $chapterName,
// ];
$paramsRef = [
    ':regionName' => $regionName,
    ':chapterName' => $chapterName,
];

if (!empty($selected_powerteam)) {
    $whereRef[] = "m.powerteam = :powerteam";
    $paramsRef[':powerteam'] = $selected_powerteam;
}

if (!empty($selected_year)) {
    $whereRef[] = "YEAR(r.referred_on) = :year";
    $paramsRef[':year'] = (int)$selected_year;
}

if (!empty($selected_month)) {
    $whereRef[] = "MONTH(r.referred_on) = :month";
    $paramsRef[':month'] = (int)$selected_month;
}

$whereRefSQL = "WHERE " . implode(" AND ", $whereRef);

// Total Referrals Given
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM referrals r
    JOIN members m ON r.member_id = m.id
    $whereRefSQL
");
$stmt->execute($paramsRef);
$total_referrals_given = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Total Referrals Received (Specific Ask)
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM referrals r
    JOIN members m ON r.member_id = m.id
    $whereRefSQL AND r.referral_type = 'Specific Ask' AND m.status = 1
");
$stmt->execute($paramsRef);
$total_referrals_received = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Total Specific Gives
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM referrals r
    JOIN members m ON r.member_id = m.id
    $whereRefSQL AND r.referral_type = 'Specific Give' AND m.status = 1
");
$stmt->execute($paramsRef);
$total_specific_gives = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// ================= Referrals per Member =================
$membersStmt = $conn->prepare("
    SELECT 
        m.id,
        m.name,
        COALESCE(SUM(CASE WHEN r.member_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS referrals_given,
        COALESCE(SUM(CASE WHEN r.assigned_member IS NOT NULL AND r.assigned_member <> '' THEN 1 ELSE 0 END), 0) AS referrals_received
    FROM members m
    LEFT JOIN referrals r 
        ON m.id = r.member_id 
        AND r.referral_type = 'Specific Ask'
        " . (!empty($selected_year) ? "AND YEAR(r.referred_on) = :year " : "") . "
        " . (!empty($selected_month) ? "AND MONTH(r.referred_on) = :month " : "") . "
    WHERE m.region = :regionName AND m.chapter = :chapterName AND m.status = 1
    " . (!empty($selected_powerteam) ? "AND m.powerteam = :powerteam " : "") . "
    GROUP BY m.id, m.name
    ORDER BY m.name
");
$membersStmt->execute($paramsRef);
$referrals_per_member = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="p-4 md:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-xl md:text-2xl lg:text-3xl font-semibold mb-6 text-gray-800">Dashboard</h2>

        <!-- Filters -->
        <form method="GET" class="flex flex-col md:flex-row gap-4 mb-6 bg-white p-4 rounded shadow">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Month</label>
                <select name="month" class="w-full border rounded px-3 py-2">
                    <option value="">All</option>
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $selected_month == $m ? 'selected' : '' ?>>
                            <?= date("F", mktime(0,0,0,$m,1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Year</label>
                <select name="year" class="w-full border rounded px-3 py-2">
                    <option value="">All</option>
                    <?php 
                    $currentYear = date("Y");
                    for ($y=$currentYear; $y>=2000; $y--): ?>
                        <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Powerteam</label>
                <select name="powerteam" class="w-full border rounded px-3 py-2">
                    <option value="">All</option>
                    <?php foreach ($powerteams as $pt): ?>
                        <option value="<?= $pt['id']; ?>" <?= $selected_powerteam == $pt['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pt['pvalue']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full md:w-auto">Filter</button>
                <a href="dashboard.php" class="bg-gray-400 text-white px-4 py-2 rounded w-full md:w-auto text-center">Clear</a>
            </div>
        </form>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white shadow rounded p-4 sm:p-6">
                <h4 class="text-base md:text-lg font-medium text-gray-700">Total Meetings</h4>
                <p class="text-xl md:text-2xl font-bold text-blue-600 mt-2"><?= $total_meetings; ?></p>
            </div>
            <div class="bg-white shadow rounded p-4 sm:p-6">
                <h4 class="text-base md:text-lg font-medium text-gray-700">Attendance Ratio</h4>
                <p class="text-xl md:text-2xl font-bold text-green-600 mt-2"><?= $attendance_ratio; ?>%</p>
            </div>
            <div class="bg-white shadow rounded p-4 sm:p-6">
                <h4 class="text-base md:text-lg font-medium text-gray-700">Referrals Given</h4>
                <p class="text-xl md:text-2xl font-bold text-indigo-600 mt-2"><?= $total_referrals_given; ?></p>
            </div>
            <div class="bg-white shadow rounded p-4 sm:p-6">
                <h4 class="text-base md:text-lg font-medium text-gray-700">Specific Asks</h4>
                <p class="text-xl md:text-2xl font-bold text-pink-600 mt-2"><?= $total_referrals_received; ?></p>
            </div>
            <div class="bg-white shadow rounded p-4 sm:p-6">
                <h4 class="text-base md:text-lg font-medium text-gray-700">Specific Gives</h4>
                <p class="text-xl md:text-2xl font-bold text-purple-600 mt-2"><?= $total_specific_gives; ?></p>
            </div>
        </div>

        <!-- Referrals per Member -->
        <h3 class="text-lg md:text-xl font-semibold text-gray-800 mt-8 mb-4">Referrals by Members</h3>
        <ul class="bg-white shadow rounded divide-y divide-gray-200">
            <?php foreach ($referrals_per_member as $row): ?>
                <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4">
                    <span class="text-gray-700 w-full sm:w-1/3 mb-2 sm:mb-0">
                        <a href="member_referrals.php?member_id=<?= $row['id']; ?>" class="text-blue-600 hover:underline">
                            <?= htmlspecialchars($row['name']); ?>
                        </a>
                    </span>
                    <span class="text-sm font-medium text-green-600 w-full sm:w-1/3 text-left sm:text-center mb-2 sm:mb-0">
                        <?= $row['referrals_given']; ?> Given
                    </span>
                    <span class="text-sm font-medium text-indigo-600 w-full sm:w-1/3 text-left sm:text-right">
                        <?= $row['referrals_received']; ?> Received
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

<?php include './partials/footer.php'; ?>
