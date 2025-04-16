<?php

    include_once 'db.php'; 

    // Function to check if username exists
    function usernameExists($conn, $username) {
        $result = $conn->query("SELECT id FROM users WHERE username = '$username'");
        return $result->num_rows > 0;
    }

    // Function to get user ID by username
    function getUserId($conn, $username) {
        $result = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        return null;
    }


    echo "<h2>Adding Default Users</h2>";

    // Default admin data
    $adminUsername = "admin";
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

    // Only add admin if it doesn't exist
    if (!usernameExists($conn, $adminUsername)) {
        $sql = "INSERT INTO users (username, password, firstname, lastname, middlename, gender, birth_day, birth_month, birth_year, image, role,admin_id, created_at) 
                VALUES ('$adminUsername', '$adminPassword', 'System', 'Administrator', 'AdSys', 'male', 1, 1, 2000, 'default.jpg', 'admin', NULL, NOW())";
        
        if ($conn->query($sql) === TRUE) {
            echo "Admin user created successfully<br>";
        } else {
            echo "Error creating admin user: " . $conn->error . "<br>";
        }
    } else {
        echo "Admin user already exists<br>";
    }



    // Default cashier data
    $cashierUsername = "cashier";
    $cashierPassword = password_hash("cashier123", PASSWORD_DEFAULT);

    $adminId = getUserId($conn, $adminUsername);

    // Only add cashier if it doesn't exist
    if (!usernameExists($conn, $cashierUsername)) {
        $sql = "INSERT INTO users (username, password, firstname, lastname, middlename, gender, birth_day, birth_month, birth_year, image, role,admin_id, created_at) 
                VALUES ('$cashierUsername', '$cashierPassword', 'Default', 'Cashier', 'CasSys', 'female', 1, 1, 2000, 'default.jpg', 'cashier', $adminId, NOW())";
        
        if ($conn->query($sql) === TRUE) {
            echo "Cashier user created successfully<br>";
        } else {
            echo "Error creating cashier user: " . $conn->error . "<br>";
        }
    } else {
        echo "Cashier user already exists<br>";
    }

    echo "<p>Database setup complete!</p>";

    // Close connection
    $conn->close();

?>