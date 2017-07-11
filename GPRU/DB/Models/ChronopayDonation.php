<?php
namespace GPRU\DB\Models;

class ChronopayDonation extends BaseModel
{
    public $donation_id;
    public $transaction_id;
    public $customer_id;
    public $transaction_type;
}
