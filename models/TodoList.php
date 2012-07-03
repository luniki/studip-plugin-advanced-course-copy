<?
class TodoList extends ActiveRecord\Model
{
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
/*
CREATE TABLE IF NOT EXISTS todo_lists (
  id int(11) NOT NULL AUTO_INCREMENT,
  description varchar(255) NOT NULL,
  owner_id varchar(255) NOT NULL,
  owner_type varchar(255) NOT NULL,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;
*/
