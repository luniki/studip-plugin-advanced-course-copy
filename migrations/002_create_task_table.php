<?php

class CreateTodoListTable extends DBMigration {

  function up() {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS acc_tasks (
  id int(11) NOT NULL AUTO_INCREMENT,
  todo_list_id int(11) NOT NULL,
  short_description varchar(255) NOT NULL,
  long_description text NOT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL,
  completed_at datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY todo_list_id (todo_list_id)
);
SQL;
    $this->db->query($sql);

  }

  function down() {
    $this->db->query('DROP TABLE IF EXISTS acc_tasks');
  }
}
