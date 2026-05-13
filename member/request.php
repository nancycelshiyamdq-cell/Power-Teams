<?php
include '../config.php';
// session_start();

include './partials/header.php';

// 1️⃣ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];
// echo "chapter :" , $regionName;
// 2️⃣ Fetch IDs from names
$regionId = $conn->prepare("SELECT id FROM region WHERE rvalue = :regionName");
$regionId->execute(['regionName' => $regionName]);
$regionId = $regionId->fetchColumn();

$chapterId = $conn->prepare("SELECT id FROM chapters WHERE svalue = :chapterName");
$chapterId->execute(['chapterName' => $chapterName]);
$chapterId = $chapterId->fetchColumn();

$powerteamId = $conn->prepare("SELECT id FROM powerteam WHERE pvalue = :powerteamName");
$powerteamId->execute(['powerteamName' => $powerteamName]);
$powerteamId = $powerteamId->fetchColumn();

// 3️⃣ Fetch members
$membersQuery = $conn->prepare("
    SELECT id, name 
    FROM members 
    WHERE region = :regionId AND chapter = :chapterId AND powerteam = :powerteamId AND status = 1
");
$membersQuery->execute([
    ':regionId' => $regionName,
    ':chapterId' => $chapterName,
    ':powerteamId' => $powerteamName
]);
$members = $membersQuery->fetchAll(PDO::FETCH_ASSOC);

// 4️⃣ Handle assignment/remark update
// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['request_id'])) {
        $newStatus = isset($_POST['approve_status']) ? 2 : 3; // 2 = Approve, 3 = Reject

        $stmt = $conn->prepare("
            UPDATE request_referral 
            SET status = :status
            WHERE id = :request_id
        ");
        $stmt->execute([
            ':status' => $newStatus,
            ':request_id' => $_POST['request_id']
        ]);

        echo "<script>alert('Status updated successfully');</script>";
    } else {
        echo "<script>alert('Request ID not found');</script>";
    }
}



// 6️⃣ Filters
$filter_member = $_GET['member_id'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$referral_type = $_GET['referral_type'] ?? 'specific_ask';

// 7️⃣ Fetch referrals with pending requests only
$params = [
    ':regionId' => $regionName,
    ':chapterId' => $chapterName,
    ':powerteamId' => $powerteamName
];

$query = "
    SELECT r.*, m.name AS member_name, rr.status AS rr_status, rr.id AS request_id, u.name AS request_name
    FROM referrals r
    JOIN members m ON r.member_id = m.id
    LEFT JOIN request_referral rr 
        ON rr.referral_id = r.id
        LEFT JOIN members u ON rr.user_id = u.id 
    WHERE m.region = :regionId 
      AND m.chapter = :chapterId 
      AND m.powerteam = :powerteamId
      AND rr.status = 1
";


$query .= " ORDER BY r.referred_on DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



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
<body>

<?php  ?>

<main>
    

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded">
                <thead class="bg-red-600 text-white">
                <tr>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Member<br>
                        <input type="text" onkeyup="filterTable(0, this.value)" placeholder="Search..." class="w-full mt-1 px-2 py-1 text-sm text-black rounded">
                    </th>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Company<br>
                        <input type="text" onkeyup="filterTable(1, this.value)" placeholder="Search..." class="w-full mt-1 px-2 py-1 text-sm text-black rounded">
                    </th>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Contact<br>
                        <input type="text" onkeyup="filterTable(2, this.value)" placeholder="Search..." class="w-full mt-1 px-2 py-1 text-sm text-black rounded">
                    </th>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Email<br>
                        <input type="text" onkeyup="filterTable(3, this.value)" placeholder="Search..." class="w-full mt-1 px-2 py-1 text-sm text-black rounded">
                    </th>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Phone<br>
                        <input type="text" onkeyup="filterTable(4, this.value)" placeholder="Search..." class="w-full mt-1 px-2 py-1 text-sm text-black rounded">
                    </th>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Request Name<br>
                        
                    </th>
                    <th class="text-left px-4 py-2 text-sm font-semibold text-white">Connect By</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Mobile No</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Remarks</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Asked At</th>
                    <?php if ($referral_type === 'specific_ask') { ?>
                        <!-- <th class="text-left px-4 py-2 text-sm font-semibold text-white">Connect By</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Mobile No</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Remarks</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Asked At</th> -->
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">approved</th>
                        <?php } elseif ($referral_type === 'specific_give') { ?>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Given At</th>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Request</th>
                        <?php } ?>
                        <th class="text-left px-4 py-2 text-sm font-semibold text-white">Reject</th>
                </tr>
           </thead>
            <tbody id="referralTable" class="bg-white divide-y divide-gray-200">
                <?php foreach ($results as $row) { ?>
                    <tr>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['member_name']) ?></td> 
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['company_name']) ?></td>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['contact_name']) ?></td>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['contact_email']) ?></td>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['contact_phone']) ?></td>
                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['request_name']) ?></td>

                     
   
    <form method="POST">
        <input type="hidden" name="referral_id" value="<?= $row['id'] ?>">
        <td class="px-4 py-2 text-sm">
            <select name="assigned_member" class="border border-gray-300 rounded px-2 py-1 w-full <?= $assignedDisabled ?>">
                <option value="">-- Select --</option>
                <?php foreach ($members as $m) { ?>
                    <option value="<?= $m['id'] ?>" <?= ($row['assigned_member'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['name']) ?>
                    </option>
                <?php } ?>
            </select>
        </td>
        <td class="px-4 py-2 text-sm">
            <input type="tel" name="mobile" value="<?= htmlspecialchars($row['mobile'] ?? '') ?>" class="border border-gray-300 rounded px-2 py-1 w-full " />
        </td>
        <td class="px-4 py-2 text-sm">
            <input type="text" name="remarks" value="<?= htmlspecialchars($row['remarks'] ?? '') ?>" class="border border-gray-300 rounded px-2 py-1 w-full " />
        </td>
        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($row['referred_on']) ?></td>
        <!-- <td class="px-4 py-2 text-sm">
            <button type="submit" name="approve_status" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                Approve
            </button>
        </td>
        <td class="px-4 py-2 text-sm">
            <button type="submit" name="reject_status" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                reject
            </button>
        </td> -->
    </form>
<form method="POST">
    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
    <input type="hidden" name="referral_id" value="<?= $row['id'] ?>">

    <td class="px-4 py-2 text-sm">
        <button type="submit" name="approve_status" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
            Approve
        </button>
    </td>
    <td class="px-4 py-2 text-sm">
        <button type="submit" name="reject_status" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
            Reject
        </button>
    </td>
</form>



                    </tr>
                <?php } ?>
            </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
