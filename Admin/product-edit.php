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

    if(empty($_POST['p_qty'])) {
        $valid = 0;
        $error_message .= "Quantity can not be empty<br>";
    }

    $path = $_FILES['p_featured_photo']['name'];
    $path_tmp = $_FILES['p_featured_photo']['tmp_name'];
    $current_photo = $_POST['current_photo'];

    if($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if(!in_array(strtolower($ext), array('jpg', 'png', 'jpeg', 'gif'))) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // Process additional photos if any
        if(!empty($_FILES['photo']['name'][0])) {
            $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
            $photo_names = array();
            
            foreach($_FILES['photo']['tmp_name'] as $key => $tmp_name) {
                if($_FILES['photo']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['photo']['name'][$key], PATHINFO_EXTENSION);
                    if(in_array(strtolower($ext), $allowed_ext)) {
                        $new_name = 'product-photo-' . uniqid() . '.' . $ext;
                        move_uploaded_file($tmp_name, "../assets/uploads/product_photos/" . $new_name);
                        $photo_names[] = $new_name;
                    }
                }
            }
            
            // Save to database
            if(!empty($photo_names)) {
                foreach($photo_names as $photo_name) {
                    $statement = $pdo->prepare("INSERT INTO tbl_product_photo (photo, p_id) VALUES (?, ?)");
                    $statement->execute(array($photo_name, $_REQUEST['id']));
                }
            }
        }

        // Update featured photo if changed
        $final_name = $current_photo;
        if($path != '') {
            // Remove old photo
            if(file_exists('../assets/uploads/'.$current_photo)) {
                unlink('../assets/uploads/'.$current_photo);
            }
            
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $final_name = 'product-featured-'.$_REQUEST['id'].'.'.$ext;
            move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);
        }

        // Update product data
        $brochure_url = isset($_POST['brochure_url']) ? $_POST['brochure_url'] : '';
        $p_is_featured = isset($_POST['p_is_featured']) ? $_POST['p_is_featured'] : 0;
        $p_is_active = isset($_POST['p_is_active']) ? $_POST['p_is_active'] : 0;
        
        $statement = $pdo->prepare("UPDATE tbl_product SET 
            p_name = ?, 
            brochure_url = ?,
            p_qty = ?,
            p_featured_photo = ?,
            p_is_featured = ?,
            p_is_active = ?,
            ecat_id = ?
            WHERE p_id = ?");
        
        $statement->execute(array(
            $_POST['p_name'],
            $brochure_url,
            $_POST['p_qty'],
            $final_name,
            $p_is_featured,
            $p_is_active,
            $_POST['ecat_id'],
            $_REQUEST['id']
        ));

        // Update colors
        $statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id = ?");
        $statement->execute(array($_REQUEST['id']));
        
        if(!empty($_POST['color'])) {
            foreach($_POST['color'] as $color_id) {
                $statement = $pdo->prepare("INSERT INTO tbl_product_color (color_id, p_id) VALUES (?, ?)");
                $statement->execute(array($color_id, $_REQUEST['id']));
            }
        }
    
        $success_message = 'Product is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    // Check the id is valid or not
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if( $total == 0 ) {
        header('location: logout.php');
        exit;
    }
}
?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $p_name = $row['p_name'];
    $p_qty = $row['p_qty'];
    $p_featured_photo = $row['p_featured_photo'];
    $p_is_featured = $row['p_is_featured'];
    $p_is_active = $row['p_is_active'];
    $ecat_id = $row['ecat_id'];
    $brochure_url = $row['brochure_url']; // Added for brochure URL
}

$statement = $pdo->prepare("SELECT * 
                        FROM tbl_end_category t1
                        JOIN tbl_mid_category t2
                        ON t1.mcat_id = t2.mcat_id
                        JOIN tbl_top_category t3
                        ON t2.tcat_id = t3.tcat_id
                        WHERE t1.ecat_id=?");
$statement->execute(array($ecat_id));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $ecat_name = $row['ecat_name'];
    $mcat_id = $row['mcat_id'];
    $tcat_id = $row['tcat_id'];
}

// Only get colors, not sizes
$statement = $pdo->prepare("SELECT * FROM tbl_product_color WHERE p_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
$color_id = array();
foreach ($result as $row) {
    $color_id[] = $row['color_id'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Product</h1>
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
                                        <option value="<?php echo $row['tcat_id']; ?>" <?php if($row['tcat_id'] == $tcat_id){echo 'selected';} ?>><?php echo $row['tcat_name']; ?></option>
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
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id = ? ORDER BY mcat_name ASC");
                                    $statement->execute(array($tcat_id));
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);   
                                    foreach ($result as $row) {
                                        ?>
                                        <option value="<?php echo $row['mcat_id']; ?>" <?php if($row['mcat_id'] == $mcat_id){echo 'selected';} ?>><?php echo $row['mcat_name']; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">End Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="ecat_id" class="form-control select2 end-cat">
                                    <option value="">Select End Level Category</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE mcat_id = ? ORDER BY ecat_name ASC");
                                    $statement->execute(array($mcat_id));
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);   
                                    foreach ($result as $row) {
                                        ?>
                                        <option value="<?php echo $row['ecat_id']; ?>" <?php if($row['ecat_id'] == $ecat_id){echo 'selected';} ?>><?php echo $row['ecat_name']; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Product Name <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="p_name" class="form-control" value="<?php echo $p_name; ?>">
                            </div>
                        </div>    
                        
                        <!-- Brochure URL Field -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Brochure URL</label>
                            <div class="col-sm-4">
                                <input type="text" name="brochure_url" class="form-control" value="<?php echo $brochure_url; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Quantity <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="p_qty" class="form-control" value="<?php echo $p_qty; ?>">
                            </div>
                        </div>
                        
                        <!-- Color Selection (Dropdown) -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Select Color</label>
                            <div class="col-sm-4">
                                <select name="color[]" class="form-control select2" multiple="multiple">
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM tbl_color ORDER BY color_name ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
                                    foreach ($result as $row) {
                                        $selected = in_array($row['color_id'], $color_id) ? 'selected' : '';
                                        echo '<option value="'.$row['color_id'].'" '.$selected.'>'.$row['color_name'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Existing Featured Photo</label>
                            <div class="col-sm-4" style="padding-top:4px;">
                                <img src="../assets/uploads/<?php echo $p_featured_photo; ?>" alt="" style="width:150px;">
                                <input type="hidden" name="current_photo" value="<?php echo $p_featured_photo; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Change Featured Photo</label>
                            <div class="col-sm-4" style="padding-top:4px;">
                                <input type="file" name="p_featured_photo">
                            </div>
                        </div>
                        
                        <!-- Other Photos -->
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Other Photos</label>
                            <div class="col-sm-4" style="padding-top:4px;">
                                <input type="file" name="photo[]" multiple>
                                
                                <?php
                                $statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=?");
                                $statement->execute(array($_REQUEST['id']));
                                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                if ($result) {
                                    echo '<div style="margin-top:15px;">';
                                    foreach ($result as $row) {
                                        echo '<div style="float:left; margin-right:10px; margin-bottom:10px; position:relative;">
                                            <img src="../assets/uploads/product_photos/'.$row['photo'].'" alt="" style="width:100px; height:100px; object-fit:cover;">
                                            <a href="product-other-photo-delete.php?id='.$row['pp_id'].'&id1='.$_REQUEST['id'].'" 
                                               class="btn btn-danger btn-xs" 
                                               style="position:absolute; top:-5px; right:-5px;"
                                               onclick="return confirm(\'Are you sure?\')">X</a>
                                        </div>';
                                    }
                                    echo '<div style="clear:both;"></div></div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Is Featured?</label>
                            <div class="col-sm-8">
                                <select name="p_is_featured" class="form-control" style="width:auto;">
                                    <option value="0" <?php if($p_is_featured == '0'){echo 'selected';} ?>>No</option>
                                    <option value="1" <?php if($p_is_featured == '1'){echo 'selected';} ?>>Yes</option>
                                </select> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Is Active?</label>
                            <div class="col-sm-8">
                                <select name="p_is_active" class="form-control" style="width:auto;">
                                    <option value="0" <?php if($p_is_active == '0'){echo 'selected';} ?>>No</option>
                                    <option value="1" <?php if($p_is_active == '1'){echo 'selected';} ?>>Yes</option>
                                </select> 
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>