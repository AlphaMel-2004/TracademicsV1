<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "MySQL connection successful\n";
    
    // Test if we can connect to the specific database
    $pdo_db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=tracademics', 'root', '');
    echo "Tracademics database connection successful\n";
    
    // Test a simple query
    $stmt = $pdo_db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users table accessible, count: " . $result['count'] . "\n";
    
} catch(Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
