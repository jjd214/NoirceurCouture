<?php include('../partials/__header.php');
include('../middleware/userMW.php');/* Authenticate.php */
?>
<style>
    .custom-tooltip {
        --bs-tooltip-bg: #343a40;
        --bs-tooltip-color: #fff;
        --bs-tooltip-max-width: 350px;
    }
</style>
<div>
    <div class="mt-5" style="margin-bottom: 10%;">
        <div class="container">
            <?php include('../partials/sessionMessage.php') ?>
            <div class="row">
                <?php include('../partials/sidebar.php') ?>
                <div class="col-md-9">
                    <?php include('../partials/myOrdersNav.php') ?>
                    <div class="card border-0 rounded-3 bg-tertiary">
                        <div class="card-body" style="height: 700px; overflow-y: scroll; scrollbar-width: none;">
                            <?php
                            $items = getOrderedItems();
                            $groupedItems = [];
                            $foundItems = false; //+ Flag to track if any items are found

                            // Fetch data from the result set and store it in an array
                            while ($cItem = mysqli_fetch_assoc($items)) {
                                $ordersCreatedAt = $cItem['orders_last_update_time'];
                                $categoryName = $cItem['category_name'];
                                $categorySlug = $cItem['category_slug'];
                                $foundItems = true; //+ Set foundItems to true if at least one item is found

                                if (!isset($groupedItems[$ordersCreatedAt][$categoryName])) {
                                    $groupedItems[$ordersCreatedAt][$categoryName] = [
                                        'items' => [],
                                        'statuses' => [
                                            'toShip' => 0,
                                            'toReceive' => 0,
                                            'delivered' => 0,
                                            'cancelled' => 0,
                                        ],
                                    ];
                                }

                                $groupedItems[$ordersCreatedAt][$categoryName]['items'][] = $cItem;

                                // Update status variables based on the current item
                                switch ($cItem['orders_status']) {
                                    case 0:
                                        $groupedItems[$ordersCreatedAt][$categoryName]['statuses']['toShip'] = 1;
                                        break;
                                    case 1:
                                        $groupedItems[$ordersCreatedAt][$categoryName]['statuses']['toReceive'] = 1;
                                        break;
                                    case 2:
                                        $groupedItems[$ordersCreatedAt][$categoryName]['statuses']['delivered'] = 1;
                                        break;
                                    case 3:
                                        $groupedItems[$ordersCreatedAt][$categoryName]['statuses']['cancelled'] = 1;
                                        break;
                                }
                            }
                            if (!$foundItems) {
                            ?>
                                <div class="text-center mt-3" id="nocartItems">
                                    <p>Currently, there are no purchase history</p>
                                </div>
                                <?php
                            } else {
                                // Display grouped items
                                foreach ($groupedItems as $ordersCreatedAt => $createdAtData) {
                                    foreach ($createdAtData as $categoryName => $categoryData) {
                                        $totalPrice = 0; // Reset $totalPrice for each category
                                        $displayedStatus = false;
                                        $firstItem = true; // Flag to track the first item in the category

                                        // Display status
                                        foreach ($categoryData['items'] as $cItem) {
                                            if (!$displayedStatus) {
                                                // Display the corresponding status only once per category
                                                if ($categoryData['statuses']['toShip'] == 1) {
                                                    $statusResult = "Preparing to ship";
                                                } else if ($categoryData['statuses']['toReceive'] == 1) {
                                                    $statusResult =  "Parcel is out for delivery";
                                                } else if ($categoryData['statuses']['delivered'] == 1) {
                                                    $statusResult =  "Parcel has been delivered";
                                                } else if ($categoryData['statuses']['cancelled'] == 1) {
                                                    $statusResult =  "Parcel has been cancelled";
                                                }
                                                $displayedStatus = true; //Display parcel status
                                            }

                                            if ($firstItem) {
                                                $isRated = getOrderIsRated($cItem['orders_tracking_no']);
                                                $rated = mysqli_fetch_array($isRated);
                                ?>
                                                <!-- If it's the first item, open a new card -->
                                                <div class='card mb-3 border-0 rounded-3 bg-main'>
                                                    <div class='card-header'>
                                                        <h5 class='card-title'>
                                                            <a href='store.php?category=<?= $cItem['category_slug'] ?>' class='fs-5 text-dark'><?= $cItem['category_name'] ?></a> <!-- Category Name -->
                                                            <?= ($cItem['category_isBan'] == 1) ? "<span class='badge bg-danger'>Banned</span>" : "" ?>
                                                            <span class='float-end text-accent fw-bold'><?= $statusResult ?>
                                                                <span class="tt fs-6" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Last update time: <?= date('F d, Y h:i:s A', strtotime($ordersCreatedAt)) ?>">
                                                                    <i class="fa-regular fa-clock"></i>
                                                                </span>
                                                                <?php
                                                                if ($rated && isset($rated['israted'])) {
                                                                ?>
                                                                    <span class="text-dark fw-normal">
                                                                        | <span class="fs-6 text-accent">RATED</span>
                                                                    </span>
                                                                <?php
                                                                } else {
                                                                }
                                                                ?>
                                                            </span> <!-- Parcel Status -->
                                                        </h5>
                                                    </div>
                                                    <div class='card-body overflow-x-auto'>
                                                    <?php
                                                    $firstItem = false;
                                                }
                                                    ?>
                                                    <!-- Display item details -->
                                                    <?php
                                                    $currentURL = urlencode($_SERVER['REQUEST_URI']);
                                                    ?>
                                                    <a href='viewOrderDetails.php?trck=<?= $cItem['orders_tracking_no'] ?>&return=<?= $currentURL ?>' class='text-dark text-decoration-none'>
                                                        <div class='productData row align-items-center py-2'>
                                                            <div class='col-md-2 text-center'>
                                                                <img src='../assets/uploads/products/<?= $cItem['product_image'] ?>' alt='Product Image' width='100px'>
                                                            </div>
                                                            <div class='col-md-5 text-start'>
                                                                <h5><?= $cItem['product_name'] ?> <?= ($cItem['pd_confirmed'] == 1) ? "<span class='badge bg-danger'>Deleted</span>" : "" ?></h5>
                                                                <h5>x<?= $cItem['orderItems_qty'] ?></h5>
                                                            </div>
                                                            <div class='col-md-5'>
                                                                <?php
                                                                if ($cItem['orderItems_price'] == $cItem['orderItems_Initprice']) {
                                                                ?>
                                                                    <h6 class='float-end d-flex justify-content-center'>₱<?= $cItem['orderItems_price'] ?></h6>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <span class='float-end d-flex justify-content-center'>
                                                                        <h6 class='text-secondary text-decoration-line-through mr-2'>₱<?= $cItem['orderItems_Initprice'] ?></h6>
                                                                        &nbsp;
                                                                        <h6 class='text-accent'>₱<?= $cItem['orderItems_price'] ?></h6>
                                                                    </span>
                                                                <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </a>
                                                <?php
                                                // Update total price
                                                $totalPrice += $cItem['orderItems_price'] * $cItem['orderItems_qty'];
                                            }
                                            // Close the card and display total price after all items in the category
                                            if (!$firstItem) {
                                                ?>
                                                    <hr>
                                                    <div class='float-end fs-6'>Order Total:&nbsp;&nbsp;
                                                        <span class='text-accent fw-bold fs-3'>₱<?= number_format($totalPrice, 2) ?></span>
                                                    </div>
                                                    </div>
                                                </div>
                                <?php
                                            }
                                        }
                                    }
                                }
                                ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <?php include('footer.php'); ?>
    </div>
</div>

<?php include('../partials/__footer.php'); ?>

<script>
    const tooltips = document.querySelectorAll('.tt')
    tooltips.forEach(t => {
        new bootstrap.Tooltip(t)
    })
</script>