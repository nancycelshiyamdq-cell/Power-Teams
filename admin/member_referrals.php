<?php
include '../config.php';
include './partials/header.php';

// ✅ Fixed member ID (you can replace this with $_GET['member_id'])
$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 35;


// ✅ Fetch member details + referral counts
$sql = "
    SELECT 
        m.id,
        m.name,
        COALESCE(given.total, 0) AS referrals_given,
        COALESCE(received.total, 0) AS referrals_received
    FROM members m
    LEFT JOIN (
        SELECT member_id, COUNT(*) AS total
        FROM referrals
        WHERE referral_type = 'Specific Ask'
        GROUP BY member_id
    ) AS given ON given.member_id = m.id
    LEFT JOIN (
        SELECT assigned_member, COUNT(*) AS total
        FROM referrals
        WHERE referral_type = 'Specific Ask'
        GROUP BY assigned_member
    ) AS received ON received.assigned_member = m.id
    WHERE m.id = :member_id
";

$stmt = $conn->prepare($sql);
$stmt->execute([':member_id' => $member_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Fetch referral details - Given
$sqlGiven = "
    SELECT r.id, r.contact_name, r.referred_on, m2.name AS to_member
    FROM referrals r
    LEFT JOIN members m2 ON r.assigned_member = m2.id
    WHERE r.member_id = :member_id AND r.referral_type = 'Specific Ask'
    ORDER BY r.referred_on DESC
";
$stmtGiven = $conn->prepare($sqlGiven);
$stmtGiven->execute([':member_id' => $member_id]);
$givenReferrals = $stmtGiven->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch referral details - Received
$sqlReceived = "
    SELECT r.id, r.contact_name, r.referred_on, m1.name AS from_member
    FROM referrals r
    LEFT JOIN members m1 ON r.member_id = m1.id
    WHERE r.assigned_member = :member_id AND r.referral_type = 'Specific Ask'
    ORDER BY r.referred_on DESC
";
$stmtReceived = $conn->prepare($sqlReceived);
$stmtReceived->execute([':member_id' => $member_id]);
$receivedReferrals = $stmtReceived->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="p-4 sm:p-6">
    <div class="max-w-4xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Member Referral Details</h2>

        <?php if ($member): ?>
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div><span class="text-gray-500 block">Member ID</span><span class="font-semibold"><?= $member['id'] ?></span></div>
                    <div><span class="text-gray-500 block">Name</span><span class="font-semibold"><?= htmlspecialchars($member['name']) ?></span></div>
                    <div><span class="text-gray-500 block">Referrals Given</span><span class="font-semibold text-green-600"><?= $member['referrals_given'] ?></span></div>
                    <div><span class="text-gray-500 block">Referrals Received</span><span class="font-semibold text-indigo-600"><?= $member['referrals_received'] ?></span></div>
                </div>
            </div>
        <?php else: ?>
            <p class="text-red-600 text-center">Member not found.</p>
        <?php endif; ?>

        <?php if (count($givenReferrals) > 0): ?>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Referrals Given</h3>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="px-4 py-2 text-left">Contact Name</th>
                                <th class="px-4 py-2 text-left">Given To</th>
                                <th class="px-4 py-2 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($givenReferrals as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['contact_name']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['to_member']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['referred_on']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center">No referrals given.</p>
        <?php endif; ?>

        <?php if (count($receivedReferrals) > 0): ?>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Referrals Received</h3>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="px-4 py-2 text-left">Contact Name</th>
                                <th class="px-4 py-2 text-left">Received From</th>
                                <th class="px-4 py-2 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($receivedReferrals as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['contact_name']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['from_member']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['referred_on']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center">No referrals received.</p>
        <?php endif; ?>
    </div>
</main>

<?php include './partials/footer.php'; ?>
