<?php
require_once 'config.php';
require_once 'classes/db.php';
require_once 'classes/tele_file.php';
require_once 'classes/mailgun.php';

# password goes straight into system() call, so we make a whitelist of characters that probably won't do harm
if ($config['archive_password'] == '' || preg_match("/[^!#$%&()*+,\\-.\\/0-9:;<=>?@A-Z\\[\\]\\^_{|}~]/i", $config['archive_password'])) {
    die("bad archive password");
}

function get_donor_dates($donors) {
    $min_date = NULL;
    $max_date = NULL;

    foreach ($donors as $donor) {
        $date = $donor->get_prop('donation_time');
        if ($min_date === NULL || $min_date > $date) $min_date = $date;
        if ($max_date === NULL || $max_date < $date) $max_date = $date;
    }
    $min_date = preg_replace("/^(\d{4})-(\d{2})-(\d{2}).*/", "\\3.\\2.\\1", $min_date);
    $max_date = preg_replace("/^(\d{4})-(\d{2})-(\d{2}).*/", "\\3.\\2.\\1", $max_date);

    return array($min_date, $max_date);
}

$db = new DB($config['db_connection_string'], $config['db_user'], $config['db_password']);
$mg = new Mailgun($config['mailgun_domain'], $config['mailgun_userpwd']);

$new_donors = $db->get_new_donors(@$config['test_order']);

if (count($new_donors) > 0) {
    $fileName = strftime("%F-%H-%M").'_dd_tele_welcome';

    $tele_file = new TeleFile();
    $tele_file->add_donors($new_donors);
    $tele_file->save("$fileName.xlsx");

    # put tele file into encrypted archive
    $archive_password = $config['archive_password'];
    system("/usr/local/bin/zip -e -P '${archive_password}' $fileName.zip $fileName.xlsx > /dev/null");
    system("rm $fileName.xlsx > /dev/null 2> /dev/null");

    $today = strftime("%d.%m.%Y");
    list($min_date, $max_date) = get_donor_dates($new_donors);
    $acquisition_date = $min_date;
    if ($min_date != $max_date) {
        $acquisition_date .= " - $max_date";
    }

    $text = "Добрый день, коллеги

Во вложении выгрузка сторонников, привлеченных ${acquisition_date}.
Они очень ждут и будут рады услышать приятный голос Валентины сегодня ${today}.

Спасибо.
Хорошего дня!";

    $mg->send_text($config['email_from'], $config['email_to'], $config['email_subject'], $text, $config['email_cc'], "@$fileName.zip");

    system("rm $fileName.zip");
} else {
    $text = "Добрый день, коллеги

Мы очень расстроены, но с момента прошлой отправки у нас не было привлечено новых сторонников.
Сегодня у Валентины есть возможность передохнуть.

Спасибо.
Хорошего дня!";

    $mg->send_text($config['email_from'], $config['email_to'], $config['email_subject'], $text, $config['email_cc']);
}

if (@$config['test_order'] == '') {
    $db->commit_last_donors();
}