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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>
<div class="gf_settings_pad">
    <div class="gf_pad_header"><?php echo get_string('activity', 'groupformation');?> - <?php echo $this->_['analysis_name']; ?>
    </div>
    <div class="gf_pad_content">

            <?php echo $this->_['analysis_status_template']; ?>
    </div>

    <div class="gf_pad_header_small">
        <?php echo get_string('statistic', 'groupformation');?>
    </div>
    <div class="gf_pad_content">
        <?php echo $this->_['analysis_statistics_template']; ?>
    </div>
</div>