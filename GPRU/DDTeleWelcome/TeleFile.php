<?php
namespace GPRU\DDTeleWelcome;

class TeleFile extends \PHPExcel {
    private $_layout = array(
        array('header' => "Time",           'donation_field' => 'time'),
        array('header' => "First Name",     'donation_field' => 'Order.first_name'),
        array('header' => "Last Name",      'donation_field' => 'Order.last_name'),
        array('header' => "Middle Name",    'donation_field' => 'Order.middle_name'),
        array('header' => "Amount",         'donation_field' => 'amount'),
        array('header' => "Email",          'donation_field' => 'Order.email'),
        array('header' => "Telephone",      'donation_field' => 'Order.phone_number'),
        array('header' => "Recruited By",   'donation_field' => 'GENERATED.recruiter_name'),
        array('header' => "Customer ID",    'donation_field' => 'ChronopayDonation.customer_id'),
        array('header' => "City",           'donation_field' => 'Order.chronopay_city'),
        array('header' => "Address",        'donation_field' => 'Order.chronopay_address'),
        array('header' => "Transaction ID", 'donation_field' => 'ChronopayDonation.transaction_id'),
    );

    private function _num_to_letter($num) {
        if ($num > 25) {
            die("too big a num");
        }

        return chr(ord('A') + $num);
    }

    public function __construct() {
        parent::__construct();

        $sheet = $this->getActiveSheet();
        $last_letter = $this->_num_to_letter(count($this->_layout));

        // init header
        $header = array();
        foreach ($this->_layout as $col) {
            $header[] = $col['header'];
        }
        $sheet->fromArray($header, NULL, "A1");

        // beautify header
        $sheet->getStyle("A1:${last_letter}1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A1:${last_letter}1")->getFont()->setBold(true);

        // set all columns to autosize themselves
        for ($i = 0; $i < count($this->_layout); ++$i) {
            $sheet->getColumnDimension($this->_num_to_letter($i))->setAutoSize(true);
        }
    }

    private $_max_row = 2;

    public function add_donations($donations) {
        $sheet = $this->getActiveSheet();

        foreach ($donations as $donation) {
            $row = array();
            $donation_arr = $donation->asArray();

            $donation_arr['GENERATED.recruiter_name'] = isset($donation_arr['Order.Recruiter.name']) ? $donation_arr['Order.Recruiter.name'] : $donation_arr['Order.recruiter_login'];
            foreach ($this->_layout as $col) {
                $row[] = $donation_arr[$col['donation_field']];
            }

            $sheet->fromArray($row, NULL, "A".$this->_max_row);
            ++$this->_max_row;
        }
    }

    public function save($filename) {
        $objWriter = new \PHPExcel_Writer_Excel2007($this);
        $objWriter->save($filename);
    }
}
