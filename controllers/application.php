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

require_once "app/controllers/studip_controller.php";
require_once dirname(dirname(__FILE__)) . "/phpar.php";

abstract class ApplicationController extends StudipController
{
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->flash = Trails_Flash::instance();
        $this->plugin = $this->dispatcher->plugin;
        \ACC\initActiveRecord();
    }

    function render_json($data)
    {
        $this->set_content_type('application/json;charset=utf-8');
        $this->render_text(json_encode($data));
    }

    function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }

    function setBaseLayout()
    {
        global $template_factory;
        $this->set_layout($template_factory->open("layouts/base_without_infobox"));
    }

    function parseRequestBody()
    {
        if ($this->format === 'json') {
        $body = file_get_contents('php://input');
            $decoded = json_decode($body, true);
            if (!is_null($decoded)) {
                return $decoded;
            }
       }

        return null;
    }
}
