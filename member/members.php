<?php
include '../config.php';
include './partials/header.php';

// 1️⃣ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}


$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];
// echo $regionName;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
    $category = $_POST['category'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $region = $_POST['region'];
    $chapter = $_POST['chapter'];
    $powerteam = $_POST['powerteam'];

    if ($id) {
        // Update existing member (no password update here)
        $stmt = $conn->prepare("UPDATE members SET name = ?, category = ?, email = ?, mobile = ?, region = ?, chapter = ?, powerteam = ? WHERE id = ?");
        $stmt->execute([$name, $category, $email, $mobile, $region, $chapter, $powerteam, $id]);
    } else {
        // Create new member with password
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO members (name, category, email, mobile, region, chapter, powerteam, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $email, $mobile, $region, $chapter, $powerteam, $password]);
    }

    header("Location: members.php");
    exit;
}


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: members.php");
    exit;
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

$regions = $conn->query("SELECT rvalue FROM region")->fetchAll();
$chapters = $conn->query("SELECT id, svalue FROM chapters")->fetchAll();
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll();
$stmt = $conn->prepare("
    SELECT 
        m.*, 
        r.rvalue AS region_name, 
        c.svalue AS chapter_name, 
        p.pvalue AS powerteam_name 
    FROM members m
    LEFT JOIN region r ON m.region = r.id
    LEFT JOIN chapters c ON m.chapter = c.id
    LEFT JOIN powerteam p ON m.powerteam = p.id
    WHERE m.region = :regionId 
      AND m.chapter = :chapterId 
      AND m.powerteam = :powerteamId
      AND m.status = 1
    ORDER BY m.id DESC
");

$stmt->execute([
    ':regionId' => $regionName,
    ':chapterId' => $chapterName,
    ':powerteamId' => $powerteamName
]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="p-4 sm:p-6">
        <!-- Members Table -->
        <div class="bg-white shadow-xl rounded-xl p-4 sm:p-6 md:p-8 overflow-x-auto">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6">Members List</h2>

            <!-- Responsive Table Wrapper -->
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class=" from-teal-500 to-emerald-500 text-dark bg-gray-100 ">
                            <tr >
                                <th scope="col" class="px-4 py-3 font-semibold ">Name</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Category</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Email</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Mobile</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Region</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Chapter</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Powerteam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($members as $index => $row): ?>
                                <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-green-50 transition">
                                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($row['name']); ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($row['category']); ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($row['email']); ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($row['mobile']); ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?= $row['region_name'] ?? '-'; ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?= $row['chapter_name'] ?? '-'; ?></td>
                                    <td class="px-4 py-3 text-gray-700"><?= $row['powerteam_name'] ?? '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

<?php include './partials/footer.php'; ?>