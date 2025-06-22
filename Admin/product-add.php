<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
    $valid = 1;

    if(empty($_POST['tcat_id'])) {
        $valid = 0;
        $error_message .= "You must have to select a top level category<br>";
    }

    if(empty($_POST['mcat_id'])) {
        $valid = 0;
        $error_message .= "You must have to select a mid level category<br>";
    }

    if(empty($_POST['ecat_id'])) {
        $valid = 0;
        $error_message .= "You must have to select an end level category<br>";
    }

    if(empty($_POST['p_name'])) {
        $valid = 0;
        $error_message .= "Product name can not be empty<br>";
    }

    // Ensure these fields have values, otherwise assign a default value
    $p_is_featured = isset($_POST['p_is_featured']) ? $_POST['p_is_featured'] : 0;  // Default to 0 if not set
    $p_is_active = isset($_POST['p_is_active']) ? $_POST['p_is_active'] : 0;  // Default to 0 if not set

    // Getting the brochure URL value (if provided)
    $brochure_url = isset($_POST['brochure_url']) ? $_POST['brochure_url'] : '';  // Default to empty if not set

    $path = $_FILES['p_featured_photo']['name'];
    $path_tmp = $_FILES['p_featured_photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $file_name = basename($path, '.' . $ext);
        if($ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif') {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    } else {
        $valid = 0;
        $error_message .= 'You must have to select a featured photo<br>';
    }

    if($valid == 1) {
        // Fetch the next product id
        $statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product'");
        $statement->execute();
        $result = $statement->fetchAll();
        foreach($result as $row) {
            $ai_id = $row[10];
        }

        // Handle multiple photos upload if provided
        if(isset($_FILES['photo']["name"]) && isset($_FILES['photo']["tmp_name"])) {
            $photo = array();
            $photo = $_FILES['photo']["name"];
            $photo = array_values(array_filter($photo));

            $photo_temp = array();
            $photo_temp = $_FILES['photo']["tmp_name"];
            $photo_temp = array_values(array_filter($photo_temp));

            $statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product_photo'");
            $statement->execute();
            $result = $statement->fetchAll();
            foreach($result as $row) {
                $next_id1 = $row[10];
            }
            $z = $next_id1;

            $m = 0;
            for($i = 0; $i < count($photo); $i++) {
                $my_ext1 = pathinfo($photo[$i], PATHINFO_EXTENSION);
                if($my_ext1 == 'jpg' || $my_ext1 == 'png' || $my_ext1 == 'jpeg' || $my_ext1 == 'gif') {
                    $final_name1[$m] = $z . '.' . $my_ext1;
                    move_uploaded_file($photo_temp[$i], "../assets/uploads/product_photos/" . $final_name1[$m]);
                    $m++;
                    $z++;
                }
            }

            if(isset($final_name1)) {
                for($i = 0; $i < count($final_name1); $i++) {
                    $statement = $pdo->prepare("INSERT INTO tbl_product_photo (photo,p_id) VALUES (?,?)");
                    $statement->execute(array($final_name1[$i], $ai_id));
                }
            }
        }

        // Move the featured photo to the uploads folder
        $final_name = 'product-featured-' . $ai_id . '.' . $ext;
        move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);

        // Saving data into the tbl_product table
        $statement = $pdo->prepare("INSERT INTO tbl_product (
            p_name,
            brochure_url,
            p_qty,
            p_featured_photo,
            p_total_view,
            p_is_featured,
            p_is_active,
            ecat_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->execute(array(
            $_POST['p_name'],
            $brochure_url,
            $_POST['p_qty'],
            $final_name,  // Assuming $final_name is the featured photo filename
            0,  // p_total_view
            $p_is_featured,  // p_is_featured value from the form
            $p_is_active,    // p_is_active value from the form
            $_POST['ecat_id']
        ));

        // Handle sizes and colors if provided
        if(isset($_POST['size'])) {
            foreach($_POST['size'] as $value) {
                $statement = $pdo->prepare("INSERT INTO tbl_product_size (size_id, p_id) VALUES (?, ?)");
                $statement->execute(array($value, $ai_id));
            }
        }

        if(isset($_POST['color'])) {
            foreach($_POST['color'] as $value) {
                $statement = $pdo->prepare("INSERT INTO tbl_product_color (color_id, p_id) VALUES (?, ?)");
                $statement->execute(array($value, $ai_id));
            }
        }

        $success_message = 'Product is added successfully.';
    }
}
?>

<!-- HTML form for adding product -->
<section class="content-header">
    <div class="content-header-left">
        <h1>Add Product</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if($error_message): ?>
            <div class="callout callout-danger">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Top Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="tcat_id" class="form-control select2 top-cat">
                                    <option value="">Select Top Level Category</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);    
                                    foreach ($result as $row) {
                                        ?>
                                        <option value="<?php echo $row['tcat_id']; ?>"><?php echo $row['tcat_name']; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Mid Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="mcat_id" class="form-control select2 mid-cat">
                                    <option value="">Select Mid Level Category</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">End Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="ecat_id" class="form-control select2 end-cat">
                                    <option value="">Select End Level Category</option>
                                </select>
                            </div>
                        </div>

                        <!-- Product Name -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Product Name <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="p_name" class="form-control">
                            </div>
                        </div>

                        <!-- Brochure URL -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Brochure URL</label>
                            <div class="col-sm-4">
                                <input type="text" name="brochure_url" class="form-control">
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Quantity <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="p_qty" class="form-control">
                            </div>
                        </div>

                        <!-- Featured Photo -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Featured Photo <span>*</span></label>
                            <div class="col-sm-4" style="padding-top:4px;">
                                <input type="file" name="p_featured_photo">
                            </div>
                        </div>

						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Is Featured?</label>
							<div class="col-sm-8">
								<select name="p_is_featured" class="form-control" style="width:auto;">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select> 
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Is Active?</label>
							<div class="col-sm-8">
								<select name="p_is_active" class="form-control" style="width:auto;">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select> 
							</div>
						</div>
						
                        <!-- Submit Button -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Add Product</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
