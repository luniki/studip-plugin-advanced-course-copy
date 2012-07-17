<?
# Copyright (c)  2012 <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

namespace ACC;

require_once 'phpar.php';

class Course extends \Seminar
{

    function toXML()
    {
        require_once 'XML/Serializer.php';

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

    # MD5('ACC') == 'c4fd1ef4041f00039def6df0331841de'
    const DATAFIELD_SOURCE_ID = 'c4fd1ef4041f00039def6df0331841de';

    function setSourceCourse($source)
    {
        require_once 'lib/classes/DataFieldEntry.class.php';

        $entries = \DataFieldEntry::getDataFieldEntries($this->getId(), 'sem');
        $entries[self::DATAFIELD_SOURCE_ID]->value = $source->getId();
        return $entries[self::DATAFIELD_SOURCE_ID]->store();
    }

    function getSourceCourse()
    {
        require_once 'lib/classes/DataFieldEntry.class.php';

        $entries = \DataFieldEntry::getDataFieldEntries($this->getId(), 'sem');

        return isset($entries[self::DATAFIELD_SOURCE_ID])
            ? $entries[self::DATAFIELD_SOURCE_ID]->value
            : null;
    }

    function hasTodoList()
    {
        \ACC\initActiveRecord();
        return \TodoList::findByOwner($this->getId(), 'copied_course', FALSE);
    }

    function getTodoList()
    {
        \ACC\initActiveRecord();
        return \TodoList::findByOwner($this->getId(), 'copied_course');
    }

    function createTodoList()
    {
        \ACC\initActiveRecord();

        $attributes = array(
            'description' => "Was ist noch zu tun?",
            'owner_id'    => $this->getId(),
            'owner_type'  => 'copied_course',
        );

        # TODO: Fehlerbehandlung?
        $list = \TodoList::create($attributes);

        $tasks = array();

        $url = \URLHelper::getLink('dispatch.php/course/basicdata/view/82c9d90eecae0dc3d5b87965413c6a25');
        $tasks[] = array(
            "Dozenten/Tutoren",

            'Beim Kopieren der Veranstaltung wurden nur die Dozenten aber nicht die Tutoren kopiert. '.
            '&Uuml;berpr&uuml;fen Sie bitte, ob die Dozenten auch in der kopierten Veranstaltung unterrichten! '.
            'Falls zutreffend erg&auml;nzen Sie die Veranstaltung um Tutoren! '.
            'Die Personalverwaltung dieser Veranstaltung finden Sie unter <a href="' . $url . '">Grunddaten - Personal</a>.');

        $tasks[] = array("R&auml;ume/Zeiten", "TODO");
        $tasks[] = array("Anmeldeverfahren", "TODO");
        $tasks[] = array("sichtbar schalten", "TODO");

        $todo_list_id = $list->id;

        foreach ($tasks as $task) {
            list($short_description, $long_description) = $task;

            $tmp = new \Task(compact('short_description',
                                     'long_description',
                                     'todo_list_id'),
                             false);

            # TODO: Fehlerbehandlung
            $tmp->save($validate);
        }
    }
}
