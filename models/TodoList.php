<?
class TodoList extends ActiveRecord\Model
{
    static $table_name = 'acc_todo_lists';

    static $has_many = array(
        array('tasks')
    );

    static function findByOwner($owner_id, $owner_type, $eager = true)
    {
        $options = array(
            'conditions' => array('owner_id = ? AND owner_type = ?',
                                  $owner_id, $owner_type));

        if ($eager) {
            $options['include'] = array('tasks');
        }

        return \TodoList::first($options);
    }
}
