<?php
include '../config.php';
// Fetch all powerteams for dropdown
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam ORDER BY pvalue ASC")->fetchAll(PDO::FETCH_ASSOC);
$selected_powerteam = $_GET['powerteam'] ?? '';

// ================= Filters =================
$selected_month   = $_GET['month'] ?? '';
$selected_year    = $_GET['year'] ?? '';
$selected_chapter = $_GET['chapter'] ?? '';

// ================= Build WHERE conditions =================
$whereClauses = [];
$params = [];
// echo $selected_chapter;
// Meeting Date Filters
if (!empty($selected_year)) {
    $whereClauses[] = "YEAR(meeting_date) = :year";
    $params[':year'] = (int)$selected_year;
}
if (!empty($selected_month)) {
    $whereClauses[] = "MONTH(meeting_date) = :month";
    $params[':month'] = (int)$selected_month;
}
// if (!empty($selected_chapter)) {
//     $whereClauses[] = "chapter = :chapter";
//     $params[':chapter'] = (int)$selected_chapter;
// }

$chapterJoin = '';
if (!empty($selected_chapter) || !empty($selected_powerteam)) {
    $chapterJoin = "INNER JOIN members m ON attendance.member_id = m.id";
}

if (!empty($selected_chapter)) {
    $whereClauses[] = "m.chapter = :chapter AND m.status = 1";
    $params[':chapter'] = (int)$selected_chapter;
}

if (!empty($selected_powerteam)) {
    $whereClauses[] = "m.powerteam = :powerteam";
    $params[':powerteam'] = (int)$selected_powerteam;
}

// echo $selected_powerteam;
// echo $chapterJoin;
/// Total Meetings
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT attendance.meeting_date) as total 
    FROM attendance
    $chapterJoin
    " . ($whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : "") 
);
$stmt->execute($params);
$total_meetings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Attendance Present
$presentSQL = $whereClauses ? implode(" AND ", $whereClauses) . " AND attendance.present='Present'" : "attendance.present='Present'";

$stmt = $conn->prepare("
    SELECT COUNT(*) as present
    FROM attendance
    $chapterJoin
    WHERE $presentSQL 
");
$stmt->execute($params);
$present_count = $stmt->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;

// ================= Attendance Ratio =================
$attendanceWhere = $whereClauses;
$attendanceParams = $params;

if ($attendanceWhere) {
    $presentSQL = "WHERE " . implode(" AND ", $attendanceWhere) . " AND present = 'Present' AND m.status = 1";
} else {
    $presentSQL = "WHERE present = 'Present' AND m.status = 1";
}

$presentWhere = $whereClauses;
$presentSQL = $presentWhere ? implode(" AND ", $presentWhere) . " AND attendance.present='Present'" : "attendance.present='Present'";
$presentSQL = $whereClauses ? implode(" AND ", $whereClauses) . " AND attendance.present='Present'" : "attendance.present='Present'";

$stmt = $conn->prepare("
    SELECT COUNT(*) as present
    FROM attendance
    $chapterJoin
    WHERE $presentSQL 
");
$stmt->execute($params);
$present_count = $stmt->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total 
                        FROM attendance
                        $chapterJoin
                        " . ($whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : ""));
$stmt->execute($params);
$total_attendance_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$attendance_ratio = $total_attendance_records > 0 ? round(($present_count / $total_attendance_records) * 100, 2) : 0;

$attendance_ratio = $total_attendance_records > 0 ? round(($present_count / $total_attendance_records) * 100, 2) : 0;

// ================= Referrals =================
$referralJoin = '';
$referralWhere = [];

if (!empty($selected_chapter) || !empty($selected_powerteam)) {
    $referralJoin = "INNER JOIN members m ON r.member_id = m.id";
}

if (!empty($selected_chapter)) {
    $referralWhere[] = "m.chapter = " . (int)$selected_chapter;
}

if (!empty($selected_powerteam)) {
    $referralWhere[] = "m.powerteam = " . (int)$selected_powerteam;
}

if (!empty($selected_year)) {
    $referralWhere[] = "YEAR(r.referred_on) = " . (int)$selected_year;
}

if (!empty($selected_month)) {
    $referralWhere[] = "MONTH(r.referred_on) = " . (int)$selected_month;
}

$refSQL = $referralWhere ? "WHERE " . implode(" AND ", $referralWhere) : "";

// Total referrals
$stmt = $conn->query("SELECT COUNT(*) as count FROM referrals r $referralJoin $refSQL");
$total_referrals_given = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Specific Ask
$askSQL = $refSQL ? "$refSQL AND r.referral_type='Specific Ask'" : "WHERE r.referral_type='Specific Ask'";
$stmt = $conn->query("SELECT COUNT(*) as count FROM referrals r $referralJoin $askSQL");
$total_referrals_received = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Specific Give
$giveSQL = $refSQL ? "$refSQL AND r.referral_type='Specific Give' AND status = 1" : "WHERE r.referral_type='Specific Give' ";
$stmt = $conn->query("SELECT COUNT(*) as count FROM referrals r $referralJoin $giveSQL");
$total_specific_gives = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// ================= Referrals per Member =================
$memberWhere = ["m.status = 1"];
if (!empty($selected_chapter)) $memberWhere[] = "m.chapter = " . (int)$selected_chapter;
if (!empty($selected_powerteam)) $memberWhere[] = "m.powerteam = " . (int)$selected_powerteam;

$memberWhereSQL = "WHERE " . implode(" AND ", $memberWhere);
// echo "test : " . $memberWhereSQL;
$stmt = $conn->query("
    SELECT 
        m.id,
        m.name,
        COALESCE(SUM(CASE WHEN r.member_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS referrals_given,
        COALESCE(SUM(CASE WHEN r.assigned_member IS NOT NULL AND r.assigned_member <> '' THEN 1 ELSE 0 END), 0) AS referrals_received
    FROM members m
    LEFT JOIN referrals r 
        ON m.id = r.member_id AND r.referral_type = 'Specific Ask'
    $memberWhereSQL
    GROUP BY m.id, m.name
    ORDER BY m.name
");
$referrals_per_member = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================= Chapters Dropdown =================
$chapters = $conn->query("SELECT id, svalue FROM chapters ORDER BY svalue ASC")->fetchAll(PDO::FETCH_ASSOC);

// ================= Chapter-wise Data (if no specific chapter selected) =================
$chapterReferrals = [];
$wherePowerteam = !empty($selected_powerteam) ? "AND m.powerteam = $selected_powerteam" : "";

if (empty($selected_chapter)) {
    foreach ($chapters as $chapter) {
        $chapterId = (int)$chapter['id'];
        $chapterName = $chapter['svalue'];

        $query = "
            SELECT 
                m.id,
                m.name,
                COALESCE(SUM(CASE WHEN r.member_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS referrals_given,
                COALESCE(SUM(CASE WHEN r.assigned_member IS NOT NULL AND r.assigned_member <> '' THEN 1 ELSE 0 END), 0) AS referrals_received
            FROM members m
            LEFT JOIN referrals r 
                ON m.id = r.member_id 
                AND r.referral_type = 'Specific Ask'
                " . (!empty($refSQL) ? "AND " . str_replace("WHERE", "", $refSQL) : "") . "
            WHERE m.chapter = $chapterId $wherePowerteam AND m.status = 1
            GROUP BY m.id, m.name
            ORDER BY m.name
        ";

        $stmt = $conn->query($query);
        $chapterReferrals[$chapterName] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<?php include './partials/header.php'; ?>

<main class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-xl md:text-2xl lg:text-3xl font-semibold mb-6 text-gray-800">Dashboard</h2>

            <!-- ✅ Filters -->
            <form method="GET" class="flex flex-col md:flex-row gap-4 mb-6 bg-white p-4 rounded shadow">
                <!-- Month -->
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

                <!-- Year -->
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

                <!-- ✅ Chapter Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Chapter</label>
                    <select name="chapter" class="w-full border rounded px-3 py-2">
                        <option value="">All Chapters</option>
                        <?php foreach ($chapters as $chapter): ?>
                            <option value="<?= $chapter['id']; ?>" <?= $selected_chapter == $chapter['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($chapter['svalue']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Powerteam Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Powerteam</label>
                    <select name="powerteam" class="w-full border rounded px-3 py-2">
                        <option value="">All Powerteams</option>
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

            <!-- ✅ Stats Cards -->
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

            <!-- ✅ Referrals per Member -->
            <div class="container mx-auto px-4 py-6">
                <?php if (!empty($selected_chapter)): ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">
                        Referrals - <?= htmlspecialchars(array_column(array_filter($chapters, fn($c) => $c['id'] == $selected_chapter), 'svalue')[0] ?? 'Chapter'); ?>
                    </h1>
                    <?php if (count($referrals_per_member) > 0): ?>
                        <ul class="bg-white shadow rounded divide-y divide-gray-200 mb-6">
                            <?php foreach ($referrals_per_member as $row): ?>
                                <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4">
                                    <a href="member_referrals.php?member_id=<?= $row['id']; ?>" 
                                       class="text-blue-600 hover:underline w-full sm:w-1/3 mb-2 sm:mb-0">
                                        <?= htmlspecialchars($row['name']); ?>
                                    </a>
                                    <span class="text-sm font-medium text-green-600 w-full sm:w-1/3 text-left sm:text-center mb-2 sm:mb-0">
                                        <?= $row['referrals_given']; ?> Given
                                    </span>
                                    <span class="text-sm font-medium text-indigo-600 w-full sm:w-1/3 text-left sm:text-right">
                                        <?= $row['referrals_received']; ?> Received
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500 italic mb-6">No referrals for this chapter.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <h1 class="text-2xl font-bold text-gray-800 mb-6">Referrals by Members (Chapter Wise)</h1>
                    <?php foreach ($chapterReferrals as $chapterName => $members): ?>
                        <h2 class="text-lg md:text-xl font-semibold text-gray-800 mt-8 mb-4">
                            <?= htmlspecialchars($chapterName); ?>
                        </h2>
                        <?php if (count($members) > 0): ?>
                            <ul class="bg-white shadow rounded divide-y divide-gray-200 mb-6">
                                <?php foreach ($members as $row): ?>
                                    <li class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4">
                                        <a href="member_referrals.php?member_id=<?= $row['id']; ?>" 
                                           class="text-blue-600 hover:underline w-full sm:w-1/3 mb-2 sm:mb-0">
                                            <?= htmlspecialchars($row['name']); ?>
                                        </a>
                                        <span class="text-sm font-medium text-green-600 w-full sm:w-1/3 text-left sm:text-center mb-2 sm:mb-0">
                                            <?= $row['referrals_given']; ?> Given
                                        </span>
                                        <span class="text-sm font-medium text-indigo-600 w-full sm:w-1/3 text-left sm:text-right">
                                            <?= $row['referrals_received']; ?> Received
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-500 italic mb-6">No referrals for this chapter.</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php include './partials/footer.php'; ?>
