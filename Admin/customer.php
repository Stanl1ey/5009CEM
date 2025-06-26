<?php 
require_once('header.php');  // includes AdminLTE CSS links
?>

<!-- page-specific CSS overrides -->
<style>
  /* 1) Make the “View Customers” header underline black */
  .content-header {
    border-bottom: 1px solid #000 !important;
  }

  /* 2) Black background + white text for table headers */
  #example1 thead th {
    background-color: #000 !important;
    border-color:     #000 !important;
    color:            #fff !important;
  }

  /* 3) Force all rows to plain white (remove green/red shading) */
  #example1 tbody tr,
  .bg-g, .bg-r {
    background-color: #fff !important;
  }
</style>

<section class="content-header">
  <div class="content-header-left">
    <h1>
      <i class="fa fa-users text-primary"></i>
       View Customers
    </h1>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-hover table-striped">
            <thead>
              <tr>
                <th width="10">#</th>
                <th width="180">Name</th>
                <th width="150">Email Address</th>
                <th width="180">Country, City, State</th>
                <th>Status</th>
                <th width="100">Change Status</th>
                <th width="100">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $i = 0;
              $statement = $pdo->prepare("
                SELECT * 
                FROM tbl_customer t1
                JOIN tbl_country t2
                  ON t1.cust_country = t2.country_id
              ");
              $statement->execute();
              $result = $statement->fetchAll(PDO::FETCH_ASSOC);
              foreach ($result as $row):
                $i++;
                $rowClass = $row['cust_status'] == 1 ? 'bg-g' : 'bg-r';
              ?>
              <tr class="<?= $rowClass; ?>">
                <td><?= $i; ?></td>
                <td><?= htmlspecialchars($row['cust_name']); ?></td>
                <td><?= htmlspecialchars($row['cust_email']); ?></td>
                <td>
                  <?= htmlspecialchars($row['country_name']); ?><br>
                  <?= htmlspecialchars($row['cust_city']); ?><br>
                  <?= htmlspecialchars($row['cust_state']); ?>
                </td>
                <td>
                  <?= $row['cust_status'] == 1 ? 'Active' : 'Inactive'; ?>
                </td>
                <td>
                  <a href="customer-change-status.php?id=<?= $row['cust_id']; ?>"
                     class="btn btn-success btn-xs">
                    Change Status
                  </a>
                </td>
                <td>
                  <a href="#"
                     class="btn btn-danger btn-xs"
                     data-href="customer-delete.php?id=<?= $row['cust_id']; ?>"
                     data-toggle="modal"
                     data-target="#confirm-delete">
                    Delete
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <button type="button" class="close" data-dismiss="modal"
                aria-hidden="true">&times;
        </button>
        <h4 class="modal-title" id="myModalLabel">
          <i class="fa fa-exclamation-triangle"></i> Delete Confirmation
        </h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this item?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default"
                data-dismiss="modal">Cancel
        </button>
        <a class="btn btn-danger btn-ok">Delete</a>
      </div>
    </div>
  </div>
</div>

<?php require_once('footer.php'); ?>
