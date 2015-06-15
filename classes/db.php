<?php
require_once 'classes/direct_donor.php';

class DB {
    private $dbh = NULL;

    public function __construct($connection_string, $user, $password) {
        $this->dbh = new PDO($connection_string, $user, $password);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbh->exec("SET NAMES utf8");
    }

    /*
        each time get_new_donors function is called, this variable will hold the biggest OrderID of those donors
        after processing the new donors, if everything is OK, commit_last_donors needs to be called, so that new_last_fetched_order
          can be stored in the database
    */
    private $new_last_fetched_order = NULL;

    /*
        Returns an array of new direct donors.
        "new direct donor" is specified as one, who has paylog.OrderID greater than the last_fetched_order stored in the database
    */
    public function get_new_donors() {
        if ($this->new_last_fetched_order !== NULL) {
            die("you forgot to commit previous donors");
        }

        $last_fetched_order = $this->dbh->query("SELECT value FROM meta WHERE name = 'last_dd_fetch_order'")->fetchColumn();
        
        if (!$last_fetched_order) {
            $this->new_last_fetched_order = $this->dbh->query("SELECT MAX(OrderID) FROM paylog WHERE (LEFT(CustomerID,6) = '006560' OR referer LIKE '%direct_dialog%') AND TransactionID > 0 AND InitialOrderID = 0")->fetchColumn();
        
            return array();
        }

        $sth = $this->dbh->prepare("
            SELECT p.OrderID, p.SubmitDate AS donation_time, r.Name AS recruited_by, p.User AS recruiter_id, p.Email AS email,
                p.LastName AS last_name, p.FirstName AS first_name, p.MiddleName AS middle_name, p.Amount AS donation_amount,
                p.Telephone AS phone_number
            FROM paylog AS p
            LEFT JOIN dd_recruiters AS r ON p.User = r.Login
            WHERE
                (LEFT(CustomerID,6) = '006560' OR referer LIKE '%direct_dialog%') AND OrderID > ? AND TransactionID > 0 AND InitialOrderID = 0
            ORDER BY SubmitDate DESC
        ");
        $sth->execute(array($last_fetched_order));

        $result = array();
        foreach ($sth as $row) {
            if ($this->new_last_fetched_order === NULL || $row['OrderID'] > $this->new_last_fetched_order) {
                $this->new_last_fetched_order = $row['OrderID'];
            }

            // in some weird cases, a recruiter is not found in dd_recruiters, so we are left with recruiter_id (which is sometimes empty, too...)
            if (!$row['recruited_by']) {
                $row['recruited_by'] = $row['recruiter_id'];
            }

            $result[] = new DirectDonor($row);
        }

        return $result;
    }

    /*
        call this function when you've successfully processed donors previously fetched with get_new_donors()
        the function just saves new_last_fetched_order in the database
    */
    public function commit_last_donors() {
        if ($this->new_last_fetched_order === NULL) {
            return;
        }
        
        $this->dbh->prepare("REPLACE INTO meta(name, value) VALUES (?, ?)")->execute(array('last_dd_fetch_order', $this->new_last_fetched_order));
    }
}
