<?php
include('dbcon.php');
include('myFunctions.php');
include('emailSMTP.php');
include('dataEncryption.php');
session_start();

/* User Registration statement */
if (isset($_POST['userRegisterBtn'])) {
    $veri_code = verificationCode();
    $acti_code = generateToken();

    $fname = mysqli_real_escape_string($con, $_POST['firstName']);
    $lname = mysqli_real_escape_string($con, $_POST['lastName']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phoneNum = mysqli_real_escape_string($con, $_POST['phoneNumber']);
    $uname = mysqli_real_escape_string($con, $_POST['username']);
    $uPass = mysqli_real_escape_string($con, $_POST['userPassword']);
    $uCPass = mysqli_real_escape_string($con, $_POST['userConfirmPassword']);
    $phonePatternPH = '/^09\d{9}$/';
    $emailPattern = '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_+])*@(([0-9a-zA-Z][-\w]*\.)+[a-zA-Z]{2,9})$/';
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,30}$/';

    $check_email_query = "SELECT user_email FROM users WHERE user_email = '$email'";
    $check_email_query_run = mysqli_query($con, $check_email_query);

    $check_uname_query = "SELECT user_username FROM users WHERE user_username = '$uname'";
    $check_uname_query_run = mysqli_query($con, $check_uname_query);

    $check_phoneNum_query = "SELECT user_phone FROM users WHERE user_phone = '$phoneNum'";
    $check_phoneNum_query_run = mysqli_query($con, $check_phoneNum_query);

    if (!preg_match($passwordPattern, $uPass)) {
        redirect("../views/register.php", "Password must be between 8 to 30 characters and must contain at least 1 uppercase letter, 1 lowercase letter, 1 number and 1 special character");
    } else if (!preg_match($phonePatternPH, $phoneNum)) {
        redirect("../views/register.php", "Invalid Philippine phone number format");
    } else if (!preg_match($emailPattern, $email)) {
        redirect("../views/register.php", "Invalid email format");
    } else if (mysqli_num_rows($check_email_query_run) > 0) {
        redirect("../views/register.php", "Email already in use try something different");
    } else if (mysqli_num_rows($check_uname_query_run) > 0) {
        redirect("../views/register.php", "username already in use try something different");
    } else if (mysqli_num_rows($check_phoneNum_query_run) > 0) {
        redirect("../views/register.php", "phone number already in use try something different");
    } else {
        if ($uPass == $uCPass) {

            //Hash Password
            $bcryptuPass = password_hash($uPass, PASSWORD_BCRYPT);

            //Insert User Data
            $insert_query = "INSERT INTO users (user_firstName, user_lastName, user_email, user_phone, user_username, user_password, user_otp, user_general_token)
                VALUES('$fname','$lname','$email','$phoneNum','$uname','$bcryptuPass', '$veri_code', '$acti_code')";
            $insert_query_run = mysqli_query($con, $insert_query);
            if ($insert_query_run) {

                /* Send OTP to EMAIL SMTP */
                $subj = "Your Verification Code from NoirceurCouture Account";
                send_otp($email, $subj, $veri_code, $fname, $lname);

                $hiddenEmail = hideEmailCharacters($email);

                $_SESSION['email'] = $hiddenEmail;
                header("Location: ../views/verifyAccount.php?tkn=$acti_code");
                $_SESSION['Successmsg'] = "Please check your email for Verification Code";
            } else {
                redirect("../views/register.php", "something went wrong");
            }
        } else {
            redirect("../views/register.php", "Passwords doesn't match");
        }
    }
}

if (isset($_POST['verifyBtn'])) {
    if (isset($_POST['tkn'])) {
        $acti_code = mysqli_real_escape_string($con, $_POST['tkn']);

        $code1 = mysqli_real_escape_string($con, $_POST['code1']);
        $code2 = mysqli_real_escape_string($con, $_POST['code2']);
        $code3 = mysqli_real_escape_string($con, $_POST['code3']);
        $code4 = mysqli_real_escape_string($con, $_POST['code4']);
        $code5 = mysqli_real_escape_string($con, $_POST['code5']);
        $code6 = mysqli_real_escape_string($con, $_POST['code6']);

        $veri_code = $code1 . $code2 . $code3 . $code4 . $code5 . $code6;

        $selectQuery = "SELECT * FROM users WHERE user_general_token = '$acti_code'";
        $result_check_acti_code = mysqli_query($con, $selectQuery);

        if (mysqli_num_rows($result_check_acti_code) > 0) {
            $row = mysqli_fetch_assoc($result_check_acti_code);

            $verification_code = $row['user_otp'];
            $accountCreated = $row['user_accCreatedAt'];

            if ($verification_code !== $veri_code) {
                header("Location: ../views/verifyAccount.php?tkn=$acti_code");
                $_SESSION['Errormsg'] = "Please provide the correct verification code";
            } else {
                $updateQuery = "UPDATE users SET user_otp = '', user_general_token = '', user_isVerified = '1' WHERE user_otp = '$veri_code' AND user_general_token = '$acti_code'";
                $resultUpdateQuery = mysqli_query($con, $updateQuery);
                if ($resultUpdateQuery) {

                    /* Send OTP to EMAIL SMTP */
                    $subj = "Congratulations! Your Account is Now Verified";
                    greetVerifiedUser($row['user_email'], $subj, $row['user_firstName'], $row['user_lastName']);

                    header("Location: ../views/login.php");
                    $_SESSION['Successmsg'] = "Your account has been activated. You can log in now.";
                } else {
                    header("Location: ../views/login.php");
                    $_SESSION['Errormsg'] = "Your account cannot be activated. Please try again.";
                }
            }
        }
    } else {
        echo "Activation code didn't get";
    }
}

if (isset($_POST['resendCodeBtn'])) {

    $veri_code = verificationCode();
    $acti_code = mysqli_real_escape_string($con, $_POST['tkn']);
    //Insert User Data
    $insert_query = "UPDATE users SET user_otp = '$veri_code' WHERE user_general_token = '$acti_code'";
    $insert_query_run = mysqli_query($con, $insert_query);
    if ($insert_query_run) {
        $selectQuery = "SELECT * FROM users WHERE user_general_token = '$acti_code'";
        $result_check_acti_code = mysqli_query($con, $selectQuery);
        $row = mysqli_fetch_assoc($result_check_acti_code);

        $email = $row['user_email'];
        $subj = "Resending Your Verification Code from NoirceurCouture Account";
        $fname = $row['user_firstName'];
        $lname = $row['user_lastName'];


        send_otp($email, $subj, $veri_code, $fname, $lname);

        header("Location: ../views/verifyAccount.php?tkn=$acti_code");
        $_SESSION['Successmsg'] = "New verification code has been sent to your email";
    }
}

if (isset($_POST['resetSendLink'])) {

    $reset_token = generateToken();

    $email = mysqli_real_escape_string($con, $_POST['emailInput']);
    $resetPassUrl = mysqli_real_escape_string($con, $_POST['resetPassUrl']);

    $selectQuery = "SELECT * FROM users WHERE user_email = '$email' AND user_isVerified = '1' AND user_role != '1'";
    $check_email_query = mysqli_query($con, $selectQuery);
    $row = mysqli_fetch_assoc($check_email_query);
    if (!empty($row['user_general_token'])) {
        header("Location: ../views/reset.php");
        $_SESSION['Errormsg'] = "Reset Password link has already been sent to your email";
    } elseif (mysqli_num_rows($check_email_query) > 0) {
        $subject = "Reset Password Link for your NoirceurCouture Account";
        $fname = $row['user_firstName'];
        $lname = $row['user_lastName'];
        $resetPassUrl .= "?tkn=" . $reset_token;

        //Insert User Data
        $insert_query = "UPDATE users SET user_general_token = '$reset_token' WHERE user_email = '$email'";
        $insert_query_run = mysqli_query($con, $insert_query);
        if ($insert_query_run) {
            userResetPassword($email, $subject, $fname, $lname, $resetPassUrl);

            header("Location: ../views/login.php");
            $_SESSION['Successmsg'] = "Reset password link has been sent to your email";
        }
    } else {
        header("Location: ../views/reset.php");
        $_SESSION['Errormsg'] = "No account found with this email";
    }
}

if (isset($_POST['resendResetSendLink'])) {

    $reset_token = generateToken();

    $email = mysqli_real_escape_string($con, $_POST['emailInput']);
    $resetPassUrl = mysqli_real_escape_string($con, $_POST['resetPassUrl']);

    $selectQuery = "SELECT * FROM users WHERE user_email = '$email' AND user_isVerified = '1'";
    $check_email_query = mysqli_query($con, $selectQuery);
    $row = mysqli_fetch_assoc($check_email_query);

    if (mysqli_num_rows($check_email_query) > 0) {
        $subject = "Resending Your Reset Password Link for your NoirceurCouture Account";
        $fname = $row['user_firstName'];
        $lname = $row['user_lastName'];
        $resetPassUrl .= "?tkn=" . $reset_token;


        //Insert User Data
        $insert_query = "UPDATE users SET user_general_token = '$reset_token' WHERE user_email = '$email'";
        $insert_query_run = mysqli_query($con, $insert_query);
        if ($insert_query_run) {
            userResetPassword($email, $subject, $fname, $lname, $resetPassUrl);

            header("Location: ../views/login.php");
            $_SESSION['Successmsg'] = "New Reset password link has been sent to your email";
        }
    } else {
        header("Location: ../views/reset.php");
        $_SESSION['Errormsg'] = "No account found with this email";
    }
}

if (isset($_POST['resetPassBtn'])) {
    $reset_token = mysqli_real_escape_string($con, $_POST['tkn']);

    $uPass = mysqli_real_escape_string($con, $_POST['NewPasswordInput']);
    $uCPass = mysqli_real_escape_string($con, $_POST['ConfirmPasswordInput']);

    $selectQuery = "SELECT user_password FROM users WHERE user_general_token = '$reset_token'";
    $check_pass_query = mysqli_query($con, $selectQuery);
    $row = mysqli_fetch_assoc($check_pass_query);

    $bcryptOldPass = $row['user_password'];

    if (password_verify($uPass, $bcryptOldPass)) {
        header("Location: ../views/resetPassword.php?tkn=$reset_token");
        $_SESSION['Errormsg'] = "New password cannot be the same as old password";
        exit;
    } else if ($uPass != $uCPass) {
        header("Location: ../views/resetPassword.php?tkn=$reset_token");
        $_SESSION['Errormsg'] = "Passwords doesn't match";
        exit;
    } else {
        //Hash Password
        $bcryptnewPass = password_hash($uPass, PASSWORD_BCRYPT);

        //Update Password
        $update_query = "UPDATE users SET user_password = '$bcryptnewPass', user_general_token = '' WHERE user_general_token = '$reset_token'";
        $update_query_run = mysqli_query($con, $update_query);
        if ($update_query_run) {
            header("Location: ../views/login.php");
            $_SESSION['Successmsg'] = "Your password has been changed. You can log in now";
        } else {
            header("Location: ../views/login.php");
            $_SESSION['Errormsg'] = "Error updating your password. Please try again later or contact our support.";
        }
    }
}

/* Seller Registration statement */
if (isset($_POST['sellerRegisterBtn'])) {
    $fname = mysqli_real_escape_string($con, $_POST['firstName']);
    $lname = mysqli_real_escape_string($con, $_POST['lastName']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phoneNum = mysqli_real_escape_string($con, $_POST['phoneNumber']);
    $uname = mysqli_real_escape_string($con, $_POST['username']);
    $brandName = mysqli_real_escape_string($con, $_POST['brandName']);
    $uPass = mysqli_real_escape_string($con, $_POST['userPassword']);
    $uCPass = mysqli_real_escape_string($con, $_POST['userConfirmPassword']);
    $role = 2;/* 0 = buyer, 1 = admin, 2 seller */
    $phonePatternPH = '/^09\d{9}$/';
    $sellerType = isset($_POST['sellerType']) ? mysqli_real_escape_string($con, $_POST['sellerType']) : 'individual';
    $emailPattern = '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_+])*@(([0-9a-zA-Z][-\w]*\.)+[a-zA-Z]{2,9})$/';

    $check_uname_query = "SELECT user_username FROM users WHERE user_username = '$uname'";
    $check_uname_query_run = mysqli_query($con, $check_uname_query);

    $check_brand_query = "SELECT category_name FROM categories WHERE category_name = '$brandName'";
    $check_brand_query_run = mysqli_query($con, $check_brand_query);

    $check_phoneNum_query = "SELECT user_phone FROM users WHERE user_phone = '$phoneNum'";
    $check_phoneNum_query_run = mysqli_query($con, $check_phoneNum_query);

    if (!preg_match($phonePatternPH, $phoneNum)) {
        redirect("../seller/seller-registration.php", "Invalid Philippine phone number format");
    } else if (mysqli_num_rows($check_uname_query_run) > 0) {
        redirect("../seller/seller-registration.php", "Username already in use try something different");
    } else if (mysqli_num_rows($check_brand_query_run) > 0) {
        redirect("../seller/seller-registration.php", "Brand Name already in use try something different");
    } else if (mysqli_num_rows($check_phoneNum_query_run) > 0) {
        redirect("../seller/seller-registration.php", "Phone Number already in use try something different");
    } else {
        if ($uPass == $uCPass) {
            //Hash Password
            $bcryptuPass = password_hash($uPass, PASSWORD_BCRYPT);
            $reset_token = "";
            // Prepare and bind the parameters
            $stmt = $con->prepare("UPDATE users SET user_general_token = ?, user_firstName = ?, user_lastName = ?, user_phone =?, user_username = ?, user_password = ?, user_role = ? WHERE user_email = ?");
            $stmt->bind_param("ssssssis", $reset_token, $fname, $lname, $phoneNum, $uname, $bcryptuPass, $role, $email);

            if ($stmt->execute()) {
                // Get the last inserted user ID of user
                $selectQuery = "SELECT * FROM users WHERE user_email = '$email'";
                $select_email_Query = mysqli_query($con, $selectQuery);
                $row = mysqli_fetch_assoc($select_email_Query);

                // Insert into users_seller_details
                $seller_details_query = "INSERT INTO users_seller_details (seller_user_ID, seller_seller_type) VALUES ('$row[user_ID]', '$sellerType')";
                $seller_details_query_run = mysqli_query($con, $seller_details_query);

                $slug = $brandName;
                $slug = strtolower($slug);
                $slug = preg_replace('/[^a-zA-Z0-9]/', '', $slug);
                $slug = str_replace(' ', '', $slug);

                // Insert into users_seller_details
                $category_details_query = "INSERT INTO categories (category_user_ID, category_name, category_slug, category_onVacation) VALUES ('$row[user_ID]', '$brandName', '$slug', '1')";
                $category_details_query_run = mysqli_query($con, $category_details_query);

                if ($seller_details_query_run && $category_details_query_run) {
                    // Redirect with success message
                    
                    redirectSwal("../views/login.php", "Seller account added. Wait for administrator confirmation.", "success");
                } else {
                    // Handle the error and redirect
                    redirect("../seller/seller-registration.php", "Error inserting seller details: " . mysqli_error($con));
                }
            } else {
                redirect("../seller/seller-registration.php", "something went wrong");
            }
        } else {
            redirect("../seller/seller-registration.php", "Passwords doesn't match");
        }
    }
}

if (isset($_POST['joinSellerConfirmEmail'])) {
    $acti_code = generateToken();
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $role = 2;/* 0 = buyer, 1 = admin, 2 seller */
    $emailPattern = '/^[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_+])*@(([0-9a-zA-Z][-\w]*\.)+[a-zA-Z]{2,9})$/';

    $check_email_query = "SELECT user_email FROM users WHERE user_email = '$email'";
    $check_email_query_run = mysqli_query($con, $check_email_query);

    if (!preg_match($emailPattern, $email)) {
        redirect("../views/index.php", "Invalid email format");
    } else if (mysqli_num_rows($check_email_query_run) > 0) {
        redirect("../views/index.php", "Email already in use try something different");
    } else {

        //Insert User Data
        $insert_query = "INSERT INTO users (user_email, user_role, user_general_token) VALUES('$email','$role','$acti_code')";

        $insert_query_run = mysqli_query($con, $insert_query);
        if ($insert_query_run) {
            $subj = "Your Seller Registration Link from NoirceurCouture Account";
            $baseUrl = "http://localhost/NoirceurCouture";
            $pageUrl = "/seller/seller-registration.php";
            $registrationUrl = $baseUrl .= $pageUrl .= "?tkn=" . $acti_code;
            sellerRegistration($email, $subj, $registrationUrl);
            redirectSwal("../views/index.php", "Check your email for registration link", "success");
        } else {
            redirect("../views/index.php", "something went wrong");
        }
    }
}

/* User Log in statement */
if (isset($_POST['loginBtn'])) {
    $loginInput = mysqli_real_escape_string($con, $_POST['loginInput']);
    $loginPass = mysqli_real_escape_string($con, $_POST['loginPasswordInput']);

    $login_Inputs_query = "SELECT u.*, s.seller_confirmed FROM users u
        LEFT JOIN users_seller_details s ON u.user_ID = s.seller_user_ID
        WHERE 
        (u.user_email = '$loginInput')
        OR (u.user_username = '$loginInput')
        OR (u.user_phone = '$loginInput')";
    $login_Inputs_query_run = mysqli_query($con, $login_Inputs_query);

    if (mysqli_num_rows($login_Inputs_query_run) > 0) {
        $userdata = mysqli_fetch_array($login_Inputs_query_run);

        $bcryptuPass = $userdata['user_password'];
        $userid = $userdata['user_ID'];
        $userRole = $userdata['user_role'];
        $isAccountBanned = $userdata['user_isBan'];
        $isAccountVerified = $userdata['user_isVerified'];

        if (!password_verify($loginPass, $bcryptuPass)) {
            redirect('../views/login.php', 'Invalid Credentials. Try again');
        } else if ($isAccountBanned == 1) {
            redirect('../views/login.php', 'This account has been banned permanently');
        } else if ($isAccountVerified != 1) {
            redirect('../views/login.php', 'This account is not verified yet');
        } else {
            $_SESSION['auth'] = true;

            $username = $userdata['user_username'];
            $fname = $userdata['user_firstName'];
            $useremail = $userdata['user_email'];
            $sellerConfirmed = $userdata['seller_confirmed'];

            $_SESSION['auth_user'] = [
                'user_ID' => $userid,
                'user_username' => $username,
                'user_firstName' => $fname,
                'user_email' => $useremail,
                'seller_confirmed' => $sellerConfirmed,
                'user_role' => $userRole,
                'user_isBan' => $isAccountBanned
            ];

            $_SESSION['user_role'] = $userRole;
            $_SESSION['seller_confirmed'] = $sellerConfirmed;
            $_SESSION['user_isBan'] = $isAccountBanned;

            if ($userRole == 1) {
                header('Location: ../admin/index.php');
            } else if ($userRole == 2) {
                $check_address_query = "SELECT * FROM addresses WHERE address_user_ID = '$userid' ";
                $check_address_query_run = mysqli_query($con, $check_address_query);

                if (mysqli_num_rows($check_address_query_run) == 0) {
                    redirectSwal('../seller/account-details.php', 'Add your pickup address first', 'warning');
                } else {
                    header('Location: ../seller/index.php');
                }
            } else if ($userRole == 0) {
                header('Location: ../views/index.php');
            }
        }
    } else {
        redirect('../views/login.php', 'Invalid Credentials. Try again');
    }
}

/* User Address Add statement */
if (isset($_POST['userAddAddrBtn'])) {
    $userId = $_POST['userID'];
    $fullN = $_POST['fullName'];
    $email = $_POST['email'];
    $phoneNum = $_POST['phoneNumber'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $fullAddr = $_POST['fullAddress'];

    $encryptedfullAddr = encryptData($fullAddr);

    // Check if there is already an address for the user
    $stmt_check_address = $con->prepare("SELECT address_id FROM addresses WHERE address_user_ID = ?");
    $stmt_check_address->bind_param("i", $userId);
    $stmt_check_address->execute();
    $result_check_address = $stmt_check_address->get_result();

    // Determine whether to set the address as default
    $addrDefault = ($result_check_address->num_rows == 0 || isset($_POST['defaultAddr'])) ? '1' : '0';

    // Check phone pattern
    $phonePatternPH = '/^09\d{9}$/';

    if (!preg_match($phonePatternPH, $phoneNum)) {
        if (isset($_POST['checkoutPage'])) {
            header("Location: " . $_POST['checkoutPage']);
            $_SESSION['Errormsg'] = "Invalid Philippine phone number format";
            exit;
        } else {
            header("Location: ../views/myAddress.php");
            $_SESSION['Errormsg'] = "Invalid Philippine phone number format";
            exit;
        }
    } else {
        // If there is a default address and $addrDefault is 1, update its address_isDefault value to 0
        if ($addrDefault == '1') {
            $stmt_update_default = $con->prepare("UPDATE addresses SET address_isDefault = 0 WHERE address_user_ID = ? AND address_isDefault = 1");
            $stmt_update_default->bind_param("i", $userId);
            $stmt_update_default->execute();
            $stmt_update_default->close();
        }

        // Prepare and bind the parameters for inserting a new address
        $stmt = $con->prepare("INSERT INTO addresses (address_user_ID, address_isDefault, address_fullName, address_email, address_region, address_province, address_city, address_barangay, address_phone, address_fullAddress)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssss", $userId, $addrDefault, $fullN, $email, $region, $province, $city, $barangay, $phoneNum, $encryptedfullAddr);

        if ($stmt->execute()) {
            // Redirect to the checkout page
            if (isset($_POST['checkoutPage'])) {
                header("Location: " . $_POST['checkoutPage']);
            } else {
                // Default redirection if checkoutPage is not set
                header("Location: ../views/myAddress.php");
            }
            $_SESSION['Successmsg'] = "Address added successfully";
        } else {
            // Redirect to the checkout page
            if (isset($_POST['checkoutPage'])) {
                header("Location: " . $_POST['checkoutPage']);
            } else {
                // Default redirection if checkoutPage is not set
                header("Location: ../views/myAddress.php");
            }
            $_SESSION['Errormsg'] = "Something went wrong";
        }
        $stmt->close();
    }
}

/* User Address Update statement */
if (isset($_POST['userUpdateAddrBtn'])) {
    $addrId = $_POST['updateAddrID'];
    $userId = $_POST['updateuserID'];
    $fullN = $_POST['fullName'];
    $email = $_POST['email'];
    $phoneNum = $_POST['phoneNumber'];
    $fullAddr = $_POST['fullAddress'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];

    $encryptedfullAddr = encryptData($fullAddr);

    //Check if address_isDefault existing
    $stmt_check_isDefault = $con->prepare("SELECT address_id, address_isDefault FROM addresses WHERE address_id = ?");
    $stmt_check_isDefault->bind_param("i", $addrId);
    $stmt_check_isDefault->execute();
    $result_check_isDefault = $stmt_check_isDefault->get_result();
    $row = $result_check_isDefault->fetch_assoc();

    //Look whether to set the address as default
    if ($row['address_isDefault'] == 0 && isset($_POST['defaultAddr'])) {
        $addrDefault = 1; //*set current address as the default address

        //*update prev default addres sto be non-default
        $stmt_update_default = $con->prepare("UPDATE addresses SET address_isDefault = 0 WHERE address_user_ID = ? AND address_isDefault = 1");
        $stmt_update_default->bind_param("i", $userId);
        $stmt_update_default->execute();
        $stmt_update_default->close();
    } else {
        $addrDefault = $row['address_isDefault']; //!keep address_isDefault on default state
    }

    $phonePatternPH = '/^09\d{9}$/';

    if (!preg_match($phonePatternPH, $phoneNum)) {
        header("Location: ../views/myAddressEdit.php?addrID=$addrId");
        $_SESSION['Errormsg'] = "Invalid Philippine phone number format";
    } else {
        // Prepare and bind the parameters
        $stmt = $con->prepare("UPDATE addresses SET address_fullName = ?, address_email = ?, address_region =?, address_province = ?, address_city = ?, address_barangay = ?, address_phone = ?, address_fullAddress = ?, address_isDefault = ? WHERE address_id = ?");
        $stmt->bind_param("sssssssssi", $fullN, $email, $region, $province, $city, $barangay, $phoneNum, $encryptedfullAddr, $addrDefault, $addrId);

        if ($stmt->execute()) {
            header("Location: ../views/myAddressEdit.php?addrID=$addrId");
            $_SESSION['Successmsg'] = "Address updated successfully";
        } else {
            header("Location: ../views/myAddressEdit.php?addrID=$addrId");
            $_SESSION['Errormsg'] = "Something went wrong";
        }
        $stmt->close();
    }
}

/* User set Default Address */
if (isset($_POST['setDefaultAddrBtn'])) {
    $addrId = $_POST['addrID'];
    $userId = $_POST['userID'];

    // Check if there is already a default address for the user
    $stmt_check_default = $con->prepare("SELECT address_id FROM addresses WHERE address_user_ID = ? AND address_isDefault = 1");
    $stmt_check_default->bind_param("i", $userId);
    $stmt_check_default->execute();
    $result_check_default = $stmt_check_default->get_result();

    // If there is a default address, update its address_isDefault value to 0
    if ($result_check_default->num_rows > 0) {
        $row = $result_check_default->fetch_assoc();
        $defaultAddrId = $row['address_id'];

        // Update the existing default address
        $stmt_update_default = $con->prepare("UPDATE addresses SET address_isDefault = 0 WHERE address_id = ?");
        $stmt_update_default->bind_param("i", $defaultAddrId);
        $stmt_update_default->execute();
        $stmt_update_default->close();
    }

    // Set the new address as default
    $stmt_set_default = $con->prepare("UPDATE addresses SET address_isDefault = 1 WHERE address_id = ?");
    $stmt_set_default->bind_param("i", $addrId);
    if ($stmt_set_default->execute()) {
        $_SESSION['Successmsg'] = "Address set as Default shipping address";
    } else {
        $_SESSION['Errormsg'] = "Something went wrong";
    }

    // Close statements
    $stmt_check_default->close();
    $stmt_set_default->close();

    // Redirect to myAddress.php
    header("Location: ../views/myAddress.php");
    exit(); // Terminate script execution
}

/* User Delete Address */
if (isset($_POST['deleteAddrBtn'])) {
    $addrId = $_POST['deleteAddrID'];
    $userId = $_POST['deleteAddruserID'];

    // Proceed with deletion
    $stmt_delete_address = $con->prepare("DELETE FROM addresses WHERE address_id = ?");
    $stmt_delete_address->bind_param("i", $addrId);
    if ($stmt_delete_address->execute()) {
        $_SESSION['Successmsg'] = "Address has been deleted";
    } else {
        $_SESSION['Errormsg'] = "Failed to delete address";
    }
    // Close $stmt_delete_address only if it's initialized
    if (isset($stmt_delete_address)) {
        $stmt_delete_address->close();
    }

    // Redirect to myAddress.php
    header("Location: ../views/myAddress.php");
    exit(); // Terminate script execution
}
