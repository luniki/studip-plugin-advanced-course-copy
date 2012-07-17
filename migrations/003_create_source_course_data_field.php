<?php

class CreateSourceCourseDataField extends DBMigration {

    function up() {
        self::createDataFieldStructures();
    }

    function down() {
        self::destroyDataFieldStructures();
    }

    static function createDataFieldStructures()
    {
        require_once 'lib/classes/DataFieldStructure.class.php';
        require_once dirname(dirname(__FILE__)) . '/ACCCourse.php';

        $structures = array(
            \ACC\Course::DATAFIELD_SOURCE_ID => 'ID of original course'
        );

        foreach ($structures as $id => $name) {

            $dfs = new DataFieldStructure(
                array(
                    'datafield_id' => $id,
                    'name'         => $name,
                    'object_type'  => 'sem',
                    'edit_perms'   => 'root',
                    'view_perms'   => 'all',
                    'priority'     => '0',
                    'type'         => 'textline',
                    'typeparam'    => ''
                ));

            $dfs->store();
        }
    }

    static function destroyDataFieldStructures()
    {
        require_once 'lib/classes/DataFieldStructure.class.php';
        require_once dirname(dirname(__FILE__)) . '/ACCCourse.php';

        DataFieldStructure::remove(\ACC\Course::DATAFIELD_SOURCE_ID);
    }
}
