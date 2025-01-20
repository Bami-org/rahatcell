<?php
require_once "includes/conn.php";

$external_packages = [];
$local_products = [];
$existing_assignments = [];

// Get filter inputs
$category = isset($_GET['category']) ? $db->real_escape_string($_GET['category']) : '';
$sub_category = isset($_GET['sub_category']) ? $db->real_escape_string($_GET['sub_category']) : '';

// Build the query with filters
$query = "
    SELECT 
        p.id AS id,
        p.amount AS amount,
        p.category_id,
        p.sub_category_id,
        p.description,
        c.name AS category_name,
        s.name AS sub_category_name,
        s.photo AS sub_category_photo
    FROM 
        product p
    JOIN 
        category c ON p.category_id = c.id
    JOIN 
        sub_category s ON p.sub_category_id = s.id
    WHERE 1=1
";

if ($category) {
    $query .= " AND c.name LIKE '%$category%'";
}

if ($sub_category) {
    $query .= " AND s.name LIKE '%$sub_category%'";
}

$local_products_result = $db->query($query);
$local_products = $local_products_result->fetch_all(MYSQLI_ASSOC);

// Fetch existing assignments
$existing_assignments_result = $db->query("SELECT * FROM assigned_product_package");
while ($row = $existing_assignments_result->fetch_assoc()) {
    $existing_assignments[$row['product_id']] = $row['package_id'];
}

if (isset($_POST['clear_packages'])) {
    $db->query("TRUNCATE TABLE assigned_product_package");
    $db->query("DELETE FROM external_packages");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_package') {
    $product_id = (int)$_POST['product_id'];

    $db->begin_transaction();

    try {
        $result = $db->query("SELECT package_id FROM assigned_product_package WHERE product_id = $product_id");
        
        if ($result && $row = $result->fetch_assoc()) {
            $package_id = $row['package_id'];

            $delete_assign_query = "DELETE FROM assigned_product_package WHERE product_id = $product_id";
            if (!$db->query($delete_assign_query)) {
                throw new Exception('Failed to delete from assigned_product_package.');
            }

            if ($package_id) {
                $delete_external_query = "DELETE FROM external_packages WHERE id = $package_id";
                if (!$db->query($delete_external_query)) {
                    throw new Exception('Failed to delete from external_packages.');
                }
            }

            $db->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No package_id found for the specified product_id.' . $product_id);
        }
    } catch (Exception $e) {
        $db->rollback();

        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function fetch_packages($db) {
    $credentials_query = $db->query("SELECT id, base_url, dealer_code, username, password FROM api_credentials");
    while ($credentials = $credentials_query->fetch_assoc()) {
        
        $veri = array(
            'Operation' => 'TopUpPrices',
            'request' => array(
                'DealerCode' => $credentials['dealer_code'],
                'Username' => $credentials['username'],
                'Password' => $credentials['password'],
            )
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $credentials['base_url']);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($veri));
        
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);

        if (isset($result->TopUpPricesResult) && ($result->TopUpPricesResult->ResponseCode == 0000) && is_array($result->TopUpPricesResult->Packages)) {
            foreach ($result->TopUpPricesResult->Packages as $package) {
                $existing_package = $db->query("SELECT id FROM external_packages WHERE external_package_id = " . (int)$package->ProductId . " AND api_credentials_id = " . (int)$credentials['id']);
                if (mysqli_num_rows($existing_package) == 0) {
                    $insert_query = "INSERT INTO external_packages (external_package_id, operator, name, amount, api_credentials_id) 
                                     VALUES (" . (int)$package->ProductId . ", '" . $db->real_escape_string($package->Operator) . "', '" . $db->real_escape_string($package->PackageName) . "', " . (float)$package->Amount . ", " . (int)$credentials['id'] . ")";
                    $db->query($insert_query);
                }
            }
        }
    }

    // Fetch external packages with api_user
    $external_packages_result = $db->query("
        SELECT ep.*, ac.username AS api_user, ac.dealer_code AS dc 
        FROM external_packages ep 
        JOIN api_credentials ac ON ep.api_credentials_id = ac.id
    ");
    return $external_packages_result->fetch_all(MYSQLI_ASSOC);
}

$external_packages = fetch_packages($db);

// Handle package assignments
if (isset($_POST['assign_packages'])) {
    foreach ($_POST['package_assignments'] as $local_product_id => $external_package_id) {
        if ($external_package_id !== "none") {
            $product_id = (int)$local_product_id;
            $package_id = (int)$external_package_id;

            $package_data = $db->query("SELECT api_credentials_id FROM external_packages WHERE id = $package_id")->fetch_assoc();
            $api_credentials_id = (int)$package_data['api_credentials_id'];

            if (isset($existing_assignments[$product_id])) {
                $update_assign_query = "UPDATE assigned_product_package 
                                        SET package_id = $package_id, api_credentials_id = $api_credentials_id
                                        WHERE product_id = $product_id";
                $db->query($update_assign_query);
            } else {
                $insert_assign_query = "INSERT INTO assigned_product_package (product_id, package_id, api_credentials_id) 
                                        VALUES ($product_id, $package_id, $api_credentials_id)";
                $db->query($insert_assign_query);
            }
        } elseif (isset($existing_assignments[$local_product_id])) {
            $delete_assign_query = "DELETE FROM assigned_product_package 
                                    WHERE product_id = " . (int)$local_product_id;
            $db->query($delete_assign_query);
        }
    }

    echo "Packages assigned successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once "includes/header.php"; ?>
    <title>بسته ها</title>
</head>
<body>
    <?php require_once "menu.php"; ?>
    <div class="container-fluid">
        
        <h4>اتصال بسته ها</h4>
        <hr class="mt-0 mb-2">
        
        <div class="row">
            <div class="col-md-6">
                <form method="post">
                    <button type="submit" name="clear_packages" class="btn btn-danger mb-3">حذف بسته های متصل</button>
                </form>
            </div>
        </div>

        <form method="get" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category">دسته بندی</label>
                        <select name="category" id="category" class="form-control">
                            <option value="">انتخاب دسته بندی</option>
                            <?php
                            $categories_result = $db->query("SELECT id, name FROM category");
                            while ($category_row = $categories_result->fetch_assoc()) {
                                $selected = ($category_row['name'] == $category) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($category_row['name']) . "\" $selected>" . htmlspecialchars($category_row['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sub_category">زیر دسته بندی</label>
                        <select name="sub_category" id="sub_category" class="form-control">
                            <option value="">انتخاب زیر دسته بندی</option>
                            <?php
                            $sub_categories_result = $db->query("SELECT id, name FROM sub_category");
                            while ($sub_category_row = $sub_categories_result->fetch_assoc()) {
                                $selected = ($sub_category_row['name'] == $sub_category) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($sub_category_row['name']) . "\" $selected>" . htmlspecialchars($sub_category_row['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">فیلتر</button>
        </form>
           

        <?php if (!empty($local_products) && !empty($external_packages)): ?>
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>محصولات داخلی</th>
                                    <th>انتخاب بسته خارجی</th>
                                    <th>نام کاربری API</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($local_products as $product): ?>
                                    <tr id="row-<?= htmlspecialchars($product['id']); ?>">
                                        <td>
                                            <?= htmlspecialchars($product['category_name']); ?> &nbsp; <?= htmlspecialchars($product['sub_category_name']); ?> &nbsp; <?= htmlspecialchars($product['amount']); ?>
                                        </td>
                                        <td>
                                            <select name="package_assignments[<?= htmlspecialchars($product['id']); ?>]" class="form-control select2">
                                                <option value="none" <?= (!isset($existing_assignments[$product['id']]) ? 'selected' : ''); ?>>بدون اتصال</option>
                                                <?php foreach ($external_packages as $package): ?>
                                                    <option value="<?= htmlspecialchars($package['id']); ?>" <?= (isset($existing_assignments[$product['id']]) && $existing_assignments[$product['id']] == $package['id'] ? 'selected' : ''); ?>>
                                                        <?= htmlspecialchars($package['operator']); ?> &nbsp; <?= htmlspecialchars($package['name']); ?> (<?= htmlspecialchars($package['amount']); ?>) (<?= htmlspecialchars($package['api_user']); ?>) (<?= htmlspecialchars($package['dc']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <?php 
                                            // Find the assigned package and display the api_user
                                            $assigned_package_id = $existing_assignments[$product['id']] ?? null;
                                            $api_user = "";
                                            $api_user_dc = "";
                                            if ($assigned_package_id) {
                                                $package_info = array_filter($external_packages, function($pkg) use ($assigned_package_id) {
                                                    return $pkg['id'] == $assigned_package_id;
                                                });
                                                $api_user = !empty($package_info) ? reset($package_info)['api_user'] : "";
                                                $api_user_dc = !empty($package_info) ? "(" . reset($package_info)['dc']. ")" : "";
                                            }
                                            echo htmlspecialchars($api_user) . " " . htmlspecialchars($api_user_dc);
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-small delete-package-btn" data-product-id="<?= htmlspecialchars($product['id']); ?>">حذف</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="submit" name="assign_packages" class="btn btn-success mt-3">متصل کردن بسته ها</button>
            </form>
        <?php endif; ?>
    </div>

    <?php require_once "includes/footer.php"; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $(document).on('click', '.delete-package-btn', function(event) {
                event.preventDefault();
                const productId = $(this).data('product-id');
                const packageRow = $(this).closest('#row-' + productId);

                if (confirm('پکیج حذف شود؟')) {
                    $.ajax({
                        url: window.location.href,
                        method: 'POST',
                        data: {
                            action: 'delete_package',
                            product_id: productId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('پکیج موفقانه حذف شد');
                                packageRow.remove();
                            } else {
                                alert('خطا');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX request failed:", textStatus, errorThrown);
                            console.error("Response:", jqXHR.responseText);
                            alert('خطا');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>