<?php

class Depbal {
    public static function calculateBalance($lid) {
        $loan = DB::queryRow("SELECT * FROM loans WHERE lid = '$lid'");

        if ($loan['loan_type'] == 'monthly') {
            return self::calculateMonthlyBalance($loan);
        } elseif ($loan['loan_type'] == 'daily') {
            return self::calculateDailyBalance($loan);
        } elseif ($loan['loan_type'] == 'rd') {
            return self::calculateRdBalance($loan);
        }

        return 0; // Default fallback
    }

    private static function calculateMonthlyBalance($loan) {
        Loan::init();
        $balance = $loan['amount'];
        $depo = 0;
        $months = Monthly::generateYearMonthArray($loan['sanction']);
        $iio = 0;
        $ppp = 0;
    
        $firstMonth = true; // Flag to check if it's the first month
        
        foreach ($months as $month) {
            $lastmonth = date("Y-m")==date("Y-m",strtotime($month))?true:false;
            $dates = Monthly::getFirstAndLastDateOfMonth($month);
            $depositsInfo = Monthly::checkMonthDeposits($loan['lid'], $dates['first_date'], $dates['last_date']);
           
            $totalDeposit = $depositsInfo[0]['total_deposit'] ?? 0;
            
    
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
        return round($balance,2);
    }
    
    
    private static function calculateDailyBalance($loan) {
        $interest = $loan['amount'] / 100 * $loan['interest'];
        $term = $interest * $loan['penalty_rate'];
        $amount = $loan['amount'];
        $totaldeposits = 0;

        $months = self::checkTerm($loan['sanction']);
        $lid = $loan['lid'];

        if ($loan['penalty_rate'] < $months) {
            $term = $months * $interest;
            $totalAmount = $amount + $term;
        } else {
            $totalAmount = $amount + $term;
        }

        $deposits = DB::queryPreparedMultipleRows("SELECT amount FROM loan_deposits WHERE lid = ? ORDER BY dated ASC", [$loan['lid']]);
        foreach ($deposits as $deposit) {
            $totalAmount -= $deposit['amount'];
            $totaldeposits += $deposit['amount'];
        }

        return round($totalAmount, 2);
    }

    private static function calculateRdBalance($loan) {
        $balance = $loan['amount'];
        $depositsMap = self::getDepositsMap($loan['lid'], 'rd');
        $interestRate = $loan['interest'] / 100;

        $months = self::generateYearMonthArray($loan['sanction']);
        foreach ($months as $month) {
            $interest = $balance * $interestRate;
            if (isset($depositsMap[$month])) {
                $balance -= $depositsMap[$month];
            }
            $balance += $interest; // Add interest to the balance
        }

        return round($balance, 2);
    }

    private static function getDepositsMap($lid, $type) {
        $deposits = DB::queryPreparedMultipleRows("SELECT amount, DATE_FORMAT(dated, '%Y-%m') as deposit_month FROM loan_deposits WHERE lid = ? AND type = ?", [$lid, $type]);
        $depositsMap = [];
        foreach ($deposits as $deposit) {
            if (!isset($depositsMap[$deposit['deposit_month']])) {
                $depositsMap[$deposit['deposit_month']] = 0;
            }
            $depositsMap[$deposit['deposit_month']] += $deposit['amount'];
        }
        return $depositsMap;
    }

    private static function generateYearMonthArray($start) {
        $startDate = new DateTime($start);
        $currentDate = new DateTime();
        $check = [];
        while ($startDate <= $currentDate) {
            $check[] = $startDate->format('Y-m');
            $startDate->modify('first day of next month');
        }
        return $check;
    }
    private static function checkTerm($sanction) {
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
