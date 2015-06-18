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
 * This file keeps track of upgrades to the groupformation module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do. The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
/**
 * Execute groupformation upgrade from the given old version
 *
 * @param int $oldversion        	
 * @return bool
 */
function xmldb_groupformation_upgrade($oldversion) {
	global $DB;
	$dbman = $DB->get_manager (); // Loads ddl manager and xmldb classes.
	                             
	// /*
	                             // * And upgrade begins here. For each one, you'll need one
	                             // * block of code similar to the next one. Please, delete
	                             // * this comment lines once this file start handling proper
	                             // * upgrade code.
	                             // *
	                             // *
	                             // * if ($oldversion < YYYYMMDD00) { //New version in version.php
	                             // * }
	                             // *
	                             
	// * Lines below (this included) MUST BE DELETED once you get the first version
	                             // * of your module ready to be installed. They are here only
	                             // * for demonstrative purposes and to show how the groupformation
	                             // * iself has been upgraded.
	                             // *
	                             // * For each upgrade block, the file groupformation/version.php
	                             // * needs to be updated . Such change allows Moodle to know
	                             // * that this file has to be processed.
	                             // *
	                             // * To know more about how to write correct DB upgrade scripts it's
	                             // * highly recommended to read information available at:
	                             // * http://docs.moodle.org/en/Development:XMLDB_Documentation
	                             // * and to play with the XMLDB Editor (in the admin menu) and its
	                             // * PHP generation posibilities.
	                             // *
	                             // * First example, some fields were added to install.xml on 2007/04/01
	                             // */
	
	if ($oldversion < 2015041701) {
		// Define field course to be added to groupformation.
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'szenario', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'grade' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'knowledge', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'szenario' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'knowledgelines', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'knowledge' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'topics', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'knowledgelines' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'topiclines', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'topics' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'maxmembers', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'topiclines' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'maxgroups', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'maxmembers' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'evaluationmethod', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'maxgroups' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// $table = new xmldb_table('groupformation_q_settings');
		// $dbman->
		// Once we reach this point, we can store the new version and consider the module
		// ... upgraded to the version 2007040100 so the next time this block is skipped.
		upgrade_mod_savepoint ( true, 2015041701, 'groupformation' );
	}
	
	if ($oldversion < 2015041900) {
		// Define field course to be added to groupformation.
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'groupoption', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'topiclines' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		upgrade_mod_savepoint ( true, 2015041900, 'groupformation' );
	}
	
	if ($oldversion < 2015051300) {
		// Define field course to be added to groupformation.
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'maxpoints', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '100', 'evaluationmethod' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		upgrade_mod_savepoint ( true, 2015051300, 'groupformation' );
	}
	
	if ($oldversion < 2015052802) {
		// For XML: <FIELD NAME="groupname" TYPE="text" NOTNULL="true" DEFAULT="group" SEQUENCE="false" COMMENT="Group name prefix for generated group names"/>
		// Define field course to be added to groupformation.
		$table = new xmldb_table ( 'groupformation' );
		$field = new xmldb_field ( 'groupname', XMLDB_TYPE_TEXT, 'medium', null, null, null, 'group', 'maxgroups' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		upgrade_mod_savepoint ( true, 2015052802, 'groupformation' );
	}
	
	if ($oldversion < 2015060100) {
		// Define field course to be added to groupformation.
		$table = new xmldb_table ( 'groupformation_started' );
		$field = new xmldb_field ( 'timecompleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'completed' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Define field course to be added to groupformation.
		$table = new xmldb_table ( 'groupformation_started' );
		$field = new xmldb_field ( 'groupid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'timecompleted' );
		// Add field course.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		upgrade_mod_savepoint ( true, 2015060100, 'groupformation' );
	}
	
	if ($oldversion < 2015060500) {
		

        // Define table groupformation_logging to be created.
        $table = new xmldb_table('groupformation_logging');

        // Adding fields to table groupformation_logging.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        // Adding keys to table groupformation_logging.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for groupformation_logging.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Groupformation savepoint reached.
        upgrade_mod_savepoint ( true, 2015060500, 'groupformation' );
	}
	
	if ($oldversion < 2015060501) {
		// Define field timestamp to be added to groupformation_logging.
		$table = new xmldb_table('groupformation_logging');
		$field = new xmldb_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
		
		// Conditionally launch add field timestamp.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Define field userid to be added to groupformation_logging.
		$table = new xmldb_table('groupformation_logging');
		$field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'timestamp');
		
		// Conditionally launch add field userid.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Define field groupformationid to be added to groupformation_logging.
		$table = new xmldb_table('groupformation_logging');
		$field = new xmldb_field('groupformationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'userid');
		
		// Conditionally launch add field groupformationid.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Define field message to be added to groupformation_logging.
		$table = new xmldb_table('groupformation_logging');
		$field = new xmldb_field('message', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'groupformationid');
		
		// Conditionally launch add field message.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Groupformation savepoint reached.
		upgrade_mod_savepoint ( true, 2015060501, 'groupformation' );
	}
	
	if ($oldversion < 2015061700) {
	
	
		// Define table groupformation_logging to be created.
		$table = new xmldb_table('groupformation_jobs');
	
		// Adding fields to table groupformation_logging.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	
		// Adding keys to table groupformation_logging.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for groupformation_logging.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Groupformation savepoint reached.
		upgrade_mod_savepoint ( true, 2015061700, 'groupformation' );
	}
	
	if ($oldversion < 2015061801) {
		
		// Define field groupformationid to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('groupformationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field groupformationid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field waiting to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('waiting', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'groupformationid');
        
        // Conditionally launch add field waiting.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field started to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('started', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'waiting');
        
        // Conditionally launch add field started.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field aborted to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('aborted', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'started');
        
        // Conditionally launch add field aborted.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field done to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('done', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'aborted');
        
        // Conditionally launch add field done.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field timecreated to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'done');
        
        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field timestarted to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('timestarted', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timecreated');
        
        // Conditionally launch add field timestarted.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field timefinished to be added to groupformation_jobs.
        $table = new xmldb_table('groupformation_jobs');
        $field = new xmldb_field('timefinished', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timestarted');
        
        // Conditionally launch add field timefinished.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }      
	
		// Groupformation savepoint reached.
		upgrade_mod_savepoint ( true, 2015061801, 'groupformation' );
	}
	
	
	// // Second example, some hours later, the same day 2007/04/01
	// // ... two more fields and one index were added to install.xml (note the micro increment
	// // ... "01" in the last two digits of the version).
	// if ($oldversion < 2007040101) {
	
	// // Define field timecreated to be added to groupformation.
	// $table = new xmldb_table('groupformation');
	// $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
	// 'introformat');
	
	// // Add field timecreated.
	// if (!$dbman->field_exists($table, $field)) {
	// $dbman->add_field($table, $field);
	// }
	
	// // Define field timemodified to be added to groupformation.
	// $table = new xmldb_table('groupformation');
	// $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
	// 'timecreated');
	
	// // Add field timemodified.
	// if (!$dbman->field_exists($table, $field)) {
	// $dbman->add_field($table, $field);
	// }
	
	// // Define index course (not unique) to be added to groupformation.
	// $table = new xmldb_table('groupformation');
	// $index = new xmldb_index('courseindex', XMLDB_INDEX_NOTUNIQUE, array('course'));
	
	// // Add index to course field.
	// if (!$dbman->index_exists($table, $index)) {
	// $dbman->add_index($table, $index);
	// }
	
	// // Another save point reached.
	// upgrade_mod_savepoint(true, 2007040101, 'groupformation');
	// }
	
	// // Third example, the next day, 2007/04/02 (with the trailing 00),
	// // some actions were performed to install.php related with the module.
	// if ($oldversion < 2007040200) {
	// // Insert code here to perform some actions (same as in install.php).
	// upgrade_mod_savepoint(true, 2007040200, 'groupformation');
	// }
	
	// /*
	// * And that's all. Please, examine and understand the 3 example blocks above. Also
	// * it's interesting to look how other modules are using this script. Remember that
	// * the basic idea is to have "blocks" of code (each one being executed only once,
	// * when the module version (version.php) is updated.
	// *
	// * Lines above (this included) MUST BE DELETED once you get the first version of
	// * yout module working. Each time you need to modify something in the module (DB
	// * related, you'll raise the version and add one upgrade block here.
	// *
	// * Finally, return of upgrade result (true, all went good) to Moodle.
	// */
	
	return true;
}
