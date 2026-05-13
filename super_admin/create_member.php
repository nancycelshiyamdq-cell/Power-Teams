<?php
include '../config.php';

// Fetch dropdown data
$regions = $conn->query("SELECT id, rvalue FROM region")->fetchAll();
$chapters = $conn->query("SELECT id, svalue FROM chapters")->fetchAll();
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
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // UPDATE
    elseif (isset($_POST['update_member'])) {

        $id = intval($_POST['member_id']);
        $name = $_POST['edit_name'];
        $email = $_POST['edit_email'];
        $mobile = $_POST['edit_mobile'];
        $region = $_POST['edit_region'];
        $chapter = $_POST['edit_chapter'];
        $powerteam = $_POST['edit_powerteam'];
        $category = $_POST['edit_category'];

        $stmt = $conn->prepare("SELECT id FROM members WHERE id != ? AND (email = ? OR mobile = ?)");
        $stmt->execute([$id, $email, $mobile]);

        if ($stmt->rowCount() > 0) {
            $errMessage =  'Duplicate email or mobile.';
        } else {
            $stmt = $conn->prepare("UPDATE members SET 
                name = ?, email = ?, mobile = ?, region = ?, chapter = ?, powerteam = ?, category = ?
                WHERE id = ?");
            $stmt->execute([$name, $email, $mobile, $region, $chapter, $powerteam, $category, $id]);
        }
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
}

// Fetch all members
$members_result = $conn->query("SELECT * FROM members ORDER BY id DESC")->fetchAll();

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="assets/images/fav_icon.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/create_admin.css">
</head>

<body class="bg-gray-100 text-gray-800 font-sans">
    <?php include './partials/header.php'; ?>

    <main>
        <div class="max-w-5xl mx-auto space-y-10">
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
                        <label class="block font-medium">Email</label>
                        <input type="email" name="<?= $editing ? 'edit_email' : 'email' ?>" value="<?= htmlspecialchars($edit_member['email']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required />
                    </div>

                    <div>
                        <label class="block font-medium">Mobile</label>
                        <input type="text" name="<?= $editing ? 'edit_mobile' : 'mobile' ?>" value="<?= htmlspecialchars($edit_member['mobile']) ?>" class="w-full border border-gray-300 rounded px-3 py-2" required />
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
                        <select name="<?= $editing ? 'edit_chapter' : 'chapter' ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="" disabled>Select Chapter</option>
                            <?php foreach ($chapters as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $edit_member['chapter'] == $c['id'] ? 'selected' : '' ?>><?= $c['svalue'] ?></option>
                            <?php endforeach; ?>
                        </select>
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
            <table>
                <thead>
                    <tr>
                        <th>Login Name</th>
                        <th>Mobile</th>
                        <th>Region</th>
                        <th>Chapter</th>
                        <th>Powerteam</th>
                        <th>Category</th>
                        <th>Chapter Access</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members_result as $member): ?>
                        <tr>
                            <td><?= htmlspecialchars($member['name']) ?></td>
                            <td><?= htmlspecialchars($member['mobile'] ?? '--') ?></td>
                            <td><?= htmlspecialchars($region_map[$member['region']] ?? $member['region']) ?></td>
                            <td><?= htmlspecialchars($chapter_map[$member['chapter']] ?? $member['chapter']) ?></td>
                            <td><?= htmlspecialchars($powerteam_map[$member['powerteam']] ?? $member['powerteam']) ?></td>
                            <td><?= htmlspecialchars($member['category'] ?? '--') ?></td>
                            <td class="">
                                <form method="POST" action="" class="inline-block p-0 m-0 bg-[transparent]" style="background-color: transparent;">
                                    <input type="hidden" name="toggle_id" value="<?= htmlspecialchars($member['id']) ?>">
                                    <input type="checkbox" id="toggle-<?= htmlspecialchars($member['id']) ?>" name="chapter_access" value="1" onchange="this.form.submit()" class="h-6 w-6 bg-transparent" <?= !empty($member['chapter_access']) ? 'checked' : '' ?>>
                                </form>
                            </td>
                            <td class="actions">
                                <a href="?edit_id=<?= $member['id'] ?>">
                                    <button class="edit bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Edit</button>
                                </a>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?= $member['id'] ?>">
                                    <button class="delete bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.tailwindcss.com"></script>
</body>
</html>
