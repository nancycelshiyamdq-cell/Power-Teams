<?php
include '../config.php';
include './partials/header.php'; 
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$regionName = $_SESSION['region'];
$chapterName = $_SESSION['chapter'];
$powerteamName = $_SESSION['powerteam'];
// $name = $_SESSION['id'];

$success_msg = "";

// Get all members
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
    ':regionId'  => $regionName,
    ':chapterId' => $chapterName,
    ':powerteamId' => $powerteamName
]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST['member_id'];
    $company = !empty($_POST['company_name']) ? $_POST['company_name'] : null;
    $contact_name = !empty($_POST['contact_name']) ? $_POST['contact_name'] : null;
    $email = !empty($_POST['contact_email']) ? $_POST['contact_email'] : null;
    $phone = !empty($_POST['contact_phone']) ? $_POST['contact_phone'] : null;
    $referral_type = $_POST['referral_type'];
    $referred_on = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO referrals 
        (member_id, company_name, contact_name, contact_email, contact_phone, referral_type, referred_on)
        VALUES (:member_id, :company, :contact_name, :email, :phone, :referral_type, :referred_on)");

    $stmt->execute([
        ':member_id' => $member_id,
        ':company' => $company,
        ':contact_name' => $contact_name,
        ':email' => $email,
        ':phone' => $phone,
        ':referral_type' => $referral_type,
        ':referred_on' => $referred_on
    ]);

    $success_msg = "<p class='text-green-600 font-semibold mb-4'>Referral recorded!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Submit Referral</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body>
<?php ?>

<main class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-2xl">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add Specific Asks & Gives</h2>
        <?= $success_msg ?>
        <form method="POST" class="space-y-5">

            <!-- <div>
                <label for="member" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                <select name="member_id" id="member" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Member --</option>
                    <?php foreach ($members as $row): ?>
                        <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div> -->
           <?php 
// session_start();
$selectedMemberName = $_SESSION['name'] ?? '';  // 👈 get name from session
?>
<div>
    <label for="member" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
    <select name="member_id" id="member" required
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">-- Select Member --</option>
        <?php foreach ($members as $row): ?>
            <option value="<?= $row['id']; ?>" 
                <?= ($row['name'] == $selectedMemberName) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($row['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>



            <div>
                <label for="referral_type" class="block text-sm font-medium text-gray-700 mb-1">Referral Type</label>
                <select name="referral_type" id="referral_type" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Specific Ask">Specific Ask</option>
                    <option value="Specific Give">Specific Give</option>
                </select>
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                <input type="text" name="company_name" id="company" placeholder="Company Name"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <input type="text" name="contact_name" id="contact_name" placeholder="Contact Person"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="contact_email" id="contact_email" placeholder="Email"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="contact_phone" id="contact_phone" placeholder="Phone"
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                    Submit
                </button>
            </div>
        </form>
    </div>
</main>
</body>
</html>
