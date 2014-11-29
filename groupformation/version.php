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
 * @package   mod_groupformation
 * @copyright 2014, Nora Wester
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();
 
$plugin->version   = 2014051200; //value out of "feedback"
$plugin->requires  = 2014050800; //value out of "feedback"
$plugin->cron      = 0;
$plugin->component = 'mod_groupformation';
// $plugin->maturity  = MATURITY_STABLE;
// $plugin->release   = 'TODO';
 
// $plugin->dependencies = array(
//     'mod_forum' => ANY_VERSION,
//     'mod_data'  => TODO
// );