<?php
namespace GPRU\DB;

class DonationsCollection extends BaseCollection
{
    public function getModelClassName()
    {
        return 'Donation';
    }
    public function getTableName()
    {
        return 'donations';
    }
    public function getPrimaryKey()
    {
        return 'donation_id';
    }

    public function getRelations()
    {
        return array(
            'Order' => array(
                    'collection' => 'OrdersCollection',
                    'type' => 'one_to_one',
                    'on_clause' => 'order_id',
                ),
            'ChronopayDonation' => array(
                    'collection' => 'ChronopayDonationsCollection',
                    'type' => 'one_to_one',
                    'on_clause' => 'donation_id',
                ),
        );
    }
}
