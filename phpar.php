<?php
# Copyright (c)  2012 <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

namespace ACC;

function initActiveRecord()
{
    require_once dirname(__FILE__) .
        "/vendor/php-activerecord/ActiveRecord.php";

    # include_once 'Log.php';
    # include_once 'Log/file.php';

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

            # if (class_exists('Log_file')) // PEAR Log installed
            # {
            #     $logger = new \Log_file('/tmp/query.log', 'ident', array('mode' => 0664, 'timeFormat' =>  '%Y-%m-%d %H:%M:%S'));
            #
            #     $cfg->set_logging(true);
            #     $cfg->set_logger($logger);
            # }
        }
    );
}
