<?php


// get customer sales
if ($action == "sales") {
    
    $customer_id = $db->clean_input($_POST["customer_id"]);
    $is_parent = mysqli_fetch_assoc($db->query("SELECT customer.parent_id FROM customer WHERE id =$customer_id"))["parent_id"] == 0 ? true : false;
    $sales = mysqli_fetch_assoc($db->query("SELECT
(
    SUM(transactions.amount) -(
    SELECT
        SUM(transactions.amount) FROM transactions
    WHERE
        transactions.category = 'product' AND transactions.customer_id = " . $customer_id . "  AND (transactions.description LIKE '%بازگشت%' AND transactions.description NOT LIKE '%توسط%')
)
) AS sales
FROM transactions
WHERE transactions.category = 'product' AND transactions.customer_id = " . $customer_id . " AND (transactions.description NOT LIKE '%بازگشت%' AND transactions.description NOT LIKE '%توسط%')"));
    if ($is_parent) {
        $cs_sales = mysqli_fetch_assoc($db->query("SELECT
(
    SUM(transactions.amount) -(
    SELECT
        SUM(transactions.amount) FROM transactions
        LEFT JOIN customer ON transactions.customer_id = customer.id
    WHERE
        transactions.category = 'product' AND customer.parent_id=" . $customer_id . " AND transactions.description LIKE 'بازگشت%'
)
) AS sales
FROM transactions
 LEFT JOIN customer ON transactions.customer_id = customer.id
WHERE transactions.category = 'product' AND customer.parent_id=" . $customer_id . " AND transactions.description NOT LIKE 'بازگشت%'"));
        exit(json_encode([
            "sales" => $sales["sales"] ?? "0",
            "customer_sales" => $cs_sales["sales"] ?? "0",
        ]));
    } else {
        exit(json_encode([
            "sales" => $sales["sales"] ?? "0",
            "customer_sales" => "0",
        ]));
    }
    
    
  /*  $customer_id = $db->clean_input($_POST["customer_id"]);
    $is_parent = mysqli_fetch_assoc($db->query("SELECT customer.parent_id FROM customer WHERE id =$customer_id"))["parent_id"] == 0 ? true : false;
    $parent_id=$db->query("SELECT parent_id FROM customer WHERE id=$customer_id")->fetch_assoc()["parent_id"];
    $sales = mysqli_fetch_assoc($db->query("SELECT SUM(price) as total FROM orders WHERE status='Success' AND orders.customer_id=$customer_id"));
    if ($is_parent) {
        $cs_sales = mysqli_fetch_assoc($db->query("SELECT SUM(price) as total FROM orders
        LEFT JOIN customer ON orders.customer_id=customer.id
        WHERE orders.status='Success' AND orders.customer_id=$customer_id AND customer.parent_id=".$parent_id));
        exit(json_encode([
            "sales" => $sales["total"] ?? "0",
            "customer_sales" => $cs_sales["total"] ?? "0",
        ]));
    } else {
        exit(json_encode([
            "sales" => $sales["total"] ?? "0",
            "customer_sales" => "0",
        ]));
    }*/
}