<?php 
include 'libs/core.php'; 

if (isset($_GET['r']) && !isset($_COOKIE['ref'])) {
	$reff = $mysqli->real_escape_string($_GET['r']);
	setcookie('ref',  $reff, time() + 604800);
}

if (isset($_POST['address']) and isset($_POST['token'])) { 
	
    # clean user's input
	$address = $mysqli->real_escape_string($_POST['address']);
	if (!isset($_COOKIE['address'])) {
		setcookie('address', $address, time() + 86400);
	} 
    # end 
	if ($_POST['token'] == $_SESSION['token']) {

		if (isset($_POST['g-recaptcha-response']) && $faucet['captcha'] == 'recaptcha') {
			$secret_key = get_data('recaptcha_secret_key');
			$CaptchaCheck = json_decode(captcha_check($_POST['g-recaptcha-response'], $secret_key, 'recaptcha'))->success;
		} elseif (isset($_POST['cf-turnstile-response']) && $faucet['captcha'] == 'turnstile') {
			$secret_key = get_data('turnstile_secret_key');
			$CaptchaCheck = json_decode(captcha_check($_POST['cf-turnstile-response'], $secret_key, 'turnstile'))->success;
		} else {
			$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Invalid Captcha</div></center>"; 
		}
		if ($CaptchaCheck and !isset($alert)) {
			if (check_blocked_ip($ip) == 'blocked') {
				$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Your Ip Is Blocked. Please Contact Admin.</div></center>";
			} elseif (check_blocked_address($address) == 'blocked') {
				$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Your Address Is Blocked. Please Contact Admin.</div></center>";
			} elseif (!empty(get_data('iphub')) and iphub(get_data('iphub')) == 'bad') {
				$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Your Ip Is Blocked By IpHub</div></center>";
				$mysqli->query("INSERT INTO ip_blocked (address) VALUES ('$ip')");
			} elseif (checkaddress($address) !== 'ok') {
				$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning text-center'>You have to wait " . checkaddress($address) . " seconds</div></center>";
			} elseif (checkip($ip) !== 'ok') {
				$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning text-center'>You have to wait " . checkip($ip) . " seconds</div></center>";
			} else {
				
				# Check Short Link
				if (get_data('shortlink_force') == 'on' || (isset($_POST['link']) && get_data('shortlink_status') == 'on')) {
					$key = get_token(15); 
					for ($i=1; $i <= count($link); $i++) { 
						if (!isset($_COOKIE[$i])) {
							$mysqli->query("INSERT INTO verify_link (wallet_address, sec_key, ip) VALUES ('$address', '$key', '$ip')");
							log_user($address, $ip);
							setcookie($i, 'Link Already Visited', time() + 86400);
							$url = $link[$i];
							$full_url = str_replace("{key}",$key,$url);
							$short_link = file_get_contents($full_url);
							break;
						}
					}
					header("Location: ". $short_link);
					exit();
				} else {

					# Normal Faucet Claim
					$faucetpay_api = get_data('faucetpay_api');
					$currency = $faucet['currency'];
					$faucetpay = new FaucetPay($faucetpay_api, $currency);
					$result = $faucetpay->send($address, $faucet['reward'], $ip);
					if (isset($_COOKIE['ref']) && $address !== $_COOKIE['ref']) {
						$ref = $mysqli->real_escape_string($_COOKIE['ref']);
						$amt = floor($faucet['commission'] / 100 * $faucet['reward']);
						$s = $faucetpay->sendReferralEarnings($ref, $amt);
					}
					if ($result['success'] == true) {
						log_user($address, $ip);
						$new_balance = $result['balance'];
						$reward = $faucet['reward'];
						$mysqli->query("INSERT INTO payouts (wallet, reward) VALUES ('$address', '$reward')");
						$mysqli->query("UPDATE settings SET value = '$new_balance' WHERE name = 'balance'");
						$alert = "<center><img style='max-width: 200px;' src='assets/img/trophy.png'><br>{$result['html']}</center>";
					} else {
						$alert = "<center><img style='max-width: 200px;' src='assets/img/trophy.png'><br><div class='alert alert-danger'>Failed to send your reward</div></center>"; 
					}
				}
			}
		} else {
			$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Invalid Captcha</div></center>"; 
		}
	} else {
		$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Invalid Token</div></center>"; 
	}
}

// Check if a User has Completed a Short-Link
if (isset($_GET['k'])) {
	$key = $mysqli->real_escape_string($_GET['k']);
	$check = $mysqli->query("SELECT * FROM verify_link WHERE sec_key = '$key' and ip = '$ip' LIMIT 1");
	if ($check->num_rows == 1) { 
		$check = $check->fetch_assoc();
		$address = $check['wallet_address'];
		$mysqli->query("DELETE FROM verify_link WHERE sec_key = '$key'");
		$faucetpay_api = get_data('faucetpay_api');
		$faucetpay = new FaucetPay($faucetpay_api, $faucet['currency']);
		$rew = get_data('shortlink_reward') + $faucet['reward'];
		$result = $faucetpay->send($address, $rew, $ip);
		log_user($address, $ip);
		$new_balance = $result['balance'];
		$mysqli->query("INSERT INTO payouts (wallet, reward) VALUES ('$address', '$rew')");
		$mysqli->query("UPDATE settings SET value = '$new_balance' WHERE name = 'balance'");
		if (isset($_COOKIE['ref']) && $address !== $_COOKIE['ref']) {
			$ref = $mysqli->real_escape_string($_COOKIE['ref']);
			$amt = floor($faucet['commission'] / 100 * $rew);
			$s = $faucetpay->sendReferralEarnings($ref, $amt);
		}
		$alert = "<center><img style='max-width: 200px;' src='assets/img/trophy.png'><br>{$result['html']}</center>";
	} else {
		$alert = "<center><img style='max-width: 200px;' src='assets/img/bots.png'><br><div class='alert alert-warning'>Invalid Key !</div></center>";
	}
}

$_SESSION['token'] = get_token(70);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?=$faucet['name']?> - <?=$faucet['description']?></title> 
	<link rel="shortcut icon" href="./assets/img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="./assets/img/favicon.ico" type="image/x-icon">
	<!-- ===== Google Fonts | Roboto ===== -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
	<!-- ===== Font-Awesome CDN ===== -->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"> 
	<!-- ===== Custom CSS ===== -->
	<link rel="stylesheet" type="text/css" href="./assets/css/main.css"> 
	<style type="text/css"> 
	body {  
		font-family: 'Roboto', sans-serif;
	}

	img, iframe {
		max-width: 100%;
	}

	.card-custom {
		background-color: rgba(226, 212, 296, 0.3);
		padding: 20px;
		border-radius: 20px;
	}

	.card-custom:hover {
		background-color: rgba(226, 212, 296, 0.5);
	}

	.table-wrapper {
		overflow-x: auto;
	}

	/* .address-input {
		border-radius: .5rem;
	} */

	::selection {
      background-color: #78C2AD; /* Greenish Background for selected text */
      color: #fff; /* Pure White text color */
    }

	#adblock-modal {
		position: fixed;
		top:0; left:0;
		width:100%; height:100%;
		background: rgba(0,0,0,0.85);
		color: white;
		font-family: Arial, sans-serif;
		display: none;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 30px;
		z-index: 99999999;
	}

	#adblock-modal h2 {
		font-size: 2rem;
		margin-bottom: 20px;
	}

	#adblock-modal p {
		font-size: 1.1rem;
		margin-bottom: 30px;
	}

	#adblock-modal button {
		background-color: #ff3b3f;
		border: none;
		padding: 14px 30px;
		color: white;
		font-weight: bold;
		font-size: 1rem;
		border-radius: 6px;
		cursor: pointer;
	}
</style>
</head>
<body> 
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
	    <div class="container">
			<a class="navbar-brand font-weight-bold" href="<?=$baseUrl?>"><?=$faucet['name']?></a>
			<ul class="navbar-nav ml-auto">
				<li class="nav-item active">
					<a class="nav-link" href="#"><i class="fa-solid fa-wallet"></i> <b>Balance:</b> <?= (empty(get_data('balance'))) ? "Make a claim to update it" : ((number_format(get_data('balance') / 100000000, 8, '.', '') . ' ' . $currency_name))?></a>
				</li>
			</ul>
	    </div>
	</nav>

	<div class="d-flex align-items-center justify-content-center mt-4">
		<?= ($ad['top'])?: '<img src="https://placehold.co/728x90">';?>
	</div>

	<div class="container my-4">
		<div class="row">
			<div class="col-sm-3 text-center mt-2">
			<?= ($ad['left'])?: '<img src="https://placehold.co/160x600">';?>	
			</div>
			<div class="col-sm-6 card-custom text-center">
				<div class="alert alert-success">
					<p class="mb-0"><i class="fa-solid fa-trophy"></i> Claim <strong><?= number_format($faucet['reward'] / 100000000, 8, '.', '') ?> <?=$currency_name?></strong> every <strong><?=floor($faucet['timer']/60)?></strong> minutes .</p>
				</div>
				<?php if (isset($alert)) { ?>
				<div class="modal fade" id="alert" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content"> 
							<div class="modal-body">
								<?=$alert?>  
							</div>
						</div>
					</div>
				</div>
				<?php } if (checkip($ip) == 'ok') { ?>
				<form action="" method="post">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<div class="form-group">
						<div class="input-group">
							<input type="text" class="form-control text-center address-input rounded" name="address" <?= (isset($_COOKIE['address'])) ? 'value="' . $_COOKIE['address'] . '"' : 'placeholder="Your FaucetPay Email Address"' ?>>
						</div>
					</div> 
					<div class="form-group">
						<div class="d-flex align-items-center justify-content-center">
							<?= ($ad['middle'])?: '<img src="https://placehold.co/300x250">';?>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group d-flex align-items-center justify-content-center">
							<?=$captcha_display?>
						</div>
					</div>
					<?php if (get_data('shortlink_status') == 'on' && get_data('shortlink_force') !== 'on') { 
						for ($i=1; $i <= count($link); $i++) { 
							if (!isset($_COOKIE[$i])) { ?>
					<label class="custom-control custom-checkbox mb-2">
						<input type="checkbox" name="link" value="yes" class="custom-control-input">
						<span class="custom-control-indicator"></span>
						<span class="custom-control-description text-dark">
							<i class="fa fa-gift" aria-hidden="true"></i> <span class="font-weight-bold">I want <b class="text-white"><?= number_format(get_data('shortlink_reward') / 100000000, 8, '.', '')?> <?=$currency_name?> bonus</b> by completing <b class="text-white">Short-Link</b></span>
						</span>
					</label> 
					<?php break; } }} ?>
					<button type="button" class="btn btn-warning btn-lg btn-block mb-3" data-toggle="modal" data-target="#next"><i class="fa fa-paper-plane" aria-hidden="true"></i> <strong>Claim Your <?=$currency_name?></strong></button>
					<div class="modal fade" id="next" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Final Step</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<div class="d-flex align-items-center justify-content-center">
										<?= ($ad['modal'])?: '<img src="https://placehold.co/300x250">';?>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-primary" id="claim">Claim</button>
								</div>
							</div>
						</div>
					</div>
					<div class="ref-info text-center">
					    <b class="text-dark">Share this link to earn <?= $faucet['commission'] ?>% commission.</b>
					    <input type="text" onclick="this.select();" name="ref" class="form-control text-center text-white border" style="background: transparent" value="<?=$faucet['url']?>?r=<?= (isset($_COOKIE['address'])) ? $_COOKIE['address'] : 'Your_FaucetPay_Email_Address';?>" readonly>
					</div>
				</form>
				<?php } 
				else { 
					$wait= 1; 
					$check = checkip($ip);
                    if ($check === 'ok') {
                        header("Refresh:0");
                        exit();
                    } else {
                        echo "<div class='alert alert-info text-center mt-2' id='waitTime'>" . intval($check) . "</div>";
                    }
				} ?> 
			</div>
			<div class="col-sm-3 text-center mt-2">
				<?= ($ad['right'])?: '<img src="https://placehold.co/160x600">';?>
			</div>
		</div>
	</div>
	<div class="d-flex align-items-center justify-content-center mb-4">
		<?= ($ad['bottom'])?: '<img src="https://placehold.co/728x90">';?>
	</div>
	<div class="container mt-4">
	    <div class="row">
		    <div class="col-md-10 m-auto text-center card-custom">
		        <h2 class="text-center text-white" style="font-weight: bold;">Recent Payouts</h2>
				<div class="table-wrapper">
					<table class="table table-striped">
						<thead class="thead-dark">
							<tr>
							<th scope="col">#</th>
							<th scope="col">Wallet</th>
							<th scope="col">Reward</th>
							<th scope="col">Time</th>
							</tr>
						</thead>
						<tbody class="text-white">
							<?php 
								$result = $mysqli->query("SELECT * FROM payouts ORDER BY id DESC LIMIT 10");
								$currency = $faucet['currency'];
								while ($row = $result->fetch_assoc()) {
							?>
							<tr>
							<td><?= $row['id'] ?></td>
							<td><?= maskData($row['wallet']) ?></td>
							<td><?= formatAmount($row['reward']) . ' ' . $currency ?></td>
							<td><?= timeAgo($row['time']) ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
            </div>
		</div>
	</div>
	<footer class="text-center text-dark mt-3">
		<p>&copy; <?= date('Y') ?> <a class="font-weight-bold text-white" href='<?=$faucet['url']?>'><?=$faucet['name']?></a>, All Rights Reserved. <span id='copyright'>Powered by <a class="font-weight-bold text-white" href='https://github.com/BlazeCoderLab/CoinFlow-Faucet-Script' target="_blank">CoinFlow Script</a></span></p>
	</footer> 
	
	<!-- ======= Anti Ad-blocker Code ======= -->
	<div id="adblock-modal" role="dialog" aria-modal="true" aria-labelledby="adblock-title" aria-describedby="adblock-desc">
		<h2 id="adblock-title">AdBlocker Detected</h2>
		<p id="adblock-desc">Please disable your ad blocker to continue using this site.</p>
		<button id="adblock-close-btn">I have disabled AdBlock</button>
    </div>
	<!-- ======= Anti Ad-blocker Code ======= -->

	<!-- ======= Core Js Files ======= -->
	<script src="assets/js/jquery-3.2.1.min.js"></script>
	<script src="assets/js/popper.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/adblock.js"></script>
	<?php if (isset($alert)) { ?>
		<script type='text/javascript'>$('#alert').modal('show');</script>
	<?php  } ?>
	<?php if (isset($wait)) { ?>
		<script type="text/javascript">
			var faucetUrl = '<?=$faucet['url']?>'; 
			var timeLeft = <?= checkip($ip) ?>; 
			var timerElement = document.getElementById('waitTime');
			
			var countdown = setInterval(function() {
				timeLeft--;
				if (timeLeft <= 0) {
					clearInterval(countdown);
					window.location.href = faucetUrl;
				} else {
					timerElement.textContent = 'You have to wait ' + timeLeft + ' seconds';
				}
			}, 1000);
	<?php  } ?>
	</script>
</body>
</html>
<?php
$mysqli->close();
?>
