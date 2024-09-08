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
            <a href="/prehis/" class="headerButton">
                <ion-icon name="calendar-number-outline"></ion-icon>
            </a>
        </div>
    <div class="pageTitle">
        Butu Comm | Dashboard
    </div>
    
</div>


    <!-- * App Header -->
<div id="appCapsule">
    <div class="section mt-2">
        <div class="profile-head">
            <div class="avatar">
                <img src="/assets/clipart.png" alt="avatar" class="imaged w64 rounded">
            </div>
            <div class="in">
                <h3 class="name">Hello There !</h3>
                <h5 class="subtext"><?php echo $agent['c_name']; ?></h5>
            </div>
        </div>
    </div>
    <div class="section full mt-2">
        <div class="profile-stats ps-2 pe-2">
            <a href="#" class="item">
                <strong><?php echo Dashboard::collectionToday($agent['aid']); ?></strong>Collection Today
            </a>
            <a href="#" class="item">
                <strong><?php echo Dashboard::customerToday($agent['aid']); ?></strong>Customer Today
            </a>

        </div>
        
    </div>


    <div class="section full mt-1">
        <div class="wide-block pt-2 pb-2">
                <div class="section full mb-3">
                    <input class="form-control text-primary" type="search" id='searchi' placeholder="Search Username">
                </div>
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
$(document).ready(function(){
	$("#searchi").autocomplete({
		source: "/api/search/",
		minLength: 1,
		select: function(event, ui) {
			$("#search").val(ui.item.value);
			$("#userID").val(ui.item.id);
		}
	}).data("ui-autocomplete")._renderItem = function( ul, item ) {
	return $( "<li class='ui-autocomplete-row'></li>" )
		.data( "item.autocomplete", item )
		.append( item.label )
		.appendTo( ul );
	};
});

</script>