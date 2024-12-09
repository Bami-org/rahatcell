<?php
require_once "includes/conn.php";

function makeApiRequest($operation, $data)
{
    $url = "http://api.example.com/"; // Replace with actual API URL
    $payload = array_merge(["Operation" => $operation], $data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $operation = $_POST['operation'];
    $response = [];

    switch ($operation) {
        case "take_loan":
            $loanAmount = $_POST['loan_amount'];
            $loanTerm = $_POST['loan_term'];
            $bankId = $_POST['bank'];

            // API request for taking loan
            $response = makeApiRequest("TakeLoan", [
                "loanAmount" => $loanAmount,
                "loanTerm" => $loanTerm,
                "bankId" => $bankId
            ]);

            break;

        case "repay_loan":
            $repayAmount = $_POST['repay_amount'];

            // API request for repaying loan
            $response = makeApiRequest("RepayLoan", [
                "repayAmount" => $repayAmount
            ]);

            break;

        default:
            echo "Invalid operation.";
            exit;
    }

    // Display response or redirect back
    if (!empty($response)) {
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    } else {
        echo "No response from API.";
    }
}
?>