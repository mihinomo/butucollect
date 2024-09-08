<?php
$agent = Agent::getAgent($_COOKIE['login']);
if(isset($_GET['dated'])){
    $dated = $_GET['dated'];
}else{
    $dated = date("Y-m-d");
}
?>

<!-- App Header -->
<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="/dashboard/" class="headerButton">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">
        Butu Comm | Dashboard
    </div>
    
</div>

    <!-- * App Header -->
<div id="appCapsule">
    
    <div class="section full mt-2">
        
        <div class="profile-stats ps-2 pe-2">
            <p class="ml-2">Date: <input type="text" id="datepicker"></p>
            <a href="#" class="item">

                <strong><?php echo Dashboard::collectiondated($agent['aid'],$dated); ?></strong>Collection <?php echo $dated; ?>
            </a>
            <a href="#" class="item">
                <strong><?php echo Dashboard::customerdated($agent['aid'],$dated); ?></strong>Customer <?php echo $dated; ?>
            </a>

        </div>
        
    </div>


    <div class="section full mt-1">
        <div class="wide-block pt-2 pb-2">
                
            <ul class="nav nav-tabs capsuled" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home" role="tab">
                        Daily Collection
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#profile" role="tab">
                        RD Collection
                    </a>
                </li>
                
            </ul>
            <div class="tab-content mt-2">
                <div class="tab-pane fade show active" id="home" role="tabpanel">
                    <div style='width:100%; height 100px;'></div>
                    <?php Dashboard::showDailyDeposit($agent['aid'],$dated); ?>
                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel">
                    
                    <?php Dashboard::showRdDeposit($agent['aid'],$dated); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(function() {
    $("#datepicker").datepicker({
        dateFormat: "yy-mm-dd",
        onSelect: function(dateText) {
            window.location.href = "?dated=" + dateText;
        }
    });
});
</script>