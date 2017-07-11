<?php
namespace GPRU\DB\Models;

class Order extends BaseModel
{
    public $order_id;
    public $email;
    public $first_name;
    public $last_name;
    public $middle_name;
    public $phone_number;
    public $chronopay_city;
    public $chronopay_address;
}
