<?php

# Copyright (c)  2012 - <mlunzena@uos.de>
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

require 'ACCCourse.php';
require 'CourseCopier.php';

class AdvancedCourseCopy extends StudipPlugin implements SystemPlugin
{
    function __construct()
    {
        parent::__construct();

        if (Navigation::hasItem('/course')) {
            $this->setupNavigation();
        }
    }

    function setupNavigation()
    {
        $course_nav = new Navigation('ACC');
        $course_nav->setURL(PluginEngine::getURL($this));

        $course_nav->addSubNavigation('show', new Navigation(_('ACC'), PluginEngine::getURL($this)));
        $course_nav->addSubNavigation('todo', new Navigation(_('2do'), PluginEngine::getURL("Todos", array(), "")));

        Navigation::addItem('/course/acc', $course_nav);

    }

    function requireContext()
    {
        if (!$this->getContext()) {
            header("HTTP/1.1 400 Bad Request", TRUE, 400);
            throw new Exception("Bad Request");
        }
    }

    function getContext()
    {
        return Request::option("cid");
    }

    function getTemplateFactory()
    {
        return new Flexi_TemplateFactory(dirname(__FILE__) . "/templates");
    }

    function getBaseLayout()
    {
        global $template_factory;
        return $template_factory->open("layouts/base_without_infobox");
    }

    function show_action()
    {
        $this->requireContext();

        $course           = new Seminar($this->getContext());
        $semesters        = Semester::getAll();
        $next_semester    = Semester::findNext();
        $plugin           = $this;

        $modules = array(
              array('name'  => \ACC\GENERAL_FILES,
                    'label' => _("allgemeine Dateien"))
            , array('name'  => \ACC\APPOINTMENT_FILES,
                    'label' => _("Terminordner"))
            , array('name'  => \ACC\STATUS_GROUPS,
                    'label' => _("Gruppenstrukturen und zugehörige Dateien"))
            , array('name'  => \ACC\LITERATURE,
                    'label' => _("Literatur"))
            , array('name'  => \ACC\SCM,
                    'label' => _("Infoseiten"))
            , array('name'  => \ACC\WIKI,
                    'label' => _("Wikieinträge"))
        );


        Navigation::activateItem('/course/acc');

        $factory = $this->getTemplateFactory();
        echo $factory->render("show",
                              compact(words("course semesters next_semester plugin modules")),
                              $this->getBaseLayout());
    }


    function copy_action()
    {
        $this->requireContext();

        $plugin = $this;

        $src = new \ACC\Course($this->getContext());

        # TODO semester checken
        $semester = Request::option("semester");
        $modules = Request::getArray("modules");

        $db = DBManager::get();
        $db->beginTransaction();
        #*************************************************************

        $copy = $this->copy($this->getContext(), $semester, $modules);

        $factory = $this->getTemplateFactory();
        echo $factory->render("copy",
                              compact(words("src copy plugin")),
                              $this->getBaseLayout());

        $copy->delete();

        #*************************************************************
        $db->rollBack();
    }


    function copy($id, $target_semester, $modules)
    {
        $copier = new \ACC\CourseCopier($id);
        $copy = $copier->copy($target_semester, $modules);

        return $copy;
    }

    function ar_action()
    {
        echo __METHOD__;

        require dirname(__FILE__) . "/vendor/php-activerecord/ActiveRecord.php";

        ActiveRecord\Config::initialize(
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
            }
        );

        $todo = Todo::create(array('description' => 'something', 'state' => 'incomplete'));

        array_walk(Todo::find('all'), function ($t) {
                var_dump($t->to_json());
            });

        array_walk(TodoList::find('all'), function ($t) {
                var_dump($t->to_json());
            });
    }
}
