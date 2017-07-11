<?php
namespace GPRU\DB;

abstract class DDBaseRecruitersCollection extends BaseCollection
{
    public function getModelClassName()
    {
        return 'DDRecruiter';
    }

    public function getPrimaryKey()
    {
        return 'recruiter_id';
    }

    public function getDBFieldsMapping()
    {
        return array(
                'recruiter_id' => 'ID',
                'login' => 'Login',
                'password' => 'Password',
                'manager_id' => 'ManagerID',
                'name' => 'Name',
                'city' => 'City',
                'date_start' => 'DateStart',
                'date_end' => 'DateEnd',
            );
    }
}
