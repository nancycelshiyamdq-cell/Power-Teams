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

<!DOCTYPE html>
<html>
<head>
    <title>Member Referral Details</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { text-align:center; }
        table { border-collapse: collapse; width: 80%; margin: 20px auto; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; }
        th { background-color: #f4f4f4; }
        .section-title { margin-top: 30px; text-align:center; }
    </style>
</head>
<body>

<h2>Member Referral Details</h2>

<?php if ($member): ?>
    <table>
        <tr>
            <th>Member ID</th>
            <td><?= $member['id'] ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?= htmlspecialchars($member['name']) ?></td>
        </tr>
        <tr>
            <th>Referrals Given</th>
            <td><?= $member['referrals_given'] ?></td>
        </tr>
        <tr>
            <th>Referrals Received</th>
            <td><?= $member['referrals_received'] ?></td>
        </tr>
    </table>
<?php else: ?>
    <p style="text-align:center;color:red;">Member not found.</p>
<?php endif; ?>

<?php if (count($givenReferrals) > 0): ?>
    <h3 class="section-title">Referrals Given</h3>
    <table>
        <thead>
            <tr>
                <!-- <th>ID</th> -->
                <th>contact Name</th>
                <th>Given To</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($givenReferrals as $row): ?>
                <tr>
                    <!-- <td><?= $row['id'] ?></td> -->
                    <td><?= htmlspecialchars($row['contact_name']) ?></td>
                    <td><?= htmlspecialchars($row['to_member']) ?></td>
                    <td><?= $row['referred_on'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align:center;">No referrals given.</p>
<?php endif; ?>

<?php if (count($receivedReferrals) > 0): ?>
    <h3 class="section-title">Referrals Received</h3>
    <table>
        <thead>
            <tr>
                <!-- <th>ID</th> -->
                <th>Contact Name</th>
                <th>Received From</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receivedReferrals as $row): ?>
                <tr>
                    <!-- <td><?= $row['id'] ?></td> -->
                    <td><?= htmlspecialchars($row['contact_name']) ?></td>
                    <td><?= htmlspecialchars($row['from_member']) ?></td>
                    <td><?= $row['referred_on'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align:center;">No referrals received.</p>
<?php endif; ?>

</body>
</html>
