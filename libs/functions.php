<?php
function get_token($length) {
	$str = "";
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$size = strlen($chars);
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}
	return $str;
}
function get_ip(){ 
	$ip = $_SERVER['REMOTE_ADDR']; 
	return $ip;
}

function captcha_check($response, $secretkey, $type) {
    if ($type === 'recaptcha') {
        $Captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $Captcha_data = array(
            'secret' => $secretkey,
            'response' => $response,
        );
    } elseif ($type === 'turnstile') {
        $Captcha_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $Captcha_data = array(
            'secret' => $secretkey,
            'response' => $response,
        );
    } else {
        return false; // Unsupported CAPTCHA type
    }

    $Captcha_options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($Captcha_data),
        ),
    );
    $Captcha_context = stream_context_create($Captcha_options);
    $Captcha_result = @file_get_contents($Captcha_url, false, $Captcha_context);

    if ($Captcha_result === FALSE) {
        return false;
    }

    $resultJson = json_decode($Captcha_result);
    return ($resultJson && $resultJson->success) ? json_encode(['success' => true]) : json_encode(['success' => false]);
}


function checkaddress($address) {
	global $mysqli;
	global $time;
	global $faucet;
	$check = $mysqli->query("SELECT * FROM logs WHERE wallet_address = '$address'");
	if ($check->num_rows == 1) {
		$check = $check->fetch_assoc();
		$time_claim = $check['claimed_at'];
		$rmn = $time - $time_claim;
		if ($rmn > $faucet['timer']) {
			return 'ok';
		} else {
			$wait  = $time_claim + $faucet['timer'] - $time;
			return $wait;
		}	
	} else {
		return 'ok';
	} 
	return 'no';
}

function checkip($ip) {
	global $mysqli;
	global $time;
	global $faucet;
	$check = $mysqli->query("SELECT * FROM ip_list WHERE ip_address = '$ip'");
	if ($check->num_rows == 1) {
		$check = $check->fetch_assoc();
		$time_claim = $check['last'];
		$rmn = $time - $time_claim;
		if ($rmn > $faucet['timer']) {
			return 'ok';
		} else {
			$wait  = $time_claim + $faucet['timer'] - $time;
			return $wait;
		}	
	} else {
		return 'ok';
	} 	
	return 'no';
}

function his($faucetpay_api) {
	$param = array(
		'api_key' => $faucetpay_api,
		'count' => '10'
	);
	$url = 'https://faucetpay.io/api/v1/payouts';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, count($param));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param); 

	$result = curl_exec($ch);

	curl_close($ch);
	$jsonhis = json_decode($result, TRUE);
	return $jsonhis['rewards'];
}

function iphub($iphub_api) {
	global $ip;
	global $mysqli;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, 'http://v2.api.iphub.info/ip/'.$ip);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Key: ' . $iphub_api));
	$result = curl_exec($ch);
	curl_close($ch);
	$obj = json_decode($result, true);
	if ($obj['block'] == '1') {
		$mysqli->query("INSERT INTO ip_blocked (address) VALUES ('$ip')");
		return 'bad';
	}
}

// Fetch setting value by name
function get_data($name) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT value FROM settings WHERE name = ?");
    if (!$stmt) {
        return false; // Prepare failed
    }
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($value);
    if ($stmt->fetch()) {
        $stmt->close();
        return $value;
    }
    $stmt->close();
    return false; // Not found or error
}


function log_user($address, $ip) {
	global $time;
	global $mysqli;
	$ref ='';
	// save a log of address
	$log_address = $mysqli->query("SELECT * FROM logs WHERE wallet_address = '$address'");
	if ($log_address->num_rows == 1) {
		$mysqli->query("UPDATE logs SET claimed_at = '$time' WHERE wallet_address = '$address'");
	} else {
		$mysqli->query("INSERT INTO logs (wallet_address, ref, claimed_at) VALUES ('$address', '$ref', '$time')");
	}
	// save a log of ip
	$log_ip = $mysqli->query("SELECT * FROM ip_list WHERE ip_address = '$ip'");
	if ($log_ip->num_rows == 1) {
		$mysqli->query("UPDATE ip_list SET last = '$time' WHERE ip_address = '$ip'");
	} else {
		$mysqli->query("INSERT INTO ip_list (ip_address, last) VALUES ('$ip', '$time')");
	} 

}
function check_blocked_address($address) {
	global $mysqli;
	$check = $mysqli->query("SELECT * FROM address_blocked WHERE address='$address' LIMIT 1");
	if ($check->num_rows == 1) {
		return 'blocked';
	}
}

function check_blocked_ip($ip) {
	global $mysqli;
	$check = $mysqli->query("SELECT * FROM ip_blocked WHERE address='$ip' LIMIT 1");
	if ($check->num_rows == 1) {
		return 'blocked';
	}
}

function maskData($str) {
    if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
        // Mask email
        list($user, $domain) = explode('@', $str);
        $userLen = strlen($user);
        if ($userLen <= 6) {
            $userMasked = substr($user, 0, 2) . str_repeat('*', $userLen - 4) . substr($user, -2);
        } else {
            $userMasked = substr($user, 0, 3) . str_repeat('*', $userLen - 6) . substr($user, -3);
        }
        return $userMasked . '@' . $domain;
    } else {
        // Mask generic wallet address (if length greater than 8)
        if (strlen($str) > 8) {
            return substr($str, 0, 4) . '......' . substr($str, -4);
        }
        return $str;
    }
}

function formatAmount($amount) {
    return number_format($amount / 100000000, 8, '.', '');
}

function timeAgo($timestamp)
{
    // Create DateTime objects for the current time and the database timestamp
    $now = new DateTime("now");
    $inserted = new DateTime($timestamp);
 
    // Calculate the time difference. Ensure the older date comes first.
    $interval = $inserted->diff($now);

    if ($interval->y >= 1) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    } elseif ($interval->m >= 1) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    } elseif ($interval->d >= 1) {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    } elseif ($interval->h >= 1) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    } elseif ($interval->i >= 1) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    } elseif ($interval->s >= 1) {
        return $interval->s . ' second' . ($interval->s > 1 ? 's' : '') . ' ago';
    } else {
        return "just now";
    }
}

?>