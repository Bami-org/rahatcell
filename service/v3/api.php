<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

define("APIKEY", "qasimRahatCellAdminApiKEY2024");

// Check API Key
// $headers = apache_request_headers();
// if (!isset($headers['Authorization']) || $headers['Authorization'] !== APIKEY) {
//     http_response_code(401);
//     echo json_encode(array("error" => "Unauthorized"));
//     exit;
// }

// // Set headers
// header("Content-Type: application/json");
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once "../../includes/conn.php";

$cDate = $db->curDate();

if ((isset($_GET["action"]) && !empty($_GET["action"])) && (isset($_GET["apiKey"]) && ($_GET["apiKey"] === APIKEY))) {
    $action = $_GET["action"];
    // login user
    if ($action == "login") {
        $data =  $_POST;
        $username = $db->clean_input($data["username"]);
        $password = $db->clean_input($data["password"]);
        $sql = $db->query("SELECT customer.*, 
        currency.name as currency,
        currency.id as currency_id
        FROM customer 
        LEFT JOIN   currency ON customer.currency_id = currency.id
        WHERE username = '$username' AND 
        password = '$password' AND `status` = 'Active'");
        if ($sql->num_rows > 0) {
            $row = $sql->fetch_assoc();
            echo json_encode([
                "customer_id" => $row["id"],
                "currency_id" => $row["currency_id"],
                "currency" => $row["currency"],
                "result" => true
            ]);
        } else {
            echo json_encode(["result" => false]);
        }
    }

    // send customer info
    if ($action == "getCustomerInfo") {
        $sql = $db->query("SELECT customer.*,
         currency.name as symbol,currency.id as c_id,customer.currency_id,balance.balance FROM customer 
        LEFT JOIN currency ON customer.currency_id= currency.id 
        LEFT JOIN balance ON balance.customer_id = customer.id 
        WHERE customer.status='Active' AND customer.id = " . $db->clean_input($_POST["customer_id"]));
        if ($sql->num_rows > 0) {
            $row = $sql->fetch_assoc();
            $sub_credit = mysqli_fetch_assoc($db->query("SELECT SUM(balance.balance) as total
            FROM balance LEFT JOIN customer ON balance.customer_id= customer.id 
            WHERE balance.balance>0 AND customer.parent_id=" . $db->clean_input($_POST["customer_id"])));
            $sub_debit = mysqli_fetch_assoc($db->query("SELECT SUM(balance.balance) as total
            FROM balance LEFT JOIN customer ON balance.customer_id= customer.id 
            WHERE balance.balance<0 AND customer.parent_id=" . $db->clean_input($_POST["customer_id"])));
            echo json_encode([
                "name" => $row["name"],
                "address" => $row["address"],
                "currency" => $row["symbol"],
                "is_parent" => $row["parent_id"] == 0 ? true : false,
                "currency_id" => $row["currency_id"],
                "balance" => $row["balance"] ?? 0,
                "customers_credit" => $sub_credit["total"] ?? 0,
                "customers_debit" => $sub_debit["total"] ?? 0
            ]);
        } else {
            echo json_encode(["result" => false]);
        }
    }

    // get announcement

    if ($action == "announcements") {
        $ann_sql = $db->query("SELECT * FROM announcement ORDER BY announcement.id DESC");
        if ($ann_sql->num_rows > 0) {
            $ann_list = [];
            while ($ann_row = $ann_sql->fetch_assoc()) {
                $ann_list[] = $ann_row;
            }
            echo json_encode($ann_list);
        } else {
            echo json_encode([]);
        }
    }

    /* 
    1 = تومن
    2 = دالر
    3 = لیر
    4 = یورو
    */
    // get customer sales
    require_once "get_sales.php";
       

    if ($action == "addSubCustomer") {
        $currency = mysqli_fetch_assoc($db->query("SELECT currency_id FROM customer WHERE id =" . $db->clean_input($_POST["customer_id"])));
        $data = $_POST;
        $user_name = $db->clean_input($data["username"]);
        $check_username = $db->query("SELECT * FROM customer WHERE username='$user_name'");
        if ($check_username->num_rows > 0) {
            echo json_encode([
                "result" => false,
                "message" => "مشتری با نام کاربری $user_name از قبل وجود دارد!"
            ]);
        } else {
            $sql = $db->insert(
                "customer",
                [
                    "name" => $db->clean_input($data["name"]),
                    "phone" => $db->clean_input($data["phone"]),
                    "address" => $db->clean_input($data["address"]),
                    "currency_id" => $db->clean_input($currency["currency_id"]),
                    "parent_id" => $db->clean_input($data["customer_id"]),
                    "username" => $db->clean_input($data["username"]),
                    "password" => $db->clean_input($data["password"]),
                ]
            );
            if ($sql) {
                echo json_encode([
                    "result" => true,
                    "message" => "مشتری موفقانه اضافه شد"
                ]);
            } else {
                echo json_encode([
                    "result" => false,
                    "message" => "خطا در ثبت کردن مشتری، لطفا دوباره امتحان کنید!"
                ]);
            }
        }
    }

    if ($action == "editSubCustomer") {
        $data = $_POST;
        $user_name = $db->clean_input($data["username"]);
        $cs_id = $db->clean_input($data["customer_id"]);
        $check_username = $db->query("SELECT * FROM customer WHERE username='$user_name' AND id != $cs_id");
        if ($check_username->num_rows > 0) {
            echo json_encode([
                "result" => false,
                "message" => "مشتری با نام کاربری $user_name از قبل وجود دارد!"
            ]);
        } else {
            $sql = $db->update(
                "customer",
                [
                    "name" => $db->clean_input($data["name"]),
                    "phone" => $db->clean_input($data["phone"]),
                    "address" => $db->clean_input($data["address"]),
                    "username" => $db->clean_input($data["username"]),
                    "password" => $db->clean_input($data["password"]),
                ],
                "id=$cs_id"
            );
            if ($sql) {
                echo json_encode([
                    "result" => true,
                    "message" => "معلومات مشتری موفقانه تغییر یافت!"
                ]);
            } else {
                echo json_encode([
                    "result" => false,
                    "message" => "خطا در بروزرسانی معلئمات مشتری"
                ]);
            }
        }
    }


    if ($action == "getSubCustomers") {
        $data = $_POST;
        $sql = $db->query("SELECT customer.*, DATE(customer.created) as created_at, currency.name as currency,currency.id as c_id,balance.balance as balance FROM customer LEFT JOIN currency ON customer.currency_id = currency.id LEFT JOIN balance ON customer.id=balance.customer_id WHERE customer.parent_id =" . $data["customer_id"]);
        $customers = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $customers[] = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "currency" => $row["currency"],
                    "phone" => $row["phone"],
                    "address" => $row["address"],
                    "username" => $row["username"],
                    "password" => $row["password"],
                    "balance" => $row["balance"] ?? "0",
                    "created" => $row["created_at"],
                ];
            }
            echo json_encode($customers);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "searchSubCustomers") {
        $data = $_POST;
        $query = $db->clean_input($data["query"]);
        $customer_id = $db->clean_input($data["customer_id"]);
        $sql = $db->query("SELECT customer.*,DATE(customer.created) as created_at,currency.name as currency,currency.id as c_id,balance.balance as balance FROM customer LEFT JOIN currency ON customer.currency_id = currency.id LEFT JOIN balance ON customer.id=balance.customer_id WHERE customer.parent_id = $customer_id AND customer.name LIKE '$query%'");
        $finded_customers = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $finded_customers[] = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "currency" => $row["currency"],
                    "phone" => $row["phone"],
                    "address" => $row["address"],
                    "username" => $row["username"],
                    "password" => $row["password"],
                    "balance"=>  $row["c_id"]==1? number_format($row["balance"]??0): $row["balance"] ?? 0,
                    "created" => $row["created_at"]??'',
                ];
            }
            echo json_encode($finded_customers);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "addSubCustomerBalance") {
        $data = $_POST;
        $balance = $db->clean_input($data["balance"]);
        $description = $db->clean_input($data["description"]);
        $check_balance = $db->query("SELECT * FROM balance WHERE customer_id =" . $db->clean_input($data["customer_id"]));
        if ($check_balance->num_rows > 0) {
            $sql = $db->query("UPDATE balance SET balance = balance + $balance, `description`= '$description' WHERE customer_id=" . $db->clean_input($_POST["customer_id"]));
            if ($sql) {
                echo json_encode(["result" => true]);
            } else {
                echo json_encode(["result" => false]);
            }
        } else {
            $sql = $db->insert("balance", [
                "customer_id" => $db->clean_input($data["customer_id"]),
                "balance" => $db->clean_input($data["balance"]),
                "description" => $description,
            ]);
            if ($sql) {
                echo json_encode(["result" => true]);
            } else {
                echo json_encode(["result" => false]);
            }
        }
        $db->insert("transactions", [
            "customer_id" => $db->clean_input($data["customer_id"]),
            "amount" => $db->clean_input($data["balance"]),
            "tr_type" => "Receipt",
            "description" => $description,
        ]);
    }

    // edit subCustomer balance
    if ($action == "editCustomerBalance") {
        $data = $_POST;
        $balance = $db->clean_input($data["balance"]);
        $description = $db->clean_input($data["description"]);
        $sql = $db->query("UPDATE balance SET balance = balance - $balance, `description`='$description', updated='" . date("Y-m-d h:i:s") . "' WHERE customer_id=" . $db->clean_input($data["customer_id"]));
        if ($sql) {
            echo json_encode(["result" => true]);
        } else {
            echo json_encode(["result" => false]);
        }
        $db->insert("transactions", [
            "customer_id" => $db->clean_input($data["customer_id"]),
            "amount" => $balance,
            "tr_type" => "Payment",
            "description" => $description,
        ]);
    }
    // get subCustomer balance
    if ($action == "getSubCustomerBalance") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->query("SELECT SUM(balance) as total FROM balance WHERE customer_id=$id");
        if ($sql) {
            $row = $sql->fetch_assoc();
            echo json_encode($row["total"] ?? "0");
        } else {
            echo json_encode([""]);
        }
    }
    // delete subCustomer
    if ($action == "deleteSubCustomer") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->delete("customer", "id=$id");
        if ($sql) {
            return true;
        } else {
            return false;
        }
    }

    // get transactions
    if ($action == "transactions") {
        $data = $_POST;
        $customer_id = $db->clean_input($data["customer_id"]);
        $sql = $db->query("SELECT transactions.amount, transactions.tr_type,transactions.`description`,DATE(transactions.created) as tr_date FROM transactions
        WHERE transactions.customer_id =$customer_id ORDER BY transactions.id DESC");
        $transactions = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $transactions[] = [
                    "amount" => $row["amount"],
                    "tr_type" => $row["tr_type"] == "Receipt" ? "دریافتی" : "پرداختی",
                    "description" => $row["description"],
                    "date" => $row["tr_date"],
                ];
            }
            echo json_encode($transactions);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "receipt_transactions") {
        $data = $_POST;
        $customer_id = $db->clean_input($data["customer_id"]);
        $sql = $db->query("SELECT transactions.amount,transactions.tr_type,transactions.`description`,DATE(transactions.created) as tr_date FROM transactions
        FROM transactions WHERE transactions.customer_id =$customer_id AND transactions.tr_type='Receipt' ORDER BY transactions.id DESC");
        $transactions = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $transactions[] = [
                    "amount" => $row["amount"],
                    "tr_type" => "دریافتی",
                     "description" => $row["description"],
                    "date" => $row["tr_date"],
                ];
            }
            echo json_encode($transactions);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "payment_transactions") {
        $data = $_POST;
        $customer_id = $db->clean_input($data["customer_id"]);
        $sql = $db->query("SELECT transactions.amount,transactions.tr_type,transactions.`description`,DATE(transactions.created) as tr_date,
        orders.account_address FROM transactions
        LEFT JOIN orders ON orders.customer_id=transactions.customer_id
        FROM transactions WHERE transactions.customer_id =$customer_id AND transactions.tr_type='Payment' ORDER BY transactions.id DESC");
        $transactions = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $transactions[] = [
                    "amount" => $row["amount"],
                    "tr_type" => "پرداختی",
                    "description" => $row["description"],
                    "date" => $row["tr_date"],
                ];
            }
            echo json_encode($transactions);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "searchTransaction") {
        $id = $db->clean_input($_POST["customer_id"]);
        $fromDate = $db->clean_input($_POST["fromDate"]);
        $toDate = $db->clean_input($_POST["toDate"]);
        $sql = $db->query("SELECT transactions.amount,transactions.tr_type,transactions.`description`,DATE(transactions.created) as tr_date
        FROM transactions WHERE transactions.customer_id=$id AND DATE(transactions.created) BETWEEN '$fromDate' AND '$toDate' ORDER BY transactions.id DESC");
        $transactions = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $transactions[] = [
                    "amount" => $row["amount"],
                    "tr_type" => $row["tr_type"] == "Receipt" ? "دریافتی" : "پرداختی",
                     "description" => $row["description"],
                    "date" => $row["tr_date"],
                ];
            }
            echo json_encode($transactions);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "searchTransactionByName") {
        $id = $db->clean_input($_POST["customer_id"]);
        $name = $db->clean_input($_POST["name"]);
        $sql = $db->query("SELECT transactions.amount,transactions.tr_type,transactions.`description`,DATE(transactions.created) as tr_date FROM transactions WHERE transactions.`description` LIKE '%$name%' AND transactions.customer_id=$id ORDER BY transactions.id DESC");
        $transactions = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $transactions[] = [
                    "amount" => $row["amount"],
                    "tr_type" => $row["tr_type"] == "Receipt" ? "دریافتی" : "پرداختی",
                    "description" => $row["description"],
                    "date" => $row["tr_date"],
                ];
            }
            echo json_encode($transactions);
        } else {
            echo json_encode([]);
        }
    }

    // subCustomer balance transactions

    if ($action == "balanceTransactions") {
        $cs_id = $db->clean_input($_POST["customer_id"]);
        $b_sql = $db->query("SELECT * FROM `transactions` 
        WHERE customer_id =$cs_id ORDER BY id DESC");
        if ($b_sql->num_rows > 0) {
            $b_trans = [];
            while ($b_row = $b_sql->fetch_assoc()) {
                $b_trans[] = $b_row;
            }
            echo json_encode($b_trans);
        }
    }
    
    
    if ($action == "ads") {
        $ad_sql = $db->query("SELECT * FROM ads ORDER BY id DESC");
        if ($ad_sql->num_rows > 0) {
            $ad_list = [];
            while ($ad_row = $ad_sql->fetch_assoc()) {
                $ad_list[] = [
                    "title" => $ad_row["title"],
                    "photo" => "uploads/ads/".$ad_row["photo"],
                ];
            }
            echo json_encode($ad_list);
        } else {
            echo json_encode([]);
        }
    }

    /* 
    1 = تومن
    2 = دالر
    3 = لیر
    4 = یورو
    */

    if ($action == "products") {
        // $id = $db->clean_input($_POST["id"]);
        $currency = $db->clean_input($_POST["currency_id"]);
        require_once "products.php";
        $p = new Products($db);
        echo $p->category();
    }

    // get orders
    if ($action == "orders") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,orders.created as orderDate, sub_category.photo as logo FROM orders LEFT JOIN product ON orders.product_id = product.id LEFT JOIN customer ON orders.customer_id = customer.id LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE orders.customer_id=$id ORDER BY orders.id DESC");
        $orders = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $orders[] = [
                    "product" => $row["product"],
                    "status" => $row["status"],
                    "account_address" => $row["account_address"],
                    "detail" => $row["detail"],
                    "logo" => "uploads/category/" .$row["logo"],
                    "date" => $row["orderDate"]
                ];
            }
            echo json_encode($orders);
        } else {
            echo json_encode([]);
        }
    }
    if ($action == "lastOrders") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,orders.created as orderDate,sub_category.photo as logo FROM orders LEFT JOIN product ON orders.product_id = product.id LEFT JOIN customer ON orders.customer_id = customer.id LEFT JOIN units ON product.unit_id=units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE orders.customer_id=$id ORDER BY orders.id DESC limit 2");
        $orders = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $orders[] = [
                    "product" => $row["product"],
                    "status" => $row["status"],
                    "account_address" => $row["account_address"],
                    "detail" => $row["detail"],
                    "logo" => "uploads/category/" .$row["logo"],
                    "date" => $row["orderDate"]
                ];
            }
            echo json_encode($orders);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "searchOrder") {
        $id = $db->clean_input($_POST["customer_id"]);
        $fromDate = $db->clean_input($_POST["fromDate"]);
        $toDate = $db->clean_input($_POST["toDate"]);
        $sql = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,orders.created as orderDate,sub_category.photo as logo FROM orders LEFT JOIN product ON orders.product_id = product.id LEFT JOIN customer ON orders.customer_id = customer.id LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE orders.customer_id=$id AND DATE(orders.created) BETWEEN '$fromDate' AND '$toDate' ORDER BY orders.id DESC");
        $orders = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $orders[] = [
                    "product" => $row["product"],
                    "status" => $row["status"],
                    "account_address" => $row["account_address"],
                    "detail" => $row["detail"],
                    "logo" => "uploads/category/" .$row["logo"],
                    "date" => $row["orderDate"]
                ];
            }
            echo json_encode($orders);
        } else {
            echo json_encode([]);
        }
    }

    if ($action == "pending_orders") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,orders.created as orderDate,sub_category.photo as logo FROM orders LEFT JOIN product ON orders.product_id = product.id LEFT JOIN customer ON orders.customer_id = customer.id LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE orders.customer_id=$id AND orders.status='Pending' ORDER BY orders.id DESC");
        $orders = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $orders[] = [
                    "product" => $row["product"],
                    "status" => $row["status"],
                    "account_address" => $row["account_address"],
                    "detail" => $row["detail"],
                    "logo" => "uploads/category/" .$row["logo"],
                    "date" => $row["orderDate"]
                ];
            }
            echo json_encode($orders);
        } else {
            echo json_encode([]);
        }
    }
    if ($action == "success_orders") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,orders.created as orderDate,sub_category.photo as logo FROM orders LEFT JOIN product ON orders.product_id = product.id LEFT JOIN customer ON orders.customer_id = customer.id LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE orders.customer_id=$id AND orders.status='Success' ORDER BY orders.id DESC");
        $orders = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $orders[] = [
                    "product" => $row["product"],
                    "status" => $row["status"],
                    "account_address" => $row["account_address"],
                    "detail" => $row["detail"],
                    "logo" => "uploads/category/" .$row["logo"],
                    "date" => $row["orderDate"]
                ];
            }
            echo json_encode($orders);
        } else {
            echo json_encode([]);
        }
    }
    if ($action == "rejected_orders") {
        $id = $db->clean_input($_POST["customer_id"]);
        $sql = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,orders.created as orderDate,sub_category.photo as logo FROM orders LEFT JOIN product ON orders.product_id = product.id LEFT JOIN customer ON orders.customer_id = customer.id LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE orders.customer_id=$id AND orders.status='Rejected' ORDER BY orders.id DESC");
        $orders = [];
        if ($sql->num_rows > 0) {
            while ($row = $sql->fetch_assoc()) {
                $orders[] = [
                    "product" => $row["product"],
                    "status" => $row["status"],
                    "account_address" => $row["account_address"],
                    "detail" => $row["detail"],
                    "logo" => "uploads/category/".$row["logo"],
                    "date" => $row["orderDate"]
                ];
            }
            echo json_encode($orders);
        } else {
            echo json_encode([]);
        }
    }

    // adding order and check & decrement balance & add buy transaction

    // if ($action == "addOrder") {
    //     $customer_id = $db->clean_input($_POST["customer_id"]);
    //     $check_customer = mysqli_fetch_assoc($db->query("SELECT name,currency_id,parent_id FROM customer WHERE id =" . $db->clean_input($_POST["customer_id"])));
    //     $parent_id = $check_customer["parent_id"];
    //     $customer_name = $check_customer["name"];
    //     $check_customer_balance = mysqli_fetch_assoc($db->query("SELECT SUM(balance) as total FROM balance WHERE customer_id = $customer_id"));
    //     $check_parent_sql = $db->query("SELECT SUM(balance) as total,customer_id FROM balance WHERE customer_id = $parent_id");
    //     $check_parent_balance = $check_parent_sql->fetch_assoc();
    //     $product_id = $db->clean_input($_POST["product_id"]);
    //     $product_price;
    //     switch ($check_customer["currency_id"]) {
    //         case 1:
    //             // for toman برای تومن
    //             $product_price = mysqli_fetch_assoc($db->query("SELECT toman_sale_price as price FROM product WHERE id= $product_id"));
    //             //    if customer is a parent
    //             if ($check_parent_sql->num_rows > 0 && $parent_id > 0) {
    //                 if ($check_parent_balance["total"] >= $product_price["price"]) {
    //                     if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $parent_id);
    //                         $sql = $db->insert("orders", [
    //                             "product_id" => $db->clean_input($_POST["product_id"]),
    //                             "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                             "account_address" => $db->clean_input($_POST["account_address"]),
    //                             "created" => $cDate,
    //                         ]);
    //                         if ($sql) {
    //                             $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $customer_id,
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"]
    //                             ]);
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $check_parent_balance["customer_id"],
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"] . " توسط  $customer_name"
    //                             ]);
    //                             echo json_encode([
    //                                 "result" => true,
    //                                 "message" => "سفارش شما موفقانه ثبت شد"
    //                             ]);
    //                         } else {
    //                             echo json_encode([
    //                                 "result" => false,
    //                                 "message" => "خرید انجام نشد!"
    //                             ]);
    //                         }
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "بیلانس کافی ندارید!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "مشتری بالاسر شما بیلانس کافی ندارد!"
    //                     ]);
    //                 }
    //             } else {
    //                 if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                     $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                     $sql = $db->insert("orders", [
    //                         "product_id" => $db->clean_input($_POST["product_id"]),
    //                         "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                         "account_address" => $db->clean_input($_POST["account_address"]),
    //                     ]);
    //                     if ($sql) {
    //                         $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                         $db->insert("transactions", [
    //                             "customer_id" => $customer_id,
    //                             "amount" => $product_price["price"],
    //                             "tr_type" => "Payment",
    //                             "description" => "خرید " . $product["product"]
    //                         ]);
    //                         echo json_encode([
    //                             "result" => true,
    //                             "message" => "سفارش شما موفقانه ثبت شد"
    //                         ]);
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "خرید انجام نشد!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "بیلانس کافی ندارید!"
    //                     ]);
    //                 }
    //             }
    //             break;
    //         case 2:
    //             // for dollar برای دالر
    //             $product_price = mysqli_fetch_assoc($db->query("SELECT dollar_sale_price as price FROM product WHERE id= $product_id"));
    //             if ($check_parent_sql->num_rows > 0 && $parent_id > 0) {
    //                 if ($check_parent_balance["total"] >= $product_price["price"]) {
    //                     if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $parent_id);
    //                         $sql = $db->insert("orders", [
    //                             "product_id" => $db->clean_input($_POST["product_id"]),
    //                             "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                             "account_address" => $db->clean_input($_POST["account_address"]),
    //                         ]);
    //                         if ($sql) {
    //                             $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $customer_id,
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"]
    //                             ]);
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $check_parent_balance["customer_id"],
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"] . " توسط  $customer_name"
    //                             ]);
    //                             echo json_encode([
    //                                 "result" => true,
    //                                 "message" => "سفارش شما موفقانه ثبت شد"
    //                             ]);
    //                         } else {
    //                             echo json_encode([
    //                                 "result" => false,
    //                                 "message" => "خرید انجام نشد!"
    //                             ]);
    //                         }
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "بیلانس کافی ندارید!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "مشتری بالاسر شما بیلانس کافی ندارد!"
    //                     ]);
    //                 }
    //             } else {
    //                 if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                     $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                     $sql = $db->insert("orders", [
    //                         "product_id" => $db->clean_input($_POST["product_id"]),
    //                         "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                         "account_address" => $db->clean_input($_POST["account_address"]),
    //                     ]);
    //                     if ($sql) {
    //                         $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                         $db->insert("transactions", [
    //                             "customer_id" => $customer_id,
    //                             "amount" => $product_price["price"],
    //                             "tr_type" => "Payment",
    //                             "description" => "خرید " . $product["product"]
    //                         ]);
    //                         echo json_encode([
    //                             "result" => true,
    //                             "message" => "سفارش شما موفقانه ثبت شد"
    //                         ]);
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "خرید انجام نشد!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "بیلانس کافی ندارید!"
    //                     ]);
    //                 }
    //             }
    //             break;
    //         case 3:
    //             // for lyar برای لیر
    //             $product_price = mysqli_fetch_assoc($db->query("SELECT lyra_sale_price as price FROM product WHERE id= $product_id"));
    //             if ($check_parent_sql->num_rows > 0 && $parent_id > 0) {
    //                 if ($check_parent_balance["total"] >= $product_price["price"]) {
    //                     if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $parent_id);
    //                         $sql = $db->insert("orders", [
    //                             "product_id" => $db->clean_input($_POST["product_id"]),
    //                             "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                             "account_address" => $db->clean_input($_POST["account_address"]),
    //                         ]);
    //                         if ($sql) {
    //                             $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $customer_id,
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"]
    //                             ]);
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $check_parent_balance["customer_id"],
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"] . " توسط  $customer_name"
    //                             ]);
    //                             echo json_encode([
    //                                 "result" => true,
    //                                 "message" => "سفارش شما موفقانه ثبت شد"
    //                             ]);
    //                         } else {
    //                             echo json_encode([
    //                                 "result" => false,
    //                                 "message" => "خرید انجام نشد!"
    //                             ]);
    //                         }
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "بیلانس کافی ندارید!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "مشتری بالاسر شما بیلانس کافی ندارد!"
    //                     ]);
    //                 }
    //             } else {
    //                 if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                     $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                     $sql = $db->insert("orders", [
    //                         "product_id" => $db->clean_input($_POST["product_id"]),
    //                         "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                         "account_address" => $db->clean_input($_POST["account_address"]),
    //                     ]);
    //                     if ($sql) {
    //                         $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                         $db->insert("transactions", [
    //                             "customer_id" => $customer_id,
    //                             "amount" => $product_price["price"],
    //                             "tr_type" => "Payment",
    //                             "description" => "خرید " . $product["product"]
    //                         ]);
    //                         echo json_encode([
    //                             "result" => true,
    //                             "message" => "سفارش شما موفقانه ثبت شد"
    //                         ]);
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "خرید انجام نشد!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "بیلانس کافی ندارید!"
    //                     ]);
    //                 }
    //             }
    //             break;
    //         case 4:
    //             // for euro برای یورو
    //             $product_price = mysqli_fetch_assoc($db->query("SELECT euro_sale_price as price FROM product WHERE id= $product_id"));
    //             if ($check_parent_sql->num_rows > 0 && $parent_id > 0) {
    //                 if ($check_parent_balance["total"] >= $product_price["price"]) {
    //                     if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                         $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $parent_id);
    //                         $sql = $db->insert("orders", [
    //                             "product_id" => $db->clean_input($_POST["product_id"]),
    //                             "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                             "account_address" => $db->clean_input($_POST["account_address"]),
    //                         ]);
    //                         if ($sql) {
    //                             $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $customer_id,
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"]
    //                             ]);
    //                             $db->insert("transactions", [
    //                                 "customer_id" => $check_parent_balance["customer_id"],
    //                                 "amount" => $product_price["price"],
    //                                 "tr_type" => "Payment",
    //                                 "description" => "خرید " . $product["product"] . " توسط  $customer_name"
    //                             ]);
    //                             echo json_encode([
    //                                 "result" => true,
    //                                 "message" => "سفارش شما موفقانه ثبت شد"
    //                             ]);
    //                         } else {
    //                             echo json_encode([
    //                                 "result" => false,
    //                                 "message" => "خرید انجام نشد!"
    //                             ]);
    //                         }
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "بیلانس کافی ندارید!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "مشتری بالاسر شما بیلانس کافی ندارد!"
    //                     ]);
    //                 }
    //             } else {
    //                 if ($check_customer_balance["total"] >= $product_price["price"]) {
    //                     $db->query("UPDATE balance SET balance = balance - $product_price[price] WHERE customer_id=" . $customer_id);
    //                     $sql = $db->insert("orders", [
    //                         "product_id" => $db->clean_input($_POST["product_id"]),
    //                         "customer_id" => $db->clean_input($_POST["customer_id"]),
    //                         "account_address" => $db->clean_input($_POST["account_address"]),
    //                     ]);
    //                     if ($sql) {
    //                         $product = mysqli_fetch_assoc($db->query("SELECT CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product FROM product LEFT JOIN units ON product.unit_id = units.id LEFT JOIN sub_category ON product.sub_category_id = sub_category.id WHERE product.id=$product_id"));
    //                         $db->insert("transactions", [
    //                             "customer_id" => $customer_id,
    //                             "amount" => $product_price["price"],
    //                             "tr_type" => "Payment",
    //                             "description" => "خرید " . $product["product"]
    //                         ]);
    //                         echo json_encode([
    //                             "result" => true,
    //                             "message" => "سفارش شما موفقانه ثبت شد"
    //                         ]);
    //                     } else {
    //                         echo json_encode([
    //                             "result" => false,
    //                             "message" => "خرید انجام نشد!"
    //                         ]);
    //                     }
    //                 } else {
    //                     echo json_encode([
    //                         "result" => false,
    //                         "message" => "بیلانس کافی ندارید!"
    //                     ]);
    //                 }
    //             }
    //             break;
    //     }
    // }
    
    if ($action == "addOrder") {
        $server_setting = mysqli_fetch_assoc($db->query("SELECT setting_value FROM api_server_settings WHERE setting_key='server'"))['setting_value'];
        $customer_id = $db->clean_input($_POST["customer_id"]);
        $product_id = $db->clean_input($_POST["product_id"]);
        $account_address = $db->clean_input($_POST["account_address"]);
        $cDate = date('Y-m-d H:i:s');
    
        $check_customer = mysqli_fetch_assoc($db->query("SELECT name, currency_id, parent_id FROM customer WHERE id = $customer_id"));
        $parent_id = $check_customer["parent_id"];
        $customer_name = $check_customer["name"];
    
        $price_column = '';
        switch ($check_customer["currency_id"]) {
            case 1: $price_column = 'toman_sale_price'; break;
            case 2: $price_column = 'dollar_sale_price'; break;
            case 3: $price_column = 'lyra_sale_price'; break;
            case 4: $price_column = 'euro_sale_price'; break;
            case 5: $price_column = 'afghani_sale_price'; break;
            default: $price_column = 'toman_sale_price'; break; // Default to toman if currency ID is unknown
        }
        $product_query = $db->query("SELECT $price_column as price FROM product WHERE id = $product_id");
        $product_price = mysqli_fetch_assoc($product_query);
    
        // Check customer balance
        $check_customer_balance = mysqli_fetch_assoc($db->query("SELECT SUM(balance) as total FROM balance WHERE customer_id = $customer_id"));
        
        // Only check parent balance if parent_id exists and is greater than 0
        if ($parent_id > 0) {
            $check_parent_balance = mysqli_fetch_assoc($db->query("SELECT SUM(balance) as total FROM balance WHERE customer_id = $parent_id"));
            $parent_has_balance = $check_parent_balance['total'] >= $product_price['price'];
        } else {
            $check_parent_balance = ['total' => 0];  // No parent, so assume 0 balance
            $parent_has_balance = true;  // Set to true because there's no parent to check
        }
    
        $order_price = $product_price["price"];
        if ($check_customer_balance['total'] >= $order_price && $parent_has_balance) {
            // Update balances
            $db->query("UPDATE balance SET balance = balance - $order_price WHERE customer_id = $customer_id");
            if ($parent_id > 0) {
                $db->query("UPDATE balance SET balance = balance - $order_price WHERE customer_id = $parent_id");
            }
    
            $order_query = "INSERT INTO orders (product_id, customer_id, account_address, created, server,price) VALUES ('$product_id', '$customer_id', '$account_address', '$cDate', '$server_setting','$order_price')";
            $db->query($order_query);
    
            // Get the last inserted order ID
            $order_id = $db->con->insert_id;
    
            // Fetch the newly created order
            $order = mysqli_fetch_assoc($db->query("SELECT * FROM orders WHERE id = $order_id"));

            
            if ($order) {
                $product = mysqli_fetch_assoc($db->query("
                    SELECT 
                        CONCAT(product.amount, ' ', units.name, ' - ', sub_category.name) as product,
                        product.category_id,
                        category.name as category_name
                    FROM product 
                    LEFT JOIN units ON product.unit_id = units.id
                    LEFT JOIN sub_category ON product.sub_category_id = sub_category.id
                    LEFT JOIN category ON product.category_id = category.id
                    WHERE product.id = $product_id
                "));
                if ($product['category_id'] == 2 && strlen($account_address) > 10) {
                    $account_address = substr($account_address, -10);
                }
                $db->insert("transactions", [
                    "customer_id" => $customer_id,
                    "amount" => $order_price,
                    "tr_type" => "Payment",
                    "description" => "خرید " . $product["product"],
                ]);
                if ($parent_id > 0) {
                    $db->insert("transactions", [
                        "customer_id" => $parent_id,
                        "amount" => $order_price,
                        "tr_type" => "Payment",
                        "description" => "خرید " . $product["product"] . " توسط " . $customer_name,
                    ]);
                }
                
                $assigned_product_query = $db->query("SELECT package_id, api_credentials_id FROM assigned_product_package WHERE product_id = $product_id");
                $assigned_product = mysqli_fetch_assoc($assigned_product_query);
                
                
                if ($assigned_product && !empty($assigned_product['package_id'])) {
                    $package_id = $assigned_product['package_id'];
                    $api_credentials_id = $assigned_product['api_credentials_id'];
                    
                    $external_package_query = $db->query("SELECT external_package_id, amount, operator FROM external_packages WHERE id = $package_id");
                    $external_package = mysqli_fetch_assoc($external_package_query);
                    
                    if ($external_package && !empty($external_package['external_package_id']) && $server_setting == 'external') {
                        $external_package_id = $external_package['external_package_id'];
                        $amount = $external_package['amount'];
                        $operator = $external_package['operator'];
                        
                        $api_credentials = $db->fetch_row("SELECT base_url, dealer_code, username, password FROM api_credentials WHERE id = $api_credentials_id");
                        
                        $veri = array(
                            'Operation' => 'TopUp',
                            'request' => array(
                                'DealerCode' => $api_credentials['dealer_code'],
                                'Username' => $api_credentials['username'],
                                'Password' => $api_credentials['password'],
                                'Operator' => $operator,
                                'Amount' => $amount,
                                'Gsm' => $account_address,
                                'TransactionId' => $order_id
                            )
                        );
                        
                        // cURL request
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $api_credentials["base_url"]);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($veri));
                        $api_response = curl_exec($ch);
                        curl_close($ch);
                        file_put_contents("/home/dpk8s26bzf65/public_html/rap/log/logs.log", "Api response  " . $api_response . "\n", FILE_APPEND);
                        
                        $api_result = json_decode($api_response, true);
                        
                        if (isset($api_result['TopUpResult']['ResponseCode']) && $api_result['TopUpResult']['ResponseCode'] !== '0000') {
                            $db->query("UPDATE orders SET server='internal' WHERE id=$order_id");
                        }
                    } else {
                        $db->query("UPDATE orders SET server='internal' WHERE id=$order_id");
                    }
                } else {
                    $db->query("UPDATE orders SET server='internal' WHERE id=$order_id");
                }
                
                 $orderData = $db->query("SELECT orders.*,CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product,
                sub_category.photo as logo,orders.created as orderDate FROM orders 
                LEFT JOIN product ON orders.product_id = product.id 
                LEFT JOIN customer ON orders.customer_id = customer.id 
                LEFT JOIN units ON product.unit_id = units.id 
                LEFT JOIN sub_category ON product.sub_category_id = sub_category.id 
                WHERE orders.customer_id=$customer_id ORDER BY orders.id DESC LIMIT 1")->fetch_assoc();
                exit(json_encode([
                    "result" => true,
                    "message" => "سفارش شما موفقانه ثبت شد",
                    "data" => [
                        "product" => $orderData["product"],
                        "status" => $orderData["status"],
                        "account_address" => $orderData["account_address"],
                        "logo" => "uploads/category/" . $orderData["logo"],
                        "date" => $orderData["orderDate"]
                    ],
                ]));

                // echo json_encode([
                //     "result" => true,
                //     "message" => "سفارش شما موفقانه ثبت شد"
                // ]);
            } else {
                echo json_encode([
                    "result" => false,
                    "message" => "خرید انجام نشد!"
                ]);
            }
        } else {
            echo json_encode([
                "result" => false,
                "error" => $parent_id,
                "message" => "بیلانس کافی ندارید یا مشتری بالاسر بیلانس کافی ندارد!"
            ]);
        }
    }



    if ($action == "today") {
        echo json_encode(["today" => jdate("l j p o")]);
    }
    
    if($action=="getPhone"){
        $phone = $db->query("SELECT phone FROM setting")->fetch_assoc()["phone"]?? "";
        exit(json_encode($phone));
    }
    
} else {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
}
