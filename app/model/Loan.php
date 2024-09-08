<?php


class Loan {
    public static function init(){
        //
    }

    public static function getUser($cid){
        $q = DB::querySingleRow("select * from users where uid='$cid'");
        return $q;
    }

    public static function activeLoans(){
        $ar = [];
        $q = DB::querySingleRow("select count(*) monthly from loans where loan_type='monthly' and status='active'");
        $r = DB::querySingleRow("select count(*) daily from loans where loan_type='daily' and status='active'");
        $s = DB::querySingleRow("select count(*) rd from loans where loan_type='rd' and status='active'");
        
        $ar['monthly'] = $q['monthly'];
        $ar['daily'] = $r['daily'];
        $ar['rd'] = $s['rd'];
        //var_dump($ar);
        return $ar;
    }

    public static function dailyDeposits(){
        $ar = [];
        $d = date("Y-m-d");
        $q = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d' and type='monthly'");
        $r = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d' and type='daily'");
        $s = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d' and type='rd'");
        
        $ar['monthly'] = $q['total'];
        $ar['daily'] = $r['total'];
        $ar['rd'] = $s['total'];
        //var_dump($ar);
        return $ar;
    }

    public static function last10daydeposit(){
        $ar = ['Deposits'];
        $days = self::getLast10Days();
        foreach($days as $d){
            $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d'");
            if ($check && $check['total'] !== null) {
                $total = $check['total'];
            } else {
                // Return 0 if there are no active loans
                $total = 0;
            }
            array_push($ar,$total);
        }
        return $ar;
        
    }
    public static function getLast10Days() {
        $dates = [];
        
        for ($i = 9; $i >= 0; $i--) {
            // Subtract $i days from the current date and add to the array
            $dates[] = date("Y-m-d", strtotime("-$i days"));
        }
    
        // No need to reverse the array, as we're populating it in ascending order now
        return $dates;
    }
}


class Monthly extends Loan{

    public static function showTotalBalance(){
        $amount = 0;
        $sql = "SELECT l.id, l.lid, l.amount, l.sanction, l.interest, l.balance, l.penalty_rate, u.c_name FROM loans l JOIN users u ON l.cid = u.uid WHERE l.status = ? AND l.loan_type = ?";
        $loans = DB::queryPrepared($sql, ['active', 'monthly'], false);
        foreach($loans as $row){
            $amount +=$row['balance'];
        }
        return $amount;
    }

    public static function getTotalSanction() {
        // Execute a query to sum amounts directly in the database
        $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loans WHERE status='active' and loan_type='monthly'");
        
        // Check if result is not null
        if ($check && $check['total'] !== null) {
            return $check['total'];
        } else {
            // Return 0 if there are no active loans
            return 0;
        }
    }
    
    public static function showLoans(){
        $sql = "SELECT l.id, l.lid, l.amount, l.sanction, l.interest, l.penalty_rate, l.balance, u.c_name FROM loans l JOIN users u ON l.cid = u.uid WHERE l.status = ? AND l.loan_type = ?";
        $loans = DB::queryPrepared($sql, ['active', 'monthly'], false);
        foreach($loans as $row){
            echo "<tr>
                    <td>BT#".$row['id']."</td>
                    <td>".$row['c_name']."</td>
                    <td>".$row['sanction']."</td>
                    <td>".$row['lid']."</td>
                    <td>₹ ".$row['amount']."</td>
                    <td>₹ ".round($row['balance'],2)."</td>
                    <td><a target='_blank' class='btn btn-primary' href='/viewloan/".$row['lid']."/'>View Details</a></td>

                </tr>";
            if($row['balance']==0){
                $lid = $row['lid'];
                $bal = self::depositsEachMonth($row);
                DB::query("update loans set balance='$bal' where lid='$lid'");
            }
            
        }
    }
    public static function last10daydeposit(){
        $ar = ['Sales'];
        $days = self::getLast10Days();
        foreach($days as $d){
            $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d' and type='monthly'");
            if ($check && $check['total'] !== null) {
                $total = $check['total'];
            } else {
                // Return 0 if there are no active loans
                $total = 0;
            }
            array_push($ar,$total);
        }
        return $ar;
        
    }
    public static function getLast10Days() {
        $dates = [];
        
        for ($i = 9; $i >= 0; $i--) {
            // Subtract $i days from the current date and add to the array
            $dates[] = date("Y-m-d", strtotime("-$i days"));
        }
    
        // No need to reverse the array, as we're populating it in ascending order now
        return $dates;
    }
    public static function getUser($uid){
        $q = DB::querySingleRow("select * from users where uid='$uid'");
        return $q;
    }
    public static function getFirstAndLastDateOfMonth($yearMonth) {
        list($year, $month) = explode('-', $yearMonth);
        $firstDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $lastDate = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year));
        return ['first_date' => $firstDate, 'last_date' => $lastDate];
    }
    
    public static function generateYearMonthArray($start) {
        $startDate = new DateTime($start);
        $currentDate = new DateTime();
        $check = [];
        while ($startDate <= $currentDate) {
            $check[] = $startDate->format('Y-m');
            $startDate->modify('first day of next month');;
        }
        return $check;
    }
    
    public static function checkMonthDeposits($lid, $first, $last) {
        // Use the prepared statement version for safety
        $sql = "SELECT SUM(amount) as total_deposit FROM loan_deposits WHERE lid=? AND dated BETWEEN ? AND ?";
        return DB::queryPreparedMultipleRows($sql, [$lid, $first, $last]);
    }
    
    public static function depositsEachMonth($loan) {
        $balance = $loan['amount'];
        $depo = 0;
        $months = self::generateYearMonthArray($loan['sanction']);
        $iio = 0;
        $ppp = 0;
    
        $firstMonth = true; // Flag to check if it's the first month
        
        foreach ($months as $month) {
            $lastmonth = date("Y-m")==date("Y-m",strtotime($month))?true:false;
            $dates = self::getFirstAndLastDateOfMonth($month);
            $depositsInfo = self::checkMonthDeposits($loan['lid'], $dates['first_date'], $dates['last_date']);
            
            $totalDeposit = $depositsInfo[0]['total_deposit'] ?? 0;
            $details = "";
            
    
            $interest = $balance * ($loan['interest'] / 100);
            $iio += $interest;
    
            if ($totalDeposit > 0) {
                $balance -= $totalDeposit; // Subtract deposit from the balance
                $depo += $totalDeposit;
            } elseif (!$firstMonth && !$lastmonth) { // Check if not the first month
                $penalty = max($balance * ($loan['penalty_rate'] / 100), 1);
                $balance += $penalty; // Add penalty to the balance if no deposit
                $ppp += $penalty;
            }
            if ($firstMonth) { // Check if not the first month
                $penalty = 0;
            }
            if(!$firstMonth){
                $balance += $interest; // Add interest to the balance
            }
            
            $firstMonth = false;
        }
        return $balance;
    }
    public static function fetchMonthDepositDetails($lid, $first, $last) {
        // Use the prepared statement version for safety
        $sql = "SELECT amount, dat, extras FROM loan_deposits WHERE lid=? AND dated BETWEEN ? AND ?";
        return DB::queryPreparedMultipleRows($sql, [$lid, $first, $last]);
    }
    
    public static function depositsEachMonthTable($loan) {
        $balance = $loan['amount'];
        $depo = 0;
        $months = self::generateYearMonthArray($loan['sanction']);
        $iio = 0;
        $ppp = 0;
    
        $firstMonth = true; // Flag to check if it's the first month
        
        foreach ($months as $month) {
            $lastmonth = date("Y-m")==date("Y-m",strtotime($month))?true:false;
            $dates = self::getFirstAndLastDateOfMonth($month);
            $depositsInfo = self::checkMonthDeposits($loan['lid'], $dates['first_date'], $dates['last_date']);
            $depositDetails = self::fetchMonthDepositDetails($loan['lid'], $dates['first_date'], $dates['last_date']);
    
            $totalDeposit = $depositsInfo[0]['total_deposit'] ?? 0;
            $details = "";
            foreach ($depositDetails as $depositInfo) {
                $details .= "₹ " . number_format($depositInfo['amount'], 2) . " on " . htmlspecialchars($depositInfo['dat']) . " (" . htmlspecialchars($depositInfo['extras']) . ")<br>";
            }
    
            $interest = $balance * ($loan['interest'] / 100);
            $iio += $interest;
    
            if ($totalDeposit > 0) {
                $balance -= $totalDeposit; // Subtract deposit from the balance
                $depo += $totalDeposit;
            } elseif (!$firstMonth && !$lastmonth) { // Check if not the first month
                $penalty = max($balance * ($loan['penalty_rate'] / 100), 1);
                $balance += $penalty; // Add penalty to the balance if no deposit
                $ppp += $penalty;
            }
            if ($firstMonth) { // Check if not the first month
                $penalty = 0;
            }
            if(!$firstMonth){
                $balance += $interest; // Add interest to the balance
            }
            
            
            
    
            if(!$firstMonth){
                // Output the row for the current month
                echo "<tr style='font-size: small;'>";
                echo "<td><b>" . htmlspecialchars(date("M Y", strtotime($month))) . "</b></td>";
                echo "<td>₹ " . number_format($interest, 2) . "</td>";
                echo "<td>" . ($totalDeposit > 0 ? '-' : "<span style='color: red;'>₹ " . number_format($penalty, 2) . "</span>") . "</td>";
                echo "<td>" . ($totalDeposit > 0 ? '₹ ' . number_format($totalDeposit, 2) : "-") . "</td>";
                echo "<td>" . ($details ?: "-") . "</td>";
                echo "<td>₹ " . number_format($balance, 2) . "</td>";
                echo "</tr>";
            }
            $firstMonth = false;
        }
        echo "<tr style='border-top:2px solid red; font-size: small;'><td class='text-success'>Final Statement</td><td>₹ " . round($iio, 2) . "</td><td class='text-danger'>₹ " . round($ppp, 0) . "</td><td class='text-info'>₹ " . $depo . "</td><td></td><td class='text-info'>₹ " . round($balance, 2) . "</td></tr>";
    }
}

class Daily extends Loan{

    public static function getTotalSanction() {
        // Execute a query to sum amounts directly in the database
        $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loans WHERE status='active' and loan_type='daily'");
        
        // Check if result is not null
        if ($check && $check['total'] !== null) {
            return $check['total'];
        } else {
            // Return 0 if there are no active loans
            return 0;
        }
    }

    public static function showLoans(){
        $sql = "SELECT l.id, l.lid, l.amount, l.sanction, l.interest, l.penalty_rate, l.balance, u.c_name FROM loans l JOIN users u ON l.cid = u.uid WHERE l.status = ? AND l.loan_type = ?";
        $loans = DB::queryPrepared($sql, ['active', 'daily'], false);
        foreach($loans as $row){
            $deposits = self::getTotalDeposits($row['lid']);
            echo "<tr>
                    <td>BT#".$row['id']."</td>
                    <td>".$row['c_name']."</td>
                    <td>".$row['sanction']."</td>
                    <td>".$row['penalty_rate']."</td>
                    <td>".$row['lid']."</td>
                    <td>₹ ".$row['amount']."</td>
                    <td>".($row['balance']<50?'<td class="badge badge-info" style="margin:0.5rem">₹ '.round($row['balance'],2):'<td>₹ '.round($row['balance'],2))."</td>
                    <td><a target='_blank' class='btn btn-primary mr-1' href='/viewdaily/".$row['lid']."/'>View Details</a>".($deposits>50?"":"<a class='btn btn-danger' style='margin-left:5px;' href='?deactivate=".$row['lid']."/'>Deactivate</a>")."</td>
                </tr>";
        }
    }

    public static function last10daydeposit(){
        $ar = ['Sales'];
        $days = Monthly::getLast10Days();
        foreach($days as $d){
            $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d' and type='daily'");
            if ($check && $check['total'] !== null) {
                $total = $check['total'];
            } else {
                // Return 0 if there are no active loans
                $total = 0;
            }
            array_push($ar,$total);
        }
        return $ar;
        
    }

    public static function getTotalDeposits($lid){
        $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE lid='$lid'");
        if ($check && $check['total'] !== null) {
            $total = $check['total'];
        } else {
            // Return 0 if there are no active loans
            $total = 0;
        }
        return $total;
    }
    public static function showTotalBalance(){
        $amount = 0;
        $sql = "SELECT l.id, l.lid, l.amount, l.sanction, l.interest, l.balance, l.penalty_rate, u.c_name FROM loans l JOIN users u ON l.cid = u.uid WHERE l.status = ? AND l.loan_type = ?";
        $loans = DB::queryPrepared($sql, ['active', 'daily'], false);
        foreach($loans as $row){
            $amount +=$row['balance'];
        }
        return $amount;
    }
    public static function depositsEachDayCalc($loan){
        $interest = $loan['amount']/100*$loan['interest'];
        $term = $interest * $loan['penalty_rate'];
        $amount = $loan['amount'];
        $totaldeposits = 0;
        
        $lid = $loan['lid'];
        $months = self::checkTerm($loan['sanction']);
        
        if($loan['penalty_rate']<$months){
            $term = $months * $interest;
            $totalAmount = $amount + $term;
        }else{
            $totalAmount = $amount + $term;
        }
        
        $l = DB::queryMultipleRows("select * from loan_deposits where lid='$lid' order by dated asc");
        foreach($l as $row){
            $totalAmount -=$row['amount'];
            $totaldeposits +=$row['amount'];
        }
    
        return $totalAmount;
    }
    public static function depositsEachDay($loan){
        $interest = $loan['amount']/100*$loan['interest'];
        $term = $interest * $loan['penalty_rate'];
        $amount = $loan['amount'];
        $totaldeposits = 0;
        
        $months = self::checkTerm($loan['sanction']);
    
        $lid = $loan['lid'];
        
        
        if($loan['penalty_rate']<$months){
            $term = $months * $interest;
            $totalAmount = $amount + $term;
            echo "<tr><td>".$loan['sanction']."</td><td class='text-danger'>₹ ".$term."</td><td class='text-danger'>".self::checkTerm($loan['sanction'])." Months</td><td> - </td><td>₹ ".$totalAmount."</td><tr>";
        }else{
            $totalAmount = $amount + $term;
            echo "<tr><td>".$loan['sanction']."</td><td>".$term."</td><td>".$loan['penalty_rate']." Months</td><td> - </td><td>₹ ".$totalAmount."</td><tr>";
        }
        
        $l = DB::queryMultipleRows("select * from loan_deposits where lid='$lid' order by dated asc");
        foreach($l as $row){
            $totalAmount -=$row['amount'];
            $totaldeposits +=$row['amount'];
            echo "<tr><td>".$row['dated']."</td><td>-</td><td>-</td><td>₹ ".$row['amount']."</td><td>₹ ".$totalAmount."</td><tr>";
        }
    
        echo "<tr style='border-top:solid red 2px;'><td>".date("Y-m-d")."</td><td>-</td><td class='text-danger'>Final Balance</td><td class='text-info'>₹ ".$totaldeposits."</td><td class='text-info'>₹ ".$totalAmount."</td><tr>";
    
    }
    
    public static function showElapsed($loan){
        $term = $loan['penalty_rate'];
        $months = self::checkTerm($loan['sanction']);
        if($term<$months){
            return $months-$term;
        }else{
            return 0;
        }
    }
    
    public static function checkTerm($sanction){
        $date1 = $sanction;
        $date2 = date("Y-m-d");
    
        // Create DateTime objects from the given dates
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
    
        // Calculate the difference between the dates
        $interval = $datetime1->diff($datetime2);
    
        // Get the difference in months
        // Total difference in months is the year difference times 12 plus the month difference
        $monthsDiff = $interval->y * 12 + $interval->m;
    
        return $monthsDiff;
    }
}

