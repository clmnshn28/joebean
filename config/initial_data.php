<?php

    include_once 'db.php'; 

    // Function to check if username exists
    function usernameExists($conn, $username) {
        $result = $conn->query("SELECT id FROM users WHERE username = '$username'");
        return $result->num_rows > 0;
    }

    echo "<h2>Adding Default Users</h2>";

    // Default admin data
    $adminUsername = "admin";
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

    // Only add admin if it doesn't exist
    if (!usernameExists($conn, $adminUsername)) {
        $sql = "INSERT INTO users (username, password, firstname, lastname, middlename, gender, birth_day, birth_month, birth_year, image, role, created_at) 
                VALUES ('$adminUsername', '$adminPassword', 'System', 'Administrator', 'AdSys', 'male', 1, 1, 2000, 'uploads/default.png', 'admin', NOW())";
        
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

    // Only add cashier if it doesn't exist
    if (!usernameExists($conn, $cashierUsername)) {
        $sql = "INSERT INTO users (username, password, firstname, lastname, middlename, gender, birth_day, birth_month, birth_year, image, role, created_at) 
                VALUES ('$cashierUsername', '$cashierPassword', 'Default', 'Cashier', 'CasSys', 'female', 1, 1, 2000, 'uploads/default.png', 'cashier', NOW())";
        
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