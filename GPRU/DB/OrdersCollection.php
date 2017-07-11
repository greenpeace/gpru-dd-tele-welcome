<?php
namespace GPRU\DB;

class OrdersCollection extends BaseCollection
{
    public function getModelClassName()
    {
        return 'Order';
    }
    public function getTableName()
    {
        return 'paylog';
    }
    public function getPrimaryKey()
    {
        return 'order_id';
    }
    public function getDBFieldsMapping()
    {
        return array(
                'order_id' => 'OrderID',
                'email' => 'Email',
                'first_name' => 'FirstName',
                'last_name' => 'LastName',
                'middle_name' => 'MiddleName',
                'phone_number' => 'Telephone',
                'chronopay_city' => 'ChronopayCity',
                'chronopay_address' => 'ChronopayAddress',
            );
    }
}
