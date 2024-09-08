<?php
if(isset($_GET['sign'])){
	$sig=$_GET['sign'];
	$phone=$_GET['phone'];
	check_valid($sig,$phone);
}

if(isset($_COOKIE['login'])){
	header('Location: /dashboard/');
	exit();
}


function check_valid($id,$phone){
	$row=DB::query("select * from agents where aid='$id' and c_phone='$phone' and status='1'");
	if(empty($row)){
		echo "<script>alert('Wrong Credentials'); window.location.replace('/login/');</script>";
	}else{
		$aai = $row[0]['aid'];
		setcookie('login',$aai,strtotime('+15 hour'),'/');
		header("Location: /dashboard/");
		exit();
	}
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1E74FD">
    <title>Butu Commercial</title>
    <link rel="icon" type="image/png" href="/assets/logo.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="manifest" href="manifest.json">
</head>

<body class="bg-white">

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->


    <!-- App Capsule -->
    <div id="appCapsule" class="pt-0">

        <div class="login-form mt-1">
            <div class="section">
                <img src="/assets/logo.png" alt="image" class="form-image">
            </div>
            <div class="section mt-1">
                <h1>Butu Commercial</h1>
                <h4>Login Below</h4>
            </div>
            <div class="section mt-1 mb-5">
                <form>
                    <div class="form-group boxed">
                        <div class="input-wrapper">
                            <input type="text" class="form-control" id="userid" name='sign' placeholder="User ID">
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>

                    <div class="form-group boxed">
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="password1" name='phone' placeholder="Password" autocomplete="off">
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>
                    <div class="form-button-group">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Log in</button>
                    </div>

                </form>
            </div>
        </div>


    </div>
    <!-- * App Capsule -->



    <!-- ============== Js Files ==============  -->
    <!-- Bootstrap -->
    <script src="/assets/js/lib/bootstrap.min.js"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- Splide -->
    <script src="/assets/js/plugins/splide/splide.min.js"></script>
    <!-- ProgressBar js -->
    <script src="/assets/js/plugins/progressbar-js/progressbar.min.js"></script>
    <!-- Base Js File -->
    <script src="/assets/js/base.js"></script>

</body>

</html>