<?
class Task extends ActiveRecord\Model
{
    static $table_name = 'acc_tasks';

    static $attr_accessible = array('short_description', 'long_description', 'completed_at');

    static $belongs_to = array(
        array('todo_list')
    );

    function isDone()
    {
        return null !== $this->completed_at;
    }
}
