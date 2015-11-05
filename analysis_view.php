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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * Prints a particular instance of groupformation
 *
 * @package mod_groupformation
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
require_once (dirname ( __FILE__ ) . '/lib.php');
require_once (dirname ( __FILE__ ) . '/locallib.php');

require_once (dirname ( __FILE__ ) . '/classes/util/test_user_generator.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once (dirname ( __FILE__ ) . '/classes/moodle_interface/storage_manager.php');

// Read URL params
$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
                                             // $g = optional_param ( 'g', 0, PARAM_INT ); // groupformation instance ID
$do_show = optional_param ( 'do_show', 'analysis', PARAM_TEXT );

$create_users = optional_param ( 'create_users', 0, PARAM_INT );
$create_answers = optional_param ( 'create_answers', false, PARAM_BOOL );
$random_answers = optional_param ( 'random_answers', false, PARAM_BOOL );
$delete_users = optional_param ( 'delete_users', false, PARAM_BOOL );

// Import jQuery and js file
groupformation_add_jquery ( $PAGE, 'survey_functions.js' );

// Determine instances of course module, course, groupformation
groupformation_determine_instance ( $id, $cm, $course, $groupformation );

// Require user login if not already logged in
require_login ( $course, true, $cm );

// Get useful stuff
$context = $PAGE->context;
$userid = $USER->id;

if (! has_capability ( 'mod/groupformation:editsettings', $context )) {
	$returnurl = new moodle_url ( '/mod/groupformation/view.php', array (
			'id' => $id,
			'do_show' => 'view' 
	) );
	redirect ( $returnurl );
} else {
	$current_tab = $do_show;
}

// Log access to page
groupformation_info ( $USER->id, $groupformation->id, '<view_teacher_overview>' );

// Set PAGE config
$PAGE->set_url ( '/mod/groupformation/analysis_view.php', array (
		'id' => $cm->id,
		'do_show' => $do_show 
) );
$PAGE->set_title ( format_string ( $groupformation->name ) );
$PAGE->set_heading ( format_string ( $course->fullname ) );

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/controller/analysis_controller.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');

$controller = new mod_groupformation_analysis_controller ( $groupformation->id );

if ($_POST) {
	if (isset ( $_POST ['start_questionnaire'] )) {
		$controller->start_questionnaire ();
	} elseif (isset ( $_POST ['stop_questionnaire'] )) {
		$controller->stop_questionnaire ();
	}
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis' 
	) );
	redirect ( $returnurl );
}

// groupformation_trigger_event($cm, $course, $groupformation, $context);

echo $OUTPUT->header ();

// Print the tabs.
require ('tabs.php');

/* ---------- Automated test user generation ------------ */

$cqt = new mod_groupformation_test_user_generator ();

if ($delete_users) {
	$cqt->delete_test_users ( $groupformation->id );
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis' 
	) );
	redirect ( $returnurl );
}
if ($create_users > 0) {
	$cqt->create_test_users ( $create_users, $groupformation->id, $create_answers, $random_answers );
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis' 
	) );
	redirect ( $returnurl );
}

/* ---------- / Automated test user generation ---------- */

/* ---------- Job Manager Usage ------------------------- */

// $jm = new mod_groupformation_job_manager ();
// $job = null;

// $job = $jm::get_job ( $groupformation->id );
// //$jm->reset_job($job);
// // var_dump($jm::get_next_job());
// if (! is_null ( $job )) {
// $result = $jm::do_groupal($job);
// var_dump ( $result );
// // $saved = $jm::save_result($job,$result);

// }

/* ---------- / Job Manager Usage ----------------------- */

/* ---------- Test Participants for Eduard -------------- */

require_once ($CFG->dirroot . '/lib/groupal/classes/Criteria/SpecificCriterion.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/Participant.php');

$values = array (
		2,
		4,
		1,
		5,
		3,
		6 
);
$participants = array ();

for($i = 1; $i <= 20; $i = $i + 1) {
	$criterion = new SpecificCriterion ( 'topic', $values, 1, 6, true, 1 );
	$participant = new Participant ( array (
			$criterion 
	), $i );
	$participants [] = $participant;
	shuffle ( $values );
}

// var_dump($participants);

/*
 * Hier kannst du nun sehen wie die aussehen und außerdem kannst du dann deine 
 * Instanz von GroupFormationTopicAlgorithm aufrufen. Ähnlich wie in job_manager::do_groupal.
 */

// $groupsize = 4

// $algorithm = new GroupFormationTopicAlgorithm($participants, $groupsize);
// $result = $algorithm->doOneFormation();

/* ---------- / Test Participants for Eduard ------------ */

echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';

echo '<input type="hidden" name="id" value="' . $id . '"/>';

echo $controller->display ();

echo '</form>';

echo $OUTPUT->footer ();
