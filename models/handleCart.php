<?php
session_start();
include('dbcon.php');

if (isset($_SESSION['auth'])) {
    if (isset($_POST['scope'])) {
        $scope = $_POST['scope'];
        switch ($scope) {
            case "add":
                $user_id = $_SESSION['auth_user']['user_ID'];
                $prod_id = $_POST['product_id'];
                $prod_qty = $_POST['product_qty'];
                $prod_rmn = $_POST['product_rmn'];
                $prod_slug = $_POST['product_slug'];
                $categ_id = $_POST['category_id'];

                $chk_existing_cart = "SELECT * FROM carts WHERE product_id='$prod_id' AND user_ID='$user_id' ";
                $chk_existing_cart_run = mysqli_query($con, $chk_existing_cart);

                if (mysqli_num_rows($chk_existing_cart_run) > 0) {
                    echo "existing";
                } else {
                    // Check if requested quantity is greater than remaining quantity
                    if ($prod_qty > $prod_rmn) {
                        // Quantity exceeds remaining quantity
                        echo "qtyerr";
                    } else {
                        // Check if product_qty is greater than 0
                        if ($prod_qty > 0) {
                            // Check if requested quantity exceeds remaining quantity
                            if ($prod_qty > $prod_rmn) {
                                echo "qtyerr"; // Quantity exceeds remaining quantity
                            } else {
                                // Insert into the cart
                                $insert_query = "INSERT INTO carts(category_id, user_ID, product_id, product_qty, product_slug) VALUES ('$categ_id','$user_id','$prod_id','$prod_qty','$prod_slug')";
                                $insert_query_run = mysqli_query($con, $insert_query);

                                if ($insert_query_run) {
                                    echo 201; // Successfully added to the cart
                                } else {
                                    echo 500; // Error adding to the cart
                                }
                            }
                        } else {
                            // Product is sold out
                            echo "soldout";
                        }
                    }
                }
                break;


            case "update":
                $user_id = $_SESSION['auth_user']['user_ID'];
                $prod_id = $_POST['product_id'];
                $prod_qty = $_POST['product_qty'];

                $chk_existing_cart = "SELECT * FROM carts WHERE product_id='$prod_id' AND user_ID='$user_id' ";
                $chk_existing_cart_run = mysqli_query($con, $chk_existing_cart);

                if (mysqli_num_rows($chk_existing_cart_run) > 0) {
                    $update_query = "UPDATE carts SET product_qty='$prod_qty' WHERE product_id='$prod_id' AND user_ID='$user_id'";
                    $update_query_run = mysqli_query($con, $update_query);
                    if ($update_query_run) {
                        echo 200;
                    } else {
                        echo 500;
                    }
                } else {
                    echo "Something went wrong";
                }
                break;
            case "delete":
                $user_id = $_SESSION['auth_user']['user_ID'];
                $cart_id = $_POST['cart_id'];

                $chk_existing_cart = "SELECT * FROM carts WHERE cart_id='$cart_id' AND user_ID='$user_id' ";
                $chk_existing_cart_run = mysqli_query($con, $chk_existing_cart);

                if (mysqli_num_rows($chk_existing_cart_run) > 0) {
                    $delete_query = "DELETE FROM carts WHERE cart_id='$cart_id'";
                    $delete_query_run = mysqli_query($con, $delete_query);
                    if ($delete_query_run) {
                        echo 200;
                    } else {
                        echo 500;
                    }
                } else {
                    echo "Something went wrong";
                }
                break;
            default:
                echo 500;
        }
    }
} else {
    echo 401;
}
