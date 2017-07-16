<?php
namespace GPRU\DDTeleWelcome\DBJoin;

use GPRU\ORM\DBJoin\DDDonationsCollection;

class DonationsCollection extends DDDonationsCollection
{
    public function getRelations()
    {
        $model = $this->getModelClassName();

        return array_merge(parent::getRelations(), array(
                "AutomailsHistory" => array(
                    "collection" => "GPRU\\ORM\\DBJoin\\AutomailsHistoryCollection",
                    "type" => "one_to_one",
                    "on_clause" => "<RIGHT>.donation_id = <LEFT>.donation_id AND <RIGHT>.type = '".\GPRU\DDTeleWelcome\AUTOMAILS_TYPE."'",
                )
            )
        );
    }
}
