<?php 
if(!isset($_COOKIE['login'])){
    header("Location: /login/");
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1E74FD">
    <title>Butu Commercial</title>
    <link rel="icon" type="image/png" href="/assets/logo.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <script src="/assets/js/jquery.js"></script>
    <link rel="stylesheet" href="/assets/jqueryui/jquery-ui.min.css">
    <script src="/assets/confirm/jquery-confirm.js"></script>
    <script src="/assets/jqueryui/jquery-ui.min.js"></script>
</head>

<body>

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->