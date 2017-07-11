<?php
namespace GPRU;

use GPRU\Settings;

require_once "PHPMailer/class.phpmailer.php";
require_once "PHPMailer/class.smtp.php";

class Email extends \PHPMailer
{
    public function __construct()
    {
        parent::__construct();
        $this->CharSet  = 'UTF-8';
        $this->Timeout  = 15;
        $this->Host     = 'mail.greenpeace.ru';
        $this->isSMTP();
    }

    public function addAddresses($addresses)
    {
        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                $this->addAddress($address);
            }
        } else {
            $this->addAddress($addresses);
        }
    }

    public function send()
    {
        if(!parent::send()){
            throw new \RuntimeException('failed to send message via PHPMailer: '.$this->ErrorInfo);
        }
    }
}