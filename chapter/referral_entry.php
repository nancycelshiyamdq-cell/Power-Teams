<?php
include '../config.php';
include './partials/header.php';
// session_start();

$success_msg = "";
// 1️⃣ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

$stmt = $conn->prepare("
    SELECT id, name FROM members 
    WHERE region = :region 
      AND chapter = :chapter 
      AND powerteam = :powerteam 
      AND status = 1
    ORDER BY name ASC
");
$stmt->execute([
    ':region'    => $region,
    ':chapter'   => $chapter,
    ':powerteam' => $powerteam,
]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST['member_id'];
    $company = $_POST['company_name'];
    $contact_name = !empty($_POST['contact_name']) ? $_POST['contact_name'] : null;
    $email = !empty($_POST['contact_email']) ? $_POST['contact_email'] : null;
    $phone = !empty($_POST['contact_phone']) ? $_POST['contact_phone'] : null;
    $referral_type = $_POST['referral_type'];
    $referred_on = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO referrals 
        (member_id, company_name, contact_name, contact_email, contact_phone, referral_type, referred_on)
        VALUES (:member_id, :company, :contact_name, :contact_email, :contact_phone, :referral_type, :referred_on)");

    $stmt->execute([
        ':member_id' => $member_id,
        ':company' => $company,
        ':contact_name' => $contact_name,
        ':contact_email' => $email,
        ':contact_phone' => $phone,
        ':referral_type'  => $referral_type,
        ':referred_on' => $referred_on,
    ]);

    $success_msg = "<p class='text-green-600 font-semibold mb-4'>Referral recorded!</p>";
}
?>

<main class="p-4 sm:p-6">
    <div class="bg-white shadow-lg rounded-lg p-4 sm:p-8 w-full max-w-2xl mx-auto">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add Specific Asks & Gives</h2>
            <?= $success_msg ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label for="member" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                    <select name="member_id" id="member" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select Member --</option>
                        <?php foreach ($members as $row) { 
                            // Get readable names
                            $region_name = $conn->query("SELECT rvalue FROM region WHERE id = " . (int)$row['region'])->fetchColumn();
                            $powerteam_name = $conn->query("SELECT pvalue FROM powerteam WHERE id = " . (int)$row['powerteam'])->fetchColumn();
                            $chapter_name = $conn->query("SELECT svalue FROM chapters WHERE id = " . (int)$row['chapter'])->fetchColumn();
                        ?>
                            <option value="<?= $row['id']; ?>">
                                <?= htmlspecialchars($row['name'] ); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label for="referral_type" class="block text-sm font-medium text-gray-700 mb-1">Referral Type</label>
                    <select name="referral_type" id="referral_type" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="specific Ask">Specific Ask</option>
                        <option value="specific Give">Specific Give</option>
                    </select>
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input type="text" name="company_name" id="company" placeholder="Company Name" required
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

<?php include './partials/footer.php'; ?>
