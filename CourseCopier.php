<?php
namespace ACC;

const GENERAL_FILES     = "general_files";
const APPOINTMENT_FILES = "appointment_files";
const STATUS_GROUPS     = "status_groups";
const LITERATURE        = "literature";
const SCM               = "scm";
const WIKI              = "wiki";

class CourseCopier
{

    function shouldCopyModule($modules, $key)
    {
        return isset($modules[$key]);
    }

    function __construct($id)
    {
        $this->sourceID = $id;
        $this->source = new \ACC\Course($id);
    }

    function copy($semester_id, $modules)
    {
        $copy = $this->shallowClone($semester_id);
        $this->storeCopy($copy);

        $this->copyUserDomains($copy);
        $this->copyStudyAreas($copy);
        $this->copyAdmissionSeminarStudiengang($copy);
        $this->copyInstitutes($copy);
        $this->copyDatafields($copy);
        $this->copyPluginActivations($copy);

        # TODO what to do?
        $this->copyLecturers($copy);

        $this->setupModules($copy);

        # allgemeine und weitere Dateien
        $this->copyFiles($copy, $modules);

        # terminordner Dateien
        $this->copyAppointmentFolders($copy, $modules);

        # gruppenstrukturen und deren ordner
        $this->copyStatusGroups($copy, $modules);

        $this->copyLiteratur($copy, $modules);
        $this->copySCM($copy, $modules);
        $this->copyWiki($copy, $modules);

        # TODO die DFS muss schon da sein
        #self::createDataFieldStructures();

        $copy->setSourceCourse($this->source);

        $copy->createTodoList();

        return $copy;
    }

    function shallowClone($semester_id)
    {
        $copy = new \ACC\Course();

        $attrs = words(
            "seminar_number institut_id name subtitle status description ".
            "location misc password read_level write_level form participants ".
            "requirements orga leistungsnachweis ects modules"
        );

        foreach ($attrs as $attr) {
            $copy->$attr = $this->source->$attr;
        }

        $copy->visible = '0';
        $copy->showscore = '0';

        $this->setAdmission($copy);
        $this->setSemester($copy, $semester_id);

        return $copy;
    }

    function setAdmission($copy)
    {
        global $SEM_CLASS, $SEM_TYPE;
        $class = $SEM_CLASS[$SEM_TYPE[$copy->status]["class"]];

        $copy->admission_type         = (int) $class['admission_type_default'];
        $copy->admission_prelim       = (int) $class['admission_prelim_default'];
        $copy->admission_starttime    = -1;
        $copy->admission_endtime      = -1;
        $copy->admission_endtime_sem  = -1;
        $copy->admission_turnout      = 0;
        $copy->admission_prelim_txt   = "";
        $copy->admission_enable_quota = 0;
    }


    function setSemester($copy, $semester_id)
    {
        # start
        $semester = \Semester::find($semester_id);
        $copy->semester_start_time = $semester->getValue("beginn");

        # duration
        $copy->semester_duration_time = $this->source->semester_duration_time;

        $copy->metadate->setSeminarStartTime($copy->semester_start_time);
        $copy->metadate->setSeminarDurationTime($copy->semester_duration_time);
        $copy->metadate->seminar_id = $copy->getId();
    }


    function storeCopy($copy)
    {
        $this->logCreation($copy);
        $copy->store();
        $copy->restore();
    }

    function logCreation($copy)
    {
        // logging
        log_event('SEM_CREATE', $copy->getId());
        log_event($copy->visible ? 'SEM_VISIBLE' : 'SEM_INVISIBLE',
                  $copy->getId(), NULL, NULL,
                  'advanced_course_copy', 'SYSTEM');
    }

    function copyUserDomains($copy)
    {
        $domains = \UserDomain::getUserDomainsForSeminar($this->source->getId());
        foreach ($domains as $domain){
            $domain->addSeminar($copy->getId());
        }
    }

    function copyLecturers($copy)
    {

        $db = \DBManager::get();

#        Seminar_id, user_id, status, position, gruppe, admission_studiengang_id, notification, mkdate, comment, visible, label, bind_calendar

        $query =
            "INSERT INTO seminar_user ".
            "(Seminar_id, user_id, status, position, gruppe, ".
            "admission_studiengang_id, notification, mkdate, comment, ".
            "visible, label, bind_calendar) ".
            "SELECT ?, user_id, status, position, gruppe, ".
            "admission_studiengang_id, notification, mkdate, comment, ".
            "visible, label, bind_calendar ".
            "FROM seminar_user ".
            "WHERE Seminar_id = ? AND status='dozent'";
            $stmt = $db->prepare($query);
            $stmt->execute(array($copy->getId(),
                                 $this->source->getId()));

    }

    function _copyLecturers($copy)
    {
        global $auth;

        # currently logged in user
        $user_id = $auth->auth['uid'];

        # if not 'dozent', use default lecturer
        if (get_global_perm($user_id) !== 'dozent') {
            $user_id = $this->getDefaultLecturer();
        }

        $copy->addMember($user_id, 'dozent');
    }

    function copyStudyAreas($copy)
    {
        $copy->setStudyAreas($this->source->getStudyAreas());
    }

    function copyAdmissionSeminarStudiengang($copy)
    {
        if ($copy->admission_type && $copy->admission_type != 3) {

            $db = \DBManager::get();

            $query =
                "INSERT INTO admission_seminar_studiengang ".
                "(seminar_id, studiengang_id, quota) ".
                "SELECT ?, studiengang_id, quota ".
                "FROM admission_seminar_studiengang ".
                "WHERE seminar_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute(array($copy->getId(),
                                 $this->source->getId()));
        }
    }

    function copyInstitutes($copy)
    {

        $db = \DBManager::get();

        $query =
            "INSERT INTO seminar_inst ".
                "(seminar_id, institut_id) ".
                "SELECT ?, institut_id ".
                "FROM seminar_inst ".
                "WHERE seminar_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute(array($copy->getId(),
                                 $this->source->getId()));
    }


    function copyDatafields($copy)
    {
        foreach ($this->source->getDataFieldEntries() as $entry) {
            $new_entry = \DataFieldEntry::createDataFieldEntry($entry->structure,
                                                              $copy->getId(),
                                                              $entry->getValue());
            if ($new_entry->isValid()) {
                $new_entry->store();
            } else {
                throw new \DomainException("Invalid datafield entry: '" . $new_entry->getName() .
                                          "' in course: '" . $this->source->getId() . "'");
            }
        }
    }

    function copyPluginActivations($copy)
    {
        $manager = \PluginManager::getInstance();
        $plugins = \PluginEngine::getPlugins('StandardPlugin',
                                            $this->source->getId());
        foreach ($plugins as $plugin) {
            $manager->setPluginActivated($plugin->getPluginId(),
                                         $copy->getId(),
                                         TRUE);
        }
    }


    function copyFiles($copy, $modules)
    {
        if (!$this->shouldCopyModule($modules, GENERAL_FILES)) {
            $this->createDefaultFolder($copy);
            return;
        }

        foreach ($this->source->getGeneralFolders(FALSE) as $folder) {
            $this->copyFolder($copy, $folder['folder_id'], $copy->getId());
        }

        foreach ($this->source->getAdditionalFolders(FALSE) as $folder) {
            $md5 = md5($copy->getId() . 'top_folder');
            $this->copyFolder($copy, $folder['folder_id'], $md5);
        }
    }

    function copyAppointmentFolders($copy, $modules)
    {
        if (!$this->shouldCopyModule($modules, APPOINTMENT_FILES)) {
            # nothing to do
            return;
        }

        foreach ($this->source->getAppointmentFolders(FALSE) as $folder) {

            # copy it as a general top folder
            $md5 = md5($copy->getId() . 'top_folder');

            $this->copyFolder($copy, $folder['folder_id'], $md5);
        }
    }

    function createDefaultFolder($copy)
    {
        // Standard Ordner im Foldersystem anlegen, damit Studis auch
        // ohne Zutun des Dozenten Uploaden können
        $db = \DBManager::get();
        $query = $db->prepare(
            "INSERT INTO folder ".
            "SET folder_id = ?, range_id = ?, user_id = ?, name = ?, ".
            "description = ?, mkdate = ?, chdate = ?");

        global $auth;

        $query->execute(
            array(
                md5(uniqid("sommervogel")),
                $copy->getId(),
                $auth->auth["uid"],
                _("Allgemeiner Dateiordner"),
                _("Ablage für allgemeine Ordner und Dokumente der Veranstaltung"),
                time(),
                time()
            )
        );

    }


    function copyFolder($copy, $folder_id, $target_id)
    {
        $done = copy_item($folder_id, $target_id, $copy->getId());
        if (!$done) {
            # TODO hier und generell
        }
    }


    function copyStatusGroups($copy, $modules)
    {
        if (!$this->shouldCopyModule($modules, STATUS_GROUPS)) {
            # nothing to do
            return;
        }

        $db = \DBManager::get();

        $query = $db->prepare(
            "INSERT INTO statusgruppen ".
            "(statusgruppe_id, name, range_id, position, size, selfassign, mkdate, chdate, calendar_group) ".
            "SELECT MD5(CONCAT(?, statusgruppe_id)), name, ?, position, size, selfassign, mkdate, chdate, calendar_group " .
            "FROM statusgruppen ".
            "WHERE range_id = ?"
        );
        $query->execute(array($copy->getId(),
                              $copy->getId(),
                              $this->source->getId()));

        foreach ($this->source->getGroupFolders(FALSE) as $folder) {

            # target_id is the statusgruppe_id which is generated this
            # way above
            $md5 = md5($copy->getID() . $folder['range_id']);

            $this->copyFolder($copy, $folder['folder_id'], $md5);
        }


    }

    function copyLiteratur($copy, $modules)
    {
        if (!$this->shouldCopyModule($modules, LITERATURE)) {
            # nothing to do
            return;
        }

        $lit = $this->source->getLiterature();

        if (!sizeof($lit['lists'])) {
            return;
        }

        $db = \DBManager::get();

        $list_query = $db->prepare(
            "INSERT INTO lit_list ".
            "(list_id, range_id, name, format, user_id, mkdate, chdate, priority, visibility) ".
            "SELECT MD5(CONCAT(?, list_id)), ?, name, format, user_id, mkdate, chdate, priority, visibility ".
            "FROM lit_list ".
            "WHERE range_id = ?"
        );

        $list_query->execute(array($copy->getId(),
                                   $copy->getId(),
                                   $this->source->getId()));

        $item_query = $db->prepare(
            "INSERT INTO lit_list_content ".
            "(list_element_id, list_id, catalog_id, user_id, mkdate, chdate, note, priority) " .
            "SELECT MD5(CONCAT(?, list_element_id)), MD5(CONCAT(?, list_id)), catalog_id, user_id, mkdate, chdate, note, priority ".
            "FROM lit_list_content ".
            "WHERE list_id IN (?)"
        );

        $item_query->execute(array($copy->getId(),
                                   $copy->getId(),
                                   array_keys($lit['lists'])));
    }

    function copySCM($copy, $modules)
    {
        if (!$this->shouldCopyModule($modules, SCM)) {
            # nothing to do
            return;
        }

        $db = \DBManager::get();

        $query =
            "INSERT INTO scm ".
            "(scm_id, range_id, user_id, tab_name, content, mkdate, chdate) ".
            "SELECT MD5(CONCAT(?, scm_id)), ?, user_id, tab_name, content, mkdate, chdate ".
            "FROM scm ".
            "WHERE range_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute(array($copy->getId(),
                             $copy->getId(),
                             $this->source->getId()));
    }

    function copyWiki($copy, $modules)
    {

        if (!$this->shouldCopyModule($modules, WIKI)) {
            # nothing to do
            return;
        }

        $db = \DBManager::get();

        $query =
            "INSERT INTO wiki ".
            "(range_id, user_id, keyword, body, chdate, version) ".
            "SELECT ?, user_id, keyword, body, chdate, version ".
            "FROM wiki ".
            "WHERE range_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute(array($copy->getId(),
                             $this->source->getId()));

        $query =
            "INSERT INTO wiki_links ".
            "(range_id, from_keyword, to_keyword) ".
            "SELECT ?, from_keyword, to_keyword ".
            "FROM wiki_links ".
            "WHERE range_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute(array($copy->getId(),
                             $this->source->getId()));

        # wiki_lock does not have to be copied
    }

    function setupModules($copy)
    {

        $modules = new \Modules();

        //Standard Thema im Forum anlegen, damit Studis auch ohne
        //Zutun des Dozenten diskutieren koennen
        if ($modules->getStatus("forum", $copy->getId(), 'sem')) {
            $this->createDefaultTopic($copy);
        }
    }

    function createDefaultTopic($copy)
    {
        global $auth;

        CreateTopic(_("Allgemeine Diskussionen"),
                    get_fullname($auth->auth["uid"]),
                    _("Hier ist Raum für allgemeine Diskussionen"),
                    0, 0, $copy->getId());
    }


    static function createDataFieldStructures()
    {
        require_once 'lib/classes/DataFieldStructure.class.php';

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
}
