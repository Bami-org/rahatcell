<?php
require_once "includes/conn.php";

$customer_type = isset($_GET['customer_type']) ? $db->real_escape_string($_GET['customer_type']) : '';
$category = isset($_GET['category']) ? (int) $_GET['category'] : '';

$query = "
    SELECT 
        p.id AS id,
        p.amount AS amount,
        p.category_id,
        p.sub_category_id,
        p.description,
        c.name AS category_name,
        s.name AS sub_category_name,
        s.photo AS sub_category_photo,
        ap.package_id,
        ep.operator,
        ep.name AS package_name,
        ep.amount AS package_amount,
        ac.username AS api_user,
        ac.dealer_code AS dc
    FROM 
        product p
    JOIN 
        category c ON p.category_id = c.id
    JOIN 
        sub_category s ON p.sub_category_id = s.id
    LEFT JOIN 
        assigned_product_package ap ON p.id = ap.product_id
    LEFT JOIN 
        external_packages ep ON ap.package_id = ep.id
    LEFT JOIN 
        api_credentials ac ON ep.api_credentials_id = ac.id
    WHERE 1=1
";

if ($customer_type) {
    $query .= " AND p.customer_type = '$customer_type'";
}

if ($category) {
    $query .= " AND p.category_id = $category";
}

$result = $db->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'data' => $products]);
?>