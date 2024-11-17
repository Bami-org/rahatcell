<?php

class Products
{
    public $db;
    function __construct($dbCon)
    {
        $this->db = $dbCon;
        return $this->category();
    }


    public function category()
    {
        $catSql = $this->db->query("SELECT * FROM category");
        $categories = [];
        while ($catRow = $catSql->fetch_assoc()) {
            $categories[] = [
                "id" => $catRow["id"],
                "name" => $catRow["name"],
                "sub_category" => $this->subCategory($catRow["id"])
            ];
        }
        return json_encode($categories);
    }

    public function subCategory($id)
    {
        $subSql = $this->db->query("SELECT id,name,photo FROM sub_category WHERE up_category=$id");
        $subCategories = [];
        while ($subRow = $subSql->fetch_assoc()) {
            $subCategories[] = [
                "id" => $subRow["id"],
                "name" => $subRow["name"],
                "photo" => "uploads/category/" . $subRow["photo"],
                "products" => $this->products($subRow["id"])
            ];
        }
        return $subCategories;
    }

    public function  products($subId)
    {
        $currencyId = $this->db->clean_input($_POST["currency_id"]);
        $subSql = $this->db->query("SELECT product.id,product.amount,
    product.toman_sale_price as toman,
    product.dollar_sale_price as dollar,
    product.lyra_sale_price as lyra,
    product.euro_sale_price as euro,
    product.afghani_sale_price as afghani,
    units.name as unit,sub_category.photo as logo FROM product
LEFT JOIN units ON product.unit_id = units.id  
LEFT JOIN sub_category ON product.sub_category_id = sub_category.id  
WHERE sub_category_id=$subId");

        $cur = "تومن";
        $products = [];
        while ($proRow = $subSql->fetch_assoc()) {
            $price = $proRow["toman"];
            if ($currencyId == 2) {
                $cur = "دالر";
                $price = $proRow["dollar"];
            } else if ($currencyId == 3) {
                $cur = "لیر";
                $price = $proRow["lyra"];
            } elseif ($currencyId == 4) {
                $cur = "یورو";
                $price = $proRow["euro"];
            }
             elseif ($currencyId == 5) {
                $cur = "افغانی";
                $price = $proRow["afghani"];
            }
            $products[] = [
                "id" => $proRow["id"],
                "logo" => "uploads/category/" . $proRow["logo"],
                "amount" => $proRow["amount"],
                "price" => $price,
                "currency" => $cur,
                "unit" => $proRow["unit"]

            ];
        }
        return $products;
    }
}
