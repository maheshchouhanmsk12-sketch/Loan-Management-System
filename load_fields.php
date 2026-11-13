<?php 
include 'db_connect.php';
extract($_POST);

$loan_id = isset($loan_id) ? (int)$loan_id : 0;

if(isset($id) && !empty($id)){
	$id_val = (int)$id;
	$qry = $conn->query("SELECT * FROM payments where id=".$id_val);
	if ($qry && $qry instanceof mysqli_result && $qry->num_rows > 0) {
		foreach($qry->fetch_array() as $k => $val){
			$$k = $val;
		}
	}
}

$meta = [];
$loan = $conn->query("SELECT l.*,concat(b.lastname,', ',b.firstname,' ',b.middlename)as name, b.contact_no, b.address from loan_list l inner join borrowers b on b.id = l.borrower_id where l.id = ".$loan_id);
if ($loan && $loan instanceof mysqli_result && $loan->num_rows > 0) {
	foreach($loan->fetch_array() as $k => $v){
		$meta[$k] = $v;
	}
}

$loan_type_id = isset($meta['loan_type_id']) ? (int)$meta['loan_type_id'] : 0;
$type_arr = [];
if ($loan_type_id > 0) {
	$t_res = $conn->query("SELECT * FROM loan_types where id = '".$loan_type_id."' ");
	if ($t_res && $t_res instanceof mysqli_result && $t_res->num_rows > 0) {
		$type_arr = $t_res->fetch_array();
	}
}

$plan_id = isset($meta['plan_id']) ? (int)$meta['plan_id'] : 0;
$plan_arr = ['months' => 1, 'interest_percentage' => 0, 'penalty_rate' => 0];
if ($plan_id > 0) {
	$p_res = $conn->query("SELECT *,concat(months,' month/s [ ',interest_percentage,'%, ',penalty_rate,' ]') as plan FROM loan_plan where id  = '".$plan_id."' ");
	if ($p_res && $p_res instanceof mysqli_result && $p_res->num_rows > 0) {
		$plan_arr = $p_res->fetch_array();
	}
}

$amount_val = isset($meta['amount']) ? (float)$meta['amount'] : 0;
$months_val = !empty($plan_arr['months']) ? (float)$plan_arr['months'] : 1;
$interest_val = isset($plan_arr['interest_percentage']) ? (float)$plan_arr['interest_percentage'] : 0;
$penalty_rate_val = isset($plan_arr['penalty_rate']) ? (float)$plan_arr['penalty_rate'] : 0;

$monthly = ($amount_val + ($amount_val * ($interest_val/100))) / $months_val;
$penalty = $monthly * ($penalty_rate_val/100);

$payments = $conn->query("SELECT * from payments where loan_id =".$loan_id);
$paid = ($payments && $payments instanceof mysqli_result) ? $payments->num_rows : 0;
$offset = $paid > 0 ? " offset $paid ": "";

$next = 'N/A';
$next_res = $conn->query("SELECT * FROM loan_schedules where loan_id = '".$loan_id."'  order by date(date_due) asc limit 1 $offset ");
if ($next_res && $next_res instanceof mysqli_result && $next_res->num_rows > 0) {
	$next_row = $next_res->fetch_assoc();
	$next = $next_row['date_due'] ?? 'N/A';
}

$sum_paid = 0;
if ($payments && $payments instanceof mysqli_result) {
	while($p = $payments->fetch_assoc()){
		$sum_paid += ($p['amount'] - $p['penalty_amount']);
	}
}

$payee_val = isset($payee) ? $payee : ($meta['name'] ?? '');
$amount_field_val = isset($amount) ? $amount : '';
$add = ($next !== 'N/A' && date('Ymd',strtotime($next)) < date("Ymd")) ? $penalty : 0;
?>
<div class="col-lg-12">
<hr>
<div class="row">
	<div class="col-md-5">
		<div class="form-group">
			<label for="">Payee</label>
			<input name="payee" class="form-control" required="" value="<?php echo htmlspecialchars($payee_val); ?>">
		</div>
	</div>
	
</div>
<hr>
<div class="row">
	<div class="col-md-5">
		<p><small>Monthly amount:<b><?php echo number_format($monthly,2) ?></b></small></p>
		<p><small>Penalty :<b><?php echo number_format($add, 2); ?></b></small></p>
		<p><small>Payable Amount :<b><?php echo number_format($monthly + $add,2) ?></b></small></p>
	</div>
	<div class="col-md-5">
		<div class="form-group">
			<label for="">Amount</label>
			<input type="number" name="amount" step="any" min="" class="form-control text-right" required="" value="<?php echo htmlspecialchars($amount_field_val); ?>">
			<input type="hidden" name="penalty_amount" value="<?php echo $add ?>">
			<input type="hidden" name="overdue" value="<?php echo $add > 0 ? 1 : 0 ?>">
		</div>
	</div>
</div>
</div>
