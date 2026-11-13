<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
ini_set('display_errors', 1);

Class Action {
	private $db;

	public function __construct() {
		if (ob_get_level() == 0) {
			ob_start();
		}
		include 'db_connect.php';
		$this->db = $conn;
	}

	function __destruct() {
		if ($this->db && $this->db instanceof mysqli) {
			@$this->db->close();
		}
		if (ob_get_level() > 0) {
			@ob_end_flush();
		}
	}

	function login(){
		extract($_POST);
		$username = isset($username) ? $this->db->real_escape_string($username) : '';
		$password = isset($password) ? $this->db->real_escape_string($password) : '';

		$qry = $this->db->query("SELECT * FROM users where username = '".$username."' and password = '".$password."' ");
		if($qry && $qry instanceof mysqli_result && $qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && $key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			return 1;
		}else{
			return 3;
		}
	}

	function login2(){
		extract($_POST);
		$email = isset($email) ? $this->db->real_escape_string($email) : '';
		$password = isset($password) ? md5($password) : '';

		$qry = $this->db->query("SELECT * FROM users where username = '".$email."' and password = '".$password."' ");
		if($qry && $qry instanceof mysqli_result && $qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && $key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			return 1;
		}else{
			return 3;
		}
	}

	function logout(){
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
		exit;
	}

	function logout2(){
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:index.php");
		exit;
	}

	function save_user(){
		extract($_POST);
		$name = isset($name) ? $this->db->real_escape_string($name) : '';
		$username = isset($username) ? $this->db->real_escape_string($username) : '';
		$password = isset($password) ? $this->db->real_escape_string($password) : '';
		$type = isset($type) ? (int)$type : 2;

		$data = " name = '$name' ";
		$data .= ", username = '$username' ";
		$data .= ", password = '$password' ";
		$data .= ", type = '$type' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set ".$data);
		}else{
			$save = $this->db->query("UPDATE users set ".$data." where id = ".(int)$id);
		}
		if($save){
			return 1;
		}
		return 0;
	}

	function delete_user(){
		extract($_POST);
		$id = isset($id) ? (int)$id : 0;
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function signup(){
		extract($_POST);
		$name = isset($name) ? $this->db->real_escape_string($name) : '';
		$contact = isset($contact) ? $this->db->real_escape_string($contact) : '';
		$address = isset($address) ? $this->db->real_escape_string($address) : '';
		$email = isset($email) ? $this->db->real_escape_string($email) : '';
		$pass_hash = isset($password) ? md5($password) : '';

		$data = " name = '$name' ";
		$data .= ", contact = '$contact' ";
		$data .= ", address = '$address' ";
		$data .= ", username = '$email' ";
		$data .= ", password = '$pass_hash' ";
		$data .= ", type = 3";

		$chk_res = $this->db->query("SELECT * FROM users where username = '$email' ");
		$chk = ($chk_res && $chk_res instanceof mysqli_result) ? $chk_res->num_rows : 0;
		if($chk > 0){
			return 2;
		}
		$save = $this->db->query("INSERT INTO users set ".$data);
		if($save){
			$qry = $this->db->query("SELECT * FROM users where username = '".$email."' and password = '".$pass_hash."' ");
			if($qry && $qry instanceof mysqli_result && $qry->num_rows > 0){
				foreach ($qry->fetch_array() as $key => $value) {
					if($key != 'password' && $key != 'passwors' && !is_numeric($key))
						$_SESSION['login_'.$key] = $value;
				}
			}
			return 1;
		}
		return 0;
	}

	function save_settings(){
		extract($_POST);
		$name = isset($name) ? str_replace("'","&#x2019;",$name) : '';
		$email = isset($email) ? $this->db->real_escape_string($email) : '';
		$contact = isset($contact) ? $this->db->real_escape_string($contact) : '';
		$about = isset($about) ? htmlentities(str_replace("'","&#x2019;",$about)) : '';

		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '$about' ";
		if(isset($_FILES['img']['tmp_name']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'../assets/img/'. $fname);
			$data .= ", cover_img = '$fname' ";
		}
		
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk && $chk instanceof mysqli_result && $chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
			$query_res = $this->db->query("SELECT * FROM system_settings limit 1");
			if ($query_res && $query_res instanceof mysqli_result) {
				$query = $query_res->fetch_array();
				if ($query) {
					foreach ($query as $key => $value) {
						if(!is_numeric($key))
							$_SESSION['setting_'.$key] = $value;
					}
				}
			}
			return 1;
		}
		return 0;
	}
	
	function save_loan_type(){
		extract($_POST);
		$type_name = isset($type_name) ? $this->db->real_escape_string($type_name) : '';
		$description = isset($description) ? $this->db->real_escape_string($description) : '';
		$data = " type_name = '$type_name' ";
		$data .= " , description = '$description' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO loan_types set ".$data);
		}else{
			$save = $this->db->query("UPDATE loan_types set ".$data." where id=".(int)$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_loan_type(){
		extract($_POST);
		$id = isset($id) ? (int)$id : 0;
		$delete = $this->db->query("DELETE FROM loan_types where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_plan(){
		extract($_POST);
		$months = isset($months) ? (int)$months : 0;
		$interest_percentage = isset($interest_percentage) ? (float)$interest_percentage : 0;
		$penalty_rate = isset($penalty_rate) ? (float)$penalty_rate : 0;

		$data = " months = '$months' ";
		$data .= ", interest_percentage = '$interest_percentage' ";
		$data .= ", penalty_rate = '$penalty_rate' ";
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO loan_plan set ".$data);
		}else{
			$save = $this->db->query("UPDATE loan_plan set ".$data." where id=".(int)$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_plan(){
		extract($_POST);
		$id = isset($id) ? (int)$id : 0;
		$delete = $this->db->query("DELETE FROM loan_plan where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_borrower(){
		extract($_POST);
		$lastname = isset($lastname) ? $this->db->real_escape_string($lastname) : '';
		$firstname = isset($firstname) ? $this->db->real_escape_string($firstname) : '';
		$middlename = isset($middlename) ? $this->db->real_escape_string($middlename) : '';
		$address = isset($address) ? $this->db->real_escape_string($address) : '';
		$contact_no = isset($contact_no) ? $this->db->real_escape_string($contact_no) : '';
		$email = isset($email) ? $this->db->real_escape_string($email) : '';
		$tax_id = isset($tax_id) ? $this->db->real_escape_string($tax_id) : '';

		$data = " lastname = '$lastname' ";
		$data .= ", firstname = '$firstname' ";
		$data .= ", middlename = '$middlename' ";
		$data .= ", address = '$address' ";
		$data .= ", contact_no = '$contact_no' ";
		$data .= ", email = '$email' ";
		$data .= ", tax_id = '$tax_id' ";
		
		if(empty($id)){
			$save = $this->db->query("INSERT INTO borrowers set ".$data);
		}else{
			$save = $this->db->query("UPDATE borrowers set ".$data." where id=".(int)$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_borrower(){
		extract($_POST);
		$id = isset($id) ? (int)$id : 0;
		$delete = $this->db->query("DELETE FROM borrowers where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_loan(){
		extract($_POST);
		$borrower_id = isset($borrower_id) ? (int)$borrower_id : 0;
		$loan_type_id = isset($loan_type_id) ? (int)$loan_type_id : 0;
		$plan_id = isset($plan_id) ? (int)$plan_id : 0;
		$amount = isset($amount) ? (float)$amount : 0;
		$purpose = isset($purpose) ? $this->db->real_escape_string($purpose) : '';

		$data = " borrower_id = $borrower_id ";
		$data .= " , loan_type_id = '$loan_type_id' ";
		$data .= " , plan_id = '$plan_id' ";
		$data .= " , amount = '$amount' ";
		$data .= " , purpose = '$purpose' ";

		$id_val = !empty($id) ? (int)$id : 0;

		if(isset($status)){
			$status = (int)$status;
			$data .= " , status = '$status' ";
			if($status == 2 && $id_val > 0){
				$plan_res = $this->db->query("SELECT * FROM loan_plan where id = $plan_id ");
				if ($plan_res && $plan_res instanceof mysqli_result && $plan_res->num_rows > 0) {
					$plan = $plan_res->fetch_array();
					$sid = [];
					for($i= 1; $i <= $plan['months'];$i++){
						$date = date("Y-m-d",strtotime(date("Y-m-d")." +".$i." months"));
						$chk = $this->db->query("SELECT * FROM loan_schedules where loan_id = $id_val and date(date_due) ='$date'  ");
						if($chk && $chk instanceof mysqli_result && $chk->num_rows > 0){
							$ls_id = $chk->fetch_array()['id'];
							$this->db->query("UPDATE loan_schedules set loan_id = $id_val, date_due ='$date' where id = $ls_id ");
						}else{
							$this->db->query("INSERT INTO loan_schedules set loan_id = $id_val, date_due ='$date' ");
							$ls_id = $this->db->insert_id;
						}
						$sid[] = $ls_id;
					}
					if (!empty($sid)) {
						$sid_str = implode(",",$sid);
						$this->db->query("DELETE FROM loan_schedules where loan_id = $id_val and id not in ($sid_str) ");
					}
				}
				$data .= " , date_released = '".date("Y-m-d H:i")."' ";
			}else if ($id_val > 0){
				$chk_res = $this->db->query("SELECT * FROM loan_schedules where loan_id = $id_val");
				if($chk_res && $chk_res instanceof mysqli_result && $chk_res->num_rows > 0){
					$this->db->query("DELETE FROM loan_schedules where loan_id = $id_val ");
				}
			}
		}

		if(empty($id_val)){
			$ref_no = mt_rand(1,99999999);
			$i= 1;
			while($i== 1){
				$check = $this->db->query("SELECT * FROM loan_list where ref_no ='$ref_no' ");
				if($check && $check instanceof mysqli_result && $check->num_rows > 0){
					$ref_no = mt_rand(1,99999999);
				}else{
					$i = 0;
				}
			}
			$data .= " , ref_no = '$ref_no' ";
			$save = $this->db->query("INSERT INTO loan_list set ".$data);
		}else{
			$save = $this->db->query("UPDATE loan_list set ".$data." where id=".$id_val);
		}

		if($save)
			return 1;
		return 0;
	}

	function delete_loan(){
		extract($_POST);
		$id = isset($id) ? (int)$id : 0;
		$delete = $this->db->query("DELETE FROM loan_list where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}

	function save_payment(){
		extract($_POST);
		$loan_id = isset($loan_id) ? (int)$loan_id : 0;
		$payee = isset($payee) ? $this->db->real_escape_string($payee) : '';
		$amount = isset($amount) ? (float)$amount : 0;
		$penalty_amount = isset($penalty_amount) ? (float)$penalty_amount : 0;
		$overdue = isset($overdue) ? (int)$overdue : 0;

		$data = " loan_id = $loan_id ";
		$data .= " , payee = '$payee' ";
		$data .= " , amount = '$amount' ";
		$data .= " , penalty_amount = '$penalty_amount' ";
		$data .= " , overdue = '$overdue' ";

		if(empty($id)){
			$save = $this->db->query("INSERT INTO payments set ".$data);
		}else{
			$save = $this->db->query("UPDATE payments set ".$data." where id = ".(int)$id);
		}
		if($save)
			return 1;
		return 0;
	}

	function delete_payment(){
		extract($_POST);
		$id = isset($id) ? (int)$id : 0;
		$delete = $this->db->query("DELETE FROM payments where id = ".$id);
		if($delete)
			return 1;
		return 0;
	}
}
