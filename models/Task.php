<?
class Task extends ActiveRecord\Model
{
    static $belongs_to = array(
        array('todo_list')
    );
}
/*
CREATE TABLE IF NOT EXISTS tasks (
  id int(11) NOT NULL AUTO_INCREMENT,
  todo_list_id int(11) NOT NULL,
  short_description varchar(255) COLLATE latin1_german2_ci NOT NULL,
  long_description text COLLATE latin1_german2_ci NOT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  completed_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY todo_list_id (todo_list_id)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci ;
*/
