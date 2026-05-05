    <?php
include '../config.php';
session_start();

// ✅ Check session
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

$filter_date = $_GET['date'] ?? '';
$filter_member = $_GET['member_id'] ?? '';

// 📥 Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_export_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 📊 Build attendance query
$query = "SELECT 
            a.meeting_date,
            a.present,
            m.name AS member_name,
            r.rvalue AS region_name,
            c.svalue AS chapter_name,
            p.pvalue AS powerteam_name
        FROM attendance a
        JOIN members m ON a.member_id = m.id
        JOIN region r ON m.region = r.id
        JOIN powerteam p ON m.powerteam = p.id
        JOIN chapters c ON m.chapter = c.id
        WHERE m.region = :region AND m.chapter = :chapter AND m.status = 1";

$params = [
    ':region' => $region,
    ':chapter' => $chapter
];

if (!empty($filter_date)) {
    $query .= " AND a.meeting_date = :date";
    $params[':date'] = $filter_date;
}

if (!empty($filter_member)) {
    $query .= " AND a.member_id = :member";
    $params[':member'] = $filter_member;
}

$query .= " ORDER BY a.meeting_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🟢 Generate Excel table
echo "<table border='1'>";
echo "<thead>
        <tr style='background-color:#f2f2f2;'>
            <th>Date</th>
            <th>Member Name</th>
            <th>Region</th>
            <th>Powerteam</th>
            <th>Chapter</th>
            <th>Status</th>
        </tr>
      </thead>";
echo "<tbody>";

if ($rows) {
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['meeting_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['member_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['region_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['powerteam_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['chapter_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['present']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center;'>No records found</td></tr>";
}

echo "</tbody>";
echo "</table>";
?>