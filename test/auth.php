<?php



function validate($role, $redirect = null)
{
    $userRole = $_SESSION['role'] ?? '';

    if (isset($role) && $userRole !==  $role) {
        header('Location: ' . $redirect ?? 'index.php');
        exit;
    }
}
