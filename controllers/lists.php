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

        # /lists
        if ('index' === $action) {
            self::require_allowed_method('GET', 'POST');
            if ('POST' === self::get_method()) {
                $action = 'create';
            }
            return;
        }

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
    }

    function index_action()
    {
        $lists = array_map(function ($t) {
                return $t->to_array();
            }, TodoList::all());

        $this->render_json($lists);
    }

    function show_action($list_id)
    {
        $this->render_json(TodoList::find($list_id)->to_array());
    }

    function new_action()
    {
        $this->setBaseLayout();
    }

    function edit_action($list_id)
    {
        $this->setBaseLayout();
        $this->list = TodoList::find($list_id);
    }

    function create_action()
    {
        $params = Request::getArray('list');
        $list = TodoList::create(array('description' => $params['description']));
        if ($list) {
            $this->redirect('lists/' . $list->id);
        }
        else {
            # TODO
        }
    }

    function update_action($list_id)
    {
        $params = Request::getArray('list');

        $list = TodoList::find($list_id);
        $list->description = $params['description'];
        if ($list->save()) {
            $this->redirect('lists/' . $list->id);
        }
        else {
            # TODO
        }
    }

    function destroy_action($list_id)
    {
        $list = TodoList::find($list_id);
        if ($list->delete()) {
            $this->render_nothing();
        }
        else {
            # TODO
        }
    }

    function tasks_action($list_id)
    {
        $tasks_controller = $this->dispatcher->load_controller('tasks');
        $tasks_controller->list = TodoList::find($list_id);
        $this->response = $tasks_controller->perform(join('/', func_get_args()));
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
