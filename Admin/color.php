<?php
require_once('header.php');  // includes AdminLTE CSS/JS
?>

<!-- page-specific CSS overrides -->
<style>
  /* 1) Black underline beneath the page title */
  .content-header {
    border-bottom: 1px solid #000 !important;
  }
  /* 2) Black background + white text for the DataTable header */
  #example1 thead th {
    background-color: #000 !important;
    border-color:     #000 !important;
    color:            #fff !important;
  }
  /* 3) Force all rows to plain white (remove any shading) */
  #example1 tbody tr,
  .bg-g, .bg-r {
    background-color: #fff !important;
  }
</style>

<!-- Content Header -->
<section class="content-header">
  <div class="content-header-left">
    <h1>
      <i class="fa fa-tint text-primary"></i>
      View Colors
    </h1>
  </div>
  <div class="content-header-right">
    <a href="color-add.php" class="btn btn-success btn-md">
      <i class="fa fa-plus-circle"></i> Add New
    </a>
  </div>
  <div class="clearfix"></div>
</section>

<!-- Main content -->
<section class="content">
  <div class="box box-info">
    <div class="box-body table-responsive">
      <table id="example1" class="table table-bordered table-hover table-striped">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Color Name</th>
            <th width="120">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 0;
          $stmt = $pdo->query("SELECT * FROM tbl_color ORDER BY color_id ASC");
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            $i++;
          ?>
          <tr>
            <td><?= $i; ?></td>
            <td><?= htmlspecialchars($row['color_name']); ?></td>
            <td>
              <a href="color-edit.php?id=<?= $row['color_id']; ?>" class="btn btn-primary btn-xs">
                <i class="fa fa-pencil"></i> Edit
              </a>
              <button class="btn btn-danger btn-xs"
                      data-toggle="modal"
                      data-target="#confirm-delete"
                      data-href="color-delete.php?id=<?= $row['color_id']; ?>">
                <i class="fa fa-trash"></i> Delete
              </button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">
          <i class="fa fa-exclamation-triangle"></i> Confirm Deletion
        </h4>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this color?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <a class="btn btn-danger btn-ok">Delete</a>
      </div>
    </div>
  </div>
</div>

<?php require_once('footer.php'); ?>
