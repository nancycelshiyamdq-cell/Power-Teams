<?php
include '../config.php';


// session_start();

include './partials/header.php';
// ✅ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

// Get session values
$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

// Filters
$filter_date = $_GET['date'] ?? '';
$filter_member = $_GET['member_id'] ?? '';

// 1️⃣ Fetch members for dropdown
$membersQuery = $conn->prepare("
    SELECT id, name 
    FROM members 
    WHERE region = :regionId AND chapter = :chapterId AND status = 1
");
$membersQuery->execute([
    ':regionId' => $region,
    ':chapterId' => $chapter
]);
$members = $membersQuery->fetchAll();  

// 2️⃣ Build attendance query
// 2️⃣ Build attendance query
$query = "SELECT 
            a.meeting_date,
            a.present,
            m.name AS member_name,
            r.rvalue AS region_name,
            c.svalue AS chapter_name,
            p.pvalue AS powerteam_name
        FROM attendance a
        JOIN members m ON a.member_id = m.id
        JOIN region r ON m.region = r.id
        JOIN powerteam p ON m.powerteam = p.id
        JOIN chapters c ON m.chapter = c.id
        WHERE m.region = :region AND m.chapter = :chapter AND m.powerteam = :powerteam AND m.status = 1";

$params = [
    ':region' => $region,
    ':chapter' => $chapter,
    ':powerteam'=> $powerteam
];

// Apply filters
if (!empty($filter_date)) {
    $query .= " AND a.meeting_date = :date";
    $params[':date'] = $filter_date;
}

if (!empty($filter_member)) {
    $query .= " AND a.member_id = :member";
    $params[':member'] = $filter_member;
}

$query .= " ORDER BY a.meeting_date DESC";

// ✅ Prepare & execute
$stmt = $conn->prepare($query);
$stmt->execute($params);
$attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="p-4 sm:p-6">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Attendance Records</h2>

        <!-- Filter Form -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="flex flex-col">
                <label for="date" class="mb-1 text-sm font-medium text-gray-700">Filter by Date:</label>
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($filter_date) ?>"
                       class="border border-gray-300 rounded px-3 py-2">
            </div>
            <div class="flex flex-col">
                <label for="member" class="mb-1 text-sm font-medium text-gray-700">Filter by Member:</label>
                <select name="member_id" id="member" class="border border-gray-300 rounded px-3 py-2">
                    <option value="">All Members</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $filter_member == $m['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end space-x-3 md:col-span-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply
                </button>
                <a href="attendance_list.php" class="text-sm text-blue-600 hover:underline">Reset</a>
            </div>
            
            <div class="flex items-end space-x-3 md:col-span-2">
    <!-- <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Apply
    </button>
    <a href="attendance_list.php" class="text-sm text-blue-600 hover:underline">Reset</a> -->
    <!-- <a href="export_excel.php?date=<?= urlencode($filter_date) ?>&member_id=<?= urlencode($filter_member) ?>" 
       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
       Download Excel
    </a> -->
</div>

        </form>
<a href="export_excel.php?date=<?= urlencode($filter_date) ?>&member_id=<?= urlencode($filter_member) ?>" 
   class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
   Download Excel
</a>


        <!-- Attendance Table -->
        <div class="overflow-x-auto bg-white p-4 rounded shadow">
            <table class="min-w-full text-left border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-b">Date</th>
                        <th class="px-4 py-2 border-b">Member Name</th>
                        <th class="px-4 py-2 border-b">Region</th>
                        <th class="px-4 py-2 border-b">Powerteam</th>
                        <th class="px-4 py-2 border-b">Chapter</th>
                        <th class="px-4 py-2 border-b">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($attendance): ?>
                        <?php foreach ($attendance as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['meeting_date']) ?></td>
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['member_name']) ?></td>
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['region_name']) ?></td>
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['powerteam_name']) ?></td>
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['chapter_name']) ?></td>
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($row['present']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-center text-gray-500">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include './partials/footer.php'; ?>
