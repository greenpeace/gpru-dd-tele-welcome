<?php
namespace GPRU\DB;

class DDDonationsCollection extends DonationsCollection
{
    public function getModelClassName()
    {
        return 'DDDonation';
    }

    // public function getCollectionConditions()
    // {
    //     return array_merge(parent::getCollectionConditions(),
    //             "(DDOrder.customer_id LIKE '006560-%' OR DDOrder.customer_id LIKE '006560-%')"
    //         );
    // }

    public function getRelations()
    {
        return array_merge(parent::getRelations(), array(
            'Order' => array(
                    'collection' => 'DDOrdersCollection',
                    'type' => 'one_to_one',
                    'on_clause' => 'order_id',
                )
        ));
    }

    public static function enrichWithRecruiters($donations, $from_collection)
    {
        $orders = array_map(function ($d) { return $d->Order; } , $donations);
        DDOrdersCollection::enrichWithRecruiters($orders, $from_collection);
    }
}
