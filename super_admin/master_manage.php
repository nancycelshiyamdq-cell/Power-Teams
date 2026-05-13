<?php
include '../config.php';

$success_msg = '';
$error_msg = '';

// ================= Handle Add =================
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $inputTable = $_POST['table'];
//     $value = trim($_POST['value'] ?? '');

//     $allowedTables = [
//         'region' => 'rvalue',
//         'chapter' => 'svalue',
//         'powerteam' => 'pvalue'
//     ];

//     if (isset($allowedTables[$inputTable]) && $value !== '') {
//         $table = $inputTable;
//         $column = $allowedTables[$inputTable];
//         $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
//         $stmt->execute([$value]);
//         $success_msg = ucfirst($inputTable) . " added successfully.";
//         header("Location: master_manage.php"); // Refresh page
//         exit;
//     } else {
//         $error_msg = "Invalid table type or empty value.";
//     }
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputTable = $_POST['table'];
    $value = trim($_POST['value'] ?? '');

    $allowedTables = [
        'region' => 'rvalue',
        'chapter' => 'svalue',
        'powerteam' => 'pvalue'
    ];

    if (isset($allowedTables[$inputTable]) && $value !== '') {
        $table = $inputTable;
        $column = $allowedTables[$inputTable];

        // ✅ Handle chapter image upload
        $imagePath = null;
        if ($table === 'chapter' && isset($_FILES['chapter_image']) && $_FILES['chapter_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/chapters/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = pathinfo($_FILES['chapter_image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('chapter_', true) . '.' . $ext;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['chapter_image']['tmp_name'], $targetPath)) {
                $imagePath = $fileName;
            }
        }

        if ($table === 'chapter') {
            $stmt = $conn->prepare("INSERT INTO chapters ($column, image) VALUES (?, ?)");
            $stmt->execute([$value, $imagePath]);
        } else {
            $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
            $stmt->execute([$value]);
        }

        $success_msg = ucfirst($inputTable) . " added successfully.";
        header("Location: master_manage.php");
        exit;
    } else {
        $error_msg = "Invalid table type or empty value.";
    }
}

// ================= Handle Delete =================
if (isset($_GET['delete'], $_GET['table'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $urlTable = $_GET['table'];

    // Map URL value to actual DB table name
    $allowedTables = [
        'region' => 'region',
        'chapter' => 'chapters',
        'powerteam' => 'powerteam'
    ];

    if (array_key_exists($urlTable, $allowedTables)) {
        $table = $allowedTables[$urlTable]; // Use actual table name
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect to refresh the page
        header("Location: master_manage.php");
        exit;
    } else {
        $error_msg = "Invalid table for deletion.";
    }
}


// ================= Fetch Data =================
$regions = $conn->query("SELECT * FROM region ORDER BY id DESC")->fetchAll();
$chapters = $conn->query("SELECT * FROM chapters ORDER BY id DESC")->fetchAll();
$powerteams = $conn->query("SELECT * FROM powerteam ORDER BY id DESC")->fetchAll();
?>
<?php include './partials/header.php'; ?>

<style>
    :root { --bni-red: #a6192e; }
    .bni-input { border-color: var(--bni-red); }
    .bni-input:focus { border-color: var(--bni-red); box-shadow: 0 0 0 1px var(--bni-red); }
    .bni-button { background-color: var(--bni-red); }
    .bni-button:hover { background-color: #851423; }
</style>

<main class="p-4 sm:p-6">
    <div class="max-w-5xl mx-auto">
    <h2 class="text-3xl font-bold text-[var(--bni-red)] mb-6 text-center">Manage Master Data</h2>

    <?php if ($success_msg): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= $success_msg ?></div>
    <?php elseif ($error_msg): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= $error_msg ?></div>
    <?php endif; ?>

    <!-- Add Master Form -->
    <!-- <form method="POST" class="bg-white p-6 rounded-xl shadow-md mb-8 space-y-4">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label for="table" class="block text-sm font-semibold mb-1">Type</label>
                <select name="table" id="table" required class="bni-input w-full rounded-md border px-4 py-2">
                    <option value="">Select</option>
                    <option value="region">Region</option>
                    <option value="chapter">Chapter</option>
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
    </form> -->
<form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-md mb-8 space-y-4">
    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label for="table" class="block text-sm font-semibold mb-1">Type</label>
            <select name="table" id="table" required onchange="toggleImageInput(this)" class="bni-input w-full rounded-md border px-4 py-2">
                <option value="">Select</option>
                <option value="region">Region</option>
                <option value="chapter">Chapter</option>
                <option value="powerteam">Powerteam</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="value" class="block text-sm font-semibold mb-1">Value</label>
            <input type="text" name="value" id="value" required class="bni-input w-full rounded-md border px-4 py-2" />
        </div>
    </div>

    <!-- 🖼 Image input (only shows when chapter selected) -->
    <div id="imageInput" class="hidden">
        <label for="chapter_image" class="block text-sm font-semibold mb-1">Chapter Image</label>
        <input type="file" name="chapter_image" accept="image/*" class="bni-input w-full rounded-md border px-4 py-2" />
    </div>

    <div class="pt-4">
        <button type="submit" class="bni-button text-white px-6 py-2 rounded-md font-semibold">Add Master</button>
    </div>
</form>

<script>
function toggleImageInput(select) {
    const imageDiv = document.getElementById('imageInput');
    imageDiv.classList.toggle('hidden', select.value !== 'chapter');
}
</script>

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
        <!-- <div>
            <h3 class="text-lg font-bold text-[var(--bni-red)] mb-2">Chapters</h3>
            <ul class="bg-white rounded shadow-sm divide-y">
                <?php foreach ($chapters as $c): ?>
                    <li class="px-4 py-2 flex justify-between items-center">
                        <span><?= htmlspecialchars($c['svalue']) ?></span>
                        <a href="?delete=1&table=chapter&id=<?= $c['id'] ?>" onclick="return confirm('Delete chapter?')" class="text-red-600 hover:underline text-sm">Delete</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div> -->
<!-- <div>
    <h3 class="text-lg font-bold text-[var(--bni-red)] mb-2">Chapters</h3>
    <ul class="bg-white rounded shadow-sm divide-y">
        <?php foreach ($chapters as $c): ?>
            <li class="px-4 py-2 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    
                    <?php if (!empty($c['image'])): ?>
                        <?php
                        // Get the base URL dynamically
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                        $baseUrl  = $protocol . $_SERVER['HTTP_HOST'] . '/powerteam/'; // adjust if project folder differs

                        // Determine which image to use
                        $chapterImage = !empty($logo_path) ? "uploads/chapters/" . $logo_path : "assets/logo_w.png";
                        ?>
                        <img src="<?= $baseUrl . htmlspecialchars($chapterImage) ?>" alt="Logo" class="h-10 w-10 object-contain" />
                    <?php endif; ?>
                </div>
                <div>
                    <span><?= htmlspecialchars($c['svalue']) ?></span>
                </div>
                <a href="?delete=1&table=chapter&id=<?= $c['id'] ?>" 
                   onclick="return confirm('Delete chapter?')" 
                   class="text-red-600 hover:underline text-sm">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div> -->
<div>
    <h3 class="text-lg font-bold text-[var(--bni-red)] mb-2">Chapters</h3>
    <ul class="bg-white rounded shadow-sm divide-y">
        <?php
        // Base URL dynamically
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . '/powerteam/'; // adjust project folder if needed
        ?>
        <?php foreach ($chapters as $c): ?>
            <li class="px-4 py-2 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <!-- Chapter Image -->
                    <?php if (!empty($c['image'])): ?>
                        <img src="<?= $baseUrl . 'uploads/chapters/' . htmlspecialchars($c['image']) ?>" 
                             alt="<?= htmlspecialchars($c['svalue']) ?>" 
                             class="h-10 w-10 object-cover rounded-full">
                    <?php else: ?>
                        <img src="<?= $baseUrl . 'assets/logo_w.png' ?>" 
                             alt="Default Logo" 
                             class="h-10 w-10 object-cover rounded-full">
                    <?php endif; ?>

                    <!-- Chapter Name -->
                    <span class="text-sm font-medium"><?= htmlspecialchars($c['svalue']) ?></span>
                </div>

                <!-- Actions: Edit / Delete -->
                <div class="flex space-x-2">
                    <a href="edit_chapter.php?id=<?= $c['id'] ?>" 
                       class="text-blue-600 hover:underline text-sm">Edit</a>
                    <a href="?delete=1&table=chapter&id=<?= $c['id'] ?>" 
                       onclick="return confirm('Delete chapter?')" 
                       class="text-red-600 hover:underline text-sm">Delete</a>
                </div>
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
    </div>
</main>

<?php include './partials/footer.php'; ?>
