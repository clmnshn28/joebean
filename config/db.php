<?php

    $conn = new mysqli("localhost", "root", "", "joebean", 3307);

    if(!$conn){
        die(mysqli_error($conn));
    }

?>