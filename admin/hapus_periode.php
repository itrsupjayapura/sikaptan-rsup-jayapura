<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
  header('Location: ../index.php'); exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
  mysqli_query($conn, "DELETE FROM data_observasi WHERE id=$id");
}
echo "OK";
