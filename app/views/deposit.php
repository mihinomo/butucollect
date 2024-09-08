<?php

if (!isset($_GET['lid'])) {
    header('Location: /loans/');
    exit();
}

$lid = $_GET['lid'];
$loan = DB::querySingleRow("SELECT * FROM loans WHERE lid = ?", [$lid]);
if (!$loan) {
    die("Invalid loan ID.");
}

$borrower = DB::querySingleRow("SELECT * FROM users WHERE uid = ?", [$loan['cid']]);
if (!$borrower) {
    die("Borrower not found.");
}

$dateToday = date('Y-m-d');

// Check if a deposit has been made today
$depositsToday = DB::queryPrepared("SELECT COUNT(*) as count FROM loan_deposits WHERE lid = ? AND dated = ?", [$lid, $dateToday], true);
if ($depositsToday['count'] >= 1) {
    $redirectUrl = "/dashboard/";
    echo "<script>alert('Cannot add more than one deposit in a day.'); window.location.replace('$redirectUrl');</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lid = $_POST['lid'];
    $depositDate = date('Y-m-d', strtotime($_POST['depositDate']));
    $depositAmount = $_POST['depositAmount'];
    $remarks = $_POST['remarks'];
    $agent = $_COOKIE['login'];

    if (!is_numeric($depositAmount)) {
        echo "<script>alert('Deposit amount must be a digit.');</script>";
    } else {
        $data = [
            'lid' => $lid,
            'amount' => $depositAmount,
            'dated' => $depositDate,
            'type' => $loan['loan_type'],
            'dat' => date('F j, Y, g:i a'),
            'agent' => $agent,
            'extras' => $remarks
        ];

        if (DB::insert('loan_deposits', $data)) {
            $lastInsertId = DB::querySingleRow("SELECT id FROM loan_deposits ORDER BY id DESC LIMIT 1");
            $lastId = $lastInsertId['id'];

            // Update loan balance
            $loanBalance = Depbal::calculateBalance($lid);
            DB::update('loans', ['balance' => $loanBalance], "lid = '$lid'");

            $redirectUrl = "/dashboard/";
            echo "<script>alert('#BT{$lastId}'); window.location.replace('$redirectUrl');</script>";
        } else {
            echo "<script>alert('Failed to insert deposit record.');</script>";
        }
    }
}
?>
<style>
/* Custom jQuery UI datepicker style */
.ui-datepicker {
    width: 100%; /* Adjust width to fit the container */
    max-width: 400px; /* Set a max-width to avoid being too wide */
    font-size: 12px; /* Smaller font size for better fit */
}

.ui-datepicker table {
    width: 100%;
}

.ui-datepicker td, .ui-datepicker th {
    padding: 5px; /* Adjust padding for smaller cells */
    font-size: 12px; /* Ensure font size is consistent */
}

.ui-datepicker .ui-datepicker-title {
    font-size: 12px; /* Adjust title font size */
}

.ui-datepicker .ui-state-hover,
.ui-datepicker .ui-state-active {
    background-color: #f0f0f0; /* Highlight color on hover and active */
}
</style>



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
            <h6>Loan Deposit (Customer Balance: <?php echo Depbal::calculateBalance($_GET['lid']); ?>)</h6>
            <form id="loanDepositForm" method="POST" enctype="multipart/form-data">
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Customer Name</label>
                    <div class="col-lg-10">
                        <input class="form-control" name='customer' value="<?= htmlspecialchars($borrower['c_name']) ?>" type="text" readonly >
                    </div>
                </div>
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Loan ID</label>
                    <div class="col-lg-10">
                        <input class="form-control" name='lid' value="<?= htmlspecialchars($lid) ?>" type="text" readonly >
                    </div>
                </div>
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Deposit Date</label>
                    <div class="col-lg-10">
                        <input class="form-control" id="depositDate" name="depositDate" onfocus="blur()"  value="<?php echo date('Y-m-d'); ?>" type="text" readonly >
                    </div>
                </div>
                
                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Amount</label>
                    <div class="col-lg-10">
                        <input class="form-control" id="depositAmount" name="depositAmount" type="text" required>
                    </div>
                </div>
                <div class="form-group ">
                    <label for="reEnterDepositAmount">Re-enter Deposit Amount</label>
                    <div class="col-lg-10">
                        <input type="text" class="form-control" id="reEnterDepositAmount" name="reEnterDepositAmount" required>
                        <span id="amountError" style="color:red; display:none;">Amounts do not match or are not valid digits.</span>
                    </div>
                </div>

                <div class="form-group ">
                    <label for="dob" class="control-label col-lg-6">Remarks</label>
                    <div class="col-lg-10">
                        <textarea class='form-control'  id="remarks" name="remarks" row='3'.></textarea>
                    </div>
                </div>


                <div class="form-button-group">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="make_deposit">Deposit</button>
                </div>
            </form>
    </div> 
</div>


<script>
$(function() {
    $('.datepicker').datepicker({ format: 'yyyy-mm-dd' }).on('show', function(e) {
        var dp = $('.datepicker');
        dp.css({
            'width': '100%', // Adjust width to fit the container
            'max-width': '400px', // Set a max-width to avoid being too wide
            'font-size': '12px' // Adjust font size for better fit
        });
    });

    $('#reEnterDepositAmount').on('change', function() {
        var amount = $('#depositAmount').val();
        var reAmount = $(this).val();
        var isValidAmount = $.isNumeric(amount) && $.isNumeric(reAmount);
        var isAmountMatch = amount === reAmount;

        if (!isValidAmount || !isAmountMatch) {
            $('#amountError').show();
            $('#depositAmount, #reEnterDepositAmount').val('');
        } else {
            $('#amountError').hide();
        }
    });

    $('#loanDepositForm').on('submit', function(e) {
        if (!$.isNumeric($('#depositAmount').val())) {
            alert('Deposit amount must be a digit.');
            e.preventDefault();
        }
    });
});

</script>