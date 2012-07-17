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

        # need list if index or create
        # TODO: entweder selbst noch einmal authorisieren für #show,
        #       #delete, #update, oder immer eine Liste fordern, die
        #       aus dem lists-Controller kommt, der selbst authorisiert
        if (in_array($action, words('index new create')) && !$this->list) {
            throw new Trails_Exception(405);
        }

        if ($this->respond_to('html') || in_array($action, words('new edit'))) {
            $this->setBaseLayout();
        }
    }

    function index_action()
    {
        if (!$this->respond_to('html')) {
            $this->render_json(array_map(function ($t) {
                        return $t->to_array();
                    }, $this->list->tasks));
        }
    }

    function show_action($task_id)
    {
        $this->task = Task::find($task_id);

        if (!$this->respond_to('html')) {
            $this->render_json($this->task->to_array());
        }
    }

    function new_action()
    {
        # see template
    }

    function edit_action($task_id)
    {
        $this->task = Task::find($task_id);
    }

    function create_action()
    {
        $params = Request::getArray('task');
        $task = Task::create(
            array(
                'short_description'  => $params['short_description']
                , 'long_description' => $params['long_description']
                , 'todo_list_id'     => $this->list->id
            )
        );

        if ($task) {
            $format = $this->format ?: 'html';
            $this->redirect('lists/' . $this->list->id . '/tasks/show/' . $task->id . $format);
        }
        else {
            throw new Trails_Exception(500);
        }
    }

    function update_action($task_id)
    {
        $attributes = $this->parseRequestBody();
        $task = Task::find($task_id);
        $status = $task->update_attributes($attributes);

        if ($this->respond_to('json')) {
            if ($status) {
                $this->render_json($task->to_array());
            }
            else {
                throw new Trails_Exception(400);
            }
        }
        else {
            # TODO responding with something other than json
        }
    }
}
