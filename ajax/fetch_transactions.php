<?php
require_once "includes/conn.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);


header('Content-Type: application/json'); // Ensure the response is JSON

$response = ['status' => 'error', 'data' => null, 'message' => ''];

try {
    if (isset($_GET['dealer_code'])) {
        $dealer_code = $_GET['dealer_code'];
        $transactions = getTransactions($dealer_code);
        $response['status'] = 'success';
        $response['data'] = $transactions;
    } else {
        $response['message'] = 'Dealer code not provided';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

function getTransactions($dealer_code)
{
    global $pdo; // Assuming you have a PDO instance named $pdo
    $stmt = $pdo->prepare("SELECT dealer_code, bank_id, amount, created FROM api_transactions WHERE dealer_code = ?");
    $stmt->execute([$dealer_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>