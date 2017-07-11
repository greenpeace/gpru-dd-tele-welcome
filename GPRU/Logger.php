<?php
namespace GPRU;

require_once 'Apache/log4php/Logger.php';

class Logger extends \Logger
{
}

if (file_exists('log4php.config.xml')) {
    Logger::configure('log4php.config.xml');
} else {
    Logger::configure(array(
        'rootLogger' => array(
            'appenders' => array('default'),
        ),
        'appenders' => array(
            'default' => array(
                'class' => 'LoggerAppenderConsole',
                'layout' => array(
                    'class' => 'LoggerLayoutPattern',
                    'params' => array(
                        'conversionPattern' => '%date{Y-m-d H:i:s,u} %logger %-5level %msg%n'
                    ),
                )
            )
        )
    ));
}
