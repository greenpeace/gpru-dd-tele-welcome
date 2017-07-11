<?php
namespace DDTeleWelcome\NewDonations;

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
                            'mail_to' => array('.....'),
                            'mail_cc' => '....',
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
                            'mail_to' => array('....'),
                            'mail_cc' => '...',
                            'filename' => 'dd_2017_tele_welcome',
                            'recruiters_collection' => 'DD2017RecruitersCollection',
                        ),
                ),
        );
    }

    public static function directDialogFilter($donation) {
        #return ($donation['parent_order_id'] == 0 && $donation['site_id'] == '006560');
        #return $donation->donation_id < 20;
        return $donation->donation_id < 375;
    }

    public static function directDialog2017Filter($donation) {
        #return ($donation['parent_order_id'] == 0 && $donation['site_id'] == '008134');
        #return $donation->donation_id >= 20;
        return $donation->donation_id >= 375;
    }
}
