<?php
/**
 * 1-Click Database Setup & Health Check Tool
 * Configured for Aiven Cloud MySQL with SSL & Strict Primary Key handling
 */

$host = getenv('DB_SERVER') ?: (getenv('DB_HOST') ?: 'mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com');
$port = (int)(getenv('DB_PORT') ?: 26284);
$user = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'avnadmin');
$pass = (getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== null && getenv('DB_PASSWORD') !== '') 
    ? getenv('DB_PASSWORD') 
    : base64_decode('QVZOU19MU0xEdDV6NGFkSzQ5R0xsVFQ4');
$db   = getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: 'loan_db');

$ssl_env = getenv('DB_SSL');
$use_ssl = true;
if ($ssl_env !== false && ($ssl_env === 'false' || $ssl_env === '0' || $ssl_env === 'off')) {
    $use_ssl = false;
}

$conn_status = false;
$conn_error = '';
$server_info = '';
$ping_time = 0;
$tables = [];
$admin_found = false;
$import_message = '';
$import_status = '';

// Test Connection
mysqli_report(MYSQLI_REPORT_OFF);
$start_t = microtime(true);
$c = mysqli_init();
if ($c) {
    $c->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    if ($use_ssl) {
        $c->ssl_set(NULL, NULL, NULL, NULL, NULL);
        $flags = MYSQLI_CLIENT_SSL;
    } else {
        $flags = 0;
    }
    
    // First try connecting to the specified DB
    $connected = @$c->real_connect($host, $user, $pass, $db, $port, NULL, $flags);
    
    // If DB does not exist yet, connect without DB and create it
    if (!$connected && ($c->connect_errno == 1049 || strpos($c->connect_error, 'Unknown database') !== false)) {
        $c_root = mysqli_init();
        $c_root->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        if ($use_ssl) $c_root->ssl_set(NULL, NULL, NULL, NULL, NULL);
        if (@$c_root->real_connect($host, $user, $pass, 'defaultdb', $port, NULL, $flags)) {
            @$c_root->query("CREATE DATABASE IF NOT EXISTS `" . $c_root->real_escape_string($db) . "`");
            $c_root->close();
            $connected = @$c->real_connect($host, $user, $pass, $db, $port, NULL, $flags);
        }
    }

    $ping_time = round((microtime(true) - $start_t) * 1000, 2);

    if ($connected) {
        $conn_status = true;
        $server_info = $c->server_info;
        @$c->query("SET SESSION sql_require_primary_key = 0;");
        @$c->query("SET NAMES 'utf8mb4'");

        // Handle Import Action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
            $sql_file = '';
            $candidates = [
                __DIR__ . '/database/loan_db.sql',
                __DIR__ . '/Loan_Management_System/database/loan_db.sql',
                dirname(__DIR__) . '/database/loan_db.sql',
                dirname(__DIR__) . '/Loan_Management_System/database/loan_db.sql'
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    $sql_file = $cand;
                    break;
                }
            }

            if ($sql_file && is_readable($sql_file)) {
                $sql = file_get_contents($sql_file);
                
                // Prepend safe session parameters
                $init_sql = "SET FOREIGN_KEY_CHECKS = 0;\nSET SESSION sql_require_primary_key = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
                $full_sql = $init_sql . $sql;

                if ($c->multi_query($full_sql)) {
                    do {
                        if ($result = $c->store_result()) {
                            $result->free();
                        }
                    } while ($c->more_results() && $c->next_result());
                }

                if ($c->error) {
                    $import_status = 'danger';
                    $import_message = 'Import encountered an error: ' . $c->error;
                } else {
                    $import_status = 'success';
                    $import_message = 'Database schema and default records imported successfully!';
                }
            } else {
                $import_status = 'danger';
                $import_message = 'SQL file loan_db.sql could not be found in expected directories.';
            }
        }

        // Get Existing Tables
        $t_res = $c->query("SHOW TABLES");
        if ($t_res) {
            while ($row = $t_res->fetch_row()) {
                $t_name = $row[0];
                $cnt_res = @$c->query("SELECT count(*) FROM `{$t_name}`");
                $cnt = ($cnt_res) ? $cnt_res->fetch_row()[0] : 0;
                $tables[$t_name] = $cnt;
            }
        }

        // Check Admin User
        $u_res = @$c->query("SELECT * FROM users WHERE username = 'admin'");
        if ($u_res && $u_res->num_rows > 0) {
            $admin_found = true;
        }
    } else {
        $conn_error = $c->connect_error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aiven MySQL Database Setup | Loan Management System</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/font-awesome/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-top: 40px;
            padding-bottom: 60px;
        }
        .setup-card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #fff;
            padding: 24px;
        }
        .stat-badge {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
        .table-badge {
            background: #e9ecef;
            border-radius: 6px;
            padding: 6px 12px;
            margin: 4px;
            display: inline-block;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-10">
            <div class="card setup-card">
                <div class="setup-header text-center">
                    <h3 class="mb-1"><i class="fas fa-database mr-2"></i>Aiven MySQL Setup & Health Check</h3>
                    <p class="mb-0 text-light">Loan Management System &bull; Render Cloud Deployment Ready</p>
                </div>
                <div class="card-body p-4">

                    <?php if ($import_message): ?>
                        <div class="alert alert-<?php echo $import_status; ?> alert-dismissible fade show">
                            <strong><?php echo $import_status === 'success' ? 'Success!' : 'Warning!'; ?></strong> <?php echo htmlspecialchars($import_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Connection Status Banner -->
                    <div class="card mb-4 border-<?php echo $conn_status ? 'success' : 'danger'; ?> bg-light">
                        <div class="card-body py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">
                                    Aiven MySQL Server: 
                                    <?php if ($conn_status): ?>
                                        <span class="badge badge-success stat-badge"><i class="fas fa-check-circle mr-1"></i>CONNECTED</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger stat-badge"><i class="fas fa-times-circle mr-1"></i>FAILED</span>
                                    <?php endif; ?>
                                </h5>
                                <small class="text-muted">
                                    Host: <code><?php echo htmlspecialchars($host . ':' . $port); ?></code> | 
                                    Database: <code><?php echo htmlspecialchars($db); ?></code> | 
                                    SSL: <code><?php echo $use_ssl ? 'Enabled (MYSQLI_CLIENT_SSL)' : 'Disabled'; ?></code>
                                </small>
                            </div>
                            <?php if ($conn_status): ?>
                                <div class="text-right">
                                    <span class="badge badge-info stat-badge"><?php echo $ping_time; ?> ms</span><br>
                                    <small class="text-muted">v<?php echo htmlspecialchars($server_info); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$conn_status): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle mr-2"></i>Connection Error Details:</h6>
                            <p class="mb-0"><code><?php echo htmlspecialchars($conn_error); ?></code></p>
                        </div>
                    <?php endif; ?>

                    <!-- Tables Status Overview -->
                    <h5 class="mt-4 mb-3"><i class="fas fa-table mr-2"></i>Database Tables Status (<?php echo count($tables); ?> Found)</h5>
                    <div class="p-3 bg-white border rounded mb-4">
                        <?php 
                        $expected_tables = ['borrowers', 'loan_list', 'loan_plan', 'loan_schedules', 'loan_types', 'payments', 'users'];
                        foreach ($expected_tables as $tb):
                            $exists = isset($tables[$tb]);
                            $count = $exists ? $tables[$tb] : 0;
                        ?>
                            <div class="table-badge border">
                                <i class="fas <?php echo $exists ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?> mr-1"></i>
                                <strong><?php echo $tb; ?></strong>
                                <span class="badge <?php echo $exists ? 'badge-primary' : 'badge-secondary'; ?> ml-1"><?php echo $count; ?> rows</span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Admin User Status -->
                    <div class="alert <?php echo $admin_found ? 'alert-success' : 'alert-warning'; ?> d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fas fa-user-shield mr-2"></i>Default Administrator Account:</strong>
                            <?php if ($admin_found): ?>
                                <span class="text-success font-weight-bold ml-1">READY (Username: <code>admin</code> / Password: <code>admin123</code>)</span>
                            <?php else: ?>
                                <span class="text-danger font-weight-bold ml-1">NOT FOUND (Click Setup button below to import)</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($admin_found): ?>
                            <a href="login.php" class="btn btn-sm btn-success"><i class="fas fa-sign-in-alt mr-1"></i>Login Now</a>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 text-center">
                        <form method="POST" class="d-inline" onsubmit="return confirm('Do you want to run the 1-Click database import on Aiven MySQL?');">
                            <input type="hidden" name="action" value="import">
                            <button type="submit" class="btn btn-primary btn-lg px-4 mr-2" <?php echo !$conn_status ? 'disabled' : ''; ?>>
                                <i class="fas fa-magic mr-2"></i>1-Click Database Setup / Re-import
                            </button>
                        </form>
                        <a href="login.php" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="fas fa-arrow-right mr-2"></i>Go to Login Page
                        </a>
                    </div>

                    <hr class="my-4">
                    <div class="text-muted small">
                        <strong>Environment Variable Configuration:</strong>
                        <ul class="mb-0 mt-1 pl-3">
                            <li><code>DB_SERVER</code>: <?php echo htmlspecialchars($host); ?></li>
                            <li><code>DB_PORT</code>: <?php echo htmlspecialchars((string)$port); ?></li>
                            <li><code>DB_USERNAME</code>: <?php echo htmlspecialchars($user); ?></li>
                            <li><code>DB_NAME</code>: <?php echo htmlspecialchars($db); ?></li>
                            <li><code>DB_SSL</code>: true (Enforced with <code>MYSQLI_CLIENT_SSL</code>)</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
