<?
class TodoList extends ActiveRecord\Model
{
    static $has_many = array(
        array('tasks')
    );
}
/*
CREATE TABLE IF NOT EXISTS todo_lists (
  id int(11) NOT NULL AUTO_INCREMENT,
  description varchar(255) COLLATE latin1_german2_ci NOT NULL,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci  ;
*/
