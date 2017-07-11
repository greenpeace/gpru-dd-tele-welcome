<?php

$new_donations_rules = array(
        array(
                'filter' => 'direct_dialog_filter',
                'handler' => 'direct_dialog_welcome_calls_handler',
                'fail_handler' => 'direct_dialog_welcome_calls_fail_handler',
                'handler_args' => array(
                        'mail_subject' => 'DD Tele Welcome',
                        'mail_to' => array('livanuk@4sformula.ru'),
                        'mail_cc' => 'yulia.yundina@greenpeace.org',
                        'filename' => 'dd_tele_welcome',
                    ),
            ),
        array(
                'filter' => 'direct_dialog_2017_filter',
                'handler' => 'direct_dialog_welcome_calls_handler',
                'fail_handler' => 'direct_dialog_welcome_calls_fail_handler',
                'handler_args' => array(
                        'mail_subject' => 'DD 2017 Tele Welcome',
                        'mail_to' => array('livanuk@4sformula.ru', 'yulia.yundina@greenpeace.org'),
                        'mail_cc' => 'sosedova.m@gmail.com',
                        'filename' => 'dd_2017_tele_welcome',
                    ),
            ),
    );

function direct_dialog_filter($donation) {
    #return ($donation['parent_order_id'] == 0 && $donation['site_id'] == '006560');
    return $donation['donation_id'] < 20;
}

function direct_dialog_2017_filter($donation) {
    #return ($donation['parent_order_id'] == 0 && $donation['site_id'] == '008134');
    return $donation['donation_id'] >= 20;
}
