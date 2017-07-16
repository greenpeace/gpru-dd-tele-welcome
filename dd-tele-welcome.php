<?php
namespace GPRU\DDTeleWelcome;

use GPRU\Logger;
use GPRU\DDTeleWelcome\NewDonationsProcessor;

const AUTOMAILS_TYPE = 'dd_welcome_calls';

$logger = Logger::getLogger('automails');

$logger->info('START');

try {
    $p = new NewDonationsProcessor();
    $p->withLogger($logger)->run();
} catch (Exception $e) {
    $logger->error("died: ".$e->getMessage(), $e);
}

$logger->info('FINISH');
