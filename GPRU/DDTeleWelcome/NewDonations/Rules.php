<?php
namespace GPRU\DDTeleWelcome\NewDonations;

use GPRU\Settings;

class Rules
{
    public static function getRules()
    {
        return array(
            array(
                    'filter' => 'directDialogFilter',
                    'handler' => 'welcomeCallsHandler',
                    'fail_handler' => 'welcomeCallsFailHandler',
                    'handler_args' => array(
                            'type' => 'direct_dialog',
                            'mail_subject' => 'DD Tele Welcome',
                            'mail_to' => Settings::get('dd_welcome_calls.rules.dd.mail_to'),
                            'mail_cc' => Settings::get('dd_welcome_calls.rules.dd.mail_cc'),
                            'filename' => 'dd_tele_welcome',
                            'recruiters_collection' => 'DDRecruitersCollection',
                        ),
                ),
            array(
                    'filter' => 'directDialog2017Filter',
                    'handler' => 'welcomeCallsHandler',
                    'fail_handler' => 'welcomeCallsFailHandler',
                    'handler_args' => array(
                            'type' => 'direct_dialog_2017',
                            'mail_subject' => 'DD 2017 Tele Welcome',
                            'mail_to' => Settings::get('dd_welcome_calls.rules.dd2017.mail_to'),
                            'mail_cc' => Settings::get('dd_welcome_calls.rules.dd2017.mail_cc'),
                            'filename' => 'dd_2017_tele_welcome',
                            'recruiters_collection' => 'DD2017RecruitersCollection',
                        ),
                ),
        );
    }

    public static function directDialogFilter($donation) {
        return (substr($donation->ChronopayDonation->customer_id, 0, 6) == '006560');
    }

    public static function directDialog2017Filter($donation) {
        return (substr($donation->ChronopayDonation->customer_id, 0, 6) == '008134');
    }
}
