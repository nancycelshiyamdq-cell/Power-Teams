<?php
include '../config.php';
include './partials/header.php';
// session_start();

// ✅ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$regionId = (int)$_SESSION['region'];     // must be integer
$chapterId = (int)$_SESSION['chapter'];   // must be integer
$powerteamId = (int)$_SESSION['powerteam']; // must be integer

$success_msg = '';
$error_msg = '';

// Organisers - only from current powerteam
$stmt = $conn->prepare("
    SELECT 
        m.id, 
        m.name,
        m.mobile,
        m.category,
        r.rvalue AS region_name,
        p.pvalue AS powerteam_name,
        c.svalue AS chapter_name
    FROM members m
    LEFT JOIN region r ON m.region = r.id
    LEFT JOIN powerteam p ON m.powerteam = p.id
    LEFT JOIN chapters c ON m.chapter = c.id
    WHERE m.region = :regionId AND m.chapter = :chapterId AND m.powerteam = :powerteamId AND status = 1
");
$stmt->execute([
    ':regionId'  => $regionId,
    ':chapterId' => $chapterId,
    ':powerteamId' => $powerteamId
]);

$organisers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// All members from chapter for invitees - from all powerteams
$stmt_all = $conn->prepare("
    SELECT 
        m.id, 
        m.name,
        m.mobile,
        m.category,
        r.rvalue AS region_name,
        p.pvalue AS powerteam_name,
        c.svalue AS chapter_name
    FROM members m
    LEFT JOIN region r ON m.region = r.id
    LEFT JOIN powerteam p ON m.powerteam = p.id
    LEFT JOIN chapters c ON m.chapter = c.id
    WHERE m.region = :regionId AND m.chapter = :chapterId AND status = 1
    ORDER BY m.name ASC
");
$stmt_all->execute([
    ':regionId'  => $regionId,
    ':chapterId' => $chapterId
]);

$members = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

// $members = $conn->query("SELECT id, name FROM members ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organiser_id = $_POST['organiser_id'] ?? '';
    $invitees = $_POST['members'] ?? [];
    $company_name = trim($_POST['company_name']);
    $industry = trim($_POST['industry']);
    $location = trim($_POST['location']);
    $date = $_POST['date'];

    if (!$organiser_id || count($invitees) === 0 || $company_name === '' || $industry === '' || $location === '' || $date === '') {
        $error_msg = "All fields are required, and at least one invitee must be selected.";
    } else {
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("INSERT INTO power_dates (organiser_id, company_name, industry, location, date, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$organiser_id, $company_name, $industry, $location, $date, null]);
            $power_date_id = $conn->lastInsertId();

            $stmt_member = $conn->prepare("INSERT INTO power_date_members (power_date_id, member_id) VALUES (?, ?)");
            foreach ($invitees as $member_id) {
                $stmt_member->execute([$power_date_id, $member_id]);
            }

            $conn->commit();
            $success_msg = "Power Date added successfully!";
        } catch (Exception $e) {
            $conn->rollBack();
            $error_msg = "Error adding Power Date: " . $e->getMessage();
        }
    }
}
?>

<style>
    :root { --bni-red: #a6192e; }
    .bni-input { border-color: var(--bni-red); }
    .bni-input:focus { border-color: var(--bni-red); box-shadow: 0 0 0 1px var(--bni-red); }
    .bni-button { background-color: var(--bni-red); }
    .bni-button:hover { background-color: #851423; }
</style>

<main class="p-4 sm:p-6">
    <div class="max-w-4xl mx-auto">
    <h2 class="text-3xl font-bold text-[var(--bni-red)] mb-6 text-center">Add Power Date</h2>

    <?php if ($success_msg): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= $success_msg ?></div>
    <?php elseif ($error_msg): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded-xl shadow-md space-y-4">
        <div>
            <label for="organiser_id" class="block text-sm font-semibold mb-1">Organiser</label>
            <select name="organiser_id" id="organiser_id" required class="bni-input w-full rounded-md border px-4 py-2">
                <option value="">Select Organiser</option>
                <?php foreach ($organisers as $member): ?>
                    <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['powerteam_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

<div>
            <label class="block text-sm font-semibold mb-1">Invite Members</label>
            <input type="text" id="memberSearch" placeholder="Search members..." autocomplete="off"
                class="bni-input w-full rounded-md border px-4 py-2" />
            <div id="memberDropdown" class="hidden border border-gray-300 rounded-md bg-white shadow-md max-h-48 overflow-y-auto mt-1">
                <?php foreach ($members as $member): ?>
                    <div class="member-option px-4 py-2 cursor-pointer hover:bg-red-50 text-sm"
                        data-id="<?= $member['id'] ?>"
                        data-name="<?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['powerteam_name']) ?>)">
                        <?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['powerteam_name']) ?>)
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Selected members tags -->
            <!-- <div id="selectedMembers" class="flex flex-wrap gap-2 mt-2"></div> -->
            <!-- Hidden inputs for form submission -->
            <div id="hiddenInputs"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="company_name" class="block text-sm font-semibold mb-1">Company Name</label>
                <input type="text" name="company_name" id="company_name" required class="bni-input w-full rounded-md border px-4 py-2" />
            </div>
            <div>
                <label for="industry" class="block text-sm font-semibold mb-1">Industry</label>
                <input type="text" name="industry" id="industry" required class="bni-input w-full rounded-md border px-4 py-2" />
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="location" class="block text-sm font-semibold mb-1">Location</label>
                <input type="text" name="location" id="location" required class="bni-input w-full rounded-md border px-4 py-2" />
            </div>
            <div>
                <label for="date" class="block text-sm font-semibold mb-1">Date</label>
                <input type="date" name="date" id="date" required class="bni-input w-full rounded-md border px-4 py-2" />
            </div>
        </div>

        <div class="pt-4" style="display: flex; justify-content: center; margin-top: 3%;">
            <button type="submit" class="bni-button text-white px-6 py-2 rounded-md font-semibold">Add Power Date</button>
        </div>
    </form>
    </div>
</main>
<script>
const searchInput = document.getElementById('memberSearch');
const dropdown = document.getElementById('memberDropdown');
const hiddenInputs = document.getElementById('hiddenInputs');
let selectedId = null;

searchInput.addEventListener('focus', function () {
    dropdown.classList.remove('hidden');
});

searchInput.addEventListener('keyup', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.member-option').forEach(function (option) {
        option.style.display = option.dataset.name.toLowerCase().includes(query) ? '' : 'none';
    });
});

document.querySelectorAll('.member-option').forEach(function (option) {
    option.addEventListener('click', function () {
        const id = this.dataset.id;
        const name = this.dataset.name;

        // Clear previous selection
        hiddenInputs.innerHTML = '';

        // Show name inside the search box
        searchInput.value = name;

        // Add hidden input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'members[]';
        input.value = id;
        hiddenInputs.appendChild(input);

        selectedId = id;

        // Hide dropdown
        dropdown.classList.add('hidden');
    });
});

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>

<?php include './partials/footer.php'; ?>
