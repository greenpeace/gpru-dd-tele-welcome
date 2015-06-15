<?php

class DirectDonor {
    /* so here's the deal about props and get
    I did not find any other way to make a class field readonly, and mandatory in constructor
    so there's that */

    private $_fields = array(
        'first_name', 'last_name', 'middle_name', 'email', 'phone_number',

        'recruited_by', # name of a Direct Donor Recruiter

        'donation_time', 'donation_amount', # first donation datetime and amount
    );

    private $_props = array();

    public function __construct($props) {
        if (!is_array($props)) {
            die("DirectDonor constructor needs an array");
        }

        foreach ($this->_fields as $field) {
            if (!isset($props[$field])) {
                die("missing $field in constructor");
            }
            $this->_props[$field] = $props[$field];
        }
    }

    public function get_prop($field) {
        if (!isset($this->_props[$field])) {
            die("can't get property $field");
        }
        return $this->_props[$field];
    }
}