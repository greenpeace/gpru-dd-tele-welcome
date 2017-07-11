<?php
namespace GPRU\DB;

class DDOrdersCollection extends OrdersCollection
{
    public function getModelClassName()
    {
        return 'DDOrder';
    }

    public function getDBFieldsMapping()
    {
        return array_merge(parent::getDBFieldsMapping(), array(
                'recruiter_login' => 'User',
            ));
    }

    public static function enrichWithRecruiters($orders, $from_collection)
    {
        $from_collection = __NAMESPACE__.'\\'.$from_collection;
        $recruiters = $from_collection::getHash( "
            DDRecruiter.login,
            DDRecruiter.name,
            DDRecruiter.date_start,
            DDRecruiter.date_end,
        ", array(
                #"where" => "DDRecruiter.date_end IS NULL OR (
                #    CONCAT(SUBSTR(DDRecruiter.date_start, 7, 4),SUBSTR(DDRecruiter.date_start, 4, 2),SUBSTR(DDRecruiter.date_start, 1, 2)) >
                #        CONCAT(SUBSTR(DDRecruiter.date_end, 7, 4),SUBSTR(DDRecruiter.date_end, 4, 2),SUBSTR(DDRecruiter.date_end, 1, 2)))",
                "hash_by" => function ($r) { return $r->login; },
            )
        );

        foreach ($orders as $order) {
            if (isset($recruiters[$order->recruiter_login])) {
                $order->Recruiter = $recruiters[$order->recruiter_login];
            }
        }
    }
}
