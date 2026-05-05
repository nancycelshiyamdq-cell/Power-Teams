<?php
include '../db.php';

include './partials/header.php';
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

$filter_date = $_GET['date'] ?? '';
$filter_member = $_GET['member_id'] ?? '';

$query = "SELECT a.*, m.name FROM attendance a 
          JOIN members m ON a.member_id = m.id 
          WHERE powerteam= $powerteam AND region = $region AND chapter =$chapter";

if ($filter_date) $query .= " AND a.meeting_date = '$filter_date'";
if ($filter_member) $query .= " AND a.member_id = $filter_member";

$query .= " ORDER BY a.meeting_date DESC";

$result = mysqli_query($conn, $query);
$members = mysqli_query($conn, "SELECT id, name FROM members  where powerteam= $powerteam AND region = $region AND chapter =$chapter");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Attendance Records</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts + Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <link rel="stylesheet" href="./css/sidebar.css">



</head>

<body>





    <main class="p-4 md:p-6 lg:p-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <header class="mb-6">
                <h2 class="text-2xl font-semibold flex items-center text-gray-800">
                    <span class="material-icons mr-2">list_alt</span>
                    Attendance Records
                </h2>
            </header>

            <div class="bg-white p-4 md:p-6 rounded-lg shadow">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="flex flex-col">
                        <label for="date" class="mb-1 text-sm font-medium text-gray-700">Filter by Date:</label>
                        <input type="date" id="date" name="date" value="<?= $filter_date ?>"
                            class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex flex-col">
                        <label for="member" class="mb-1 text-sm font-medium text-gray-700">Filter by Member:</label>
                        <select name="member_id" id="member"
                            class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Members</option>
                            <?php while ($m = mysqli_fetch_assoc($members)) { ?>
                                <option value="<?= $m['id'] ?>" <?= $filter_member == $m['id'] ? 'selected' : '' ?>>
                                    <?= $m['name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="flex items-end space-x-3 md:col-span-2">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                            <span class="material-icons mr-1">filter_list</span> Apply
                        </button>
                        <a href="attendance_list.php"
                            class="text-sm text-blue-600 hover:underline hover:text-blue-800">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border-b">Date</th>
                                <th class="px-4 py-2 border-b">Member Name</th>
                                <th class="px-4 py-2 border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b"><?= $row['meeting_date'] ?></td>
                                    <td class="px-4 py-2 border-b"><?= $row['name'] ?></td>
                                    <td class="px-4 py-2 border-b"><?= $row['present'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>



</body>

</html>