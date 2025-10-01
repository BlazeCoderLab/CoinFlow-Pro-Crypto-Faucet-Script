<?php

// Include your existing database connection which sets $mysqli (MySQLi)
require_once '../libs/config.php';

// Run all table creation queries and inserts in a transaction.
$queries = [

    // address_blocked table
    "CREATE TABLE IF NOT EXISTS `address_blocked` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `address` VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // ip_blocked table
    "CREATE TABLE IF NOT EXISTS `ip_blocked` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `address` VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // ip_list table
    "CREATE TABLE IF NOT EXISTS `ip_list` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `ip_address` VARCHAR(50) NOT NULL,
      `last` INT UNSIGNED NOT NULL,
      INDEX (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // logs table
    "CREATE TABLE IF NOT EXISTS `logs` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `wallet_address` VARCHAR(75) NOT NULL,
      `total_claimed` DECIMAL(20,8) NOT NULL DEFAULT 0,
      `ref` VARCHAR(100) NOT NULL,
      `claimed_at` INT UNSIGNED NOT NULL,
      INDEX (`wallet_address`),
      INDEX (`claimed_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // payouts table
    "CREATE TABLE IF NOT EXISTS `payouts` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `wallet` VARCHAR(255) NOT NULL,
      `reward` DECIMAL(20,8) NOT NULL,
      `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX (`wallet`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // settings table
    "CREATE TABLE IF NOT EXISTS `settings` (
      `id` INT UNSIGNED NOT NULL PRIMARY KEY,
      `name` VARCHAR(50) NOT NULL UNIQUE,
      `value` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // verify_link table
    "CREATE TABLE IF NOT EXISTS `verify_link` (
      `sec_key` VARCHAR(75) NOT NULL,
      `wallet_address` VARCHAR(75) NOT NULL,
      `ip` VARCHAR(30) NOT NULL,
      PRIMARY KEY (`sec_key`),
      INDEX (`wallet_address`),
      INDEX (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// Insert initial settings (id, name, value) tuples
$insertSettings = [
    [1, 'name', 'CoinFlow'],
    [2, 'description', 'Sleek, Fast and Lightweight Crypto Faucet Script'],
    [3, 'currency', 'TRX'],
    [4, 'faucetpay_api', ''],
    [5, 'timer', '60'],
    [6, 'reward', '100'],
    [7, 'referral', '10'],
    [8, 'shortlink_status', 'off'],
    [9, 'shortlink_reward', ''],
    [10, 'shortlink_force', 'off'],
    [11, 'captcha', 'recaptcha'],
    [12, 'recaptcha_site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'],
    [13, 'recaptcha_secret_key', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'],
    [14, 'hcaptcha_site_key', ''],
    [15, 'hcaptcha_secret_key', ''],
    [16, 'turnstile_site_key', ''],
    [17, 'turnstile_secret_key', ''],
    [18, 'username', 'admin'],
    [19, 'password', '$2y$10$uJRJItG3HRwgpFPd/9PiP.o6qlEnlA8yZ8gxHmrCErbov3QyeGE2C'],
    [20, 'top_ad', ''],
    [21, 'left_ad', ''],
    [22, 'right_ad', ''],
    [23, 'middle_ad', ''],
    [24, 'bottom_ad', ''],
    [25, 'modal_ad', ''],
    [26, 'iphub', ''],
    [27, 'balance', '']
];

// Function to execute all queries inside a transaction
function runInstallation($mysqli, $queries, $settings) {
    $mysqli->begin_transaction();

    try {
        // Create tables
        foreach ($queries as $sql) {
            if (!$mysqli->query($sql)) {
                throw new Exception("Error creating table: " . $mysqli->error);
            }
        }

        // Check if settings already exist to prevent duplicate inserts
        $result = $mysqli->query("SELECT COUNT(*) as count FROM `settings`");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                // Insert initial settings
                $stmt = $mysqli->prepare("INSERT INTO `settings` (`id`, `name`, `value`) VALUES (?, ?, ?)");
                foreach ($settings as $setting) {
                    [$id, $name, $value] = $setting;
                    $stmt->bind_param("iss", $id, $name, $value);
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting settings: " . $stmt->error);
                    }
                }
                $stmt->close();
            }
        } else {
            throw new Exception("Could not query settings table.");
        }

        $mysqli->commit();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        return $e->getMessage();
    }
}

// Run installation
$message = runInstallation($mysqli, $queries, $insertSettings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Installation - CoinFlow Pro</title>
    <!-- ===== Bootstrap CDN ===== -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        body{
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1 class="fw-bold text-info">Installation - CoinFlow Pro</h1>
    <?php if ($message === true): ?>
        <p class="my-3 fw-semibold">Installation completed successfully! You can now delete or disable this installer for security reasons.</p>
        <p><a class="btn btn-primary" href="<?= $baseUrl ?>admin">Go to Admin Panel</a></p>
    <?php else: ?>
        <p class="text-warning fw-semibold">Installation failed: <?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</body>
</html>
