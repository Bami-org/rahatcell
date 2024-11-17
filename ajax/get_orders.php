<?php
require_once "../includes/conn.php";


$sql = $db->query("SELECT 
orders.*, 
CONCAT(product.amount,' ',units.name,' ',sub_category.name) as product,
customer.name as customer,
CASE
    WHEN currency.id = 1 THEN CONCAT(product.toman_sale_price,' ',currency.name)
    WHEN currency.id = 2 THEN CONCAT(product.dollar_sale_price,' ',currency.name)
    WHEN currency.id = 3 THEN CONCAT(product.lyra_sale_price,' ',currency.name)
    WHEN currency.id = 4 THEN CONCAT(product.euro_sale_price,' ',currency.name)
    WHEN currency.id = 5 THEN CONCAT(product.afghani_sale_price,' ',currency.name)
END as sale,
CASE
    WHEN currency.id = 1 THEN CONCAT(product.toman_buy_price,' ',currency.name)
    WHEN currency.id = 2 THEN CONCAT(product.dollar_buy_price,' ',currency.name)
    WHEN currency.id = 3 THEN CONCAT(product.lyra_buy_price,' ',currency.name)
    WHEN currency.id = 4 THEN CONCAT(product.euro_buy_price,' ',currency.name)
     WHEN currency.id = 5 THEN CONCAT(product.afghani_buy_price,' ',currency.name)
END as buy,
CASE
    WHEN currency.id = 1 THEN CONCAT(product.toman_sale_price - product.toman_buy_price,' ',currency.name)
    WHEN currency.id = 2 THEN CONCAT(product.dollar_sale_price - product.dollar_buy_price,' ',currency.name)
    WHEN currency.id = 3 THEN CONCAT(product.lyra_sale_price - product.lyra_buy_price,' ',currency.name)
    WHEN currency.id = 4 THEN CONCAT(product.euro_sale_price - product.euro_buy_price,' ',currency.name)
    WHEN currency.id = 5 THEN CONCAT(product.afghani_sale_price - product.euro_buy_price,' ',currency.name)
END as benefit
FROM orders 
LEFT JOIN product ON orders.product_id = product.id 
LEFT JOIN customer ON orders.customer_id = customer.id 
LEFT JOIN currency ON customer.currency_id = currency.id 
LEFT JOIN units ON product.unit_id = units.id 
LEFT JOIN sub_category ON product.sub_category_id = sub_category.id 
ORDER BY orders.id DESC LIMIT 2000");

if ($sql->num_rows > 0) {
    $data = [];
    $n = 1;
    $color = "warning";
    $status = "در انتظار";

    while ($row = $sql->fetch_assoc()) {
        $server = $row["server"];
        if($row["server"] == 'internal') {
            $server = "Manual";
            $server_color = "primary";
        } else {
            $server = "API";
            $server_color = "success";
        }
        switch ($row["status"]) {
            case 'Success':
                $status = "اجرا شد";
                $color = "success";
                break;
            case 'Rejected':
                $status = "رد شد";
                $color = "danger";
                break;
            default:
                $status = "در انتظار";
                $color = "warning";
        }
        $data[] = [
            "num" => $n++,
            "id" => $row["id"],
            "product" => $row["product"],
            "customer" => $row["customer"],
            "sale" => $row["sale"],
            "buy" => $row["buy"],
            "benefit" => $row["benefit"],
            "account_address" => $row["account_address"],
            "status" => "<div style='font-size: 14px' class='badge py-1 font-weight-light badge-pill badge-$color'>$status</div>",
            "server" => "<div style='font-size: 12px' class='badge py-1 font-weight-light badge-pill badge-$server_color'>$server</div>",
             "status1" => $row["status"],
            "created" => $db->convertFullDate($row["created"],$setting["date_type"]),
        ];
    }
    $response = [
        'data' => $data,
        // 'pagination' => [
        //     'current_page' => $page,
        //     'total_pages' => $totalPages,
        //     'total_records' => $totalRecords
        // ]
    ];
    // echo json_encode([
    //     'data' => $data, // Array of order objects
    //     'page' => 1 // Current page number
    // ]);
    echo json_encode($data);
}
