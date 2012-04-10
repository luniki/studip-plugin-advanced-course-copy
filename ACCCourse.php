<?
namespace ACC;

require 'XML/Serializer.php';

class Course extends \Seminar
{


    function toXML()
    {
        $data = $this->old_settings;


        $data['domains']               = $this->getDomains();
        $data['studyareas']            = $this->getStudyAreas();
        $data['admission_studiengang'] = $this->getAdmissionSeminarStudiengang();
        $data['institutes']            = $this->getInstitutes();
        $data['datafields']            = $this->getDataFieldEntries();

        $data['plugins']               = $this->getActivatedPlugins();

        $data['files']                 = $this->getFiles();
        $data['general_folders']       = $this->getGeneralFolders();
        $data['additional_folders']    = $this->getAdditionalFolders();

        # TODO
        $data['groups']                = $this->getGroups();
        $data['group_folders']         = $this->getGroupFolders();
        $data['literature']            = $this->getLiterature();
        $data['scm']                   = array_map(function($page) {return $page['tab_name'];}, $this->getInfoPages($cid));
        $data['wiki']                  = array_map(function($page) {return $page['keyword'] . ':' . $page['version'];}, $this->getWikiPages($cid));

        $data['appointment_folders']   = $this->getAppointmentFolders();

        $data['lecturers']             = $this->getMembers("dozent");

        $serializer = new \XML_Serializer(array("indent" => "  "));
        $serializer->serialize($data);
        return $serializer->getSerializedData();
    }

    function getDomains()
    {
        return array_map(function ($d) { return $d->getName(); },
                         \UserDomain::getUserDomainsForSeminar($this->getId()));
    }

    function getDataFieldEntries()
    {
        return \DataFieldEntry::getDataFieldEntries($this->getId(), "sem");
    }

    function getActivatedPlugins()
    {
        return \PluginEngine::getPlugins('StandardPlugin',
                                        $this->getId());
    }

    function getFiles()
    {
        $db = \DBManager::get();
        $stmt = $db->prepare("SELECT dokument_id, dokumente.* FROM dokumente WHERE seminar_id = ? ORDER BY name");
        $stmt->execute(array($this->getId()));
        return $stmt->fetchGrouped();
    }

    function getGeneralFolders($recursive = TRUE)
    {
        return $this->getFolders(array($this->getId()), $recursive);
    }

    function getAdditionalFolders($recursive = TRUE)
    {
        return $this->getFolders(array(md5($this->getId() . "top_folder")), $recursive);
    }

    function getFolders($ids, $recursive)
    {
        if (!sizeof($ids)) {
            return array();
        }
        $db = \DBManager::get();
        $stmt = $db->prepare("SELECT folder.* FROM folder WHERE range_id IN (?)");
        $stmt->execute(array($ids));
        $folders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($recursive) {
            foreach ($folders as &$row) {
                $row['children'] = $this->getFolders($row["folder_id"], TRUE);
            }
        }

        return $folders;
    }

    function getAppointments()
    {
        $db = \DBManager::get();
        $stmt = $db->prepare("SELECT issue_id, themen.* FROM themen WHERE seminar_id = ?");
        $stmt->execute(array($this->getId()));
        return $stmt->fetchGrouped();
    }

    function getAppointmentFolders($recursive = TRUE)
    {
        $appointments = $this->getAppointments($this->getId());
        return $this->getFolders(array_keys($appointments), $recursive);
    }


    function getGroups()
    {
        $db = \DBManager::get();
        $stmt = $db->prepare("SELECT statusgruppe_id, statusgruppen.* FROM statusgruppen WHERE range_id = ?");
        $stmt->execute(array($this->getId()));
        return $stmt->fetchGrouped();
    }

    function getGroupFolders($recursive = TRUE)
    {
        $groups = $this->getGroups($this->getId());
        return $this->getFolders(array_keys($groups), $recursive);
    }



    function getLiterature()
    {
        $db = \DBManager::get();

        $stmt = $db->prepare("SELECT list_id, lit_list.* FROM lit_list WHERE range_id = ? ORDER BY name");
        $stmt->execute(array($this->getId()));
        $lists = $stmt->fetchGrouped();

        if (sizeof($lists)) {
            $stmt = $db->prepare("SELECT * FROM lit_list_content WHERE list_id IN (?) ORDER BY list_id, list_element_id");
            $stmt->execute(array(array_keys($lists)));
            $joins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return compact("lists", "joins");
    }

    function getInfoPages()
    {
        $db = \DBManager::get();
        $stmt = $db->prepare("SELECT * FROM scm WHERE range_id = ? ORDER BY tab_name");
        $stmt->execute(array($this->getId()));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    function getWikiPages()
    {
        $db = \DBManager::get();

        $stmt = $db->prepare("SELECT * FROM wiki WHERE range_id = ? ORDER BY keyword, version");
        $stmt->execute(array($this->getId()));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    function getAdmissionSeminarStudiengang()
    {
        $db = \DBManager::get();

        $stmt = $db->prepare("SELECT * FROM admission_seminar_studiengang ".
                             "WHERE seminar_id = ?");
        $stmt->execute(array($this->getId()));


        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}
