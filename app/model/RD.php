<?php 

class RD {

    public static function getTotalSanction() {
        // Prepare SQL query
        $sql = "SELECT SUM(amount) AS total_sanction FROM loans WHERE status = ? AND loan_type = ?";
    
        // Execute prepared statement
        $result = DB::queryPrepared($sql, ['active', 'rd'], true);
    
        // Fetch total sanction
        $totalSanction = isset($result['total_sanction']) ? (float)$result['total_sanction'] : 0;
    
        return $totalSanction;
    }

    public static function todayDeposit(){
        $date = date("Y-m-d");
        $sql = "SELECT SUM(amount) AS total FROM loan_deposits WHERE dated = ? AND type = ?";
    
        // Execute prepared statement
        $result = DB::queryPrepared($sql, [$date, 'rd'], true);
    
        // Fetch total sanction
        $totalSanction = isset($result['total']) ? (float)$result['total'] : 0;
        return $totalSanction;
    }
    
    
    public static function showLoans(){
        $sql = "SELECT l.id, l.lid, l.amount, l.sanction, l.interest, l.balance, l.penalty_rate, u.c_name FROM loans l JOIN users u ON l.cid = u.uid WHERE l.status = ? AND l.loan_type = ?";
        $loans = DB::queryPrepared($sql, ['active', 'rd'], false);
        foreach($loans as $row){
            echo "<tr>
                    <td>BT#".$row['id']."</td>
                    <td><b class='text-info'>".$row['c_name']."</b></td>
                    <td>".$row['sanction']."</td>
                    <td>".$row['penalty_rate']."</td>
                    <td>".$row['lid']."</td>
                    <td>₹ ".$row['amount']." / per day</td>
                    <td>₹ ".$row['balance']."</td>
                    <td><a target='_blank' class='btn btn-primary' href='/viewrd/".$row['lid']."/'>View Details</a></td>
                </tr>";
        }
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

    public static function last10daydeposit(){
        $ar = ['Sales'];
        $days = Monthly::getLast10Days();
        foreach($days as $d){
            $check = DB::querySingleRow("SELECT SUM(amount) AS total FROM loan_deposits WHERE dated='$d' and type='rd'");
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

    public static function showTotalBalance(){
        $amount = 0;
        $sql = "SELECT l.id, l.lid, l.amount, l.sanction, l.interest, l.penalty_rate, u.c_name FROM loans l JOIN users u ON l.cid = u.uid WHERE l.status = ? AND l.loan_type = ?";
        $loans = DB::queryPrepared($sql, ['active', 'rd'], false);
        foreach($loans as $row){
            $amount +=self::depositsEachDayCalc($row);
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
            echo "<tr><td>".$loan['sanction']."</td><td class='text-danger'>₹ ".$term."</td><td class='text-danger'>".checkTerm($loan['sanction'])." Months</td><td> - </td><td>₹ ".$totalAmount."</td><tr>";
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
        $months = checkTerm($loan['sanction']);
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