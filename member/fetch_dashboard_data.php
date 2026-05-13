<?php
include '../config.php';

include './partials/header.php';

$type  = $_GET['type'] ?? '';
$month = $_GET['month'] ?? null;
$year  = $_GET['year'] ?? null;

$regionId = $_SESSION['region'];
$chapterId = $_SESSION['chapter'];
$powerteamId = $_SESSION['powerteam'];

$where = "WHERE m.region = :regionId AND m.chapter = :chapterId AND m.powerteam = :powerteamId";
$params = [':regionId' => $regionId, ':chapterId' => $chapterId, ':powerteamId' => $powerteamId];

if ($month) { $where .= " AND MONTH(a.meeting_date) = :month"; $params[':month'] = $month; }
if ($year) { $where .= " AND YEAR(a.meeting_date) = :year"; $params[':year'] = $year; }

$rows = [];

switch ($type) {    
    case 'meetings':
        $sql = "SELECT DISTINCT a.meeting_date as date
                FROM attendance a
                JOIN members m ON a.member_id = m.id
                $where
                ORDER BY a.meeting_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = array_map(fn($row) => ['name' => 'Meeting', 'date' => $row['date']], $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'referrals_given':
        $sql = "SELECT r.id, r.referred_on as date, m.name
                FROM referrals r
                JOIN members m ON r.member_id = m.id
                $where AND r.assigned_member IS NOT NULL
                ORDER BY r.referred_on DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'specific_gives':
        $sql = "SELECT r.id, r.referred_on as date, m.name
                FROM referrals r
                JOIN members m ON r.member_id = m.id
                $where AND r.referral_type = 'Specific Give'
                ORDER BY r.referred_on DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

header('Content-Type: application/json');
echo json_encode($rows);
