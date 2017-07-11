<?php
namespace DDTeleWelcome\DB;

use GPRU\DB\DDDonationsCollection;

class DonationsCollection extends DDDonationsCollection
{
    public function getRelations()
    {
        $model = $this->getModelClassName();

        return array_merge(parent::getRelations(), array(
                "AutomailsHistory" => array(
                    "collection" => "AutomailsHistoryCollection",
                    "type" => "one_to_one",
                    "on_clause" => "<RIGHT>.donation_id = <LEFT>.donation_id AND <RIGHT>.type = 'dd_welcome_call'",
                )
            )
        );
    }
}
