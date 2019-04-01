<?php

use Transaction\Test;
use Transaction\Transaction;

include "Test.php";
include "../Transaction.php";
include "../Step.php";

$test = new Test(5);

$transaction = new Transaction($test);

$transaction
    ->incr("Increment")
    ->incr3("Increment 2")
    ->incr2("Increment 3");
try {
    $transaction->commit();
} catch (\Exception $e) {
}

var_dump($test->count);