<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
    header('location: index.php');
    exit;
} else {
    // Check if the id is valid
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if($total == 0) {
        header('location: index.php');
        exit;
    }
}

foreach($result as $row) {
    $p_name = $row['p_name'];
    $p_featured_photo = $row['p_featured_photo'];
    $p_short_description = $row['p_short_description'];
    $brochure_url = $row['brochure_url']; // Add Brochure URL
}

?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Product</h1>
	</div>
	<div class="content-header-right">
		<a href="product.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>

<!-- Product Display Section -->
<div class="product">
    <div class="row">
        <div class="col-md-5">
            <img src="assets/uploads/<?php echo $p_featured_photo; ?>" alt="<?php echo $p_name; ?>" />
        </div>
        <div class="col-md-7">
            <div class="p-title"><h2><?php echo $p_name; ?></h2></div>
            <div class="p-short-des">
                <p>
                    <?php echo $p_short_description; ?>
                </p>
            </div>

            <!-- Brochure URL display with View More Button -->
            <div class="p-brochure-url">
                <a href="<?php echo $brochure_url; ?>" target="_blank" class="btn btn-info">View More</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
