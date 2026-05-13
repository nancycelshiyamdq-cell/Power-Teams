<?php
include '../config.php';
// start session manually here
// include '../config.php';

if (isset($_GET['download']) && $_GET['download'] === 'excel') {
    session_start(); 
    if (!isset($_SESSION['region'], $_SESSION['chapter'])) {
        die("Session expired.");
    }

    $regionName = $_SESSION['region'];
    $chapterName = $_SESSION['chapter'];

    $query = "SELECT r.*, m.name as member_name
              FROM referrals r
              JOIN members m ON r.member_id = m.id
              WHERE m.region = :region
              AND m.chapter = :chapter
              AND m.status = 1
              ORDER BY r.referred_on DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':region' => $regionName,
        ':chapter' => $chapterName
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean output buffer
    if (ob_get_length()) {
        ob_clean();
    }

    // Send headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="referrals.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // ✅ Add header row
    fputcsv($output, ['Member', 'Company', 'Contact', 'Email', 'Phone', 'Referred On']);

    // ✅ Add data rows
    foreach ($results as $row) {
        fputcsv($output, [
            $row['member_name'],
            $row['company_name'],
            $row['contact_name'],
            $row['contact_email'],
            $row['contact_phone'],
            $row['referred_on'],
        ]);
    }

    fclose($output);
    exit;
}

include './partials/header.php';

// session_start();

// 1️⃣ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];
// echo $regionName;
// 2️⃣ Fetch the corresponding IDs from their tables
$regionStmt = $conn->prepare("SELECT id FROM region WHERE rvalue = :regionName");
$regionStmt->execute(['regionName' => $regionName]);
$regionId = $regionStmt->fetchColumn();

$chapterStmt = $conn->prepare("SELECT id FROM chapters WHERE svalue = :chapterName");
$chapterStmt->execute(['chapterName' => $chapterName]);
$chapterId = $chapterStmt->fetchColumn();

$powerteamStmt = $conn->prepare("SELECT id FROM powerteam WHERE pvalue = :powerteamName");
$powerteamStmt->execute(['powerteamName' => $powerteamName]);
$powerteamId = $powerteamStmt->fetchColumn();



// 4️⃣ Now fetch members based on the resolved IDs
$members = $conn->prepare("
    SELECT id, name, mobile, category 
    FROM members  
    WHERE chapter = :chapter 
      AND region = :region 
      AND powerteam = :powerteam
      AND status = 1
");

$members->execute([
    ':chapter'   => $chapterName,
    ':region'    => $regionName,
    ':powerteam' => $powerteamName,
]);

// $members->execute([
//     ':chapter' => $chapterName,
//     ':region'  => $regionName
// ]);

$members = $members->fetchAll(PDO::FETCH_ASSOC);


// Handle assignment/remark updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_submit'])) {
    $referral_id = $_POST['referral_id'];

    // fetch current row
    $stmt = $conn->prepare("SELECT assigned_member, mobile, remarks FROM referrals WHERE id = :id");
    $stmt->execute([':id' => $referral_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $assigned_member = !empty($_POST['assigned_member']) ? $_POST['assigned_member'] : $existing['assigned_member'];
    $mobile = !empty($_POST['mobile']) ? $_POST['mobile'] : $existing['mobile'];
    // $remarks = isset($_POST['remarks']) && $_POST['remarks'] !== '' ;
$remarks = $_POST['remarks'] ?? '';


    $stmt = $conn->prepare("
        UPDATE referrals 
        SET assigned_member = :assigned_member,
            mobile = :mobile,
            remarks = :remarks 
        WHERE id = :id
    ");
    $stmt->execute([
        ':assigned_member' => $assigned_member,
        ':mobile' => $mobile,
        ':remarks' => $remarks,
        ':id' => $referral_id,
    ]);
}

// Filters
$filter_member = $_GET['member_id'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$referral_type = $_GET['referral_type'] ?? 'specific_ask'; 
// Base query with session filters
$query = "SELECT r.*, m.name as member_name
          FROM referrals r
          JOIN members m ON r.member_id = m.id
          WHERE m.region = :region
            AND m.chapter = :chapter
            AND m.powerteam = :powerteam
            AND m.status = 1";

$params = [
    ':region'    => $regionName,
    ':chapter'   => $chapterName,
    ':powerteam' => $powerteamName
];

// Referral type filter
if ($referral_type === 'specific_ask') {
    $query .= " AND r.referral_type = :ref_type";
    $params[':ref_type'] = 'specific ask';
} elseif ($referral_type === 'specific_give') {
    $query .= " AND r.referral_type = :ref_type";
    $params[':ref_type'] = 'specific give';
}

// Optional filters
if ($filter_member !== '') {
    $query .= " AND r.member_id = :member_id";
    $params[':member_id'] = (int) $filter_member;
}
if ($filter_date_from) {
    $query .= " AND r.referred_on >= :date_from";
    $params[':date_from'] = $filter_date_from;
}
if ($filter_date_to) {
    $query .= " AND r.referred_on <= :date_to";
    $params[':date_to'] = $filter_date_to;
}

$query .= " ORDER BY r.referred_on DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Excel download
if (isset($_GET['download']) && $_GET['download'] === 'excel') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="referrals.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Member', 'Company', 'Contact', 'Email', 'Phone', 'Referred On']);
    foreach ($results as $row) {
        fputcsv($output, [
            $row['member_name'],
            $row['company_name'],
            $row['contact_name'],
            $row['contact_email'],
            $row['contact_phone'],
            $row['referred_on'],
        ]);
    }
    fclose($output);
    exit;
}
?>

<main class="p-4 md:p-6 lg:p-8">
    <div class="max-w-full mx-auto">
        <h2 class="text-xl md:text-2xl font-semibold mb-4 text-gray-800">Specific Asks/Gives List</h2>

        <!-- Tab Buttons -->
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="?referral_type=specific_ask<?= $filter_member ? "&member_id=$filter_member" : '' ?><?= $filter_date_from ? "&date_from=$filter_date_from" : '' ?><?= $filter_date_to ? "&date_to=$filter_date_to" : '' ?>"
               class="px-4 py-2 rounded text-sm font-medium transition <?= $referral_type === 'specific_ask' ? 'bg-red-600 text-white' : 'bg-white text-red-600 border border-red-600 hover:bg-red-50' ?>">
                📥 Specific Asks
            </a>
            <a href="?referral_type=specific_give<?= $filter_member ? "&member_id=$filter_member" : '' ?><?= $filter_date_from ? "&date_from=$filter_date_from" : '' ?><?= $filter_date_to ? "&date_to=$filter_date_to" : '' ?>"
               class="px-4 py-2 rounded text-sm font-medium transition <?= $referral_type === 'specific_give' ? 'bg-red-600 text-white' : 'bg-white text-red-600 border border-red-600 hover:bg-red-50' ?>">
                📤 Specific Gives
            </a>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded shadow-md p-4 mb-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="referral_type" value="<?= htmlspecialchars($referral_type) ?>" />
                <div>
                    <label for="member_id" class="block mb-1 text-xs font-medium text-gray-700">Filter by Member</label>
                    <select name="member_id" id="member_id" class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
                        <option value="">All Members</option>
                        <?php foreach ($members as $m) { ?>
                            <option value="<?= $m['id'] ?>" <?= $filter_member == $m['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block mb-1 text-xs font-medium text-gray-700">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filter_date_from) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm" />
                </div>
                <div>
                    <label for="date_to" class="block mb-1 text-xs font-medium text-gray-700">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($filter_date_to) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm" />
                </div>
                <div class="flex flex-col justify-end gap-2">
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition text-sm font-medium">
                        🔍 Apply Filters
                    </button>
                    <button type="submit" name="download" value="excel" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition text-sm font-medium">
                        📥 Download Excel
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-red-600 text-white">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium">Member</th>
                            <th class="px-2 py-2 text-left font-medium">Company</th>
                            <th class="px-2 py-2 text-left font-medium">Contact</th>
                            <th class="px-2 py-2 text-left font-medium">Email</th>
                            <th class="px-2 py-2 text-left font-medium">Phone</th>
                            <?php if ($referral_type === 'specific_ask') { ?>
                                <th class="px-2 py-2 text-left font-medium">Connect By</th>
                                <th class="px-2 py-2 text-left font-medium">Mobile</th>
                                <th class="px-2 py-2 text-left font-medium">Remarks</th>
                                <th class="px-2 py-2 text-left font-medium">Date</th>
                                <th class="px-2 py-2 text-center font-medium">Action</th>
                            <?php } elseif ($referral_type === 'specific_give') { ?>
                                <th class="px-2 py-2 text-left font-medium">Date</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody id="referralTable" class="bg-white divide-y divide-gray-100">
                        <?php foreach ($results as $row) { ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2"><?= htmlspecialchars($row['member_name']) ?></td>
                                <td class="px-2 py-2"><?= htmlspecialchars($row['company_name']) ?></td>
                                <td class="px-2 py-2"><?= htmlspecialchars($row['contact_name']) ?></td>
                                <td class="px-2 py-2"><?= htmlspecialchars($row['contact_email']) ?></td>
                                <td class="px-2 py-2"><?= htmlspecialchars($row['contact_phone']) ?></td>

                                <?php if ($referral_type === 'specific_ask') { ?>
                                    <form method="POST" class="contents">
                                        <input type="hidden" name="referral_id" value="<?= $row['id'] ?>">
                                        <td class="px-2 py-2">
                                            <select name="assigned_member" class="border border-gray-300 rounded px-1 py-1 w-full text-xs">
                                                <option value="">Select</option>
                                                <?php foreach ($members as $m) { ?>
                                                    <option value="<?= $m['id'] ?>" <?= ($row['assigned_member'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($m['name']) . ' (' . htmlspecialchars($m['mobile']) . ' - ' . htmlspecialchars($m['category']) . ')' ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text" name="mobile" value="<?= !empty($row['mobile']) ? htmlspecialchars($row['mobile']) : '' ?>"
                                                class="border border-gray-300 rounded px-1 py-1 w-full text-xs"
                                                maxlength="10" pattern="\d{10}"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                placeholder="10-digit" />
                                        </td>
                                        <td class="px-2 py-2">
                                         <textarea name="remarks" rows="4"
    class="border border-gray-300 rounded px-1 py-1 w-full text-xs resize-y min-w-[150px]"
><?= htmlspecialchars($row['remarks'] ?? '') ?></textarea>
                                        </td>
                                        <td class="px-2 py-2"><?= htmlspecialchars($row['referred_on']) ?></td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="submit" name="assign_submit"
                                                class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 text-xs">
                                                Save
                                            </button>
                                        </td>
                                    </form>
                                <?php } elseif ($referral_type === 'specific_give') { ?>
                                    <td class="px-2 py-2"><?= htmlspecialchars($row['referred_on']) ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Search Filters -->
        <div class="mt-4 bg-white rounded shadow-md p-4">
            <h3 class="text-sm font-semibold mb-3 text-gray-700">Quick Search</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Member</label>
                    <input type="text" id="searchMember" onkeyup="filterTable(0, this.value)" placeholder="Search member..." class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company</label>
                    <input type="text" id="searchCompany" onkeyup="filterTable(1, this.value)" placeholder="Search company..." class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Contact</label>
                    <input type="text" id="searchContact" onkeyup="filterTable(2, this.value)" placeholder="Search contact..." class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="text" id="searchEmail" onkeyup="filterTable(3, this.value)" placeholder="Search email..." class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" id="searchPhone" onkeyup="filterTable(4, this.value)" placeholder="Search phone..." class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function filterTable(colIndex, searchValue) {
    const table = document.getElementById("referralTable");
    const rows = table.getElementsByTagName("tr");
    searchValue = searchValue.toLowerCase();

    for (let i = 0; i < rows.length; i++) {
        const cell = rows[i].getElementsByTagName("td")[colIndex];
        if (cell) {
            const text = cell.textContent || cell.innerText;
            rows[i].style.display = text.toLowerCase().indexOf(searchValue) > -1 ? "" : "none";
        }
    }
}
</script>

</body>
</html>
