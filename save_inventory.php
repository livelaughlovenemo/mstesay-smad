<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: login.php');
require_once "includes/db.php";

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['supplier']) && isset($_POST['inv'])) {
    $today = date('Y-m-d');
    $supplier = $_POST['supplier'];

    foreach ($_POST['inv'] as $category => $products) {
        foreach ($products as $product => $kilos) {
            $kilos = (float)$kilos;
            if ($kilos > 0) {
                $stmt = $pdo->prepare("INSERT INTO inventory (inv_date, category, product_name, supplier, kilos)
                                       VALUES (:d,:c,:p,:s,:k)");
                $stmt->execute([
                    'd'=>$today,'c'=>$category,'p'=>$product,'s'=>$supplier,'k'=>$kilos
                ]);
            }
        }
    }
}
header("Location: dashboard.php#inventory");
exit;
