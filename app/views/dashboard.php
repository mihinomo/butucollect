<?php
$agent = Agent::getAgent($_COOKIE['login']);
if(isset($_GET['dated'])){
    $dated = $_GET['dated'];
}else{
    $dated = date("Y-m-d");
}
?>
<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
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
    <div class="right">
        <a type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#ModalBasic" class="headerButton">
            <ion-icon name="qr-code-outline"></ion-icon>
        </a>
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

<div class="modal fade modalbox" id="ModalBasic" data-bs-backdrop="static" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                        <a href="#" data-bs-dismiss="modal">Close</a>
                    </div>
                    <div class="modal-body">
                    <h1>QR Code Scanner</h1>
                    <button id="start-button">Scan</button>
                    <div id="qr-reader"></div>
                    <div id="form-container">
                        <form id="dynamic-form">
                            <!-- Form fields will be injected here -->
                        </form>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        let html5QrCode; // Variable to hold the Html5Qrcode instance

        function onScanSuccess(decodedText, decodedResult) {
            // Stop the QR code scanner once a QR code is successfully scanned
            html5QrCode.stop().then(() => {
                // Fetch API request based on the scanned QR code data
                fetch('https://your-api-endpoint.com/data')
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            // Populate the form dynamically based on the API response
                            const formContainer = document.getElementById('form-container');
                            const form = document.getElementById('dynamic-form');
                            form.innerHTML = ''; // Clear existing form fields

                            data.forEach(item => {
                                const input = document.createElement('input');
                                input.type = 'text';
                                input.name = item.lid;
                                input.placeholder = item.name;
                                form.appendChild(input);
                                form.appendChild(document.createElement('br'));
                            });

                            // Show the form container
                            formContainer.style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }).catch(error => console.error('Error stopping QR scanner:', error));
        }

        function onScanError(errorMessage) {
            console.error('QR code scan error:', errorMessage);
        }

        function startScanning() {
            if (html5QrCode) {
                // If scanner is already initialized, restart scanning
                html5QrCode.start({ facingMode: "environment" }, { fps: 10 }, onScanSuccess, onScanError)
                    .catch(error => console.error('Error restarting QR scanner:', error));
            } else {
                // Initialize the QR code scanner
                html5QrCode = new Html5Qrcode("qr-reader");
                html5QrCode.start({ facingMode: "environment" }, { fps: 10 }, onScanSuccess, onScanError)
                    .catch(error => console.error('Error starting QR scanner:', error));
            }
        }

        // Set up the button click event
        document.getElementById('start-button').addEventListener('click', startScanning);
    </script>