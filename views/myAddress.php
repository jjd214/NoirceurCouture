<?php
include('../partials/__header.php');
include('../middleware/userMW.php');
include('../models/dataEncryption.php');
?>

<style>
    .border-accent {
        border-color: #bb6c54 !important;
    }
</style>

<div class="container mt-5">
    <?php include('../partials/sessionMessage.php') ?>
    <div class="row">
        <?php include('../partials/sidebar.php') ?>
        <div class="col-md-9">
            <div>
                <div class="card border rounded-3 bg-tertiary">
                    <div class="card-header">
                        <h5 class="card-title ">
                            <span>My Addresses</span>
                            <span class="float-end">
                                <a href="myAddressAddNew.php" class="btn btn-main">
                                    Add New Address
                                </a>
                            </span>
                        </h5>
                    </div>
                    <?php
                    // Fetch user details
                    $useridResult = getUserDetails();
                    // Flag to track if any addresses exist
                    $addressesExist = false;
                    while ($dataid = mysqli_fetch_array($useridResult)) {
                        $userid = $dataid['user_ID'];

                        // Fetch user addresses based on user ID
                        $userAddressResult = getUserAddress($_SESSION['auth_user']['user_ID']); // Pass userid to fetch addresses for specific user

                        // Check if there are any addresses for the user
                        if (mysqli_num_rows($userAddressResult) == 0) {
                            // If no addresses exist, display a message
                    ?>
                            <div class="text-center mt-3" id="nocartItems">
                                <p>No address existing yet</p>
                            </div>
                            <?php
                        } else {
                            $addressesExist = true;

                            // Load region data
                            $regionData = json_decode(file_get_contents("../assets/js/ph-json/region.json"), true);

                            // Load province data
                            $provinceData = json_decode(file_get_contents("../assets/js/ph-json/province.json"), true);

                            // Load city data
                            $cityData = json_decode(file_get_contents("../assets/js/ph-json/city.json"), true);

                            // Load barangay data
                            $barangayData = json_decode(file_get_contents("../assets/js/ph-json/barangay.json"), true);

                            while ($data = mysqli_fetch_array($userAddressResult)) {
                                $addrQTY = $data['addrUserQTY'];
                                $addrid = $data['address_id'];
                                $isDefault = $data['address_isDefault'];
                                $fname = $data['address_fullName'];
                                $email = $data['address_email'];
                                $regionCode = $data['address_region'];
                                $provinceCode = $data['address_province'];
                                $cityCode = $data['address_city'];
                                $barangayCode = $data['address_barangay'];
                                $phone = $data['address_phone'];
                                $fulladdr = $data['address_fullAddress'];

                                $decryptedfullAddr = decryptData($fulladdr);

                                //!Fetch region name based on region code
                                $regionName = "";
                                $regionUrl = "../assets/js/ph-json/region.json";
                                /* $regionData = json_decode(file_get_contents($regionUrl), true); //!Fetch the RegionData */

                                //!loop through regionData & find regionName corresponding to region code
                                foreach ($regionData as $RegD) {
                                    if ($RegD['region_code'] == $regionCode) {
                                        $regionName = $RegD['region_name'];
                                        break;
                                    }
                                }

                                //+Fetch Province name based on Province code
                                $provinceName = "";
                                $provinceUrl = "../assets/js/ph-json/province.json";
                                /* $provinceData = json_decode(file_get_contents($provinceUrl), true); //+Fetch the provinceData */

                                //+loop through provinceData & find provinceName corresponding to region code
                                foreach ($provinceData as $ProvD) {
                                    if ($ProvD['province_code'] == $provinceCode) {
                                        $provinceName = $ProvD['province_name'];
                                        break;
                                    }
                                }

                                //?Fetch Province name based on Province code
                                $cityName = "";
                                $cityUrl = "../assets/js/ph-json/city.json";
                                /* $cityData = json_decode(file_get_contents($cityUrl), true); //+Fetch the ProvinceData */

                                //?loop through ProvinceData & find provinceName corresponding to region code
                                foreach ($cityData as $CtyD) {
                                    if ($CtyD['city_code'] == $cityCode) {
                                        $cityName = $CtyD['city_name'];
                                        break;
                                    }
                                }

                                //*Fetch Barangay name based on Barangay code
                                $barangayName = "";
                                $barangayUrl = "../assets/js/ph-json/barangay.json";
                                /* $barangayData = json_decode(file_get_contents($barangayUrl), true); //+Fetch the barangayData */

                                //*loop through barangayData & find barangayName corresponding to region code
                                foreach ($barangayData as $BrgyD) {
                                    if ($BrgyD['brgy_code'] == $barangayCode) {
                                        $barangayName = $BrgyD['brgy_name'];
                                        break;
                                    }
                                }

                            ?>

                                <div class="card-body">
                                    <!-- Display user addresses in cards -->
                                    <div class="card bg-main border-0">
                                        <div class="row card-body col-md-12 ">
                                            <div class="col-md-9">
                                                <div id="namePhoneEmail">
                                                    <span class="fw-bold"><?= $fname ?></span> |
                                                    <span class="fw-normal"><?= $phone ?></span> |
                                                    <span class="fw-normal"><?= $email ?></span>
                                                </div>
                                                <div id="fullAddress">
                                                    <span><?= $decryptedfullAddr ?></span>
                                                </div>
                                                <div id="location">
                                                    <span><?= $barangayName ?></span>,
                                                    <span><?= $cityName ?></span>,<br>
                                                    <span><?= $provinceName ?></span>,
                                                    <span><?= $regionName ?></span>
                                                </div>
                                                <?php
                                                if ($isDefault == 1) {
                                                ?>
                                                    <div id="isDefault" class="mt-2">
                                                        <span class="border-0 btn-main rounded-3 p-1">Default</span>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="col-md-3">
                                                <span class="float-end mb-2">
                                                    <a href="myAddressEdit.php?addrID=<?= $addrid ?>" class="btn btn-main">Edit</a>

                                                    <?php
                                                    if ($isDefault != 1 || $isDefault == 1 && $addrQTY == 1) {
                                                    ?>
                                                        <a href="#" class="btn btn-tertiary" data-bs-toggle="modal" data-bs-target="#deleteAddrModal<?= $addrid ?>">Delete</a>
                                                        <!-- Modal -->
                                                        <div class="modal fade" id="deleteAddrModal<?= $addrid ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content bg-main">
                                                                    <form action="../models/authcode.php" method="POST">
                                                                        <div class="modal-body fs-5">
                                                                            <input type="hidden" name="deleteAddrID" value="<?= $addrid ?>">
                                                                            <input type="hidden" name="deleteAddruserID" value="<?= $userid ?>">
                                                                            Are you sure you want to delete this Address?
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-tertiary" data-bs-dismiss="modal">Close</button>
                                                                            <button name="deleteAddrBtn" class="btn btn-main">Delete Address</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>

                                                </span>

                                                <form action="../models/authcode.php" method="POST">
                                                    <input type="hidden" name="addrID" value="<?= $addrid ?>">
                                                    <input type="hidden" name="userID" value="<?= $userid ?>">
                                                    <button name="setDefaultAddrBtn" class="btn btn-main float-end" <?= ($isDefault == 1) ? 'hidden' : '' ?>>Set as Default</button>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                    <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /* Prevent user to write letter or symbols in phone number */
    function inpNum(e) {
        e = e || window.event;
        var charCode = (typeof e.which == "undefined") ? e.keyCode : e.which;
        var charStr = String.fromCharCode(charCode);

        // Allow only numeric characters
        if (!charStr.match(/^[0-9]+$/)) {
            e.preventDefault();
        }

        // Allow a maximum of 11 digits
        var inputValue = e.target.value || '';
        var numericValue = inputValue.replace(/[^0-9]/g, '');

        if (numericValue.length >= 11) {
            e.preventDefault();
        }

        // Apply Philippine phone number format (optional)
        if (numericValue.length === 1 && numericValue !== '0') {
            // Add '0' at the beginning if the first digit is not '0'
            e.target.value = '0' + numericValue;
        } else if (numericValue.length >= 2 && !numericValue.startsWith('09')) {
            // Ensure it starts with '09'
            e.target.value = '09' + numericValue.substring(2);
        }
    }
</script>

<div style="margin-top:8.5%;">
    <?php include('footer.php'); ?>
</div>

<?php
include('../assets/js/ph-address-selector.php');
include('../partials/__footer.php');
?>