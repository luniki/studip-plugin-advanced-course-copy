<?php

class CreateTodoListTable extends DBMigration {

  function up() {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS acc_todo_lists (
  id int(11) NOT NULL AUTO_INCREMENT,
  description varchar(255) NOT NULL,
  owner_id varchar(255) NOT NULL,
  owner_type varchar(255) NOT NULL,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id)
);
SQL;
    $this->db->query($sql);

  }

  function down() {
    $this->db->query('DROP TABLE IF EXISTS acc_todo_lists');
  }
}
