<?php
include '../config.php';

// Fetch dropdown data
$regions = $conn->query("SELECT id, rvalue FROM region")->fetchAll();
$chapters = $conn->query("SELECT id, svalue FROM chapters")->fetchAll();
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll();

// Create lookup maps
$region_map = [];
foreach ($regions as $row) $region_map[$row['id']] = $row['rvalue'];

$chapter_map = [];
foreach ($chapters as $row) $chapter_map[$row['id']] = $row['svalue'];

$powerteam_map = [];
foreach ($powerteams as $row) $powerteam_map[$row['id']] = $row['pvalue'];

$editing = false;
$edit_admin = [];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
    } elseif (isset($_POST['update_admin'])) {
        $id = $_POST['admin_id'];
        $username = $_POST['username'];
        $region = $_POST['region'];
        $chapter = $_POST['chapter'];
        $powerteam = $_POST['powerteam'];

        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Username already exists.');</script>";
        } else {
            $update = $conn->prepare("UPDATE admins SET username=?, region=?, chapter=?, powerteam=? WHERE id=?");
            $update->execute([$username, $region, $chapter, $powerteam, $id]);
        }
    } elseif (isset($_POST['create_admin'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $region = $_POST['region'];
        $chapter = $_POST['chapter'];
        $powerteam = $_POST['powerteam'];

        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Username already exists.');</script>";
        } else {
            $insert = $conn->prepare("INSERT INTO admins (username, password, region, chapter, powerteam) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$username, $password, $region, $chapter, $powerteam]);
        }
    }
}

// Handle GET edit request
if (isset($_GET['edit_id'])) {
    $editing = true;
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_admin = $stmt->fetch();
}

$admins = $conn->query("SELECT id, username, region, chapter, powerteam FROM admins ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BNI Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="assets/images/fav_icon.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --bni-red: #a6192e;
        }
        .bni-input {
            border-color: var(--bni-red);
        }
        .bni-input:focus {
            border-color: var(--bni-red);
            box-shadow: 0 0 0 1px var(--bni-red);
        }
        .bni-button {
            background-color: var(--bni-red);
        }
        .bni-button:hover {
            background-color: #851423;
        }
    </style>

</head>
<body class="bg-gray-50">
    <?php include './partials/header.php'; ?>

<main class="max-w-4xl mx-auto py-10">
    <h2 class="text-3xl font-bold text-center text-[var(--bni-red)] mb-6">
        <?= $editing ? 'Edit Admin' : 'Create Admin' ?>
    </h2>

    <form method="POST" class="bg-white p-8 rounded-xl shadow-md space-y-6">
        <?php if ($editing): ?>
            <input type="hidden" name="admin_id" value="<?= $edit_admin['id'] ?>">
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="col-span-full">
                <label for="username" class="block text-sm font-semibold mb-1">Login Name</label>
                <input type="text" id="username" name="username" required value="<?= $editing ? htmlspecialchars($edit_admin['username']) : '' ?>" class="bni-input w-full rounded-md border px-4 py-2" />
            </div>

            <?php if (!$editing): ?>
                <div class="col-span-full">
                    <label for="password" class="block text-sm font-semibold mb-1">Password</label>
                    <input type="password" id="password" name="password" required class="bni-input w-full rounded-md border px-4 py-2" />
                </div>
            <?php endif; ?>

            <div>
                <label for="region" class="block text-sm font-semibold mb-1">Region</label>
                <select id="region" name="region" required class="bni-input w-full rounded-md border px-4 py-2">
                    <option value="" disabled <?= !$editing ? 'selected' : '' ?>>Select Region</option>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $editing && $edit_admin['region'] == $r['id'] ? 'selected' : '' ?>><?= $r['rvalue'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="chapter" class="block text-sm font-semibold mb-1">Chapter</label>
                <select id="chapter" name="chapter" required class="bni-input w-full rounded-md border px-4 py-2">
                    <option value="" disabled <?= !$editing ? 'selected' : '' ?>>Select Chapter</option>
                    <?php foreach ($chapters as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $editing && $edit_admin['chapter'] == $c['id'] ? 'selected' : '' ?>><?= $c['svalue'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-span-full">
                <label for="powerteam" class="block text-sm font-semibold mb-1">Powerteam</label>
                <select id="powerteam" name="powerteam" required class="bni-input w-full rounded-md border px-4 py-2">
                    <option value="" disabled <?= !$editing ? 'selected' : '' ?>>Select Powerteam</option>
                    <?php foreach ($powerteams as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $editing && $edit_admin['powerteam'] == $p['id'] ? 'selected' : '' ?>><?= $p['pvalue'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex justify-between items-center pt-4">
            <button type="submit" name="<?= $editing ? 'update_admin' : 'create_admin' ?>" class="bni-button text-white font-semibold px-6 py-2 rounded-md">
                <?= $editing ? 'Update Admin' : 'Create Admin' ?>
            </button>
            <?php if ($editing): ?>
                <a href="create_admin.php" class="text-sm text-red-500 hover:underline">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <h3 class="text-xl font-bold mt-10 mb-4 text-[var(--bni-red)]">Admins List</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left bg-white rounded-md shadow-sm">
            <thead class="bg-[var(--bni-red)] text-white">
                <tr>
                    <th class="px-4 py-2">Login Name</th>
                    <th class="px-4 py-2">Region</th>
                    <th class="px-4 py-2">Chapter</th>
                    <th class="px-4 py-2">Powerteam</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2"><?= htmlspecialchars($admin['username']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($region_map[$admin['region']] ?? $admin['region']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($chapter_map[$admin['chapter']] ?? $admin['chapter']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($powerteam_map[$admin['powerteam']] ?? $admin['powerteam']) ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="?edit_id=<?= $admin['id'] ?>" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="delete_id" value="<?= $admin['id'] ?>">
                                <button type="submit" onclick="return confirm('Are you sure?')" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>