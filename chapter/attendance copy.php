<?php
include '../config.php';
include './partials/header.php';

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SELECT * FROM members WHERE powerteam = $powerteam AND region = $region AND chapter = $chapter");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $success_msg = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $meeting_date = $_POST['meeting_date'];

        foreach ($_POST['attendance'] as $member_id => $status) {
            $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE member_id = :member_id AND meeting_date = :meeting_date");
            $check_stmt->execute([
                ':member_id' => $member_id,
                ':meeting_date' => $meeting_date
            ]);

            if ($check_stmt->rowCount() > 0) {
                $update_stmt = $conn->prepare("UPDATE attendance SET present = :status WHERE member_id = :member_id AND meeting_date = :meeting_date");
                $update_stmt->execute([
                    ':status' => $status,
                    ':member_id' => $member_id,
                    ':meeting_date' => $meeting_date
                ]);
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO attendance (member_id, meeting_date, present) VALUES (:member_id, :meeting_date, :status)");
                $insert_stmt->execute([
                    ':member_id' => $member_id,
                    ':meeting_date' => $meeting_date,
                    ':status' => $status
                ]);
            }
        }

        $success_msg = "✅ Attendance recorded/updated successfully!";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">

    <main class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl">
            <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2 text-gray-800">
                <span class="material-icons text-green-500">check_circle</span> Mark Attendance
            </h2>

            <form method="POST" class="space-y-6">

                <!-- ✅ Success message -->
                <?php if (!empty($success_msg)): ?>
                    <div id="successMsg" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <?= $success_msg ?>
                    </div>
                <?php endif; ?>

                <!-- Date -->
                <div>
                    <label for="meeting_date" class="font-medium text-gray-700">Meeting Date</label>
                    <input type="date" id="meeting_date" name="meeting_date" required
                           class="mt-1 border border-gray-300 rounded-md p-2 w-full focus:ring focus:ring-blue-200">
                </div>

                <!-- Select All Controls -->
                <div class="flex justify-between bg-gray-50 p-3 rounded-md border mb-3">
                    <div class="font-medium text-gray-700">Select All:</div>
                    <div class="flex gap-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="select_all" value="Present" class="select-all text-green-500 focus:ring-green-400">
                            <span>Present</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="select_all" value="Absent" class="select-all text-red-500 focus:ring-red-400">
                            <span>Absent</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="select_all" value="Late" class="select-all text-yellow-500 focus:ring-yellow-400">
                            <span>Late</span>
                        </label>
                        <button type="button" class="text-sm text-gray-500 underline" id="clear-selection">Clear</button>
                    </div>
                </div>

                <!-- Member Attendance Rows -->
                <div class="space-y-4">
                    <?php foreach ($members as $row): ?>
                        <div class="border-b pb-2">
                            <label class="block font-medium text-gray-800 mb-2"><?= htmlspecialchars($row['name']); ?></label>
                            <div class="flex gap-6">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="attendance[<?= $row['id']; ?>]" value="Present"
                                           class="radio text-green-500 focus:ring-green-400" required>
                                    <span>Present</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="attendance[<?= $row['id']; ?>]" value="Absent"
                                           class="radio text-red-500 focus:ring-red-400">
                                    <span>Absent</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="attendance[<?= $row['id']; ?>]" value="Late"
                                           class="radio text-yellow-500 focus:ring-yellow-400">
                                    <span>Late</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-center mt-6">
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-md flex items-center gap-2 transition">
                        <span class="material-icons">done</span> Submit Attendance
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // ✅ Handle "Select All" functionality
        document.querySelectorAll('.select-all').forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedValue = this.value;
                document.querySelectorAll('.radio').forEach(input => {
                    if (input.value === selectedValue) {
                        input.checked = true;
                    }
                });
            });
        });

        // ✅ Clear all selections
        document.getElementById('clear-selection').addEventListener('click', () => {
            document.querySelectorAll('.radio').forEach(input => input.checked = false);
            document.querySelectorAll('.select-all').forEach(r => r.checked = false);
        });

        // ✅ Auto-hide success message after 4 seconds
        setTimeout(() => {
            const msg = document.getElementById('successMsg');
            if (msg) msg.style.display = 'none';
        }, 4000);
    </script>

</body>
</html>
