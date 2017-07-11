<?php
namespace DDTeleWelcome;

use GPRU\Automails\Meta as AutomailsMeta;
use GPRU\DB\DonationsCollection as AllDonations;
use DDTeleWelcome\DB\DonationsCollection as DD_Donations;
use DDTeleWelcome\NewDonations\Rules;
use DDTeleWelcome\NewDonations\Handlers;

class NewDonationsProcessor
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        echo memory_get_usage() . " before reading meta\n";
        $meta = new AutomailsMeta('dd_welcome_calls');
        $last_id = $meta->get('last_id');
        if (!isset($last_id)) {
            $this->logger->info("can't get last processed id");
            $last_donation = AllDonations::getOne("Donation.donation_id", array(
                    "order by" => "Donation.donation_id DESC"
                )
            );
            if (!isset($last_donation)) {
                $this->logger->info("can't find last donation");
                return;
            }
            //$meta->set('last_id', $last_donation->donation_id);
            //return;
            $last_id = 0;
        }

        echo memory_get_usage() . " before getting donations\n";
        $donations = $this->getLastDonations($last_id);
        //var_dump($donations[0]->asArray());
        $this->process($donations);
    }

    private function process($donations)
    {
        $new_donations_rules = Rules::getRules();
        $processed_donations = array();
        foreach ($new_donations_rules as $rule) {
            $next_donations = array();
            $to_process = array();
            $filter = $rule['filter'];

            foreach ($donations as $donation) {
                if (Rules::$filter($donation)) {
                    if (!isset($processed_donations[$donation->donation_id])) {
                        $to_process[] = $donation;
                        $processed_donations[$donation->donation_id] = 'automails_calls';
                    }
                } else {
                    $next_donations[] = $donation;
                }
            }

            if (count($to_process) > 0) {
                $this->logger->info(count($to_process)." donations selected by $filter");
                $handler = $rule['handler'];
                Handlers::$handler($rule['handler_args'], $to_process);
                $this->logger->info("processed");
                sleep(5);
            } elseif (isset($rule['fail_handler'])) {
                $this->logger->info("nothing is selected by $filter, running fail handler");
                $fail_handler = $rule['fail_handler'];
                Handlers::$fail_handler($rule['handler_args']);
            }

            $donations = $next_donations;
        }
    }

    private function getLastDonations($last_id)
    {
        return DD_Donations::get("
                Donation.amount,
                Donation.time,
                ChronopayDonation.customer_id,
                ChronopayDonation.transaction_id,
                Order.recruiter_login,
                Order.email,
                Order.first_name,
                Order.last_name,
                Order.middle_name,
                Order.phone_number,
                Order.chronopay_city,
                Order.chronopay_address,
            ", array(
                "where" => "Donation.donation_id >= :last_donation_id
                    AND (
                        ChronopayDonation.customer_id LIKE '006560-%'
                        OR ChronopayDonation.customer_id LIKE '008134-%'
                    )
                    AND Donation.donation_type = 'Purchase'
                    AND AutomailsHistory?.donation_id IS NULL
                ",
                "order by" => "Donation.donation_id DESC",
                "aliases" => array(
                    "Order" => "DDDonation.Order",
                    "Donation" => "DDDonation",
                    "ChronopayDonation" => "DDDonation.ChronopayDonation",
                    "AutomailsHistory" => "DDDonation.AutomailsHistory",
                ),
                "limit" => 10,
                "params" => array(':last_donation_id' => $last_id),
            )
        );
    }
}
