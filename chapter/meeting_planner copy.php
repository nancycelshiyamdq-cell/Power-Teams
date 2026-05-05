<?php
include '../config.php';

include './partials/header.php';

// Get members in same powerteam
$powerteam_members = $conn->prepare("SELECT id, name FROM members WHERE powerteam = ?");
$powerteam_members->execute([$powerteam]);

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
    $updateAt = date('Y-m-d H:i:s'); // Now in IST (Chennai time)
    $meeting_id = $_POST['meeting_id'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE meeting_planner SET  updateAt = ?, remarks = ?, status = ? WHERE id = ?");
    $update->execute([$updateAt, $remarks, $status, $meeting_id]);

}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Meeting Planner</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 30px;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background-color: #a6192e;
            color: white;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
        }

        .save-btn {
            background-color: #a6192e;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .save-btn:hover {
            background-color: #8e1426;
        }

        .filter-input {
            width: 100%;
            padding: 4px;
            box-sizing: border-box;
        }

        .bni-button {
            background-color: #a6192e;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .bni-button:hover {
            background-color: #8e1426;
        }

        .center-card {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            font-family: 'Arial', sans-serif;
        }

        .center-card h2 {
            text-align: center;
            color: #a6192e;
            margin-bottom: 20px;
        }

        .center-card label {
            font-weight: 500;
            display: block;
            margin-top: 15px;
            color: #333;
        }

        .center-card input,
        .center-card select,
        .center-card textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        .center-card button {
            background-color: #a6192e;
            color: #fff;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .center-card button:hover {
            background-color: #8e1426;
        }

        .dropdown-section {
            display: none;
        }
    </style>
</head>

<body>

    <div class="center-card">


        <h2>Plan a 121 Meeting</h2>

        <?php if (!empty($success))
            echo "<p style='color: green;'>$success</p>"; ?>

        <form method="POST">
            <label for="meeting_date">Meeting Date</label>
            <input type="date" name="meeting_date" required>

            <label for="meeting_type">Meeting Type</label>
            <select name="meeting_type" id="meeting_type" onchange="toggleDropdowns()" required>
                <option value="">-- Select Type --</option>
                <option value="Powerteam">PowerTeam</option>
                <option value="Global">Global</option>
            </select>

            <div id="powerteam_dropdown" style="display:none;">
                <label>Powerteam Members</label>
                <select name="member_id">
                    <?php foreach ($powerteam_members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="global_dropdown" style="display:none;">
                <label>All Members</label>
                <select name="member_id">
                    <?php foreach ($all_members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label for="agenda">Meeting Agenda</label>
            <textarea name="agenda" rows="4" required></textarea>

            <button type="submit" name="insert_meeting" style="background-color: #a6192e;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.3s ease;">
                Plan Meeting
            </button>
        </form>
    </div>
    <script>
        function toggleDropdowns() {
            var type = document.getElementById('meeting_type').value;
            document.getElementById('powerteam_dropdown').style.display = type === 'Powerteam' ? 'block' : 'none';
            document.getElementById('global_dropdown').style.display = type === 'Global' ? 'block' : 'none';
        }
    </script>

    <?php
    // Fetch planned meetings
    $stmt = $conn->query("
    SELECT mp.*, m.name 
    FROM meeting_planner mp 
    JOIN members m ON mp.member_id = m.id 
    ORDER BY mp.meeting_date DESC
");
    $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>




    <div class="table-wrapper">
            <h2>Planned Meetings</h2>
        <table id="meetingsTable">
            <thead>
                <tr>
                    <th>
                        Planned On<br>
                        <input type="text" class="filter-input" onkeyup="filterTable(0)">
                    </th>
                    <th>
                        To Meet<br>
                        <input type="text" class="filter-input" onkeyup="filterTable(1)">
                    </th>
                    <th>
                        Type<br>
                        <input type="text" class="filter-input" onkeyup="filterTable(2)">
                    </th>
                    <th>
                        Agenda<br>
                        <input type="text" class="filter-input" onkeyup="filterTable(3)">
                    </th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Updated On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("
                SELECT mp.*, m.name 
                FROM meeting_planner mp 
                JOIN members m ON mp.member_id = m.id 
                ORDER BY mp.meeting_date DESC
            ");
                $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($meetings as $row):
                    ?>
                    <tr>
                        <form method="POST">
                            <td><?= $row['meeting_date'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['meeting_type'] ?></td>
                            <td><?= htmlspecialchars($row['agenda']) ?></td>
                            <td>
                                <input type="text" name="remarks" value="<?= htmlspecialchars($row['remarks']) ?>">
                            </td>
                            <td>
                                <select name="status">
                                    <option value="">--</option>
                                    <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed
                                    </option>
                                    <option value="Not happened" <?= $row['status'] === 'Not happened' ? 'selected' : '' ?>>Not
                                        happened</option>
                                </select>
                            </td>
                            <td><?= $row['updateAt'] ?></td>
                            <td>
                                <input type="hidden" name="meeting_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="update_meeting" class="save-btn">Save</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
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
</body>

</html>