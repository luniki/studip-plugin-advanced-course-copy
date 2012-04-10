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

require_once "application.php";

class TasksController extends ApplicationController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        # /tasks
        if ('index' === $action) {
            self::require_allowed_method('GET', 'POST');
            if ('POST' === self::get_method()) {
                $action = 'create';
            }
        }

        else {

            # :id consists of digits
            if (!preg_match('/\d+/A', $action)) {
                # TODO remove after debug
                # return false;
                return;
            }

        # /lists/:id/tasks
        if ('tasks' === @$args[0]) {
            $args[0] = $action;
            $action = 'tasks';
        }

        # /lists/:id
        else {
            $args = array($action);
            $action = self::map_method_to_action();
        }





        if (preg_match('/\d+/A', $action)) {
            $this->task_id = $action;
            $action = self::map_method_to_action();
        }

        }

        # need list unless index or create
        if (in_array($action, words('index create'))
            && !$this->list) {
            throw new Trails_Exception(405);
        }
    }

    function index_action()
    {
        $tasks = array_map(function ($t) {
                return $t->to_array();
            }, $this->list->tasks);

        $this->render_json($tasks);
    }

    function show_action()
    {
        #        $todo = Todo::create(array('description' => 'something', 'state' => 'incomplete'));

        #        array_walk(Todo::find('all'), function ($t) {
        #                var_dump($t->to_json());
        #            });

        $this->render_json(Task::find($this->task_id)->to_array());
    }

     function create_action()
     {
         $this->render_text(__METHOD__);
     }

     function update_action()
     {
         $this->render_text(__METHOD__);
     }

     function destroy_action()
     {
         $this->render_text(__METHOD__);
     }
}
