<div class="container">
	<h2><?php echo $title; ?></h2>

	<!-- Display status message -->
	<?php if (!empty($success_msg)) { ?>
		<div class="col-xs-12">
			<div class="alert alert-success"><?php echo $success_msg; ?></div>
		</div>
	<?php } elseif (!empty($error_msg)) { ?>
		<div class="col-xs-12">
			<div class="alert alert-danger"><?php echo $error_msg; ?></div>
		</div>
	<?php } ?>

	<div class="row">
		<div class="col-md-12 search-panel">
			<!-- Search form -->
			<form method="post">
				<div class="input-group mb-3">
					<input type="text" name="searchKeyword" class="form-control" placeholder="Search by keyword..." value="<?php echo $searchKeyword; ?>">
					<div class="input-group-append">
						<input type="submit" name="submitSearch" class="btn btn-outline-secondary" value="Search">
						<input type="submit" name="submitSearchReset" class="btn btn-outline-secondary" value="Reset">
					</div>
				</div>
			</form>

			<!-- Add link -->
			<div class="float-right">
				<a href="<?php echo base_url('/users/account');?>">Home</a>
				<a href="<?php echo site_url('members/add/'); ?>" class="btn btn-success"><i class="plus"></i> New Member</a>
			</div>
		</div>

		<!-- Data list table -->
		<table class="table table-striped table-bordered">
			<thead class="thead-dark">
				<tr>
					<th id='id'>#</th>
					<th id='first'>First Name</th>
					<th id='last'>Last Name</th>
					<th id='email'>Email</th>
					<th id='gender'>Gender</th>
					<th id='country'>Country</th>
					<th style="cursor: default !important">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($members)) {
					foreach ($members as $row) { ?>
						<tr>
							<td><?php echo $row['recordId']; ?></td>
							<td><?php echo $row['first_name']; ?></td>
							<td><?php echo $row['last_name']; ?></td>
							<td><?php echo $row['email']; ?></td>
							<td><?php echo $row['gender']; ?></td>
							<td><?php echo $row['country']; ?></td>
							<td>
								<a href="<?php echo site_url('members/view/' . $row['recordId']); ?>" class="btn btn-primary">view</a>
								<a href="<?php echo site_url('members/edit/' . $row['recordId']); ?>" class="btn btn-warning">edit</a>
								<a href="<?php echo site_url('members/delete/' . $row['recordId']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure to delete?')">delete</a>
							</td>
						</tr>
					<?php }
				} else { ?>
					<tr>
						<td colspan="7">No member(s) found...</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		<!-- Display pagination links -->
		<div class="pagination pull-right">
			<?php echo $this->pagination->create_links(); ?>
		</div>
	</div>
</div>
<script>
	var $ = jQuery;
	jQuery(document).ready(function() {
		$('th').click(function() {
			var table = $(this).parents('table').eq(0)
			var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
			this.asc = !this.asc
			if (!this.asc) {
				rows = rows.reverse()
			}
			for (var i = 0; i < rows.length; i++) {
				table.append(rows[i])
			}
		});

		function comparer(index) {
			return function(a, b) {
				var valA = getCellValue(a, index),
					valB = getCellValue(b, index)
				return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
			}
		}

		function getCellValue(row, index) {
			return $(row).children('td').eq(index).text()
		}

	});
</script>
