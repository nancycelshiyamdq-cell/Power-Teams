<?php
include '../config.php';

include './partials/header.php';
error_reporting(E_ALL & ~E_WARNING);
ini_set('display_errors', 0);

// Check login and role
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Get session values
$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];

// Fetch dropdown data
$regions = $conn->query("SELECT id, rvalue FROM region")->fetchAll();
$chapStmt = $conn->prepare("SELECT id, svalue FROM chapters WHERE id = ?");
$chapStmt->execute([$chapterName]);
$chapters = $chapStmt->fetchAll();
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll();

// Mappings
$region_map = [];
foreach ($regions as $row) {
    $region_map[$row['id']] = $row['rvalue'];
}

$chapter_map = [];
foreach ($chapters as $row) {
    $chapter_map[$row['id']] = $row['svalue'];
}

$powerteam_map = [];
foreach ($powerteams as $row) {
    $powerteam_map[$row['id']] = $row['pvalue'];
}

$errMessage = null;

// CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // DELETE
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);

        // Delete related attendance records first
        $stmt = $conn->prepare("DELETE FROM attendance WHERE member_id = ?");
        $stmt->execute([$delete_id]);

        // Delete related referral records next
        $stmt = $conn->prepare("DELETE FROM referrals WHERE member_id = ?");
        $stmt->execute([$delete_id]);

        // Delete referrals first
        $stmt = $conn->prepare("DELETE FROM referrals WHERE referred_member_id = ?");
        $stmt->execute([$delete_id]);

        // Now delete the member
        $stmt = $conn->prepare(query: "DELETE FROM members WHERE id = ?");
        $stmt->execute([$delete_id]);
    }

    // Handle toggle chapter_access
    elseif (isset($_POST['toggle_id'])) {
        $memberId = (int)$_POST['toggle_id'];

        $chapterAccess = isset($_POST['chapter_access']) ? 1 : 0;

        // Prepare and execute update query
        $stmt = $conn->prepare("UPDATE members SET chapter_access = ? WHERE id = ?");
        $stmt->execute([$chapterAccess, $memberId]);

        // Optionally, redirect to avoid resubmission on page refresh
        header(header: "Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // UPDATE
    elseif (isset($_POST['update_member'])) {

        $id       = intval($_POST['member_id']);
        $name     = $_POST['edit_name'];
        $region   = $_POST['edit_region'];
        $chapter  = $_POST['edit_chapter'];
        $powerteam = $_POST['edit_powerteam'];
        $category = $_POST['edit_category'];

        $stmt = $conn->prepare("UPDATE members SET
            name = ?, region = ?, chapter = ?, powerteam = ?, category = ?
            WHERE id = ?");
        $stmt->execute([$name, $region, $chapter, $powerteam, $category, $id]);
    }

    // CREATE
    elseif (isset($_POST['create_member'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $region = $_POST['region'];
        $chapter = $_POST['chapter'];
        $powerteam = $_POST['powerteam'];
        $category = $_POST['category'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Email already exists.');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO members (name, email, mobile, region, chapter, powerteam, category, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $mobile, $region, $chapter, $powerteam, $category, $password]);
        }
    }
    // ✅ Handle Active / Deactive toggle
elseif (isset($_POST['status_toggle_id'])) {
    $memberId = (int) $_POST['status_toggle_id'];
    $newStatus = isset($_POST['status']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE members SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $memberId]);

    header("Location: " . $_SERVER['REQUEST_URI']); 
    exit();
}

}
// ✅ Get session values
$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];

// ✅ Fetch the corresponding IDs from the tables
$regionStmt = $conn->prepare("SELECT id FROM region WHERE rvalue = ?");
$regionStmt->execute([$regionName]);
$regionId = $regionStmt->fetchColumn();

$chapterStmt = $conn->prepare("SELECT id FROM chapters WHERE svalue = ?");
$chapterStmt->execute([$chapterName]);
$chapterId = $chapterStmt->fetchColumn();

$powerteamStmt = $conn->prepare("SELECT id FROM powerteam WHERE pvalue = ?");
$powerteamStmt->execute([$powerteamName]);
$powerteamId = $powerteamStmt->fetchColumn();
// echo $regionName;
// ✅ Fetch only members that match session's region, chapter, and powerteam
$stmt = $conn->prepare("
    SELECT * FROM members 
    WHERE region = :region_id 
      AND chapter = :chapter_id 
    ORDER BY id DESC
");
$stmt->execute([
    ':region_id' => $regionName,
    ':chapter_id' => $chapterName
]);
$members_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Edit mode check
$editing = false;
$edit_member = [
    'id' => '',
    'name' => '',
    'email' => '',
    'mobile' => '',
    'category' => '',
    'region' => '',
    'chapter' => '',
    'powerteam' => ''
];

if (isset($_GET['edit_id'])) {
    $editing = true;
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([intval($_GET['edit_id'])]);
    $edit_member = $stmt->fetch();
}
?>

<link rel="stylesheet" href="./css/create_admin.css">

<main class="p-4 md:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
            <!-- Create/Edit Form -->
            <div class="bg-white p-6 rounded shadow-md">
                <h2 class="text-xl font-semibold mb-4 text-red-700"><?= $editing ? 'Edit Member' : 'Create Member' ?></h2>

                <?php if (!empty($errMessage)): ?>
                    <h6 class="text-[red]"><?php echo $errMessage; ?></h6>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <?php if ($editing): ?>
                        <input type="hidden" name="member_id" value="<?= $edit_member['id'] ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block font-medium">Name</label>
                        <input type="text" name="<?= $editing ? 'edit_name' : 'name' ?>" value="<?= htmlspecialchars($edit_member['name']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required />
                    </div>

                    <div>
                        <label class="block font-medium">Email <?= $editing ? '<span class="text-xs text-gray-400 font-normal">(cannot be changed)</span>' : '' ?></label>
                        <input type="email" name="<?= $editing ? 'edit_email' : 'email' ?>" value="<?= htmlspecialchars($edit_member['email']) ?>"
                            class="w-full border rounded px-3 py-2 <?= $editing ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed' : 'border-gray-300' ?>"
                            <?= $editing ? 'readonly' : 'required' ?> />
                    </div>

                    <div>
                        <label class="block font-medium">Mobile <?= $editing ? '<span class="text-xs text-gray-400 font-normal">(cannot be changed)</span>' : '' ?></label>
                        <input type="text" name="<?= $editing ? 'edit_mobile' : 'mobile' ?>" value="<?= htmlspecialchars($edit_member['mobile']) ?>"
                            class="w-full border rounded px-3 py-2 <?= $editing ? 'bg-gray-100 text-gray-500 border-gray-200 cursor-not-allowed' : 'border-gray-300' ?>"
                            <?= $editing ? 'readonly' : 'required' ?> />
                    </div>

                    <?php if (!$editing): ?>
                        <div>
                            <label class="block font-medium">Password</label>
                            <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2" required />
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block font-medium">Region</label>
                        <select name="<?= $editing ? 'edit_region' : 'region' ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="" disabled>Select Region</option>
                            <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $edit_member['region'] == $r['id'] ? 'selected' : '' ?>><?= $r['rvalue'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

             <div>
    <label class="block font-medium">Chapter</label>
    <?php $currentChapter = $chapters[0] ?? null; ?>
    <input type="text" value="<?= htmlspecialchars($currentChapter['svalue'] ?? '') ?>" 
        class="w-full border border-gray-200 rounded px-3 py-2 bg-gray-100 text-gray-500 cursor-not-allowed" readonly />
    <input type="hidden" name="<?= $editing ? 'edit_chapter' : 'chapter' ?>" value="<?= $currentChapter['id'] ?? '' ?>">
</div>

                    <div>
                        <label class="block font-medium">Powerteam</label>
                        <select name="<?= $editing ? 'edit_powerteam' : 'powerteam' ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="" disabled>Select Powerteam</option>
                            <?php foreach ($powerteams as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $edit_member['powerteam'] == $p['id'] ? 'selected' : '' ?>><?= $p['pvalue'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium">Category</label>
                        <input type="text" name="<?= $editing ? 'edit_category' : 'category' ?>" value="<?= htmlspecialchars($edit_member['category']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required />
                    </div>

                    <div class="flex items-center gap-4" style="display: flex; justify-content: center; margin-top: 3%;">
                        <button type="submit" name="<?= $editing ? 'update_member' : 'create_member' ?>" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                            <?= $editing ? 'Update Member' : 'Create Member' ?>
                        </button>
                        <?php if ($editing): ?>
                            
                            <a href="create_member.php" class="text-sm text-gray-600 underline hover:text-gray-900">Back to Create</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Members List -->
            <div class="bg-white rounded shadow-md overflow-hidden">
                <h2 class="text-lg font-semibold p-3 text-red-700 border-b">Members List</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs" style="border-collapse: collapse;">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-medium text-gray-600 align-middle">Name</th>
                                <th class="px-3 py-3 text-left font-medium text-gray-600 align-middle">Mobile</th>
                                <th class="px-3 py-3 text-left font-medium text-gray-600 align-middle">Category</th>
                                <th class="px-3 py-3 text-left font-medium text-gray-600 align-middle">Team</th>
                                <th class="px-3 py-3 text-center font-medium text-gray-600 align-middle">Access</th>
                                <th class="px-3 py-3 text-center font-medium text-gray-600 align-middle">Status</th>
                                <th class="px-3 py-3 text-center font-medium text-gray-600 align-middle">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($members_result as $member): ?>
                                <tr class="hover:bg-gray-50" style="vertical-align: middle;">
                                    <td class="px-3 py-3 text-gray-900 font-medium align-middle"><?= htmlspecialchars($member['name']) ?></td>
                                    <td class="px-3 py-3 text-gray-700 align-middle"><?= htmlspecialchars($member['mobile'] ?? '--') ?></td>
                                    <td class="px-3 py-3 text-gray-700 align-middle"><?= htmlspecialchars($member['category'] ?? '--') ?></td>
                                    <td class="px-3 py-3 text-gray-700 align-middle"><?= htmlspecialchars($powerteam_map[$member['powerteam']] ?? $member['powerteam']) ?></td>
                                    <td class="px-3 py-3 text-center align-middle">
                                        <div class="flex items-center justify-center">
                                            <input type="checkbox"
                                                data-member-id="<?= htmlspecialchars($member['id']) ?>"
                                                onchange="toggleChapterAccess(this)"
                                                class="h-4 w-4 text-red-600 rounded cursor-pointer"
                                                <?= !empty($member['chapter_access']) ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle">
                                        <div class="flex items-center justify-center">
                                            <form method="POST" action="" class="inline-flex items-center">
                                                <input type="hidden" name="status_toggle_id" value="<?= $member['id'] ?>">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="status" value="1" class="sr-only peer" onchange="this.form.submit()" <?= $member['status'] == 1 ? 'checked' : '' ?>>
                                                    <div class="relative w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                                </label>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="?edit_id=<?= $member['id'] ?>" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" class="inline-flex items-center">
                                                <input type="hidden" name="delete_id" value="<?= $member['id'] ?>">
                                                <button type="submit" onclick="return confirm('Delete this member?')" class="inline-flex items-center px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</main>

<script>
function toggleChapterAccess(checkbox) {
    const memberId = checkbox.dataset.memberId;
    const formData = new FormData();
    formData.append('toggle_id', memberId);
    if (checkbox.checked) {
        formData.append('chapter_access', '1');
    }
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).catch(() => {
        // Revert checkbox state if request failed
        checkbox.checked = !checkbox.checked;
    });
}
</script>

<?php

include './partials/footer.php'; ?>
