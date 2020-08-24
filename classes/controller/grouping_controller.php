<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Controller for grouping view
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/test_user_generator.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/advanced_job_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/state_machine.php');

/**
 * Class mod_groupformation_grouping_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_grouping_controller {

    /** @var int The id of the groupformation activity */
    private $groupformationid = null;

    /** @var int The id of the course module */
    public $cmid;

    /** @var stdClass course module */
    public $cm;

    /** @var array all groups generated by algorithm */
    private $groups = array();

    /** @var mod_groupformation_storage_manager instance of storage manager */
    private $store = null;

    /** @var mod_groupformation_groups_manager instance of groups manager */
    private $groupsmanager = null;

    /** @var mod_groupformation_user_manager instance of groups manager */
    private $usermanager = null;

    /** @var stdClass instance of job */
    private $job = null;

    /** @var mod_groupformation_state_machine Activity state machine */
    private $statemachine;

    /**
     * Creates an instance of grouping_controller for groupformation
     *
     * @param int $groupformationid
     * @param stdClass $cm
     * @throws dml_exception
     */
    public function __construct($groupformationid, $cm = null) {
        global $DB;

        $this->groupformationid = $groupformationid;
        if (!is_null($cm)) {
            $this->cmid = $cm->id;
            $this->cm = $cm;
        }

        $this->store = new mod_groupformation_storage_manager ($groupformationid);

        $this->statemachine = new mod_groupformation_state_machine($groupformationid);

        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);

        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);

        $this->groups = $this->groupsmanager->get_generated_groups('id, groupname,performance_index,moodlegroupid');

        $this->users = $this->groupsmanager->get_group_users();

        foreach ($this->users as $user) {
            if (!isset($this->groups[$user->groupid]->users)) {
                $this->groups[$user->groupid]->users = array();
            }
            $this->groups[$user->groupid]->users[$user->userid] = $user;
        };

        $getuserid = function($u) {
            return $u->userid;
        };

        $userids = array_map($getuserid, $this->users);

        $selectfields = implode(',', ['id', get_all_user_name_fields(true)]);
        $this->userrecords = $DB->get_records_list('user', 'id', $userids, null, $selectfields);

        $ajm = new mod_groupformation_advanced_job_manager();

        $this->job = $ajm::get_job($this->groupformationid);
        if (is_null($this->job)) {
            $groupingid = ($cm->groupmode != 0) ? $cm->groupingid : 0;
            $ajm::create_job($groupformationid, $groupingid);
            $this->job = $ajm::get_job($this->groupformationid);
        } else {
            $groupingid = ($cm->groupmode != 0) ? $cm->groupingid : 0;
            $ajm::update_job($this->job, $groupingid);
            $this->job = $ajm::get_job($this->groupformationid);
        }
    }

    /**
     * POST action to start job, sets it to 'waiting'
     *
     * @param stdClass $cm
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function start($cm) {

        $ajm = new mod_groupformation_advanced_job_manager();

        $usermanager = new mod_groupformation_user_manager($this->groupformationid);

        $users = $usermanager->handle_complete_questionnaires();

        $this->job->groupingid = $cm->groupingid;
        $ajm::set_job($this->job, "waiting", true);

        $context = groupformation_get_context($this->groupformationid);
        $enrolledusers = get_enrolled_users($context, 'mod/groupformation:onlystudent');

        foreach (array_values($enrolledusers) as $user) {
            groupformation_set_activity_completion($cm->id, $user->id);
        }

        $this->statemachine->next();

        return $users;
    }

    /**
     * POST action to abort current waiting or running job
     */
    public function abort() {

        $ajm = new mod_groupformation_advanced_job_manager();

        $ajm::set_job($this->job, "aborted", false, false);

        $this->statemachine->prev();
    }

    /**
     * POST action to adopt groups to moodle
     */
    public function adopt() {
        $ajm = new mod_groupformation_advanced_job_manager();
        $ajm::set_job($this->job, "waiting_groups", false, false);

        $this->statemachine->prev();
    }

    /**
     * POST action to adopt groups to moodle
     *
     * @param stdClass $cm
     * @throws moodle_exception
     */
    public function edit($cm) {
        $returnurl = new moodle_url ('/mod/groupformation/grouping_edit_view.php', array(
            'id' => $cm->id, 'do_show' => 'grouping'));
        redirect($returnurl);
    }

    /**
     * POST action to delete generated and/or adopted groups (moodle groups)
     */
    public function delete() {
        $this->groupsmanager->delete_generated_groups();

        $ajm = new mod_groupformation_advanced_job_manager();

        $job = $ajm::get_job($this->groupformationid);

        $ajm::reset_job($job);

        $this->statemachine->next();
    }

    /**
     * Stores the editing of groups
     *
     * @param string $groupsstring
     * @throws coding_exception
     * @throws dml_exception
     */
    public function save_edit($groupsstring) {
        $groupsarrayafter = json_decode($groupsstring, true);
        $groupskeysafter = array_keys($groupsarrayafter);
        $useridsafter = array();
        foreach ($groupsarrayafter as $array) {
            $useridsafter = array_merge($useridsafter, $array);
        }

        $groupsarraybefore = array();
        $useridsbefore = array();

        foreach (array_keys($this->groups) as $key) {
            $groupmembers = array_keys($this->get_group_members($key));

            $groupsarraybefore["" . $key] = $groupmembers;
            $useridsbefore = array_merge($useridsbefore, $groupmembers);
        }

        $groupskeysbefore = array_keys($groupsarraybefore);

        $samegroupids = count(
            array_intersect($groupskeysbefore, $groupskeysafter)) == count(
            $groupsarrayafter) && count($groupsarraybefore) == count($groupsarrayafter);
        $nousertwice = (count(array_unique($useridsafter)) == count($useridsbefore));
        $samenumberofusers = count($useridsafter) == count($useridsbefore);
        $nousermissing = count(array_intersect($useridsbefore, $useridsafter)) == count($useridsafter);
        if ($samegroupids && $nousertwice && $nousermissing && $samenumberofusers) {
            $this->groupsmanager->update_groups($groupsarrayafter, $groupsarraybefore);
        }
    }

    /**
     * Returns settings for template
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_settings() {
        $assigns = array();

        $array = array(
                'button1' => array(
                        'type' => 'submit', 'name' => 'start', 'value' => '0', 'state' => 'disabled',
                        'text' => get_string('grouping_start', 'groupformation')),
                'button2' => array(
                        'type' => 'submit', 'name' => 'delete', 'value' => '0', 'state' => 'disabled',
                        'text' => get_string('grouping_delete', 'groupformation')),
                'button3' => array(
                        'type' => 'submit', 'name' => 'adopt', 'value' => '0', 'state' => 'disabled',
                        'text' => get_string('grouping_adopt', 'groupformation')),
                'button4' => array(
                        'type' => 'submit', 'name' => 'edit', 'value' => '0', 'state' => 'disabled',
                        'text' => get_string('grouping_edit', 'groupformation'))
        );

        $state = $this->statemachine->get_state();

        switch ($state) {
            case 'q_closed':
                // 000000 = none.
                $assigns['status'] = array(
                        get_string('grouping_status_1', 'groupformation'), 0);
                $array['button1']['value'] = 1;
                $array['button1']['state'] = '';
                break;

            case 'gf_started':
                // 100000 = waiting.
                // 010000 = started.
                $assigns['status'] = array(
                        get_string('grouping_status_2', 'groupformation'), 1);
                $array['button1']['name'] = 'abort';
                $array['button1']['text'] = get_string('grouping_abort', 'groupformation');
                $array['button1']['value'] = 1;
                $array['button1']['state'] = '';
                $assigns['emailnotifications'] = $this->store->get_email_setting();
                break;

            case 'gf_aborted' :
                // 001000 = aborted.
                $assigns['status'] = array(
                        get_string('grouping_status_3', 'groupformation'), 1);
                break;

            case 'gf_done' :
                // 000110 = done - groups_generated.
                $assigns['status'] = array(
                        get_string('grouping_status_4', 'groupformation'), 0);
                $array['button2']['value'] = 1;
                $array['button2']['state'] = '';
                $array['button3']['value'] = 1;
                $array['button3']['state'] = '';
                $array['button4']['value'] = 1;
                $array['button4']['state'] = '';
                break;

            case 'ga_done':
                // 000111 = done - groups_generated - groups_adopted.
                $assigns['status'] = array(
                        get_string('grouping_status_5', 'groupformation'), 0);
                $array['button2']['value'] = 1;
                $array['button2']['state'] = '';
                $array['button2']['text'] = get_string('grouping_delete_moodle_groups', 'groupformation');
                break;

            case 'ga_started':
                // 100000 = waiting - groups_generated.
                // 010000 = started - groups_generated.
                $assigns['status'] = array(
                        get_string('grouping_status_6', 'groupformation'), 1);
                $assigns['emailnotifications'] = $this->store->get_email_setting();
                break;

            default:
                $assigns['status'] = array(
                        get_string('grouping_status_0', 'groupformation'), 0);
                break;
        }

        $assigns['buttons'] = $array;

        $users = $this->store->get_users_for_grouping();
        $count = count($users[0]) + count($users[1]);

        $assigns['student_count'] = $count;
        $assigns['cmid'] = $this->cmid;
        $assigns['onlyactivestudents'] = $this->store->get_grouping_setting();

        return $assigns;
    }

    /**
     * Returns statistics for template
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_statistics() {
        $assigns = array();
        $state = $this->statemachine->get_state();
        if ($state == 'gf_done' || $state == 'ga_done') {

            $assigns['numbOfGroups'] = count($this->groups);
            $assigns['maxSize'] = $this->groupsmanager->get_max_groups_size($this->groups);

        } else {
            $assigns['grouping_no_data'] = get_string('no_data_to_display', 'groupformation');
        }

        return $assigns;
    }

    /**
     * Returns generated groups for template
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function load_generated_groups() {
        $assigns = array();

        $topics = $this->store->ask_for_topics();
        $options = null;
        if ($topics) {
            $xmlcontent = $this->store->get_knowledge_or_topic_values('topic');
            $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $xmlcontent . ' </OPTIONS>';
            $options = mod_groupformation_util::xml_to_array($xmlcontent);
        }

        $state = $this->statemachine->get_state();

        if ($state == 'gf_done' || $state == 'ga_done') {

            foreach ($this->groups as $key => $value) {

                $gpi = (is_null($value->performance_index)) ? '-' : $value->performance_index;

                $pos = strrpos($value->groupname, "_");
                $number = substr($value->groupname, $pos + 1, strlen($value->groupname) - $pos);
                $title = "";
                if ($topics) {
                    $title = $options[$number - 1];
                }

                $assigns[$key] = array(
                        'topic' => $title,
                        'groupname' => $value->groupname, 'groupquallity' => $gpi,
                        'grouplink' => $this->get_group_link($value->moodlegroupid),
                        'group_members' => $this->get_group_members($key));
            }
        } else {
            $assigns['grouping_no_data'] = get_string('no_data_to_display', 'groupformation');
        }

        return $assigns;
    }

    /**
     * sets the buttons of grouping settings
     *
     * @return array
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function load_edit_header() {
        global $PAGE;

        $assigns = array();

        $settingsgroupview = new mod_groupformation_template_builder ();
        $settingsgroupview->set_template('grouping_edit_header');
        $url = new moodle_url ('/mod/groupformation/grouping_view.php', array(
                'id' => $this->cmid));
        $assigns['buttons'] = array(
                'button1' => array(
                        'id' => 'submit_groups', 'type' => 'submit', 'name' => 'save_edit', 'value' => '1', 'state' => '',
                        'text' => get_string('submit')),
                'button2' => array(
                        'id' => 'cancel_groups', 'type' => 'cancel', 'name' => 'cancel_edit', 'value' => $url->out(), 'state' => '',
                        'text' => get_string('cancel'))
        );
        $context = $PAGE->context;
        $count = count(get_enrolled_users($context, 'mod/groupformation:onlystudent'));

        $assigns['student_count'] = $count;
        $assigns['cmid'] = $this->cmid;
        $assigns['onlyactivestudents'] = $this->store->get_grouping_setting();

        return $assigns;
    }

    /**
     * Assign groups-data to template
     *
     * @return array
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function load_edit_groups() {
        $assigns = array();
        $state = $this->statemachine->get_state();
        if ($state == "gf_done" || $state == "ga_done") {

            $groupsstring = "";
            $groupsarray = array();
            $generatedgroups = array();
            foreach ($this->groups as $key => $value) {

                $gpi = (is_null($value->performance_index)) ? '-' : $value->performance_index;

                $groupmembers = $this->get_group_members($key);
                $groupsarray[$key] = array_keys($groupmembers);

                $gids = implode(',', array_keys($groupmembers));
                $groupsstring .= $gids . "\n";

                $generatedgroups[$key] = array(
                    'id' => 'group_id_' . $key,
                    'groupname' => $value->groupname,
                    'groupquallity' => $gpi,
                    'grouplink' => $this->get_group_link($value->moodlegroupid),
                    'group_members' => $groupmembers);
            }
            $assigns['generated_groups'] = $generatedgroups;

            $v = array();
            foreach ($groupsarray as $array) {
                $v = array_merge($v, $array);
            }

            $groupsstring = json_encode($groupsarray);

            $assigns['groups_string'] = $groupsstring;

        } else {
            $assigns['grouping_no_data'] = get_string('no_data_to_display', 'groupformation');
        }

        return $assigns;
    }

    /**
     * Gets the name and moodle link of group members
     *
     * @param int $groupid
     * @return array
     */
    private function get_group_members($groupid) {
        global $CFG, $COURSE;

        $userids = array_keys($this->groups[$groupid]->users);

        $usermanager = $this->usermanager;

        $groupmembers = array();
        foreach ($userids as $user) {

            $values = $usermanager->get_user_values($user);
            

            $url = $CFG->wwwroot . '/user/view.php?id=' . $user . '&course=' . $COURSE->id;

            $username = $user;

            $userrecord = $this->userrecords[$username];

            if (!is_null($userrecord)) {
                $username = fullname($userrecord);
            }

            if (!(strlen($username) > 2)) {
                $username = $user;
            }
            $userlink = $url;

            $groupmembers [$user] = [
                'name' => $username, 'link' => $userlink, 'id' => $user, 'value' => array_values($values)[4]->value];
        }

        return $groupmembers;
    }

    /**
     * Get the moodle-link to group and set state of the link(enabled || disabled)
     *
     * @param int $groupid
     * @return array
     * @throws moodle_exception
     */
    private function get_group_link($groupid) {
        $link = array();
        if ($this->statemachine->get_state() == "ga_done") {
            $url = new moodle_url ('/group/members.php', array(
                'group' => $groupid));
            $link [] = $url;
            $link [] = '';
        } else {

            $link [] = '';
            $link [] = 'disabled';
        }

        return $link;
    }
}