<?php

use GPRU\ORM\DBJoin\DDOrdersCollection;
use GPRU\ORM\DBJoin\DDDonationsCollection;
use GPRU\ORM\DBJoin\ChronopayDonationsCollection;

$ai = file_get_contents('ai.txt');
$time = '2017-01-01 12:34:56';

createDDDonation('006560', 'Purchase', array('order' => array('recruiter_login' => 'Recruiter')));
createDDDonation('006560', 'Rebill');
createDDDonation('006560', 'Purchase', array('order' => array('recruiter_login' => '')));
createDDDonation('006560', 'Rebill');
createDDDonation('008134', 'Purchase', array('order' => array('recruiter_login' => '2017Recruiter')));
createDDDonation('008134', 'Rebill');
createDDDonation('008134', 'Purchase', array('order' => array('recruiter_login' => '')));
createDDDonation('008134', 'Rebill');

file_put_contents('ai.txt', $ai);

function createDDDonation($site_id, $type, $opts = array()) {
    global $ai, $time;

    $ai++;
    $order_arr = array(
        'email' => "example_$ai@email.com",
        'first_name' => "Имя_$ai",
        'last_name' => "Фамилия_$ai",
        'middle_name' => "Отчество_$ai",
        'phone_number' => "7-123-345-67-$ai",
        'chronopay_city' => "Город_$ai",
        'chronopay_address' => "Адрес_$ai",
        'recruiter_login' => "Recruiter_$ai",
        'amount' => 1000 + $ai,
    );
    if (isset($opts['order'])) {
        $order_arr = array_merge($order_arr, $opts['order']);
    }
    $order_id = DDOrdersCollection::insertOne($order_arr);

    $donation_arr = array(
        'order_id' => $order_id,
        'amount' => 1000 + $ai,
        'time' => $time,
        'donation_type' => $type,
    );
    if (isset($opts['donation'])) {
        $donation_arr = array_merge($donation_arr, $opts['donation']);
    }
    $donation_id = DDDonationsCollection::insertOne($donation_arr);

    $chronopay_donation_arr = array(
        'donation_id' => $donation_id,
        'transaction_id' => $ai,
        'customer_id' => $site_id."-$ai",
        'transaction_type' => $type,
    );
    if (isset($opts['chronopay_donation'])) {
        $chronopay_donation_arr = array_merge($chronopay_donation_arr, $opts['chronopay_donation']);
    }
    ChronopayDonationsCollection::insertOne($chronopay_donation_arr);
}
