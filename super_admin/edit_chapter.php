<?php
include '../config.php';

include './partials/header.php';
$chapterId = $_GET['id'] ?? null;
if (!$chapterId) die("Invalid chapter ID.");

// Fetch chapter
$stmt = $conn->prepare("SELECT * FROM chapters WHERE id = :id");
$stmt->execute([':id' => $chapterId]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$chapter) die("Chapter not found.");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['svalue'];
    
    // Handle image upload
    $image = $chapter['image'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = '../uploads/chapters/';
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $imageName;
        }
    }

    // Update chapter
    $update = $conn->prepare("UPDATE chapters SET svalue = :name, image = :image WHERE id = :id");
    $update->execute([
        ':name' => $name,
        ':image' => $image,
        ':id' => $chapterId
    ]);

    header("Location: master_manage.php"); // redirect back to list
    exit;
}
?>
<div class="max-w-md mx-auto mt-8 bg-white rounded-lg shadow-lg p-6 flex items-center space-x-4">
    <!-- Chapter Image -->
    <div class="flex-shrink-0">
        <?php if (!empty($chapter['image'])): ?>
            <img src="../uploads/chapters/<?= htmlspecialchars($chapter['image']) ?>" 
                 alt="Chapter Image" 
                 class="w-24 h-24 rounded object-cover border">
        <?php else: ?>
            <div class="w-24 h-24 rounded bg-gray-100 flex items-center justify-center border">
                No Image
            </div>
        <?php endif; ?>
    </div>

    <!-- Input Fields -->
    <div class="flex-1">
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- Chapter Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Chapter Name</label>
                <input type="text" name="svalue" 
                       value="<?= htmlspecialchars($chapter['svalue']) ?>" 
                       required
                       class="mt-1 block w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Chapter Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Change Image</label>
                <input type="file" name="image" accept="image/*" 
                       class="mt-1 block w-full text-sm text-gray-700">
            </div>

            <!-- Update Button -->
            <div>
                <button type="submit" 
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- <h2>Edit Chapter</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Chapter Name:</label>
    <input type="text" name="svalue" value="<?= htmlspecialchars($chapter['svalue']) ?>" required>
    
    <label>Chapter Image:</label>
    <?php if (!empty($chapter['image'])): ?>
        <img src="../uploads/chapters/<?= htmlspecialchars($chapter['image']) ?>" alt="Chapter Image" width="80">
    <?php endif; ?>
    <input type="file" name="image" accept="image/*">
    
    <button type="submit">Update</button>
</form> -->
