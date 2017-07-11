<?php
namespace GPRU\DB\Models;

class Donation extends BaseModel
{
    public $donation_id;
    public $order_id;
    public $amount;
    public $time;
    public $donation_type;

    public $Order;
    public $ChronopayDonation;
}
