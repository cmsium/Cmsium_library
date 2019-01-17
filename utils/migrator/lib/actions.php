<?php

namespace Migrator\Actions;

use DB;
use Migrator\Helpers;
use Files;

function createDb() {
    echo 'Initializing DB creation...'.PHP_EOL;
    $conn = DB\connectDB(false);
    if (DB\createDB($conn)) {
        echo 'DB created successfully!'.PHP_EOL;
    } else {
        die('Something went wrong!'.PHP_EOL);
    }
}

function dropDb() {
    echo 'Initializing DB drop...'.PHP_EOL;
    $conn = DB\connectDB(false);
    if (DB\dropDB($conn)) {
        echo 'DB dropped successfully! Cleaning migrations history...'.PHP_EOL;
        Helpers\clearMigrations(ROOTDIR.Files\getConfig('history_path'));
        echo 'Done!'.PHP_EOL;
    } else {
        die('Something went wrong!'.PHP_EOL);
    }
}