<?php
include '../config.php';
include './partials/header.php';

// Fetch members
$members = $conn->query("SELECT id, name,mobile,category FROM members where powerteam= $powerteam AND region = $region AND chapter =$chapter")->fetchAll();

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

// Base query
$query = "SELECT r.*, m.name as member_name FROM referrals r 
          JOIN members m ON r.member_id = m.id WHERE powerteam= $powerteam AND region = $region AND chapter =$chapter";
$params = [];

if ($referral_type === 'specific_ask') {
    $query .= " AND r.referral_type = :ref_type";
    $params['ref_type'] = 'specific ask';
} elseif ($referral_type === 'specific_give') {
    $query .= " AND r.referral_type = :ref_type";
    $params['ref_type'] = 'specific give';
}

if ($filter_member !== '') {
    $query .= " AND r.member_id = :member_id";
    $params['member_id'] = (int) $filter_member;
}
if ($filter_date_from) {
    $query .= " AND r.referred_on >= :date_from";
    $params['date_from'] = $filter_date_from;
}
if ($filter_date_to) {
    $query .= " AND r.referred_on <= :date_to";
    $params['date_to'] = $filter_date_to;
}
$query .= " ORDER BY referred_on DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specific Asks/Gives List</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body class="bg-gray-50">

<main class="p-4 sm:p-6">
    <div class="main-content">
        <h2 class="text-xl sm:text-2xl font-semibold mb-4 sm:mb-6">Specific Asks/Gives List</h2>

        <!-- Tabs -->
        <div class="tab-buttons flex flex-wrap gap-2 sm:gap-4 mb-4 sm:mb-6">
            <a href="?referral_type=specific_ask<?= $filter_member ? "&member_id=$filter_member" : '' ?><?= $filter_date_from ? "&date_from=$filter_date_from" : '' ?><?= $filter_date_to ? "&date_to=$filter_date_to" : '' ?>"
                class="px-3 py-2 rounded border text-sm sm:text-base <?= $referral_type === 'specific_ask' ? 'bg-red-600 text-white border-blue-600' : 'bg-white text-blue-600 border-blue-600 hover:bg-blue-100' ?>">
                Specific Asks
            </a>
            <a href="?referral_type=specific_give<?= $filter_member ? "&member_id=$filter_member" : '' ?><?= $filter_date_from ? "&date_from=$filter_date_from" : '' ?><?= $filter_date_to ? "&date_to=$filter_date_to" : '' ?>"
                class="px-3 py-2 rounded border text-sm sm:text-base <?= $referral_type === 'specific_give' ? 'bg-red-600 text-white border-blue-600' : 'bg-white text-blue-600 border-blue-600 hover:bg-blue-100' ?>">
                Specific Gives
            </a>
        </div>

        <!-- Filters -->
        <form method="GET" class="filter-form mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="referral_type" value="<?= htmlspecialchars($referral_type) ?>" />
            <div>
                <label for="member_id" class="block mb-1 text-sm font-medium">Member</label>
                <select name="member_id" id="member_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">All Members</option>
                    <?php foreach ($members as $m) { ?>
                        <option value="<?= $m['id'] ?>" <?= $filter_member == $m['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="date_from" class="block mb-1 text-sm font-medium">Referred From</label>
                <input type="date" name="date_from" id="date_from"
                    value="<?= htmlspecialchars($filter_date_from) ?>"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
            </div>
            <div>
                <label for="date_to" class="block mb-1 text-sm font-medium">Referred To</label>
                <input type="date" name="date_to" id="date_to"
                    value="<?= htmlspecialchars($filter_date_to) ?>"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
            </div>
            <div class="flex flex-col justify-end space-y-2">
                <button type="submit"
                    class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 text-sm">Apply Filters</button>
                <button type="submit" name="download" value="excel"
                    class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 text-sm">Download Excel</button>
            </div>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded text-sm">
                <thead class="bg-red-600 text-white">
                    <tr class="text-left">
                        <th class="px-2 sm:px-4 py-2">Member<br>
                            <input type="text" onkeyup="filterTable(0, this.value)" placeholder="Search..."
                                class="w-full mt-1 px-2 py-1 text-xs sm:text-sm text-black rounded">
                        </th>
                        <th class="px-2 sm:px-4 py-2">Company<br>
                            <input type="text" onkeyup="filterTable(1, this.value)" placeholder="Search..."
                                class="w-full mt-1 px-2 py-1 text-xs sm:text-sm text-black rounded">
                        </th>
                        <th class="px-2 sm:px-4 py-2">Contact<br>
                            <input type="text" onkeyup="filterTable(2, this.value)" placeholder="Search..."
                                class="w-full mt-1 px-2 py-1 text-xs sm:text-sm text-black rounded">
                        </th>
                        <th class="px-2 sm:px-4 py-2">Email<br>
                            <input type="text" onkeyup="filterTable(3, this.value)" placeholder="Search..."
                                class="w-full mt-1 px-2 py-1 text-xs sm:text-sm text-black rounded">
                        </th>
                        <th class="px-2 sm:px-4 py-2">Phone<br>
                            <input type="text" onkeyup="filterTable(4, this.value)" placeholder="Search..."
                                class="w-full mt-1 px-2 py-1 text-xs sm:text-sm text-black rounded">
                        </th>
                        <?php if ($referral_type === 'specific_ask') { ?>
                            <th class="px-2 sm:px-4 py-2">Connect By</th>
                            <th class="px-2 sm:px-4 py-2">Mobile No</th>
                            <th class="px-2 sm:px-4 py-2">Remarks</th>
                            <th class="px-2 sm:px-4 py-2">Asked At</th>
                            <th class="px-2 sm:px-4 py-2">Action</th>
                        <?php } elseif ($referral_type === 'specific_give') { ?>
                            <th class="px-2 sm:px-4 py-2">Given At</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody id="referralTable" class="divide-y divide-gray-200">
                    <?php foreach ($results as $row) { ?>
                        <form method="POST">
                            <tr>
                                <input type="hidden" name="referral_id" value="<?= $row['id'] ?>">
                                <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['member_name']) ?></td>
                                <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['company_name']) ?></td>
                                <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['contact_name']) ?></td>
                                <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['contact_email']) ?></td>
                                <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['contact_phone']) ?></td>

                                <?php if ($referral_type === 'specific_ask') { ?>
                                    <td class="px-2 sm:px-4 py-2">
                                        <select name="assigned_member" class="border border-gray-300 rounded px-2 py-1 w-full text-sm">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($members as $m) { ?>
                                                <option value="<?= $m['id'] ?>" <?= ($row['assigned_member'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                                                   <?= htmlspecialchars($m['name']) . ' (' . htmlspecialchars($m['mobile']) . ' - ' . htmlspecialchars($m['category']) . ')' ?>

                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="px-2 sm:px-4 py-2">
                                        <input type="text" name="mobile" value="<?= !empty($row['mobile']) ? htmlspecialchars($row['mobile']) : '' ?>"
                                            class="border border-gray-300 rounded px-2 py-1 w-full text-sm"
                                            maxlength="10" pattern="\d{10}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            placeholder="10-digit number" />
                                    </td>
                                    <td class="px-2 sm:px-4 py-2">
                                        <input type="text" name="remarks"
                                            value="<?= htmlspecialchars($row['remarks'] ?? '') ?>"
                                            class="border border-gray-300 rounded px-2 py-1 w-full text-sm" />
                                    </td>
                                    <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['referred_on']) ?></td>
                                    <td class="px-2 sm:px-4 py-2">
                                        <button type="submit" name="assign_submit"
                                            class="bg-green-600 text-white px-2 sm:px-3 py-1 rounded hover:bg-green-700 text-xs sm:text-sm">
                                            Save
                                        </button>
                                    </td>
                                <?php } elseif ($referral_type === 'specific_give') { ?>
                                    <td class="px-2 sm:px-4 py-2"><?= htmlspecialchars($row['referred_on']) ?></td>
                                <?php } ?>
                            </tr>
                        </form>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
