<?php
session_start();
require_once '../../connection/conn.php';

$branchId = isset($_SESSION['branch_id']) ? intval($_SESSION['branch_id']) : 1;

echo "BranchId in session: " . $branchId . "\n\n";

$stmtAll = $conn->prepare("SELECT FooterId, BranchId, Content, ScrollSpeed, ExpiryDate FROM footer ORDER BY FooterId DESC");
$stmtAll->execute();
$result = $stmtAll->get_result();

echo "All footer records:\n";
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['FooterId'] . " | BranchId: " . $row['BranchId'] . " | Content: " . $row['Content'] . "\n";
}

echo "\n\nRecords for BranchId = " . $branchId . ":\n";
$stmtBranch = $conn->prepare("SELECT FooterId, BranchId, Content FROM footer WHERE BranchId = ?");
$stmtBranch->bind_param('i', $branchId);
$stmtBranch->execute();
$resultBranch = $stmtBranch->get_result();
while ($row = $resultBranch->fetch_assoc()) {
    echo "ID: " . $row['FooterId'] . " | Content: " . $row['Content'] . "\n";
}

$stmtAll->close();
$stmtBranch->close();
?>
