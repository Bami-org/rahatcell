<?php
require_once "../includes/conn.php";
ini_set('memory_limit', '512M');
$sql = $db->query("SELECT transactions.*,customer.name,currency.name as c_name
FROM transactions LEFT JOIN customer ON transactions.customer_id = customer.id
LEFT JOIN currency ON customer.currency_id=currency.id ORDER BY transactions.id DESC LIMIT 3000");
if ($sql->num_rows > 0) {
    $data = [];
    $n = 1;
    while ($row = $sql->fetch_assoc()) {
        $data[] = [
            "id" => $row['id'],
            "num" => $n++,
            "tr_type" => $row['tr_type'] == 'Receipt' ? '<div class="badge badge-success p-1 px-2 text-sm-center font-weight-normal m-0">دریافتی</div>' : '<div class="badge badge-danger p-1 px-2 text-sm-center font-weight-normal m-0">پرداختی</div>',
            "amount" => $row['amount'],
            "currency" => $row['c_name'],
            "description" => $row['description'],
            "name" => $row['name'],
            "check" => "<input type='checkbox' value='" . $row['amount'] . "' onchange='getSum(this)'>",
            "c_name" => $row['c_name'],
            "category" => $row['category'] == 'balance'? 'بیلانس': 'محصول',
            "created" => $db->convertFullDate($row["created"],$setting["date_type"]),
        ];
    }
    echo json_encode($data);
}
