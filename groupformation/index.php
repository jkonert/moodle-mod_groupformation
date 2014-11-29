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
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once(dirname(__FILE__).'/lib.php');

	$id = required_param('id', PARAM_INT);           // Course ID

	// in the template it isn't in a if statement
	// Ensure that the course specified is valid
	if (!$course = $DB->get_record('course', array('id'=> $id), '*', MUST_EXIST)) {
		print_error('Course ID is incorrect');
	}

	require_course_login($course);
	
	add_to_log($course->id, 'groupformation', 'view all', 'index.php?id='.$course->id, '');
	
	$coursecontext = context_course::instance($course->id);
	
	$PAGE->set_url('/mod/groupformation/index.php', array('id' => $id));
	$PAGE->set_title(format_string($course->fullname));
	$PAGE->set_heading(format_string($course->fullname));
	$PAGE->set_context($coursecontext);
	
	echo $OUTPUT->header();
	
	if (! $groupformations = get_all_instances_in_course('groupformation', $course)) {
		notice(get_string('nonewmodules', 'groupformation'), new moodle_url('/course/view.php', array('id' => $course->id)));
	}
	
	$table = new html_table();
	if ($course->format == 'weeks') {
		$table->head = array(get_string('week'), get_string('name'));
		$table->align = array('center', 'left');
	} else if ($course->format == 'topics') {
		$table->head = array(get_string('topic'), get_string('name'));
		$table->align = array('center', 'left', 'left', 'left');
	} else {
		$table->head = array(get_string('name'));
		$table->align = array('left', 'left', 'left');
	}
	
	foreach ($groupformations as $groupformation) {
		if (!$groupformation->visible) {
			$link = html_writer::link(
					new moodle_url('/mod/groupformation.php', array('id' => $groupformation->coursemodule)),
					format_string($groupformation->name, true),
					array('class' => 'dimmed'));
		} else {
			$link = html_writer::link(
					new moodle_url('/mod/groupformation.php', array('id' => $groupformation->coursemodule)),
					format_string($groupformation->name, true));
		}
		
		if ($course->format == 'weeks' or $course->format == 'topics') {
			$table->data[] = array($groupformation->section, $link);
		} else {
			$table->data[] = array($link);
		}
	}
	
	echo $OUTPUT->heading(get_string('modulnameplurals', 'groupformation'), 2);
	echo html_writer::table($table);
	echo $OUTPUT->footer();