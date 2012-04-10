<?php

class CourseCopier
{

    function __construct($id)
    {
        $this->sourceID = $id;
        $this->source = new Seminar($id);
    }

    function copy()
    {
        $copy = $this->shallowClone();
        $this->storeCopy($copy);

        $this->copyUserDomains($copy);
        $this->copyLecturers($copy);
        $this->copyStudyAreas($copy);
        $this->copyAdmissionSeminarStudiengang($copy);

        $this->copyInstitutes($copy);
        $this->copyDatafields($copy);
        $this->copyPluginActivations($copy);

        $this->setupModules($copy);

        return $copy;
    }

    function shallowClone()
    {
        $copy = new Seminar();

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

        # TODO

        /*
          # TODO should we copy admission stuff?
        $copy->admission_endtime = $sem_create_data['sem_admission_date'];
        $copy->admission_turnout = $sem_create_data['sem_turnout'];
        $copy->admission_type = (int)$sem_create_data['sem_admission'];
        $copy->admission_prelim = (int)$sem_create_data['sem_payment'];
        $copy->admission_prelim_txt = stripslashes($sem_create_data['sem_paytxt']);
        $copy->admission_starttime = $sem_create_data['sem_admission_start_date'];
        $copy->admission_endtime_sem = $sem_create_data['sem_admission_end_date'];
        $copy->admission_enable_quota = $sem_create_data['admission_enable_quota'];
        */

        # TODO
        /*
          # TODO what's the target start time?
          $copy->semester_start_time = $this->source->semester_start_time;
          $copy->semester_duration_time = $this->source->semester_duration_time;

          $copy->metadate->setSeminarStartTime($sem_create_data['sem_start_time']);
          $copy->metadate->setSeminarDurationTime($sem_create_data['sem_duration_time']);
          $copy->metadate->seminar_id = $copy->getId();
        */


        return $copy;
    }


    function storeCopy($copy)
    {
        // logging
        log_event('SEM_CREATE', $copy->getId());
        log_event($copy->visible ? 'SEM_VISIBLE' : 'SEM_INVISIBLE',
                  $copy->getId(), NULL, NULL,
                  'advanced_course_copy', 'SYSTEM');

        $copy->store();
    }


    function copyUserDomains($copy)
    {
        $domains = UserDomain::getUserDomainsForSeminar($this->id);
        foreach ($domains as $domain){
            $domain->addSeminar($copy->getId());
        }
    }

    function copyLecturers($copy)
    {
        throw new Exception("TODO");

        // alle ausgewählten Dozenten durchlaufen
        if (is_array($sem_create_data["sem_doz"]))
        {
            $self_included = FALSE;
            $count_doz=0;
            foreach ($sem_create_data["sem_doz"] as $key=>$val)
            {
                $group=select_group($sem_create_data["sem_start_time"]);

                if ($key == $user_id)
                    $self_included=TRUE;

                $next_pos = get_next_position("dozent",$sem_create_data["sem_id"]);

                $query = "insert into seminar_user SET Seminar_id = '".
                    $sem_create_data["sem_id"]."', user_id = '".
                    $key."', status = 'dozent', gruppe = '$group', visible = 'yes',".
                    " mkdate = '".time()."', position = '$next_pos', label = ".DBManager::get()->quote($sem_create_data["sem_doz_label"][$key], PDO::PARAM_STR)." ";
                $db3->query($query);// Dozenten eintragen:w

                if ($db3->affected_rows() >=1)
                    $count_doz++;
            }
        }



    }

    function copyStudyAreas($copy)
    {
        $copy->setStudyAreas($this->source->getStudyAreas());
    }

    function copyAdmissionSeminarStudiengang($copy)
    {
        throw new Exception("TODO");

        //Eintrag der zugelassen Studiengänge
        if ($sem_create_data["sem_admission"] && $sem_create_data["sem_admission"] != 3) {
            if (is_array($sem_create_data["sem_studg"])){
                foreach($sem_create_data["sem_studg"] as $key=>$val){
                    $query = "INSERT INTO admission_seminar_studiengang VALUES('".$sem_create_data["sem_id"]."', '$key', '".$val["ratio"]."' )";
                    $db3->query($query);// Studiengang eintragen
                }
            }
        }
    }

    function copyInstitutes($copy)
    {
        throw new Exception("TODO");

        //Eintrag der beteiligten Institute
        if (is_array($sem_create_data["sem_bet_inst"])>0)
        {
            $count_bet_inst=0;
            foreach ($sem_create_data["sem_bet_inst"] as $tmp_array) //Alle beteiligten Institute durchlaufen
            {
                $query = "INSERT INTO seminar_inst VALUES('".$sem_create_data["sem_id"]."', '$tmp_array')";
                $db3->query($query);// Institut eintragen
                if ($db3->affected_rows() >= 1)
                    $count_bet_inst++;
            }
        }

        //Heimat-Institut ebenfalls eintragen, wenn noch nicht da
        $query = "INSERT IGNORE INTO seminar_inst values('".$sem_create_data["sem_id"]."', '".$sem_create_data["sem_inst_id"]."')";
        $db3->query($query);

    }

    function copyDatafields($copy)
    {
        throw new Exception("TODO");

        //Store the additional datafields
        if (is_array($sem_create_data["sem_datafields"])) {
            foreach ($sem_create_data['sem_datafields'] as $id=>$val) {
                $struct = new DataFieldStructure(array("datafield_id"=>$id, 'type'=>$val['type'], 'name'=>$val['name']));
                $entry  = DataFieldEntry::createDataFieldEntry($struct, $sem_create_data['sem_id'], $val['value']);
                if ($entry->isValid())
                    $entry->store();
                else
                    $errormsg .= "error§" . sprintf(_("Fehlerhafte Eingabe im Feld '%s': %s (Eintrag wurde nicht gespeichert)"), $entry->getName(), $entry->getDisplayValue());
            }
        }


    }

    function copyPluginActivations($copy)
    {
        throw new Exception("TODO");

        // save activation of plugins
        if (count($sem_create_data["enabled_plugins"]) > 0) {
            $enabled_plugins = PluginEngine::getPlugins('StandardPlugin');
            $context = $sem->getId();

            foreach ($enabled_plugins as $plugin) {
                $plugin_id = $plugin->getPluginId();
                $plugin_status = in_array($plugin_id, $sem_create_data['enabled_plugins']);

                if (PluginManager::getInstance()->isPluginActivated($plugin_id, $context) != $plugin_status) {
                    PluginManager::getInstance()->setPluginActivated($plugin_id, $context, $plugin_status);
                }
            }
        }

    }



    function copyFiles($copy)
    {
    }

    function copyStatusGroups($copy)
    {
    }

    function copyLiteratur($copy)
    {
    }

    function copySCM($copy)
    {
    }

    function copyWiki($copy)
    {
    }

    function setupModules($copy)
    {
        throw new Exception("TODO");

        $modules = new Modules();

        //Standard Thema im Forum anlegen, damit Studis auch ohne
        //Zutun des Dozenten diskutieren koennen

        $user_id = TODO;

        if ($modules->getStatus("forum", $copy->getId(), 'sem')) {
            CreateTopic(_("Allgemeine Diskussionen"),
                        get_fullname($user_id),
                        _("Hier ist Raum für allgemeine Diskussionen"),
                        0, 0, $copy->getId());
        }

        //Standard Ordner im Foldersystem anlegen, damit Studis auch ohne Zutun des Dozenten Uploaden k&ouml;nnen
        if ($modules->getStatus("documents", $copy->getId(), 'sem')) {

            $db3->query("INSERT INTO folder SET folder_id='".md5(uniqid("sommervogel"))."', range_id='".$sem_create_data["sem_id"]."', user_id='".$user_id."', name='"._("Allgemeiner Dateiordner")."', description='"._("Ablage für allgemeine Ordner und Dokumente der Veranstaltung")."', mkdate='".time()."', chdate='".time()."'");
        }
    }
}
