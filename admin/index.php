<?php
include '../libs/core.php';

function clean($var)
{
	global $mysqli;
	$var = trim($var);
	return $mysqli->real_escape_string($var);
}

if (isset($_POST['admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

	if (empty($username) || empty($password)) {
        $alert = "Please fill in all fields!";
    } else {
		// Fetch stored username
		$stmt = $mysqli->prepare("SELECT value FROM settings WHERE name = ?");
		$username_key = 'username';
		$stmt->bind_param("s", $username_key);
		$stmt->execute();
		$stmt->bind_result($stored_username);
		$stmt->fetch();
		$stmt->close();

		// Fetch stored hashed password
		$stmt = $mysqli->prepare("SELECT value FROM settings WHERE name = ?");
		$password_key = 'password';
		$stmt->bind_param("s", $password_key);
		$stmt->execute();
		$stmt->bind_result($stored_hashed_password);
		$stmt->fetch();
		$stmt->close();

		// Verify
		if ($username === $stored_username && password_verify($password, $stored_hashed_password)) {
			$_SESSION['logged_in'] = true;
			$_SESSION['admin_username'] = $username;
			header("Refresh:0");
			exit();
		} else {
			$alert = "Invalid username or password.";
		}
	}
} elseif (!isset($_SESSION['logged_in'])) {
	$alert = "Please Login First!";
}

if(isset($_GET['logout'])) {
	session_unset();
	session_destroy();
	header('Location: ./');
}

if (isset($_POST['general'])) {
    $updates = [
        'name' => clean($_POST['name']),
        'description' => clean($_POST['description']),
        'currency' => clean($_POST['currency']),
        'faucetpay_api' => clean($_POST['api']),
        'timer' => clean($_POST['timer']),
        'reward' => clean($_POST['reward']),
        'referral' => clean($_POST['ref']),
        'shortlink_status' => clean($_POST['status']),
        'shortlink_reward' => clean($_POST['rewardlink']),
        'shortlink_force' => clean($_POST['force']),
    ];

    foreach ($updates as $name => $value) {
        if ($name === 'timer' && $value === 'GOT_YOU_CHEATER') {
            // do nothing
            continue;
        }
        $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $value, $name);
            $stmt->execute();
            $stmt->close();
        }
    }
} elseif (isset($_POST['captchas'])) {
    $updates = [
        'captcha' => clean($_POST['captcha']),
        'recaptcha_site_key' => clean($_POST['repub']),
        'recaptcha_secret_key' => clean($_POST['resec']),
        'turnstile_site_key' => clean($_POST['turnsite']),
        'turnstile_secret_key' => clean($_POST['turnsec']),
    ];

    foreach ($updates as $name => $value) {
        $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $value, $name);
            $stmt->execute();
            $stmt->close();
        }
    }
} elseif (isset($_POST['advertisement'])) {
    $updates = [
        'top_ad' => clean($_POST['topad']),
        'left_ad' => clean($_POST['leftad']),
        'right_ad' => clean($_POST['rightad']),
        'above_ad' => clean($_POST['abovead']),
        'bottom_ad' => clean($_POST['bottomad']),
        'modal_ad' => clean($_POST['modalad']),
    ];

    foreach ($updates as $name => $value) {
        $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $value, $name);
            $stmt->execute();
            $stmt->close();
        }
    }
} elseif (isset($_POST['security'])) {
    $iphub = clean($_POST['iphub']);
    $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = 'iphub'");
    if ($stmt) {
        $stmt->bind_param("s", $iphub);
        $stmt->execute();
        $stmt->close();
    }

    if (!empty($_POST['banadddress'])) {
        $banadddress = clean($_POST['banadddress']);
        $stmt = $mysqli->prepare("INSERT INTO address_blocked (address) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $banadddress);
            $stmt->execute();
            $stmt->close();
        }
    }
    if (!empty($_POST['banip'])) {
        $banip = clean($_POST['banip']);
        $stmt = $mysqli->prepare("INSERT INTO ip_blocked (address) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $banip);
            $stmt->execute();
            $stmt->close();
        }
    }
    if (!empty($_POST['unbanadddress'])) {
        $unbanadddress = clean($_POST['unbanadddress']);
        $stmt = $mysqli->prepare("DELETE FROM address_blocked WHERE address = ?");
        if ($stmt) {
            $stmt->bind_param("s", $unbanadddress);
            $stmt->execute();
            $stmt->close();
        }
    }
    if (!empty($_POST['unbanip'])) {
        $unbanip = clean($_POST['unbanip']);
        $stmt = $mysqli->prepare("DELETE FROM ip_blocked WHERE address = ?");
        if ($stmt) {
            $stmt->bind_param("s", $unbanip);
            $stmt->execute();
            $stmt->close();
        }
    }
} elseif (isset($_POST['adminInfo'])) {
    $name = trim($_POST['username']);
    $pass = $_POST['password'];

    if (empty($name) || empty($pass)) {
        $msg = "Please fill in all fields!"; 
        $type = "warning";
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = 'username'");
        if ($stmt) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $mysqli->prepare("UPDATE settings SET value = ? WHERE name = 'password'");
        if ($stmt) {
            $stmt->bind_param("s", $hashed_pass);
            $stmt->execute();
            $stmt->close();
        }

        $msg = "Details updated successfully!";
        $type = "success";
    }
}

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?= $faucet['name'] ?> - <?= $faucet['description'] ?></title>
	<link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
	<!-- ===== Google Fonts | Roboto ===== -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
	<!-- ===== Custom Bootstrap Stylesheet ===== -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
	<style type="text/css">
		body {
			font-family: 'Roboto', sans-serif;
		}

		.bg-blue {
			background: #007bff;
		}
		
		.text-blue {
			color: #007bff;
		}
		
		.shadow-blue{
			box-shadow: 5px 5px 0 0 rgba(0, 123, 255, .5);
			transition: all .3s ease-in;
		}

		.shadow-blue:hover {
		    box-shadow: none;
		}

		.shadow-green{
			box-shadow: 5px 5px 0 0 rgba(40, 167, 69, .5);
			transition: all .3s ease-in;
		}

		.shadow-green:hover {
		    box-shadow: none;
		}

		.alert {
			margin-bottom: 20px;
		}

		button {
			cursor: pointer;
		}

		.login-wrapper {
			width: 100%;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #030712;
			color: #f3f4f6;
		}
		.login-wrapper .login {
			background: #1e293b;
		}
	</style>
</head>

<body>
	<?php if (!isset($_SESSION['logged_in'])) {?>
		<?php include_once 'login.php'; ?>
	<?php } else { ?>
		<div class="container">
			<h2 class="bg-blue shadow-blue text-center my-3 text-white rounded p-3"><?= $faucet['name'] ?> Admin Panel</h2>

			<ul class="nav nav-pills">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#general">General</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#captcha">Captcha</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#ads">Ads</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#security">Security</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#admin">Profile</a>
				</li>
				<ul class="nav nav-pills ml-auto">
					<li class="nav-item ml-1">
						<a class="nav-link btn-sm btn-success" type="button" target="_blank" href="<?= $baseUrl ?>">Visit Website</a>
					</li>
					<li class="nav-item ml-1">
						<a class="nav-link btn-sm btn-danger" type="button" href="?logout">Logout</a>
					</li>
				</ul>
			</ul>
			<div id="myTabContent" class="tab-content">
				<div class="tab-pane fade show active" id="general">

					<form action="" method="post">
						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">General Settings</div>
						<div class="form-group">
							<label for="name">Name</label>
							<input type="text" name="name" class="form-control" id="name" aria-describedby="namehelp" value="<?= get_data('name') ?>">
							<small id="namehelp" class="form-text text-muted">Your Faucet's Name</small>
						</div>
						<div class="form-group">
							<label for="description">Description</label>
							<input type="text" name="description" class="form-control" id="description" aria-describedby="descriptionhelp" value="<?= get_data('description') ?>">
							<small id="descriptionhelp" class="form-text text-muted">Your Faucet's Description</small>
						</div>

						<!-- ======= Rewards Setting Section ======= -->
						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">Reward Settings</div>
						<div class="form-group">
							<label for="api">FaucetPay Api</label>
							<input type="password" name="api" class="form-control" id="api" aria-describedby="apihelp" value="GOT_YOU_CHEATER">
							<small id="apihelp" class="form-text text-muted">FaucetPay Api</small>
						</div>
						
						<div class="form-group">
                            <label for="currency">Currency</label>
                            <select name="currency" id="currency" class="form-control" aria-describedby="currencyhelp" required>
                                <?php
                                $currentCurrency = get_data('currency');
                                foreach ($currencies as $code => $name) {
                                    $selected = ($code == $currentCurrency) ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
                                ?>
                            </select>
                            <small id="currencyhelp" class="form-text text-muted">Your Faucet's Currency Supported By FaucetPay</small>
                        </div>

						<div class="form-group">
							<label for="currency">Timer</label>
							<input type="number" name="timer" class="form-control" id="timer" aria-describedby="timerhelp" value="<?= get_data('timer') ?>">
							<small id="timerhelp" class="form-text text-muted">Your Faucet's Timer in seconds</small>
						</div>
						<div class="form-group">
							<label for="reward">Reward</label>
							<input type="text" name="reward" class="form-control" id="reward" aria-describedby="rewardhelp" value="<?= get_data('reward') ?>">
							<small id="rewardhelp" class="form-text text-muted">Your Faucet's Reward</small>
						</div>
						<div class="form-group">
							<label for="ref">Referral Commision</label>
							<input type="number" name="ref" class="form-control" id="ref" aria-describedby="refhelp" value="<?= get_data('referral') ?>">
							<small id="refhelp" class="form-text text-muted">Referral Commision in %</small>
						</div>

						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">Short-Link Settings</div>
						<div class="bg-info text-center text-white p-2 my-2 rounded">Open libs/config.php to setup your Short-Links</div>
						<div class="form-group">
							<label for="rewardlink">Short Link Reward</label>
							<input type="text" name="rewardlink" class="form-control" id="rewardlink" aria-describedby="rewardlinkhelp" value="<?= get_data('shortlink_reward') ?>">
							<small id="rewardlinkhelp" class="form-text text-muted">Your Faucet's Short Link Reward</small>
						</div>
						<div class="form-group">
							<label for="status">Short Link Status</label>
							<select class="form-control" id="status" name="status">
								<?php
								$status = get_data('shortlink_status');
								$options = ['on' => 'On', 'off' => 'Off'];
								foreach ($options as $code => $name) {
									$selected = ($code == $status) ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="force">Force Short Link</label>
							<select class="form-control" id="force" name="force">
								<?php
								$status = get_data('shortlink_force');
								$options = ['on' => 'On', 'off' => 'Off'];
								foreach ($options as $code => $name) {
									$selected = ($code == $status) ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
								?>
							</select>
						</div>
						<button type="submit" name="general" class="btn btn-success shadow-green btn-lg btn-block">Save Changes</button>
					</form>

				</div>
				<div class="tab-pane fade" id="captcha">
					<form action="" method="post">
						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">Captcha Settings</div>
						<div class="form-group">
							<label for="captcha">Captcha Type</label>
							<select class="form-control" id="captcha" name="captcha">
								<?php
								$currentCaptcha = get_data('captcha');
								foreach ($captchas as $code => $name) {
                                    $selected = ($code == $currentCaptcha) ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="repub">Recaptcha Site Key</label>
							<input type="text" name="repub" class="form-control" id="repub" value="<?= get_data('recaptcha_site_key') ?>">
						</div>
						<div class="form-group">
							<label for="resec">Recaptcha Secret Key</label>
							<input type="text" name="resec" class="form-control" id="resec" value="<?= get_data('recaptcha_secret_key') ?>">
						</div>
						<div class="form-group">
							<label for="turnsite">Turnstile Site Key</label>
							<input type="text" name="turnsite" class="form-control" id="turnsite" value="<?= get_data('turnstile_site_key') ?>">
						</div>
						<div class="form-group">
							<label for="turnsec">Turnstile Secret Key</label>
							<input type="text" name="turnsec" class="form-control" id="turnsec" value="<?= get_data('turnstile_secret_key') ?>">
						</div>
						<button type="submit" name="captchas" class="btn btn-success shadow-green btn-lg btn-block">Save Changes</button>
					</form>
				</div>
				<div class="tab-pane fade" id="ads">
					<form action="" method="post">
						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">Advertisements</div>
						<div class="form-group">
							<label for="topad">Top Ad</label>
							<textarea class="form-control" id="topad" rows="4" name="topad"><?= get_data('top_ad') ?></textarea>
						</div>
						<div class="form-group">
							<label for="leftad">Left Ad</label>
							<textarea class="form-control" id="leftad" rows="4" name="leftad"><?= get_data('left_ad') ?></textarea>
						</div>
						<div class="form-group">
							<label for="rightad">Right Ad</label>
							<textarea class="form-control" id="rightad" rows="4" name="rightad"><?= get_data('right_ad') ?></textarea>
						</div>
						<div class="form-group">
							<label for="abovead">Middle Ad</label>
							<textarea class="form-control" id="abovead" rows="4" name="abovead"><?= get_data('middle_ad') ?></textarea>
						</div>
						<div class="form-group">
							<label for="bottomad">Bottom Ad</label>
							<textarea class="form-control" id="bottomad" rows="4" name="bottomad"><?= get_data('bottom_ad') ?></textarea>
						</div>
						<div class="form-group">
							<label for="modalad">Modal Ad</label>
							<textarea class="form-control" id="modalad" rows="4" name="modalad"><?= get_data('modal_ad') ?></textarea>
						</div>
						<button type="submit" name="advertisement" class="btn btn-success shadow-green btn-lg btn-block">Save Changes</button>
					</form>
				</div>
				<div class="tab-pane fade" id="security">
					<form action="" method="post">
						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">Security Settings</div>
						<div class="form-group">
							<label for="iphub">IpHub Api</label>
							<input type="text" name="iphub" class="form-control" id="iphub" aria-describedby="iphubhelp" value="<?= get_data('iphub') ?>">
							<small id="iphubhelp" class="form-text text-muted">Get your own api at <a href="https://iphub.info" class="font-weight-bold text-blue" target="_blank">iphub.info</a></small>
						</div>
						<div class="form-group">
							<label for="banadddress">Ban Address</label>
							<input type="text" name="banadddress" class="form-control" id="banadddress" value="">
						</div>
						<div class="form-group">
							<label for="unbanadddress">UnBan Address</label>
							<input type="text" name="unbanadddress" class="form-control" id="unbanadddress" value="">
						</div>
						<div class="form-group">
							<label for="banip">Ban Ip</label>
							<input type="text" name="banip" class="form-control" id="banip" value="">
						</div>
						<div class="form-group">
							<label for="unbanip">UnBan Ip</label>
							<input type="text" name="unbanip" class="form-control" id="unbanip" value="">
						</div>
						<button type="submit" name="security" class="btn btn-success shadow-green btn-lg btn-block">Save Changes</button>
					</form>
				</div>
				<div class="tab-pane fade" id="admin">
					<form action="" method="post">
						<div class="bg-blue shadow-blue text-center text-white font-weight-bold p-2 my-2 rounded">Admin Settings</div>
						<?php if (isset($msg) && isset($type)) { 
							// Determine the class for styling based on message type
                			$class = ($type == 'success') ? 'alert-success' : (($type == 'warning') ? 'alert-warning' : 'alert-info');	
						?>
							<div class="alert <?= $class ?> text-center font-weight-bold" role="alert">
                    			<?= htmlspecialchars($msg) ?>
                			</div>
						<?php } ?>
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" name="username" class="form-control" id="username" required>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="text" name="password" class="form-control" id="password" required>
						</div>
						<button type="submit" name="adminInfo" class="btn btn-success shadow-green btn-lg btn-block">Update Details</button>
					</form>
				</div>
			</div>
			<footer class="text-center text-dark mt-3">
				<!--- Please do not remove the link to support us, thanks! -->
				<p>&copy; <?= date('Y') ?> <a class="font-weight-bold" href='<?= $faucet['url'] ?>'><?= $faucet['name'] ?></a>, Powered by <a class="font-weight-bold" href='https://github.com/BlazeCoderLab/CoinFlow-Faucet-Script' target="_blank">CoinFlow Script</a></p>
			</footer>
		</div>
	<?php } ?>
	<!-- ======= JS Files ======= -->
	<script src="../assets/js/jquery-3.2.1.min.js"></script>
	<script src="../assets/js/popper.min.js"></script>
	<script src="../assets/js/bootstrap.min.js"></script>
</body>

</html>
<?php
$mysqli->close();
?>