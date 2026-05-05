<?php
include '../config.php';
include './partials/header.php';
// session_start();

// ✅ Ensure session variables exist
if (!isset($_SESSION['region'], $_SESSION['chapter'], $_SESSION['powerteam'])) {
    die("Session expired. Please log in again.");
}

$region = $_SESSION['region'];
$chapter = $_SESSION['chapter'];
$powerteam = $_SESSION['powerteam'];

// echo $region;
try {
    $stmt = $conn->prepare("
        SELECT 
            m.id,
            m.name,
            m.category,
            m.email,
            m.mobile,
            m.chapter_access,
            m.password
        FROM members m
        WHERE m.region = :region AND m.chapter = :chapter AND m.powerteam = :powerteam AND status = 1
    ");

    // Bind parameters correctly
    $stmt->execute([
        ':region'    => $region,
        ':chapter'   => $chapter,
        ':powerteam' => $powerteam
    ]);

    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $success_msg = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $meeting_date = $_POST['meeting_date'];
$powerteam_id = $powerteam; // session already stores the powerteam id

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
       $insert_stmt = $conn->prepare("INSERT INTO attendance (member_id, meeting_date, present, powerteam_id) VALUES (:member_id, :meeting_date, :status, :powerteam_id)");
$insert_stmt->execute([
    ':member_id' => $member_id,
    ':meeting_date' => $meeting_date,
    ':status' => $status,
    ':powerteam_id' => $powerteam_id
]);
    }
}

        // foreach ($_POST['attendance'] as $member_id => $status) {
        //     $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE member_id = :member_id AND meeting_date = :meeting_date");
        //     $check_stmt->execute([
        //         ':member_id' => $member_id,
        //         ':meeting_date' => $meeting_date
        //     ]);

        //     if ($check_stmt->rowCount() > 0) {
        //         $update_stmt = $conn->prepare("UPDATE attendance SET present = :status WHERE member_id = :member_id AND meeting_date = :meeting_date");
        //         $update_stmt->execute([
        //             ':status' => $status,
        //             ':member_id' => $member_id,
        //             ':meeting_date' => $meeting_date
        //         ]);
        //     } else {
        //         $insert_stmt = $conn->prepare("INSERT INTO attendance (member_id, meeting_date, present) VALUES (:member_id, :meeting_date, :status)");
        //         $insert_stmt->execute([
        //             ':member_id' => $member_id,
        //             ':meeting_date' => $meeting_date,
        //             ':status' => $status
        //         ]);
        //     }
        // }

        $success_msg = "✅ Attendance recorded/updated successfully!";
    }
    // echo json_encode([
    //     'success' => true,
    //     'data' => $members
    // ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

?>

<main class="p-4 sm:p-6">
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 w-full max-w-3xl mx-auto">
            <h2 class="text-2xl font-semibold mb-4 flex items-center gap-2 text-gray-800">
                <span class="material-icons text-green-500">check_circle</span> Mark Attendance
            </h2>

            <!-- ✅ Display Session Info -->
            <!-- <div class="mb-4 bg-gray-50 border rounded-md p-3 text-sm text-gray-700">
                <p><strong>Region:</strong> <?= htmlspecialchars($region) ?></p>
                <p><strong>Chapter:</strong> <?= htmlspecialchars($chapter) ?></p>
                <p><strong>Powerteam:</strong> <?= htmlspecialchars($powerteam) ?></p>
            </div> -->

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
                <div class="flex flex-col sm:flex-row sm:justify-between bg-gray-50 p-3 rounded-md border mb-3 gap-2">
                    <div class="font-medium text-gray-700">Select All:</div>
                    <div class="flex flex-wrap gap-3">
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
                    <?php if (!empty($members)): ?>
                        <?php foreach ($members as $row): ?>
                            <div class="border-b pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <!-- Left: Name & Mobile -->
                                <div>
                                    <label class="block font-medium text-gray-800">
                                        <?= htmlspecialchars($row['name']); ?>
                                        <span class="text-sm text-gray-500">(<?= htmlspecialchars($row['mobile']); ?>)</span>
                                    </label>
                                </div>
                                <!-- Right: Attendance Radios -->
                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendance[<?= $row['id']; ?>]" value="Present" class="radio text-green-500 focus:ring-green-400" required>
                                        <span class="text-sm">Present</span>
                                    </label>
                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendance[<?= $row['id']; ?>]" value="Absent" class="radio text-red-500 focus:ring-red-400">
                                        <span class="text-sm">Absent</span>
                                    </label>
                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendance[<?= $row['id']; ?>]" value="Late" class="radio text-yellow-500 focus:ring-yellow-400">
                                        <span class="text-sm">Late</span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center">No members found for your region, chapter, and powerteam.</p>
                    <?php endif; ?>
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

<?php include './partials/footer.php'; ?>

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
