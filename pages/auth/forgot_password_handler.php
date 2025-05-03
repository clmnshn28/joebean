<?php
    // Handle forgot password functionality with OTP verification
    include '../../config/db.php';
    session_start();
    header('Content-Type: application/json');

    // Function to send OTP email
    function sendOTPEmail($email, $otp) {
     
        // Simple env loading from root .env file
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                if (!empty($line) && strpos($line, '#') !== 0 && strpos($line, '=') !== false) {
                    putenv(trim($line));
                }
            }
        }
        // Gmail credentials
        $gmailUser = getenv('EMAIL_USERNAME');
        $gmailPassword = getenv('EMAIL_PASSWORD');
        
        $to = $email;
        $from =  $gmailUser;
        $fromName = "JoeBean System";
        $subject = "JoeBean - Password Reset OTP";
        
   
        // HTML content (keep your existing HTML)
        $htmlMessage = '
        <html>
        <head>
            <title>Password Reset OTP</title>
        </head>
        <body style="font-family: Poppins, sans-serif, Inter, Arial; max-width: 600px; margin: 0 auto;">
            <div style="padding: 20px; border: 3px solid rgba(101, 109, 74, 0.61); border-radius: 10px; background-color:rgba(194, 197, 170, 0.52);">
                <h2 style="color: #656D4A;">Password Reset Request</h2>
                <p style="color: rgb(0, 0, 0);">You have requested to reset your password for your JoeBean account.</p>
                <p style="color: rgb(0, 0, 0);">Your OTP code is:</p>
                <div style="background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 24px; letter-spacing: 5px; font-weight: bold; margin: 20px 0; border-radius: 10px; color: #656D4A;">
                    '.$otp.'
                </div>
                <p style="color: rgb(0, 0, 0);">This code will expire in <span style="font-weight:bold">5 minutes.</span></p>
                <p style="color: rgb(0, 0, 0);">If you did not request this password reset, please ignore this email.</p>
                <p style="color: rgb(0, 0, 0);">Thank you,<br> <span style="font-weight:bold">JoeBean Team</span></p>
            </div>
        </body>
        </html>
        ';
        
        $plainMessage = "Your OTP for password reset is: $otp\n\nThis code will expire in 5 minutes.";
        
        // Create email headers and body
        $boundary = md5(time());
        
        // Headers and body setup
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: $fromName <$from>\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($plainMessage)) . "\r\n";
        
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlMessage)) . "\r\n";
        
        $body .= "--$boundary--";
        
        // Try alternative approach - first try TLS on port 587
        try {
            error_log("Attempting to connect to SMTP server");
            
            // Try SSL first on port 465
            $smtp = @fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 30);
            
            // If SSL fails, try TLS on port 587
            if (!$smtp) {
                error_log("SSL connection failed, trying TLS: $errstr ($errno)");
                $smtp = @fsockopen('tls://smtp.gmail.com', 587, $errno, $errstr, 30);
            }
            
            // If all fails, try without encryption
            if (!$smtp) {
                error_log("TLS connection failed, trying plain: $errstr ($errno)");
                $smtp = @fsockopen('smtp.gmail.com', 25, $errno, $errstr, 30);
            }
            
            if (!$smtp) {
                error_log("All SMTP connection attempts failed: $errstr ($errno)");
                return false;
            }
            
            // Read server greeting
            $server_response = fgets($smtp, 515);
            error_log("Server greeting: " . trim($server_response));
            
            // Use a reliable hostname for EHLO - don't rely on SERVER_NAME
            $hostname = gethostname();
            if (empty($hostname)) $hostname = 'localhost';
            
            // Send EHLO
            fputs($smtp, "EHLO $hostname\r\n");
            $ehlo_response = fgets($smtp, 515);
            
            // Clear buffer - read all available lines
            while($line = fgets($smtp, 515)) {
                if (substr($line, 3, 1) == ' ') break;
            }
        
            // Auth
            fputs($smtp, "AUTH LOGIN\r\n");
            $auth_response = fgets($smtp, 515);
            
            // Username
            fputs($smtp, base64_encode($gmailUser) . "\r\n");
            $user_response = fgets($smtp, 515);
            
            // Password
            fputs($smtp, base64_encode($gmailPassword) . "\r\n");
            $pass_response = fgets($smtp, 515);
            
            if (substr($pass_response, 0, 3) != '235') {
                error_log("Authentication failed: " . trim($pass_response));
                fclose($smtp);
                return false;
            }
            
            // MAIL FROM
            fputs($smtp, "MAIL FROM: <$from>\r\n");
            $from_response = fgets($smtp, 515);
            
            // RCPT TO
            fputs($smtp, "RCPT TO: <$to>\r\n");
            $to_response = fgets($smtp, 515);
            
            // DATA
            fputs($smtp, "DATA\r\n");
            $data_response = fgets($smtp, 515);
            
            // Send headers and body
            fputs($smtp, "Subject: $subject\r\n");
            fputs($smtp, $headers . "\r\n");
            fputs($smtp, $body . "\r\n.\r\n");
            $send_response = fgets($smtp, 515);
            
            // QUIT
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);
            
            return (substr($send_response, 0, 3) == '250');
        } 
        catch (Exception $e) {
            error_log("Exception in SMTP process: " . $e->getMessage());
            return false;
        }
    }

    // Handle AJAX requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        // Step 1: Request OTP
        if ($action === 'request_otp') {
            $email = $_POST['email'] ?? '';
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
                exit;
            }
            
            // Check if email exists in the database
            $checkStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Email not found in our records']);
                exit;
            }
            
            // Generate OTP (6 digits)
            $otp = rand(100000, 999999);
            
            date_default_timezone_set('Asia/Manila');
            $now = new DateTime();
            // Set expiration time (5 minutes from now)
            $expiry = $now->modify('+5 minutes')->format('Y-m-d H:i:s');
            
            // Delete any existing OTPs for this email
            $deleteStmt = $conn->prepare("DELETE FROM otps WHERE email = ?");
            $deleteStmt->bind_param("s", $email);
            $deleteStmt->execute();
            
            // Insert new OTP into database
            $insertStmt = $conn->prepare("INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $email, $otp, $expiry);
            
            if ($insertStmt->execute()) {
                // Send email with OTP
                if (sendOTPEmail($email, $otp)) {
                    // Store email in session for later use
                    $_SESSION['reset_email'] = $email;
                    
                    // Return success with masked email
                    $masked_email = substr($email, 0, 2) . str_repeat('*', 5) . 
                                    substr($email, strpos($email, '@') - 2);
                    
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'OTP sent successfully',
                        'masked_email' => $masked_email
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP email']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to generate OTP']);
            }
        }
        
        // Step 2: Verify OTP
        else if ($action === 'verify_otp') {
            $otp = $_POST['otp'] ?? '';
            $email = $_SESSION['reset_email'] ?? '';
            
            if (empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'Session expired. Please try again']);
                exit;
            }
            
            // Check if OTP exists and is valid
            $verifyStmt = $conn->prepare("SELECT * FROM otps WHERE email = ? AND otp = ? AND expires_at > NOW()");
            $verifyStmt->bind_param("ss", $email, $otp);
            $verifyStmt->execute();
            $result = $verifyStmt->get_result();
            
            if ($result->num_rows > 0) {
                // OTP is valid
                $_SESSION['otp_verified'] = true;
                echo json_encode(['status' => 'success', 'message' => 'OTP verified successfully']);
            } else {
                // Check if OTP is expired
                $expiredStmt = $conn->prepare("SELECT * FROM otps WHERE email = ? AND otp = ? AND expires_at <= NOW()");
                $expiredStmt->bind_param("ss", $email, $otp);
                $expiredStmt->execute();
                $expiredResult = $expiredStmt->get_result();
                
                if ($expiredResult->num_rows > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
                }
            }
        }
        
        // Step 3: Reset Password
        else if ($action === 'reset_password') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $email = $_SESSION['reset_email'] ?? '';
            
            // Check if user is verified
            if (empty($email) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
                exit;
            }
            
            // Validate passwords match
            if ($newPassword !== $confirmPassword) {
                echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
                exit;
            }
            
            // Validate password strength
            if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || 
                !preg_match('/[a-z]/', $newPassword) || 
                !(preg_match('/[0-9]/', $newPassword) || preg_match('/[^A-Za-z0-9]/', $newPassword))) {
                echo json_encode(['status' => 'error', 'message' => 'Password does not meet requirements']);
                exit;
            }
            
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update user's password
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $email);
            
            if ($updateStmt->execute()) {
                // Clear OTP records for this email
                $deleteStmt = $conn->prepare("DELETE FROM otps WHERE email = ?");
                $deleteStmt->bind_param("s", $email);
                $deleteStmt->execute();
                
                // Clear session data
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                
                echo json_encode(['status' => 'success', 'message' => 'Password reset successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to reset password']);
            }
        }
        
        // Resend OTP
        else if ($action === 'resend_otp') {
            $email = $_SESSION['reset_email'] ?? '';
            
            if (empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'Session expired. Please try again']);
                exit;
            }
            
            // Generate new OTP
            $otp = rand(100000, 999999);
            
            // Set expiration time (5 minutes from now)
            $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            
            // Delete any existing OTPs for this email
            $deleteStmt = $conn->prepare("DELETE FROM otps WHERE email = ?");
            $deleteStmt->bind_param("s", $email);
            $deleteStmt->execute();
            
            // Insert new OTP into database
            $insertStmt = $conn->prepare("INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $email, $otp, $expiry);
            
            if ($insertStmt->execute()) {
                // Send email with OTP
                if (sendOTPEmail($email, $otp)) {
                    echo json_encode(['status' => 'success', 'message' => 'New OTP sent successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP email']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to generate OTP']);
            }
        }
        
        else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    } else {
        // Redirect to login page if accessed directly
        header("Location: admin_login.php");
        exit;
    }

?>