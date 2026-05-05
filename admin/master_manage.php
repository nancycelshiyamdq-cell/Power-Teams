<?php
include '../config.php';
include './partials/header.php';

// Check login and role
if (!isset($_SESSION['username'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$success_msg = '';
$error_msg = '';

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputTable = $_POST['table'];
    $value = trim($_POST['value'] ?? '');  // Make sure this is defined

    switch ($inputTable) {
        case 'region':
            $table = 'region';
            $column = 'rvalue';
            break;
        case 'chapter':
            $table = 'chapters';
            $column = 'svalue';
            break;
        case 'powerteam':
            $table = 'powerteam';
            $column = 'pvalue';
            break;
        default:
            $error_msg = "Invalid table type.";
            $table = '';
            $column = '';
    }

    if ($value !== '' && $table !== '' && $column !== '') {
        $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
        $stmt->execute([$value]);
        $success_msg = ucfirst($inputTable) . " added successfully.";
    } else {
        $error_msg = "Value cannot be empty or invalid table type selected.";
    }
}


// Handle Delete
if (isset($_GET['delete'], $_GET['table'], $_GET['id'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];
    $conn->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
    header("Location: master_manage.php"); exit;
}

// Fetch data
$regions = $conn->query("SELECT * FROM region ORDER BY id DESC;")->fetchAll();
$chapters = $conn->query("SELECT * FROM chapters ORDER BY id DESC")->fetchAll();
$powerteams = $conn->query("SELECT * FROM powerteam ORDER BY id DESC")->fetchAll();
?>

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

<main class="max-w-5xl mx-auto py-10 px-4">
        <h2 class="text-3xl font-bold text-[var(--bni-red)] mb-6 text-center">Manage Master Data</h2>

        <?php if ($success_msg): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= $success_msg ?></div>
        <?php elseif ($error_msg): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= $error_msg ?></div>
        <?php endif; ?>

        <!-- Form Section -->
        <form method="POST" class="bg-white p-6 rounded-xl shadow-md mb-8 space-y-4">
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label for="table" class="block text-sm font-semibold mb-1">Type</label>
                    <select name="table" id="table" required class="bni-input w-full rounded-md border px-4 py-2">
                        <option value="">Select</option>
                        <!-- <option value="region">Region</option>
                        <option value="chapter">Chapter</option> -->
                        <option value="powerteam">Powerteam</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="value" class="block text-sm font-semibold mb-1">Value</label>
                    <input type="text" name="value" id="value" required class="bni-input w-full rounded-md border px-4 py-2" />
                </div>
            </div>
            <div class="pt-4">
                <button type="submit" class="bni-button text-white px-6 py-2 rounded-md font-semibold">Add Master</button>
            </div>
        </form>

        <!-- Display Tables -->
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Region -->
            <div>
                <h3 class="text-lg font-bold text-[var(--bni-red)] mb-2">Regions</h3>
                <ul class="bg-white rounded shadow-sm divide-y">
                    <?php foreach ($regions as $r): ?>
                        <li class="px-4 py-2 flex justify-between items-center">
                            <span><?= htmlspecialchars($r['rvalue']) ?></span>
<a href="?delete=1&table=region&id=<?= $r['id'] ?>" onclick="return confirm('Delete region?')" class="text-red-600 hover:underline text-sm">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Chapter -->
            <div>
                <h3 class="text-lg font-bold text-[var(--bni-red)] mb-2">Chapters</h3>
                <ul class="bg-white rounded shadow-sm divide-y">
                    <?php foreach ($chapters as $c): ?>
                        <li class="px-4 py-2 flex justify-between items-center">
                            <span><?= htmlspecialchars($c['svalue']) ?></span>
                            <a href="?delete=1&table=chapter&id=<?= $c['id'] ?>" onclick="return confirm('Delete chapter?')" class="text-red-600 hover:underline text-sm">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Powerteam -->
            <div>
                <h3 class="text-lg font-bold text-[var(--bni-red)] mb-2">Powerteams</h3>
                <ul class="bg-white rounded shadow-sm divide-y">
                    <?php foreach ($powerteams as $p): ?>
                        <li class="px-4 py-2 flex justify-between items-center">
                            <span><?= htmlspecialchars($p['pvalue']) ?></span>
                            <a href="?delete=1&table=powerteam&id=<?= $p['id'] ?>" onclick="return confirm('Delete powerteam?')" class="text-red-600 hover:underline text-sm">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </main>

<?php include './partials/footer.php'; ?>
