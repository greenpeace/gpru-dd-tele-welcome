<?php
namespace GPRU\DB;

class ChronopayDonationsCollection extends BaseCollection
{
    public function getModelClassName()
    {
        return 'ChronopayDonation';
    }
    public function getTableName()
    {
        return 'chronopay_donations';
    }

    public function getPrimaryKey()
    {
        return 'donation_id';
    }
}
