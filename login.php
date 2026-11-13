<?php 
ob_start();
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
if(isset($_SESSION['login_id'])) {
    header("location:index.php?page=home");
    exit;
}
include('./db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admin Login | Loan Management System</title>
 	
  <?php include('./header.php'); ?>

</head>
<style>
	html, body {
		width: 100%;
		height: 100%;
		margin: 0;
		padding: 0;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	}
	main#main {
		width: 100%;
		height: 100%;
		position: relative;
		overflow: hidden;
		display: flex;
	}
	#login-left {
		width: 60%;
		height: 100%;
		position: relative;
		background: url(assets/img/loan-cover.jpg) no-repeat center center;
		background-size: cover;
	}
	#login-left::before {
		content: "";
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: linear-gradient(135deg, rgba(0, 50, 100, 0.45) 0%, rgba(0, 0, 0, 0.6) 100%);
	}
	#login-left .hero-text {
		position: absolute;
		bottom: 40px;
		left: 40px;
		color: #ffffff;
		z-index: 2;
		max-width: 500px;
	}
	#login-left .hero-text h2 {
		font-size: 2.2rem;
		font-weight: 700;
		margin-bottom: 8px;
		text-shadow: 0 2px 8px rgba(0,0,0,0.5);
	}
	#login-left .hero-text p {
		font-size: 1.05rem;
		opacity: 0.9;
		text-shadow: 0 1px 4px rgba(0,0,0,0.5);
	}
	#login-right {
		width: 40%;
		height: 100%;
		background: #111827;
		display: flex;
		align-items: center;
		justify-content: center;
		position: relative;
		padding: 24px;
	}
	#login-right .card {
		width: 100%;
		max-width: 420px;
		border-radius: 14px;
		box-shadow: 0 10px 30px rgba(0,0,0,0.4);
		border: 1px solid rgba(255,255,255,0.08);
		background: #ffffff;
		z-index: 1;
	}
	.logo-badge {
		width: 76px;
		height: 76px;
		margin: 0 auto 12px auto;
		background: #e3f2fd;
		color: #007bff;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 2.6rem;
		box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
	}
	.form-control:focus {
		border-color: #007bff;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
	}

	@media (max-width: 991px) {
		#login-left {
			display: none;
		}
		#login-right {
			width: 100%;
			background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%);
		}
	}
</style>

<body>

  <main id="main">
  		<div id="login-left">
  			<div class="hero-text">
  				<h2>Loan Management System</h2>
  				<p>Seamlessly manage loan applications, borrowers, repayment schedules, and ledger payments.</p>
  			</div>
  		</div>

  		<div id="login-right">
  			<div class="card">
  				<div class="card-body p-4">
  					<div class="logo-badge">
		  				<i class="fa fa-money-bill-wave"></i>
		  			</div>
		  			<h4 class="text-center font-weight-bold text-dark mb-1">Loan Management System</h4>
		  			<p class="text-center text-muted small mb-3">Sign in to your Administrator Account</p>
		  			
  					<form id="login-form">
  						<div id="msg-container"></div>
  						<div class="form-group mb-3">
  							<label for="username" class="font-weight-semibold text-dark small mb-1">Username</label>
  							<div class="input-group">
  								<div class="input-group-prepend">
  									<span class="input-group-text bg-light border-right-0"><i class="fa fa-user text-muted"></i></span>
  								</div>
  								<input type="text" id="username" name="username" class="form-control border-left-0" placeholder="admin" required autocomplete="username">
  							</div>
  						</div>
  						<div class="form-group mb-3">
  							<label for="password" class="font-weight-semibold text-dark small mb-1">Password</label>
  							<div class="input-group">
  								<div class="input-group-prepend">
  									<span class="input-group-text bg-light border-right-0"><i class="fa fa-lock text-muted"></i></span>
  								</div>
  								<input type="password" id="password" name="password" class="form-control border-left-0 border-right-0" placeholder="admin123" required autocomplete="current-password">
  								<div class="input-group-append">
  									<button class="btn btn-outline-secondary border-left-0 bg-light" type="button" id="togglePassword"><i class="fa fa-eye-slash text-muted" id="toggleIcon"></i></button>
  								</div>
  							</div>
  						</div>
  						<button type="submit" id="loginBtn" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm mt-3">
  							<i class="fa fa-sign-in-alt mr-2"></i>Sign In
  						</button>
  					</form>

  					<div class="text-center mt-3 pt-3 border-top">
  						<small class="text-muted"><i class="fa fa-shield-alt mr-1"></i>Default Credentials: <strong>admin</strong> / <strong>admin123</strong></small>
  					</div>
  					<div class="text-center mt-2">
  						<small><a href="db_setup.php" class="text-primary font-weight-bold"><i class="fa fa-database mr-1"></i>Aiven Database Setup & Health Check</a></small>
  					</div>
  				</div>
  			</div>
  		</div>

  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

</body>
<script>
	// Password visibility toggle
	$('#togglePassword').click(function(){
		var passInput = $('#password');
		var icon = $('#toggleIcon');
		if(passInput.attr('type') === 'password'){
			passInput.attr('type', 'text');
			icon.removeClass('fa-eye-slash').addClass('fa-eye');
		} else {
			passInput.attr('type', 'password');
			icon.removeClass('fa-eye').addClass('fa-eye-slash');
		}
	});

	// Handle login submission
	$('#login-form').submit(function(e){
		e.preventDefault();
		var btn = $('#loginBtn');
		var originalHtml = btn.html();
		btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Authenticating...');
		$('#msg-container').empty();

		$.ajax({
			url:'ajax.php?action=login',
			method:'POST',
			data:$(this).serialize(),
			error:function(err){
				console.log(err);
				$('#msg-container').html('<div class="alert alert-danger py-2 small"><i class="fa fa-exclamation-triangle mr-1"></i>Network or server error. Please try again.</div>');
				btn.removeAttr('disabled').html(originalHtml);
			},
			success:function(resp){
				var cleanResp = $.trim(resp);
				if(cleanResp == 1 || cleanResp == '1' || cleanResp == 2 || cleanResp == '2'){
					btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fa fa-check mr-2"></i>Success! Redirecting...');
					setTimeout(function(){
						location.href = 'index.php?page=home';
					}, 400);
				}else{
					$('#msg-container').html('<div class="alert alert-danger py-2 small"><i class="fa fa-times-circle mr-1"></i>Username or password is incorrect.</div>');
					btn.removeAttr('disabled').html(originalHtml);
				}
			}
		});
	});
</script>	
</html>
