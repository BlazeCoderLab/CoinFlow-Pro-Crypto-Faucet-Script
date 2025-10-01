<?php
$db_host = "localhost";
$db_user = "DATABASE_USER";
$db_pass = "DATABASE_PASSWORD";
$db_name = "DATABASE_NAME";

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// DB error handling
if ($mysqli->connect_error) {
    die("Connection Failed: " . $mysqli->connect_error);
}

$baseUrl = "https://yourdomain.com/"; // Your Website URL (domain name)

$url = $baseUrl . 'link.php?k={key}&format=text'; // Default url for shortlinks [Don't change it]

$link[1] = "https://cuty.io/api?api=f2741c3c62d457125243be984d08db3225565ad5&url={$url}";
$link[2] = "https://ez4short.com/api?api=671ce36bc74acfacb13abd8b8e08a1a944ef0dde&url={$url}";
$link[3] = "https://tmearn.net/api?api=e637e51917072a05b24132b8fddf5973bd333c3a&url={$url}";
?>   
