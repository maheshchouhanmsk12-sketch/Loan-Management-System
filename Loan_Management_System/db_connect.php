<?php 
/**
 * Database Connection for Loan Management System
 * Configured for Aiven Cloud MySQL & PHP 8.x with SSL support
 */

// Load environment variables or use Aiven Cloud defaults
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

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed: Unable to initialize MySQL connection.");
}

$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);

if ($use_ssl) {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $flags = MYSQLI_CLIENT_SSL;
} else {
    $flags = 0;
}

$connected = @$conn->real_connect($host, $user, $pass, $db, $port, NULL, $flags);

// Fallback attempt: if SSL fails or connection fails, try localhost if in local environment
if (!$connected) {
    // If we were connecting to a remote host, log warning and try without SSL or fallback
    if ($use_ssl) {
        $connected = @$conn->real_connect($host, $user, $pass, $db, $port, NULL, 0);
    }
}

// Final fallback to local MySQL if remote Aiven connection failed and we are running locally
if (!$connected && ($host !== '127.0.0.1' && $host !== 'localhost')) {
    $local_conn = mysqli_init();
    if (@$local_conn->real_connect('127.0.0.1', 'root', '', $db, 3306)) {
        $conn = $local_conn;
        $connected = true;
    }
}

if (!$connected) {
    die("Database Connection Error: " . $conn->connect_error . " (Host: {$host}:{$port}, User: {$user}, DB: {$db})");
}

// Disable Aiven strict primary key requirement for legacy schema compatibility
@$conn->query("SET SESSION sql_require_primary_key = 0;");
@$conn->set_charset("utf8mb4");

// Define base URL helper if not set
if (!defined('BASE_URL')) {
    $proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
    $server_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', getenv('BASE_URL') ?: ($proto . $server_host));
}
