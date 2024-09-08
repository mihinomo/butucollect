<?php


class DB {

    public static $host = "localhost";
    public static $daily = "daily";
    public static $rd = "rd";

    public static $newdb = "newbutu";
    public static $user = "root";
    public static $pass = "mihinomo123";

    private static function con() {

        $pdo = new PDO("mysql:host=".self::$host.";dbname=".self::$newdb.";charset=utf8", self::$user, self::$pass);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    public static function query($query, $params = array()) {
        $stmt = self::con()->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        return $data;
    }

    public static function queryt($query, $params = array()) {
        $stmt = self::con()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public static function queryRow($query, $params = array()) {
        $stmt = self::con()->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        return $data[0];
    }

    private static $mysqli = null;

    private static function connect() {
        $host = 'localhost';
        $user = 'root';
        $pass = 'mihinomo123';
        $db = 'newbutu';

        self::$mysqli = new mysqli($host, $user, $pass, $db);

        if (self::$mysqli->connect_error) {
            die("Connection failed: " . self::$mysqli->connect_error);
        }

        // Set connection options for performance
        self::$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5); // Connection timeout
        self::$mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true); // Convert integers and floats to PHP types
    }

    public static function querySingleRow($sql, $params = []) {
        if (self::$mysqli === null) {
            self::connect();
        }
    
        // Prepare the SQL statement
        $stmt = self::$mysqli->prepare($sql);
    
        if (!$stmt) {
            die("Prepare failed: " . self::$mysqli->error);
        }
    
        // Bind parameters dynamically if provided
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Assuming all are strings for simplicity
            $stmt->bind_param($types, ...$params);
        }
    
        // Execute the statement
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
    
        // Get the result
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
    
        return $row;
    }

    public static function queryMultipleRows($sql) {
        if (self::$mysqli === null) {
            self::connect();
        }

        $result = self::$mysqli->query($sql);

        if (!$result) {
            die("Query failed: " . self::$mysqli->error);
        }

        $rows = $result->fetch_all(MYSQLI_ASSOC);

        $result->free(); // Free the result set

        return $rows;
    }

    public static function queryPreparedMultipleRows($sql, $params = []) {
        if (self::$mysqli === null) {
            self::connect();
        }
    
        $stmt = self::$mysqli->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . self::$mysqli->error);
        }
    
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Assuming all parameters are strings for simplicity
            $stmt->bind_param($types, ...$params);
        }
    
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
    
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
    
        return $rows;
    }

    public static function queryPrepared($sql, $params = [], $isSingleRow = false) {
        if (self::$mysqli === null) {
            self::connect();
        }
    
        $stmt = self::$mysqli->prepare($sql);
        if (!$stmt) {
            // Consider logging this error and returning a controlled response
            throw new Exception("Prepare failed: " . self::$mysqli->error);
        }
    
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Assuming all parameters are strings
            $stmt->bind_param($types, ...$params);
        }
    
        if (!$stmt->execute()) {
            // Consider logging this error and returning a controlled response
            throw new Exception("Execute failed: " . $stmt->error);
        }
    
        $result = $stmt->get_result();
        if ($isSingleRow) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        } else {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $rows;
        }
    }
    
    public static function insert($table, $data) {
        if (self::$mysqli === null) {
            self::connect();
        }
    
        // Prepare the SQL statement
        $sql = "INSERT INTO $table (" . implode(', ', array_keys($data)) . ") VALUES (" . implode(', ', array_fill(0, count($data), '?')) . ")";
    
        // Prepare the statement
        $stmt = self::$mysqli->prepare($sql);
        if (!$stmt) {
            // Return false if prepare fails
            return false;
        }
    
        // Bind parameters dynamically
        $types = '';
        foreach ($data as $value) {
            $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        }
    
        // Using an array of references workaround
        $params = [];
        foreach ($data as $key => $value) {
            $params[$key] = &$data[$key];
        }
    
        // Prepend $types to the $params array
        array_unshift($params, $types);
    
        // Call bind_param with a dynamic number of parameters
        call_user_func_array([$stmt, 'bind_param'], $params);
    
        // Execute the statement
        $result = $stmt->execute();
    
        // Close the statement
        $stmt->close();
        //var_dump($result);
    
        // Return the result of the execution
        return $result;
    }
    
    public static function update($table, $data, $condition) {
        if (self::$mysqli === null) {
            self::connect();
        }
    
        $setPart = [];
        foreach ($data as $key => $value) {
            $setPart[] = "$key = ?";
        }
        $setPartString = implode(', ', $setPart);
    
        $sql = "UPDATE $table SET $setPartString WHERE $condition";
    
        $stmt = self::$mysqli->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . self::$mysqli->error);
        }
    
        $types = str_repeat('s', count($data)); // Assuming all are strings for simplicity
    
        // Prepare an array of references
        $values = array_values($data);
        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
    
        // Prepend $types to the refs array
        array_unshift($refs, $types);
    
        // Use call_user_func_array to dynamically pass the parameters to bind_param
        call_user_func_array([$stmt, 'bind_param'], $refs);
    
        if (!$stmt->execute()) {
            return false;
        }
    
        $stmt->close();
        return true;
    }
    public static function logLogin($agentId) {
        if (self::$mysqli === null) {
            self::connect();
        }

        // Define the table and data array for logging
        $table = 'login_logs';
        $data = array(
            'agent_id' => $agentId,
            'login_time' => date('Y-m-d H:i:s')
        );

        // Insert data into the database using DB class
        self::insert($table, $data);
    }

    public static function queryPreparedIn($sql, array $params) {
        if (self::$mysqli === null) {
            self::connect();
        }
    
        // The array of values to be included in the IN clause
        $inParams = (array)$params; // Cast to array to ensure countability
        $inPlaceholders = implode(',', array_fill(0, count($inParams), '?'));
    
        // Replace the placeholder in the SQL with the correct number of parameter placeholders
        $sql = str_replace('IN (?)', 'IN (' . $inPlaceholders . ')', $sql);
    
        $stmt = self::$mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . self::$mysqli->error);
        }
    
        // Dynamic binding of parameters
        $types = str_repeat('s', count($inParams)); // Assuming all parameters are strings for simplicity
        $stmt->bind_param($types, ...$inParams);
    
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
    
        return $rows;
    }

    public static function insertBatch($table, array $data) {
        if (empty($data)) {
            return; // Nothing to insert
        }
    
        if (self::$mysqli === null) {
            self::connect();
        }
    
        // Extract column names from the first item of the data array
        $columns = array_keys(current($data));
        // Ensure column names are correctly formatted, especially if they might conflict with SQL keywords
        $columnsString = '`' . implode('`, `', $columns) . '`';
    
        // Prepare placeholders for each value in each row
        $allValues = []; // To collect all values for binding
        $typesString = ''; // To collect the types of all values
        $placeholders = [];
    
        foreach ($data as $row) {
            $rowPlaceholders = [];
            foreach ($row as $value) {
                $allValues[] = $value; // Collect values from each row
                $rowPlaceholders[] = '?'; // Placeholder for each value
    
                // Determine the type of the value
                if (is_int($value)) {
                    $typesString .= 'i'; // Integer
                } elseif (is_float($value)) {
                    $typesString .= 'd'; // Double
                } elseif (is_string($value)) {
                    $typesString .= 's'; // String
                } else {
                    $typesString .= 'b'; // Blob and others, fallback
                }
            }
            $placeholders[] = '(' . implode(',', $rowPlaceholders) . ')';
        }
    
        $placeholdersString = implode(', ', $placeholders);
        $sql = "INSERT INTO $table ($columnsString) VALUES $placeholdersString";
    
        $stmt = self::$mysqli->prepare($sql);
        if (!$stmt) {
            // Using mysqli_error() to get detailed error info from the mysqli connection
            throw new Exception("Prepare failed: " . self::$mysqli->error);
        }
    
        // Using call_user_func_array to bind the $typesString and all $allValues dynamically
        $bindNames = array_merge(array($typesString), $allValues);
        $refs = [];
        foreach ($bindNames as $i => &$bindName) {
            $refs[$i] = &$bindName; 
        }
        call_user_func_array(array($stmt, 'bind_param'), $refs);
    
        if (!$stmt->execute()) {
            // Again, using mysqli_error() for detailed error info
            throw new Exception("Execute failed: " . $stmt->error . ' Error: ' . mysqli_error(self::$mysqli));
        }
    
        $stmt->close();
    }

}
