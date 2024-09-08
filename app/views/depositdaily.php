<?php 

$loan = Daily::getdaily($lid);
$customer = Daily::dailyCustomer($loan['cid']);

if(isset($_GET['make_deposit'])){
    if(isset($_COOKIE['login'])){
		$agent=$_COOKIE['login'];
		$amount=$_GET['amount'];
		$dated=$_GET['deposit'];
		$type="rd";
		$rmks=$_POST['rmks'];
		if(Daily::checkGenuine($lid,$dated)=='tru'){
			Daily::insertDeposit($lid,$amount,$dated,$type,$agent,$rmks);
			echo "<script>alert('Invoice : #BTR-".Daily::fetch_invoice($lid)."'); window.location.replace('/index/');</script>";
		}else{
			echo "<script>alert('RE - ENRY FOUND');</script>";
		}
	}else{
		header('Location: index.php');
		exit();
	}
}

?>

<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="/index/" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">
        Account - <?php echo $lid; ?>
    </div>
    
</div>
<br><br><Br>
<div id="appCapsule" class="pt-0 col-12">
    <div class="login-form mt-1 ml-4">
            <form>
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Customer Name</label>
                    <div class="col-lg-10">
                        <input class="form-control" name='customer' value="<?php echo $customer['c_name']; ?>" type="text" readonly >
                    </div>
                </div>
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Deposit Date</label>
                    <div class="col-lg-10">
                        <input class="form-control" name='deposit' onfocus="blur()"  value="<?php echo date('Y-m-d'); ?>" type="text" readonly >
                    </div>
                </div>
                
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Amount</label>
                    <div class="col-lg-10">
                        <input class="form-control" name='amount' id='amount' type="text" required>
                    </div>
                </div>
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Confirm Amount</label>
                    <div class="col-lg-10">
                        <input class="form-control" name='amm' id="amver" type="text" required>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Remarks</label>
                    <div class="col-lg-10">
                        <textarea class='form-control' name="rmks" row='3'.></textarea>
                    </div>
                </div>


                <div class="form-button-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="make_deposit">Deposit</button>
                </div>
            </form>
    </div> 
</div>



<script>
$("#amver").on("change", function(){
    var val = $("#amount").val();
    var ver = $(this).val();
    if(val!==ver){
        alert("Deposit amount does not match");
        $("#amount").val("");
        $(this).val("");
    }
});
$('#amount').on('keyup', function(){
    var $a = $('#amount').val();
    if($.isNumeric($a)){
        //
    }else{
        alert('Please enter amount in numbers');
        $('#amount').val('');
    }
});
</script>