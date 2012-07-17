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

class DynamicURLNavigation extends Navigation
{

    function getURL()
    {
        if (is_callable($this->url)) {
            $callable = $this->url;
            $this->url = $callable();
        }
        return parent::getURL();
    }
}


class ContextualNavigation extends Navigation
{

    function isVisible($needs_image = false)
    {
        $params = URLHelper::getLinkParams();
        if (!isset($params['cid'])) {
            return false;
        }

        return parent::isVisible($needs_image);
    }
}


class AdvancedCourseCopy extends StudipPlugin implements SystemPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->setupNavigation();
    }

    function setupNavigation()
    {
        $context = $this->getContext();
        if ($context) {
            $this->setupWhatsNextNavigation($context);
        }

        $this->setupCopyCourseNavigation();
    }

    function setupWhatsNextNavigation($context)
    {
        # setup "what's next" links
        $course = new \ACC\Course($context);
        if ($course->hasTodoList()) {

            $nav = $this->createWhatsNext();

            # put "what's next" to the left in "Verwaltung" for admin and root
            if (Navigation::hasItem('/admin/course')) {
                Navigation::insertItem('/admin/course/whatsnext', $nav, 'details');
            }

            # put "what's next" to the left in "Verwaltung" for dozent and tutor
            if (Navigation::hasItem('/course/admin/main')) {
                Navigation::insertItem('/course/admin/whatsnext', $nav, 'main');
            }

            $admin_nav = clone $nav;
            $admin_nav->setTitle(_('Administration dieser Veranstaltung'));

            # redirect course link "Verwaltung" to "what's next"
            if (Navigation::hasItem('/course/main/admin')) {
                Navigation::addItem('/course/main/admin', $admin_nav);
            }
        }
    }

    function createWhatsNext()
    {
        $nav = new ContextualNavigation('What\'s next?');
        $nav->setURL(PluginEngine::getURL($this, array(), 'whatsnext'));
        $nav->setDescription(_("Ihre gerade kopierte Veranstaltung muss noch um Daten ergänzt werden, die nicht kopiert werden konnten."));
        return $nav;
    }

    function setupCopyCourseNavigation()
    {
        # replace course copy links
        $plugin = $this;

        $course_nav = new DynamicURLNavigation('Veranstaltung kopieren (+)');
        $course_nav->setURL(function () use ($plugin) {
                return PluginEngine::getURL($plugin, array("list" => "TRUE"));
            });
        $course_nav->setImage('icons/16/black/add/seminar.png" class="plugin-acc-copy');

        if (Navigation::hasItem('/admin/course')) {
            Navigation::addItem('/admin/course/copy', $course_nav);
        }

        if (Navigation::hasItem('/course/admin/main')) {
            Navigation::addItem('/course/admin/main/copy', $course_nav);
        }
    }

    function requireContext()
    {
        $context = $this->getContext();
        if (!$context) {
            if (!$GLOBALS['perm']->have_perm("admin")) {
                header("HTTP/1.1 400 Bad Request", TRUE, 400);
                throw new Exception("Bad Request");
            }
            //Auswähler für Admin-Bereich:
            else {
                # TODO: in eigene Methode auslagern
                PageLayout::setTitle(_("Kopieren von Veranstaltungen"));
                $this->activateNavigation();
                $GLOBALS['view_mode'] = "sem";
                $GLOBALS['i_page'] = "copy_assi.php";
                $_REQUEST['list'] = TRUE;

                require_once 'lib/admin_search.inc.php';
                include 'lib/include/html_head.inc.php';
                include 'lib/include/header.php';
                ob_start();
                $url = htmlReady(PluginEngine::getURL($this));
                register_shutdown_function(function () use ($url) {
                        $content = ob_get_clean();
                        $content = preg_replace('/href="[^"]*do_copy[^"]*=([0-9a-f]{32})[^"]*"/', 'href="' . $url . '?cid=$1"', $content);
                        echo $content;
                    });
                include 'lib/include/admin_search_form.inc.php';
                exit;
            }
        }

        return new \ACC\Course($context);
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
        return $template_factory->open("layouts/base");
    }


    function activateNavigation()
    {
        if (Navigation::hasItem('/admin/course/copy')) {
            Navigation::activateItem('/admin/course/copy');
        }

        if (Navigation::hasItem('/course/admin/main')) {
            Navigation::activateItem('/course/admin/main');
        }
    }

    function authorize($course)
    {
        global $perm, $SessSemName;
        if (!$perm->have_studip_perm("dozent", $course->getId())) {
            throw new AccessDeniedException("Not authorized");
        }
    }

    function show_action()
    {
        $course = $this->requireContext();
        $this->authorize($course);

        $this->activateNavigation();

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

        $factory = $this->getTemplateFactory();
        echo $factory->render("show",
                              compact(words("course semesters next_semester plugin modules")),
                              $this->getBaseLayout());
    }

    function copy_action()
    {
        $course = $this->requireContext();
        $this->authorize($course);

        $plugin = $this;

        $semester = Request::option("semester");
        $modules = Request::getArray("modules");

        $copier = new \ACC\CourseCopier($course->getId());
        $copy = $copier->copy($semester, $modules);

        $_SESSION['plugin-acc-flash'] = array("success" => TRUE);

        $url = PluginEngine::getURL($this,
                                    array('cid' => $copy->getId()),
                                    'whatsnext');
        header('Location: ' . $url);
    }

    function whatsnext_action()
    {
        $course = $this->requireContext();
        $this->authorize($course);

        # get flash if existent
        $flash = @$_SESSION['plugin-acc-flash'];
        unset($_SESSION['plugin-acc-flash']);

        if (Navigation::hasItem('/course/admin/whatsnext')) {
            Navigation::activateItem('/course/admin/whatsnext');
        }
        else if (Navigation::hasItem('/admin/course/whatsnext')) {
            Navigation::activateItem('/admin/course/whatsnext');
        }

        $plugin = $this;
        $list = $course->getTodoList();

        $factory = $this->getTemplateFactory();
        echo $factory->render("whatsnext",
                              compact(words("course plugin list flash")),
                              $this->getBaseLayout());
    }
}
