<?
require_once 'PHPExcel.php';

class TeleFile extends PHPExcel {
    private $_layout = array(
        array('header' => "Time",           'donor_field' => 'donation_time'),
        array('header' => "First Name",     'donor_field' => 'first_name'),
        array('header' => "Last Name",      'donor_field' => 'last_name'),
        array('header' => "Middle Name",    'donor_field' => 'middle_name'),
        array('header' => "Amount",         'donor_field' => 'donation_amount'),
        array('header' => "Email",          'donor_field' => 'email'),
        array('header' => "Telephone",      'donor_field' => 'phone_number'),
        array('header' => "Recruited By",   'donor_field' => 'recruited_by'),
        array('header' => "Customer ID",    'donor_field' => 'customer_id'),
        array('header' => "City",           'donor_field' => 'city'),
        array('header' => "Address",        'donor_field' => 'address'),
        array('header' => "Transaction ID", 'donor_field' => 'transaction_id'),
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
        $sheet->getStyle("A1:${last_letter}1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A1:${last_letter}1")->getFont()->setBold(true);

        // set all columns to autosize themselves
        for ($i = 0; $i < count($this->_layout); ++$i) {
            $sheet->getColumnDimension($this->_num_to_letter($i))->setAutoSize(true);
        }
    }

    private $_max_row = 2;

    public function add_donors($donors) {
        $sheet = $this->getActiveSheet();

        foreach ($donors as $donor) {
            $row = array();

            foreach ($this->_layout as $col) {
                $row[] = $donor->get_prop($col['donor_field']);
            }

            $sheet->fromArray($row, NULL, "A".$this->_max_row);
            ++$this->_max_row;
        }
    }

    public function save($filename) {
        $objWriter = new PHPExcel_Writer_Excel2007($this);
        $objWriter->save($filename);
    }
}