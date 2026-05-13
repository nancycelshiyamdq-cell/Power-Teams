<?php
include '../config.php';

include './partials/header.php';
// session_start();

// ✅ Ensure session variables exist
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

// ------------------- Handle POST Form Submission -------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $category = $_POST['category'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $region_post = $_POST['region'];
    $chapter_post = $_POST['chapter'];
    $powerteam_post = $_POST['powerteam'];

    if ($id) {
        // Update existing member
        $stmt = $conn->prepare("UPDATE members SET name = ?, category = ?, email = ?, mobile = ?, region = ?, chapter = ?, powerteam = ? WHERE id = ?");
        $stmt->execute([$name, $category, $email, $mobile, $region_post, $chapter_post, $powerteam_post, $id]);
    } else {
        // Create new member with password
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO members (name, category, email, mobile, region, chapter, powerteam, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $email, $mobile, $region_post, $chapter_post, $powerteam_post, $password]);
    }
$success_msg = "<p class='text-green-600 font-semibold mb-4'>Members Success!</p>";
    // header("Location: members.php");
    // exit;
}

// ------------------- Handle Delete -------------------
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id]);
    // header("Location: members.php");
    // header("Location: " . $_SERVER['REQUEST_URI']);
    // exit;    
$success_msg = "<p class='text-green-600 font-semibold mb-4'>Members Success!</p>";
}

// ------------------- Edit Data -------------------
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ------------------- Fetch Reference Data -------------------
$regions = $conn->query("SELECT id, rvalue FROM region")->fetchAll(PDO::FETCH_ASSOC);
$chapters = $conn->query("SELECT id, svalue FROM chapters")->fetchAll(PDO::FETCH_ASSOC);
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll(PDO::FETCH_ASSOC);

// ------------------- Pagination Setup -------------------
$items_per_page = 10; // Number of members per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1
$offset = ($current_page - 1) * $items_per_page;

// ------------------- Count Total Members -------------------
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM members m
    WHERE m.region = :region AND m.chapter = :chapter AND m.powerteam = :powerteam AND m.status = 1
");
$count_stmt->execute([
    ':region'    => $region,
    ':chapter'   => $chapter,
    ':powerteam' => $powerteam
]);
$total_members = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_members / $items_per_page);

// ------------------- Fetch Members Based on Session with Pagination -------------------
$stmt = $conn->prepare("
    SELECT
        m.*,
        m.name AS member_name,
        r.rvalue AS region_name,
        c.svalue AS chapter_name,
        p.pvalue AS powerteam_name
    FROM members m
    LEFT JOIN region r ON m.region = r.id
    LEFT JOIN chapters c ON m.chapter = c.id
    LEFT JOIN powerteam p ON m.powerteam = p.id
    WHERE m.region = :region AND m.chapter = :chapter AND m.powerteam = :powerteam AND m.status = 1
    ORDER BY m.id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':region', $region, PDO::PARAM_INT);
$stmt->bindValue(':chapter', $chapter, PDO::PARAM_INT);
$stmt->bindValue(':powerteam', $powerteam, PDO::PARAM_INT);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="p-4 md:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
    <!-- Form Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4"><?= $edit_data ? 'Edit Member' : 'Add Member'; ?></h2>
        <form method="POST" action="members.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id']; ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="<?= $edit_data['name'] ?? ''; ?>" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <input type="text" name="category" value="<?= $edit_data['category'] ?? ''; ?>" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="<?= $edit_data['email'] ?? ''; ?>" required class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Mobile</label>
                <input type="text" name="mobile" value="<?= $edit_data['mobile'] ?? ''; ?>" required class="w-full border rounded px-3 py-2">
            </div>

            <?php if (!$edit_data): ?>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="text" name="password" required class="w-full border rounded px-3 py-2">
                </div>
            <?php endif; ?>

            <!-- Region & Chapter Fixed from Session -->
            <div>
                <label class="block text-sm font-medium mb-1">Region</label>
                <select name="region" class="w-full border rounded px-3 py-2" required disabled>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= $r['id']; ?>" <?= ($r['id'] == $region) ? 'selected' : ''; ?>>
                            <?= $r['rvalue']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="region" value="<?= $region; ?>">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Chapter</label>
                <select name="chapter" class="w-full border rounded px-3 py-2" required disabled>
                    <?php foreach ($chapters as $c): ?>
                        <option value="<?= $c['id']; ?>" <?= ($c['id'] == $chapter) ? 'selected' : ''; ?>>
                            <?= $c['svalue']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="chapter" value="<?= $chapter; ?>">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Powerteam</label>
                <select name="powerteam" class="w-full border rounded px-3 py-2" required>
                    <option value="" disabled <?= !$edit_data ? 'selected' : '' ?>>Select Powerteam</option>
                    <?php foreach ($powerteams as $p): ?>
                        <option value="<?= $p['id']; ?>" <?= ($edit_data && $edit_data['powerteam'] == $p['id']) ? 'selected' : ''; ?>>
                            <?= $p['pvalue']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2 flex gap-4 mt-4">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    <?= $edit_data ? 'Update Member' : 'Add Member'; ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="members.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Members Table -->
    <div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Members List</h2>
        <table class="min-w-full table-auto text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Name</th>
                    <th class="px-4 py-3 text-left font-semibold">Category</th>
                    <th class="px-4 py-3 text-left font-semibold">Email</th>
                    <th class="px-4 py-3 text-left font-semibold">Mobile</th>
                    <th class="px-4 py-3 text-left font-semibold">Region</th>
                    <th class="px-4 py-3 text-left font-semibold">Chapter</th>
                    <th class="px-4 py-3 text-left font-semibold">Powerteam</th>
                    <th class="px-4 py-3 text-left font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $index => $row): ?>
                    <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-green-50 transition">
                        <td class="px-4 py-3 text-gray-800"><?= $row['name']; ?></td>
                        <td class="px-4 py-3 text-gray-700"><?= $row['category']; ?></td>
                        <td class="px-4 py-3 text-gray-700"><?= $row['email']; ?></td>
                        <td class="px-4 py-3 text-gray-700"><?= $row['mobile']; ?></td>
                        <td class="px-4 py-3 text-gray-700"><?= $row['region_name']; ?></td>
                        <td class="px-4 py-3 text-gray-700"><?= $row['chapter_name']; ?></td>
                        <td class="px-4 py-3 text-gray-700"><?= $row['powerteam_name']; ?></td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="members.php?edit=<?= $row['id']; ?>" class="inline-block px-3 py-1 bg-blue-500 text-white text-xs font-medium rounded-full hover:bg-blue-600 transition">Edit</a>
                                <a href="members.php?delete=<?= $row['id']; ?>" onclick="return confirm('Delete this member?');" class="inline-block px-3 py-1 bg-red-500 text-white text-xs font-medium rounded-full hover:bg-red-600 transition">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Page Info -->
            <div class="text-sm text-gray-600">
                Showing <?= min($offset + 1, $total_members) ?> to <?= min($offset + $items_per_page, $total_members) ?> of <?= $total_members ?> members
            </div>

            <!-- Pagination Buttons -->
            <div class="flex items-center gap-2">
                <!-- First Page -->
                <?php if ($current_page > 1): ?>
                    <a href="?page=1" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                <?php endif; ?>

                <!-- Previous Page -->
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?= $current_page - 1 ?>" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                        <i class="fas fa-angle-left"></i> Previous
                    </a>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);

                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-2 <?= $i == $current_page ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> rounded transition">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Next Page -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $current_page + 1 ?>" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                        Next <i class="fas fa-angle-right"></i>
                    </a>
                <?php endif; ?>

                <!-- Last Page -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?= $total_pages ?>" class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    </div>
</main>

<?php include './partials/footer.php'; ?>
