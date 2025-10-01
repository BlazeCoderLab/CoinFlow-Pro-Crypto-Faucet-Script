<?php
session_start();

// Require Core Files
require_once 'config.php';
require_once 'functions.php';
require_once 'FaucetPay.php';

$ip = get_ip();
$time = time();

// RootPath Of The Project
define('ROOT_PATH', dirname(__DIR__));

$faucet['name'] = get_data('name');
$faucet['description'] = get_data('description');
$faucet['url'] = $baseUrl;
$faucet['currency'] = get_data('currency');
$faucet['timer'] = get_data('timer');
$faucet['reward'] = get_data('reward');
$reward_rounded = htmlspecialchars(rtrim(rtrim(sprintf("%.8f", $faucet['reward']/100000000), '0'), '.'));
$faucet['commission'] = get_data('referral');
$faucet['captcha'] = get_data('captcha');
$ad['top'] = get_data('top_ad');
$ad['left'] = get_data('left_ad');
$ad['right'] = get_data('right_ad');
$ad['middle'] = get_data('middle_ad');
$ad['bottom'] = get_data('bottom_ad');
$ad['modal'] = get_data('modal_ad');

$currency_name = $faucet['currency'];

// List of Supported Currencies
$currencies = [
    'BTC' => 'Bitcoin',
    'ETH' => 'Ethereum',
    'DOGE' => 'Dogecoin',
    'LTC' => 'Litecoin',
    'BCH' => 'Bitcoin Cash',
    'DASH' => 'Dash',
    'DGB' => 'DigiByte',
    'TRX' => 'Tron',
    'USDT' => 'Tether',
    'FEY' => 'Feyorra',
    'ZEC' => 'Zcash',
    'BNB' => 'Binance Coin',
    'SOL' => 'Solana',
    'XRP' => 'Ripple',
    'POL' => 'Polygon',
    'ADA' => 'Cardano',
    'TON' => 'Toncoin',
    'XLM' => 'Stellar',
    'USDC' => 'USD Coin',
    'XMR' => 'Monero',
    'TARA' => 'Taraxa',
    'TRUMP' => 'Trump',
    'PEPE' => 'Pepe',
];

$captchas = [
  'recaptcha' => 'Recaptcha',
  'turnstile' => 'Turnstile',
];

# get captcha
switch ($faucet['captcha']) {
  case 'recaptcha':
  $sitekey = get_data('recaptcha_site_key');
  $captcha_display = "<div class='g-recaptcha' data-sitekey='{$sitekey}'></div><script src='https://www.google.com/recaptcha/api.js' async defer></script>";
  break;
  case 'turnstile':
  $sitekey = get_data('turnstile_site_key');
  $captcha_display = "<div class='cf-turnstile' data-sitekey='{$sitekey}' data-theme='light' data-size='normal'></div><script src='https://challenges.cloudflare.com/turnstile/v0/api.js' async defer></script>";
  break;
}

?>
