<?php

date_default_timezone_set("Asia/Kabul");

class DB
{
    // database connection
    public $con;
    private $transactionStarted = false;
    public function __construct($host, $user, $pass, $db)
    {
        $this->con = mysqli_connect($host, $user, $pass, $db);
        $this->con->set_charset("utf8");
        if (mysqli_connect_error()) {
            trigger_error("Failed to connect to Database: " . mysqli_connect_error());
        }
    }
    public function __destruct()
    {
        mysqli_close($this->con);
    }

    public function real_escape_string($string)
    {
        return $this->con->real_escape_string($string);
    }

    public function curDate()
    {
        return date('Y-m-d h:i:s');
    }

    public function query($query)
    {
        return $this->con->query($query);
    }
    public function beginTransaction()
    {
        if (!$this->transactionStarted) {
            mysqli_begin_transaction($this->con);
            $this->transactionStarted = true;
        }
    }

    public function commit()
    {
        if ($this->transactionStarted) {
            mysqli_commit($this->con);
            $this->transactionStarted = false;
        }
    }

    public function rollback()
    {
        if ($this->transactionStarted) {
            mysqli_rollback($this->con);
            $this->transactionStarted = false;
        }
    }

    // insert row in table
    public function insert($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $query = mysqli_query($this->con, "INSERT INTO $table($columns) VALUES($values)");
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    // read rows from table
    public function read($table, $condition = null)
    {
        $query = "SELECT * FROM $table";
        if ($condition != null) {
            $query .= " WHERE $condition";
        }
        $result = mysqli_query($this->con, $query);
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    // update row from table
    public function update($table, $data, $condition = null)
    {
        $set = array();
        foreach ($data as $column => $value) {
            $set[] = "$column = '$value'";
        }
        $set = implode(",", $set);
        if ($condition != null) {
            if (mysqli_query($this->con, "UPDATE $table SET $set WHERE $condition"))
                return true;
            else
                return false;
        } else {
            if (mysqli_query($this->con, "UPDATE $table SET $set"))
                return true;
            else
                return false;
        }
    }

    // delete row from table
    public function delete($table, $condition)
    {
        $query = mysqli_query($this->con, "DELETE FROM $table WHERE $condition");
        if ($query)
            return true;
        else
            return false;
    }

    public function today()
    {
        return gregorian_to_jalali(date('Y'), date('m'), date('d'), '-');
    }

    public function toShamsi($date)
    {
        $get_date = explode("-", $date);
        $coDate = gregorian_to_jalali($get_date[0], $get_date[1], $get_date[2], "-");
        return $coDate;
    }
    public function toMiladi($date)
    {
        $get_date = explode("-", $date);
        $coDate = jalali_to_gregorian($get_date[0], $get_date[1], $get_date[2], "-");
        return $coDate;
    }
    public function convertFullDate($date, $type = 'miladi')
    {
        if ($type == 'shamsi') {
            $parts = explode(" ", $date);
            $p = explode("-", $parts[0]);
            return gregorian_to_jalali($p[0], $p[1], $p[2], "-") . " " . $parts[1];
        } else {
            return $date;
        }
    }
    // count rows from table
    public function row_count($table)
    {
        return mysqli_num_rows($this->con->query("SELECT * FROM $table"));
    }

    // get total sum of a column in a row of table
    public function sum($table, $column)
    {
        $sum = mysqli_fetch_assoc($this->con->query("SELECT SUM($column) AS total FROM $table"));
        return $sum["total"];
    }
    // clean user input
    public function clean_input($string)
    {
        $string = htmlspecialchars($string);
        $string = htmlentities($string);
        $string = mysqli_real_escape_string($this->con, $string);
        return $string;
    }
    // set location fro header
    public function route($route)
    {
        header_remove();
        header("location: $route");
    }

    // get sum of a column where day date = today
    public function day_sum($table, $sum_column, $date_column)
    {
        $today = mysqli_fetch_assoc(mysqli_query($this->con, "SELECT SUM($sum_column) as total FROM $table WHERE DAY($date_column) = DAY(NOW())"));
        if ($today["total"] != null) {
            return $today["total"];
        } else {
            return 0;
        }
    }

    public function fetch_row($query)
    {
        $result = $this->con->query($query);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row;
        }
        return null;
    }

    public function show_err()
    {
        print_r(mysqli_error($this->con));
        exit;
    }
}
require_once "jdf.php";
$db = new DB("localhost", "root", "", "rahatcell");

session_start();
require_once "error_log.php";
$today = gregorian_to_jalali(date("Y"), date("m"), date("d"), "/");
$setting = mysqli_fetch_assoc($db->query("SELECT * FROM setting"));