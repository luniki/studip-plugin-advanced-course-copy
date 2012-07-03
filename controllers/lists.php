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

class ListsController extends ApplicationController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        # /lists/:list_id/tasks/...
        if ('tasks' === @$args[0]) {
            # action contains list_id, move it to args
            $args[0] = $action;
            $action = 'tasks';
        }

        # initialize list
        if (isset($args[0])) {
            $this->list = TodoList::find($args[0]);
        }

        # setup layout
        if ($this->respond_to('html') ||
            in_array($action, words('new edit'))) {

            $this->setBaseLayout();
        }

        return $this->must_authorize($action);
    }


    private function must_authorize($action)
    {

        if ($this->list->owner_type == 'copied_course') {
            if ($this->course_permission('dozent')) {
                return TRUE;
            } else if ($this->course_permission('tutor')) {
                return $action == 'show';
            }
        }

        return FALSE;
    }

    function course_permission($role)
    {
        global $perm, $SessSemName;
        return $perm->have_studip_perm($role, $SessSemName[1]);
    }

    function index_action()
    {
        $this->lists = TodoList::all();

        if (!$this->respond_to('html')) {
            $this->render_json(array_map(function ($t) {
                        return $t->to_array();
                    }, $this->lists));
        }
    }

    function show_action($list_id)
    {
        if (!$this->respond_to('html')) {
            $this->render_json($this->list->to_array());
        }
    }

    function new_action()
    {
    }

    function edit_action($list_id)
    {
    }

    function create_action()
    {
        $params = Request::getArray('list');
        $list = TodoList::create(array('description' => $params['description']));
        if ($list) {
            $this->redirect('lists/show/' . $list->id);
        }
        else {
            # TODO
        }
    }

    function update_action($list_id)
    {
        $params = Request::getArray('list');

        $this->list->description = $params['description'];
        if ($this->list->save()) {
            $this->redirect('lists/show/' . $this->list->id);
        }
        else {
            # TODO
        }
    }

    function destroy_action($list_id)
    {
        $deleted = $this->list->delete();

        if ($deleted) {
            if ($this->respond_to('html')) {
                $this->redirect('lists/index');
            } else {
                $this->render_nothing();
            }
        }
        else {
            # TODO
            throw new Trails_Exception(500);
        }
    }

    function tasks_action($list_id)
    {
        $tasks_controller = $this->dispatcher->load_controller('tasks');
        $tasks_controller->list = $this->list;

        # /lists/:list_id/tasks/{args[1..]}
        $args = array_slice(func_get_args(), 1);

        $unconsumed = join('/', $args) ?: 'index';

        if ($this->format) {
            $unconsumed .= "." . $this->format;
        }

        $this->response = $tasks_controller->perform($unconsumed);
        $this->performed = TRUE;
    }

    /**
     * Exception handler called when the performance of an action raises an
     * exception.
     *
     * @param  object     the thrown exception
     */
    function rescue($exception)
    {
        # TODO do this the right way (which is?)
        if ($exception instanceof ActiveRecord\RecordNotFound) {
            return $this->dispatcher->trails_error(new Trails_Exception(404));
        } else {
            throw $exception;
        }
    }
}
