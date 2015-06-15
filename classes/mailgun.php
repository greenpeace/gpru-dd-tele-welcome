<?php
class Mailgun {
    private $domain;
    private $userpwd;
    
    public function __construct($domain, $userpwd) {
        if (!(isset($domain) && isset($userpwd))) {
            throw new InvalidArgumentException("domain and userpwd arguments are required");
        }
        $this->domain = $domain;
        $this->userpwd = $userpwd;
    }
    
    public function send_text($from, $to, $subject, $text, $cc, $attachment = '') {
        if (!(isset($from) && isset($to) && isset($subject) && isset($text))) {
            throw new InvalidArgumentException("from, to, subject and text arguments are required");
        }
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/mg.xn--c1akatkdl.xn--p1ai/messages');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:key-f2f549791c93ecc3dbdca603a1cf8497');
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = array('from' => $from,
                      'to' => $to,
                      'subject' => $subject,
                      'text' => $text);
        
        if ($cc) {
            $data['cc'] = $cc;
        }
        if ($attachment) {
            $data['attachment'] = $attachment;
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $rv = curl_exec($ch);
        
        $jrv = json_decode($rv);
        if ($jrv->message != 'Queued. Thank you.') {
            throw new RuntimeException('failed to send message via mailgun: '.$rv);
        }
    }
}