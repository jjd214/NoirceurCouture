<?php
ob_start();
$page = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], "/") + 1);
?>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="index.php">
            <img src="../assets/images/logo/NoirceurCouture_WT.png" class="navbar-brand-img h-100" alt="LOGO">
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto  max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white <?= $page == "index.php" ? 'active bg-gradient-primary' : '' ?>" href="index.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= in_array($page, ["your-store.php"]) ? 'active bg-gradient-primary' : '' ?>" href="your-store.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">store</i>
                    </div>
                    <span class="nav-link-text ms-1">Your Store</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= in_array($page, ["product.php", "editProduct.php", "addProduct.php"]) ? 'active bg-gradient-primary' : '' ?>" href="product.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory_2</i>
                    </div>
                    <span class="nav-link-text ms-1">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?=
                                                in_array($page, ["orders.php", "ordersPreparing.php", "ordersShipped.php", "ordersDeliver.php", "ordersCancelled.php", "viewOrderDetails.php"])
                                                    ? 'active bg-gradient-primary'
                                                    : '' ?>" href="orders.php">

                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">shopping_cart_checkout</i>
                    </div>
                    <span class="nav-link-text ms-1">Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= $page == "reports.php" ? 'active bg-gradient-primary' : '' ?>" href="reports.php">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">receipt</i>
                    </div>
                    <span class="nav-link-text ms-1">Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?=
                                                in_array($page, ["notifications.php"])
                                                    ? 'active bg-gradient-primary'
                                                    : '' ?>" href="notifications.php">

                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">notifications</i>
                    </div>
                    <span class="nav-link-text ms-1">Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?=
                                                in_array($page, ["account-details.php", "changePassword.php"])
                                                    ? 'active bg-gradient-primary'
                                                    : '' ?>" href="account-details.php">

                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">settings</i>
                    </div>
                    <span class="nav-link-text ms-1">Account Details</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?=
                                                in_array($page, ["requestDeleteAccountSeller.php"])
                                                    ? 'active bg-gradient-primary'
                                                    : '' ?>" href="requestDeleteAccountSeller.php">

                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">delete</i>
                    </div>
                    <span class="nav-link-text ms-1">Request Account Deletion</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
        <div class="mx-3">
            <a class="btn bg-gradient-primary mt-4 w-100" href="../models/logout.php" type="button">Log Out</a>
        </div>
    </div>
</aside>