<?php
namespace GPRU\DDTeleWelcome\NewDonations;

use GPRU\Email;
use GPRU\Settings;
use GPRU\Automails\Meta as AutomailsMeta;
use GPRU\DDTeleWelcome\TeleFile;
use GPRU\DDTeleWelcome\DBJoin\DonationsCollection as DD_Donations;

class Handlers
{
    private $meta;
    private $logger;

    public function __construct($meta, $logger)
    {
        $this->meta = $meta;
        $this->logger = $logger;
    }

    public function welcomeCallsHandler($args, $donations)
    {
        $fileName = strftime("%Y-%m-%d").'_'.$args['filename'];

        DD_Donations::enrichWithRecruiters($donations, $args['recruiters_collection']);
        $tele_file = new TeleFile();
        $tele_file->add_donations($donations);
        $tele_file->save("$fileName.xlsx");
        $archive_password = Settings::get('dd_welcome_calls.archive_password');
        system("/usr/local/bin/zip -e -P '${archive_password}' $fileName.zip $fileName.xlsx > /dev/null");
        system("rm $fileName.xlsx > /dev/null 2> /dev/null");

        $today = strftime("%d.%m.%Y");
        list($min_date, $max_date) = self::getDonationDates($donations);
        $acquisition_date = $min_date;
        if ($min_date != $max_date) {
            $acquisition_date .= " - $max_date";
        }

        $text = "Добрый день, коллеги

Во вложении выгрузка сторонников, привлеченных ${acquisition_date}.
Они очень ждут и будут рады услышать приятный голос Валентины сегодня ${today}.

Спасибо.
Хорошего дня!";

        $email = new Email(Settings::get('dd_welcome_calls.mail_from'), 'DD Tele Welcome');
        $email->addAddresses($args['mail_to']);
        $email->Subject = $args['mail_subject'];
        $email->addCC($args['mail_cc']);
        $email->addAttachment("$fileName.zip");
        $email->Body = $text;

        $email->send();

        $this->meta->set($args['type'].'_last_fetch_date', $max_date);

        system("rm $fileName.zip > /dev/null 2> /dev/null");
    }

    private static function getDonationDates($donations) {
        $min_date = NULL;
        $max_date = NULL;

        foreach ($donations as $donation) {
            $date = $donation->time;
            if ($min_date === NULL || $min_date > $date) $min_date = $date;
            if ($max_date === NULL || $max_date < $date) $max_date = $date;
        }
        $min_date = preg_replace("/^(\d{4})-(\d{2})-(\d{2}).*/", "\\3.\\2.\\1", $min_date);
        $max_date = preg_replace("/^(\d{4})-(\d{2})-(\d{2}).*/", "\\3.\\2.\\1", $max_date);

        return array($min_date, $max_date);
    }

    public function welcomeCallsFailHandler($args)
    {
        $last_fetch_date = $this->meta->get($args['type'].'_last_fetch_date');
        if (!isset($last_fetch_date)) {
            return;
        }

        $start = 'Мы очень расстроены, но у нас не было привлечено новых сторонников.';
        if (isset($last_fetch_date)) {
            $start = "Мы очень расстроены, но c $last_fetch_date у нас не было привлечено новых сторонников.";
        }
        $text = "Добрый день, коллеги

$start
Сегодня у Валентины есть возможность передохнуть.

Спасибо.
Хорошего дня!";

        $email = new Email(Settings::get('dd_welcome_calls.mail_from'), 'DD Tele Welcome');
        $email->addAddresses($args['mail_to']);
        $email->Subject = $args['mail_subject'];
        $email->addCC($args['mail_cc']);
        $email->Body = $text;

        $email->send();
    }
}
