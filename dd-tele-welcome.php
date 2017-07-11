<?php

use GPRU\Logger;
use DDTeleWelcome\NewDonationsProcessor;
// use

// require_once 'new_donations_processor.php';

$logger = Logger::getLogger('automails');

$logger->info('START');

//try {

    //new_donations_processor();
    $new_donations_processor = new NewDonationsProcessor($logger);
    $new_donations_processor->run();
try {
} catch (Exception $e) {
    $logger->error("died: ".$e->getMessage(), $e);
}

$logger->info('FINISH');
