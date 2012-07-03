<?php
namespace ACC;

function initActiveRecord()
{
    require_once dirname(__FILE__) .
        "/vendor/php-activerecord/ActiveRecord.php";

    include_once 'Log.php';
    include_once 'Log/file.php';

    if (\ActiveRecord\Config::instance()
        ->get_default_connection_string()) {
        return;
    }

    \ActiveRecord\Config::initialize(
        function($cfg)
        {
            $cfg->set_model_directory(dirname(__FILE__) . '/models');
            $cfg->set_connections(
                array(
                    'development' => sprintf('mysql://%s:%s@%s/%s',
                                             $GLOBALS['DB_STUDIP_USER'],
                                             $GLOBALS['DB_STUDIP_PASSWORD'],
                                             $GLOBALS['DB_STUDIP_HOST'],
                                             $GLOBALS['DB_STUDIP_DATABASE']
                    )
                )
            );
            if (class_exists('Log_file')) // PEAR Log installed
            {
                $logger = new \Log_file('/tmp/query.log','ident',array('mode' => 0664, 'timeFormat' =>  '%Y-%m-%d %H:%M:%S'));

                $cfg->set_logging(true);
                $cfg->set_logger($logger);
            }
        }
    );
}
