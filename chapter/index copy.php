<?php
include '../config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['mobile']) && isset($_SESSION['role']) && $_SESSION['role'] === 'chapter') {
  header('Location: dashboard.php');
  exit;
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mobile = $_POST['mobile'] ?? '';
  $password = $_POST['password'] ?? '';
  $region = $_POST['region'] ?? '';
  $chapter = $_POST['chapter'] ?? '';
  $powerteam = $_POST['powerteam'] ?? '';

  if ($mobile && $password && $region && $chapter && $powerteam) {
    $stmt = $conn->prepare("SELECT * FROM members WHERE mobile = ? LIMIT 1");
    $stmt->execute([$mobile]);
    $member = $stmt->fetch();

    if ($member) {
      if (empty($member['chapter_access']) || $member['chapter_access'] != 1) {
        $error = "You don't have chapter access.";
      } else {
        if ($password === $member['password'] || password_verify($password, $member['password'])) {
          $_SESSION['mobile'] = $member['mobile'];
          $_SESSION['region'] = $region;
          $_SESSION['chapter'] = $chapter;
          $_SESSION['powerteam'] = $powerteam;
          $_SESSION['role'] = 'chapter';

          header('Location: dashboard.php');
          exit;
        } else {
          $error = 'Invalid mobile number or password.';
        }
      }
    } else {
      $error = 'Invalid mobile number or password.';
    }
  } else {
    $error = 'Please fill all fields.';
  }
}

// Dropdowns
$regions = $conn->query("SELECT id, rvalue FROM region")->fetchAll();
$chapters = $conn->query("SELECT id, svalue FROM chapters")->fetchAll();
$powerteams = $conn->query("SELECT id, pvalue FROM powerteam")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Power Team Login - BNI</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>

<body class="bg-gray-50 font-['Roboto'] min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-2xl font-semibold text-red-600 text-center mb-6">Power Team Login</h2>

    <?php if (!empty($error)) : ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4">
      <div>
        <label for="mobile" class="block text-gray-700">Mobile Number</label>
        <input type="tel" id="mobile" name="mobile" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div>
        <label for="password" class="block text-gray-700">Password</label>
        <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div>
        <label for="region" class="block text-gray-700">Region</label>
        <select id="region" name="region" required class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="" disabled selected hidden>Select Region</option>
          <?php foreach ($regions as $r): ?>
            <option value="<?= htmlspecialchars($r['id']) ?>">
              <?= htmlspecialchars($r['rvalue']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="chapter" class="block text-gray-700">Chapter</label>
        <select id="chapter" name="chapter" required class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="" disabled selected hidden>Select Chapter</option>
          <?php foreach ($chapters as $c): ?>
            <option value="<?= htmlspecialchars($c['id']) ?>">
              <?= htmlspecialchars($c['svalue']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="powerteam" class="block text-gray-700">Powerteam Name</label>
        <select id="powerteam" name="powerteam" required class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="" disabled selected hidden>Select Powerteam</option>
          <?php foreach ($powerteams as $p): ?>
            <option value="<?= htmlspecialchars($p['id']) ?>">
              <?= htmlspecialchars($p['pvalue']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex flex-col gap-2">
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 rounded">Login</button>
        <a href="../index.php" class="text-sm text-red-500 hover:underline text-center">Back</a>
      </div>
    </form>
  </div>
</body>

</html>
