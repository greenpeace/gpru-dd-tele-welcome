<?php
namespace GPRU\DDTeleWelcome;

use GPRU\Automails\Meta as AutomailsMeta;
use GPRU\Automails\History as AutomailsHistory;
use GPRU\ORM\DBJoin\DonationsCollection as AllDonations;
use GPRU\DDTeleWelcome\DBJoin\DonationsCollection as DD_Donations;
use GPRU\DDTeleWelcome\NewDonations\Rules;
use GPRU\DDTeleWelcome\NewDonations\Handlers;

class NewDonationsProcessor
{
    private $logger;
    private $meta;

    public function __construct()
    {
        $this->meta = new AutomailsMeta(AUTOMAILS_TYPE);
    }

    public function withLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function run()
    {
        $last_id = $this->meta->get('last_id');
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
            $this->meta->set('last_id', $last_donation->donation_id);
            return;
        }
        $donations = $this->getLastDonations($last_id);
        $this->process($donations);

        if (count($donations) > 0) {
            $this->meta->set('last_id', $donations[count($donations) - 1]->donation_id);
        }
    }

    private function process($donations)
    {
        $new_donations_rules = Rules::getRules();
        $handlers = new Handlers($this->meta, $this->logger);
        $processed_donations = array();
        foreach ($new_donations_rules as $rule) {
            $next_donations = array();
            $to_process = array();
            $filter = $rule['filter'];

            foreach ($donations as $donation) {
                if (Rules::$filter($donation)) {
                    if (!isset($processed_donations[$donation->donation_id])) {
                        $to_process[] = $donation;
                        $processed_donations[$donation->donation_id] = AUTOMAILS_TYPE;
                    }
                } else {
                    $next_donations[] = $donation;
                }
            }

            if (count($to_process) > 0) {
                $this->logger->info(count($to_process)." donations selected by $filter");
                $handler = $rule['handler'];
                $handlers->$handler($rule['handler_args'], $to_process);
                $this->logger->info("processed");
                AutomailsHistory::rememberDonations($to_process, AUTOMAILS_TYPE);
                sleep(5);
            } elseif (isset($rule['fail_handler'])) {
                $this->logger->info("nothing is selected by $filter, running fail handler");
                $fail_handler = $rule['fail_handler'];
                $handlers->$fail_handler($rule['handler_args']);
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
                "where" => "Donation.donation_id > :last_donation_id
                    AND (
                        ChronopayDonation.customer_id LIKE '006560-%'
                        OR ChronopayDonation.customer_id LIKE '008134-%'
                    )
                    AND Donation.donation_type = 'Purchase'
                    AND AutomailsHistory?.donation_id IS NULL
                ",
                "order by" => "Donation.donation_id",
                "aliases" => array(
                    "Order" => "DDDonation.Order",
                    "Donation" => "DDDonation",
                    "ChronopayDonation" => "DDDonation.ChronopayDonation",
                    "AutomailsHistory" => "DDDonation.AutomailsHistory",
                ),
                "params" => array(':last_donation_id' => $last_id),
            )
        );
    }
}
