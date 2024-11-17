<?php
require_once "/home/dpk8s26bzf65/public_html/rap/includes/conn.php";

$start_time = time();

while (time() - $start_time < 60) {
    // Fetch pending external orders with product and assigned product package info
    $orders_sql = $db->query("
        SELECT 
            orders.id AS order_id,
            orders.product_id,
            orders.customer_id,
            assigned_product_package.api_credentials_id
        FROM orders
        LEFT JOIN assigned_product_package ON orders.product_id = assigned_product_package.product_id
        WHERE orders.server = 'external' AND orders.status = 'Pending'
    ");

    if ($orders_sql->num_rows > 0) {
        while ($order = $orders_sql->fetch_assoc()) {
            // Fetch API credentials using the api_credential_id from assigned_product_package
            $credentials_sql = $db->query("
                SELECT base_url, dealer_code, username, password 
                FROM api_credentials 
                WHERE id = " . intval($order['api_credentials_id'])
            );
            $credentials = $credentials_sql->fetch_assoc();

            if (!$credentials) {
                file_put_contents("/home/dpk8s26bzf65/public_html/rap/log/logs.log", "API credentials not found for order ID: " . $order['order_id'] . "\n", FILE_APPEND);
                continue;
            }

            // Prepare verification request
            $veri = array(
                'Operation' => 'TopUpStatus',
                'request' => array(
                    'DealerCode' => $credentials['dealer_code'],
                    'Username'   => $credentials['username'],
                    'Password'   => $credentials['password'],
                    'TransactionId' => $order['order_id']
                )
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $credentials['base_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($veri));
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);

            if (isset($result['TopUpStatusResult']['ResponseCode'])) {
                $responseCode = $result['TopUpStatusResult']['ResponseCode'];

                if ($responseCode == '0001') {
                    $status = 'Success';
                } elseif ($responseCode == '0002') {
                    $status = 'Pending';
                } elseif ($responseCode == '0003' || $responseCode == '0110') {
                    // Fetch customer and product details
                    $currency = mysqli_fetch_assoc($db->query("SELECT customer.name as cs_name, customer.currency_id, currency.name as currency 
                        FROM customer 
                        LEFT JOIN currency ON customer.currency_id = currency.id 
                        WHERE customer.id = " . intval($order['customer_id'])
                    ));
                    $cur = $currency["currency"];
                    $cur_id = $currency["currency_id"];

                    // Fetch product details
                    $product_sql = $db->query("
                        SELECT 
                            CASE 
                                WHEN $cur_id = 1 THEN product.toman_sale_price
                                WHEN $cur_id = 2 THEN product.dollar_sale_price
                                WHEN $cur_id = 3 THEN product.lyra_sale_price
                                WHEN $cur_id = 4 THEN product.euro_sale_price
                            END AS sale_price,
                            CONCAT(product.amount, ' ', units.name, ' - ', sub_category.name) AS product
                        FROM product
                        LEFT JOIN units ON product.unit_id = units.id
                        LEFT JOIN sub_category ON product.sub_category_id = sub_category.id
                        LEFT JOIN orders ON product.id = orders.product_id
                        LEFT JOIN customer ON orders.customer_id = customer.id
                        WHERE product.id = " . intval($order['product_id'])
                    );
                    
                    $price_info = mysqli_fetch_assoc($product_sql);

                    if ($price_info) {
                        $money = $price_info['sale_price'];
                        $product = $price_info['product'];
                        
                        // Update balance for the customer
                        $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id = " . intval($order['customer_id']));

                        // Insert transaction for the customer
                        $db->insert(
                            "transactions",
                            [
                                "customer_id" => $order['customer_id'],
                                "amount" => $money,
                                "tr_type" => "Receipt",
                                "description" => "بازگشت $money $cur بابت رد شدن سفارش $product",
                            ]
                        );

                        // Fetch and update balance for parent customer if applicable
                        $parent_id_sql = $db->query("SELECT parent_id FROM customer WHERE id = " . intval($order['customer_id']));
                        $parent_id = $parent_id_sql->fetch_assoc();
                        if ($parent_id["parent_id"] > 0) {
                            $customer_name = $currency["cs_name"];
                            $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id = " . intval($parent_id["parent_id"]));
                            $db->insert(
                                "transactions",
                                [
                                    "customer_id" => $parent_id["parent_id"],
                                    "amount" => $money,
                                    "tr_type" => "Receipt",
                                    "description" => "بازگشت $money $cur بابت رد شدن سفارش $product توسط $customer_name",
                                ]
                            );
                        }
                        
                        $status = 'Rejected';
                    } else {
                        file_put_contents("/home/dpk8s26bzf65/public_html/rap/log/logs.log", "Product not found for order ID: " . $order['order_id'] . "\n", FILE_APPEND);
                        continue;
                    }
                } else {
                    $status = 'Pending';
                }

                // Update the order status
                $db->update("orders", ['status' => $status], "id = " . intval($order['order_id']));
            } else {
                file_put_contents("/home/dpk8s26bzf65/public_html/rap/log/logs.log", "Response code not found for order ID: " . $order['order_id'] . "\n", FILE_APPEND);
            }
        }
    }
    sleep(15);
}
?>
