<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Products</h1>
    </div>
    <div class="content-header-right">
        <a href="product-add.php" class="btn btn-primary btn-sm">Add Product</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="10">#</th>
                                <th>Photo</th>
                                <th>Product Name</th>
                                <th width="60">Quantity</th>
                                <th width="80">Featured?</th>
                                <th width="80">Active?</th>
                                <th>Category</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT
                                                        t1.p_id,
                                                        t1.p_name,
                                                        t1.p_qty,
                                                        t1.p_featured_photo,
                                                        t1.p_is_featured,
                                                        t1.p_is_active,
                                                        t1.ecat_id,
                                                        t1.brochure_url,
                                                        t2.ecat_id,
                                                        t2.ecat_name,
                                                        t3.mcat_name,
                                                        t4.tcat_name
                                            FROM tbl_product t1
                                            JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
                                            JOIN tbl_mid_category t3 ON t2.mcat_id = t3.mcat_id
                                            JOIN tbl_top_category t4 ON t3.tcat_id = t4.tcat_id
                                            ORDER BY t1.p_id DESC
                            ");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td style="width:82px;">
                                        <img src="../assets/uploads/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px; height:60px; object-fit:cover; border-radius:4px;">
                                    </td>
                                    <td><?php echo $row['p_name']; ?></td>
                                    <td><?php echo $row['p_qty']; ?></td>
                                    <td>
                                        <?php if($row['p_is_featured'] == 1) {
                                            echo '<span class="badge badge-success" style="background-color:green; padding:5px 8px;">Yes</span>';
                                        } else {
                                            echo '<span class="badge badge-success" style="background-color:red; padding:5px 8px;">No</span>';
                                        } ?>
                                    </td>
                                    <td>
                                        <?php if($row['p_is_active'] == 1) {
                                            echo '<span class="badge badge-success" style="background-color:green; padding:5px 8px;">Yes</span>';
                                        } else {
                                            echo '<span class="badge badge-danger" style="background-color:red; padding:5px 8px;">No</span>';
                                        } ?>
                                    </td>
                                    <td>
                                        <div class="category-card">
                                            <div class="category-type"><?php echo strtoupper($row['tcat_name']); ?></div>
                                            <div class="category-details">
                                                <div class="category-brand"><?php echo strtoupper($row['mcat_name']); ?></div>
                                                <div class="category-model"><?php echo strtoupper($row['ecat_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="product-edit.php?id=<?php echo $row['p_id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                            <a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $row['p_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
                                            
                                            <?php if(!empty($row['brochure_url'])) { ?>
                                                <a href="<?php echo $row['brochure_url']; ?>" target="_blank" class="btn btn-info btn-xs brochure-btn">Brochure</a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Modal -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure want to delete this item?</p>
                <p style="color:red;">Be careful! This product will be deleted from the order table, payment table, size table, color table and rating table also.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Category Card Styling */
    .category-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        border-radius: 4px;
        padding: 8px 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .category-type {
        font-weight: bold;
        font-size: 14px;
        color: #007bff;
        margin-bottom: 3px;
    }
    
    .category-brand {
        font-weight: 600;
        font-size: 13px;
        color: #343a40;
    }
    
    .category-model {
        font-size: 13px;
        color: #6c757d;
        font-style: italic;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .brochure-btn {
        margin-top: 5px;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    
    .btn-xs {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    /* Table Styling */
    .table th {
        background-color: #343a40;
        color: white;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.04);
    }
</style>

<script>
    $(document).ready(function() {
        $('#example1').DataTable({
            "pageLength": 10,
            "ordering": true,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });
        
        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    });
</script>

<?php require_once('footer.php'); ?>