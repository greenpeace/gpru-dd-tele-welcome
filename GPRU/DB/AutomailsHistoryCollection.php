<?php
namespace GPRU\DB;

class AutomailsHistoryCollection extends BaseCollection
{
    public function getTableName()
    {
        return 'automails_history';
    }

    public function getModelClassName()
    {
        return 'AutomailsHistory';
    }

    public function getPrimaryKey()
    {
        return 'donation_id';
    }
}
