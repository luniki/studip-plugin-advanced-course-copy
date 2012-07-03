<?
class Task extends ActiveRecord\Model
{
    static $attr_accessible = array('short_description', 'long_description', 'completed_at');

    static $belongs_to = array(
        array('todo_list')
    );

    function isDone()
    {
        return null !== $this->completed_at;
    }
}
/*
CREATE TABLE IF NOT EXISTS tasks (
  id int(11) NOT NULL AUTO_INCREMENT,
  todo_list_id int(11) NOT NULL,
  short_description varchar(255) NOT NULL,
  long_description text NOT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  completed_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY todo_list_id (todo_list_id)
) ENGINE=MyISAM;
*/
