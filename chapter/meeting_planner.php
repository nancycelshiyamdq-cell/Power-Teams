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

// 2️⃣ Fetch the corresponding IDs from their tables
$regionStmt = $conn->prepare("SELECT id FROM region WHERE rvalue = :regionName");
$regionStmt->execute(['regionName' => $regionName]);
$regionId = $regionStmt->fetchColumn();

$chapterStmt = $conn->prepare("SELECT id FROM chapters WHERE svalue = :chapterName");
$chapterStmt->execute(['chapterName' => $chapterName]);
$chapterId = $chapterStmt->fetchColumn();

$powerteamStmt = $conn->prepare("SELECT id FROM powerteam WHERE pvalue = :powerteamName");
$powerteamStmt->execute(['powerteamName' => $powerteamName]);
$powerteamId = $powerteamStmt->fetchColumn();

// Get members in same powerteam
$powerteam_members = $conn->prepare("SELECT id, name FROM members WHERE powerteam = ?");
$powerteam_members->execute([$powerteamId]);

// Get all members
$all_members = $conn->query("SELECT id, name FROM members")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert_meeting'])) {
    $meeting_date = $_POST['meeting_date'];
    $meeting_type = $_POST['meeting_type'];
    $member_id = $_POST['member_id'];
    $agenda = $_POST['agenda'];

    $stmt = $conn->prepare("INSERT INTO meeting_planner (meeting_date, member_id, meeting_type, agenda) VALUES (?, ?, ?, ?)");
    $stmt->execute([$meeting_date, $member_id, $meeting_type, $agenda]);
    $success = "Meeting planned successfully.";
}

// Handle remarks/status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_meeting'])) {
    date_default_timezone_set('Asia/Kolkata');
    $updateAt = date('Y-m-d H:i:s');
    $meeting_id = $_POST['meeting_id'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE meeting_planner SET updateAt = ?, remarks = ?, status = ? WHERE id = ?");
    $update->execute([$updateAt, $remarks, $status, $meeting_id]);
}
?>

<main class="p-4 sm:p-6">
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 w-full max-w-2xl mx-auto mb-6">
        <h2 class="text-xl font-semibold text-red-700 mb-4 text-center">Plan a 121 Meeting</h2>
        <?php if (!empty($success)): ?>
            <p class="text-green-600 text-sm mb-4"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Meeting Date</label>
                <input type="date" name="meeting_date" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Meeting Type</label>
                <select name="meeting_type" id="meeting_type" onchange="toggleDropdowns()" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">-- Select Type --</option>
                    <option value="Powerteam">PowerTeam</option>
                    <option value="Global">Global</option>
                </select>
            </div>
            <div id="powerteam_dropdown" style="display:none;">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Powerteam Members</label>
                <select name="member_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <?php foreach ($powerteam_members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="global_dropdown" style="display:none;">
                <label class="block text-sm font-semibold text-gray-700 mb-1">All Members</label>
                <select name="member_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <?php foreach ($all_members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Meeting Agenda</label>
                <textarea name="agenda" rows="4" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
            </div>
            <button type="submit" name="insert_meeting" class="w-full bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-4 rounded transition">Plan Meeting</button>
        </form>
    </div>

<?php
$stmt = $conn->query("
    SELECT mp.*, m.name 
    FROM meeting_planner mp 
    JOIN members m ON mp.member_id = m.id 
    ORDER BY mp.meeting_date DESC
");
$meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="overflow-x-auto bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold text-red-700 mb-4">Planned Meetings</h2>
        <table id="meetingsTable" class="min-w-full text-sm border-collapse">
            <thead class="bg-red-700 text-white">
                <tr>
                    <th class="px-3 py-2 text-left">Planned On<br><input type="text" class="filter-input mt-1 w-full text-gray-800 border rounded px-1 py-0.5 text-xs" onkeyup="filterTable(0)"></th>
                    <th class="px-3 py-2 text-left">To Meet<br><input type="text" class="filter-input mt-1 w-full text-gray-800 border rounded px-1 py-0.5 text-xs" onkeyup="filterTable(1)"></th>
                    <th class="px-3 py-2 text-left">Type<br><input type="text" class="filter-input mt-1 w-full text-gray-800 border rounded px-1 py-0.5 text-xs" onkeyup="filterTable(2)"></th>
                    <th class="px-3 py-2 text-left">Agenda<br><input type="text" class="filter-input mt-1 w-full text-gray-800 border rounded px-1 py-0.5 text-xs" onkeyup="filterTable(3)"></th>
                    <th class="px-3 py-2 text-left">Remarks</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Updated On</th>
                    <th class="px-3 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($meetings as $row): ?>
                <tr class="hover:bg-gray-50">
                    <form method="POST">
                        <td class="px-3 py-2"><?= htmlspecialchars($row['meeting_date']) ?></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($row['meeting_type']) ?></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($row['agenda']) ?></td>
                        <td class="px-3 py-2"><input type="text" name="remarks" value="<?= htmlspecialchars($row['remarks']) ?>" class="border border-gray-300 rounded px-2 py-1 text-xs w-full"></td>
                        <td class="px-3 py-2">
                            <select name="status" class="border border-gray-300 rounded px-2 py-1 text-xs w-full">
                                <option value="">--</option>
                                <option value="Completed" <?= $row['status']==='Completed'?'selected':'' ?>>Completed</option>
                                <option value="Not happened" <?= $row['status']==='Not happened'?'selected':'' ?>>Not happened</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-500"><?= htmlspecialchars($row['updateAt']) ?></td>
                        <td class="px-3 py-2">
                            <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="update_meeting" class="bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-1 rounded">Save</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include './partials/footer.php'; ?>

<script>
function toggleDropdowns() {
    var type = document.getElementById('meeting_type').value;
    document.getElementById('powerteam_dropdown').style.display = type === 'Powerteam' ? 'block' : 'none';
    document.getElementById('global_dropdown').style.display = type === 'Global' ? 'block' : 'none';
}
function filterTable(colIndex) {
    const inputFields = document.querySelectorAll(".filter-input");
    const table = document.getElementById("meetingsTable");
    const tr = table.getElementsByTagName("tr");
    for (let i = 1; i < tr.length; i++) {
        let showRow = true;
        for (let j = 0; j < inputFields.length; j++) {
            const td = tr[i].getElementsByTagName("td")[j];
            const filter = inputFields[j].value.toLowerCase();
            if (td && !td.innerText.toLowerCase().includes(filter)) {
                showRow = false;
                break;
            }
        }
        tr[i].style.display = showRow ? "" : "none";
    }
}
</script>
