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
 * Data Generator for the tool_cleanupusers plugin.
 *
 * @package    tool_cleanupusers
 * @category   test
 * @copyright  2016/17 N Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Data Generator class for the tool_cleanupusers plugin.
 *
 * @package    tool_cleanupusers
 * @category   test
 * @copyright  2016/17 N Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cleanupusers_generator extends testing_data_generator {
    /**
     * Creates User to test the tool_cleanupusers plugin.
     */
    public function test_create_preparation () {
        global $DB;
        $generator = advanced_testcase::getDataGenerator();
        $data = array();

        $mytimestamp = time();

        // Creates several user:
        // user is not suspended did sign in.
        // listuser is equal to neutraluser
        // suspendeduser is suspended never signed in.
        // notsuspendeduser signed in one year ago
        // suspendeduser2 is suspended
        // deleteuser is suspended signed in one year ago
        // archivedbyplugin has entry in tool_cleanupusers and tool_cleanupusers_archive was suspended one year ago.
        // reactivatebyplugin wassuspended by plugin (has entry in both tables) however lastaccess is only few hours ago.

        $user = $generator->create_user(array('username' => 'user', 'lastaccess' => $mytimestamp, 'suspended' => '0'));
        $data['user'] = $user;

        $listuser = $generator->create_user(array('username' => 'n_merr03', 'lastaccess' => $mytimestamp, 'suspended' => '0'));
        $data['listuser'] = $listuser;

        $tendaysago = $mytimestamp - 864000;
        $suspendeduser = $generator->create_user(array('username' => 'suspendeduser', 'suspended' => '1'));
        $DB->insert_record_raw('tool_cleanupusers', array('id' => $suspendeduser->id, 'archived' => 1,
            'timestamp' => $tendaysago), true, false, true);
        $DB->insert_record_raw('tool_cleanupusers_archive', array('id' => $suspendeduser->id,
            'username' => $suspendeduser->username, 'suspended' => $suspendeduser->suspended,
            'lastaccess' => $tendaysago), true, false, true);
        $data['suspendeduser'] = $suspendeduser;

        $timestamponeyearago = $mytimestamp - 31622600;
        $notsuspendeduser = $generator->create_user(array('username' => 'notsuspendeduser', 'suspended' => '0',
            'lastaccess' => $timestamponeyearago));
        $data['notsuspendeduser'] = $notsuspendeduser;

        $suspendeduser2 = $generator->create_user(array('username' => 'suspendeduser2', 'suspended' => '1'));
        $data['suspendeduser2'] = $suspendeduser2;

        $deleteduser = $generator->create_user(array('username' => 'deleteduser', 'suspended' => '1',
            'lastaccess' => $timestamponeyearago));
        $data['deleteduser'] = $deleteduser;

        // User that was archived by the plugin and will be deleted in cron-job.
        $suspendeduser3 = $generator->create_user(array('username' => 'anonym', 'suspended' => '1', 'firstname' => 'Anonym'));
        $DB->insert_record_raw('tool_cleanupusers', array('id' => $suspendeduser3->id, 'archived' => true,
            'timestamp' => $timestamponeyearago), true, false, true);
        $DB->insert_record_raw('tool_cleanupusers_archive', array('id' => $suspendeduser3->id,
            'username' => 'archivedbyplugin', 'suspended' => 1, 'lastaccess' => $timestamponeyearago),
            true, false, true);
        $data['archivedbyplugin'] = $suspendeduser3;

        // User that was archived by the plugin and will be deleted in cron-job.
        $suspendeduser3 = $generator->create_user(array('username' => 'anonym3', 'suspended' => '1',
            'firstname' => 'Anonym', 'idnumber' => 3));
        $DB->insert_record_raw('tool_cleanupusers', array('id' => $suspendeduser3->id, 'archived' => true,
            'timestamp' => $timestamponeyearago), true, false, true);
        $DB->insert_record_raw('tool_cleanupusers_archive', array('id' => $suspendeduser3->id,
            'username' => 'archivedbyplugin2', 'suspended' => 0, 'lastaccess' => $timestamponeyearago),
            true, false, true);
        $data['archivedbyplugin2'] = $suspendeduser3;

        $timestampshortago = $mytimestamp - 3456;
        // User that was archived by the plugin and will be reactivated in cron-job.
        $reactivatebyplugin = $generator->create_user(array('username' => 'anonym2', 'suspended' => '1',
            'firstname' => 'Anonym', 'idnumber' => 2));
        $DB->insert_record_raw('tool_cleanupusers', array('id' => $reactivatebyplugin->id, 'archived' => true,
            'timestamp' => $timestampshortago), true, false, true);
        $DB->insert_record_raw('tool_cleanupusers_archive', array('id' => $reactivatebyplugin->id,
            'username' => 'reactivatebyplugin',
            'suspended' => 0, 'lastaccess' => $mytimestamp), true, false, true);
        $data['reactivatebyplugin'] = $reactivatebyplugin;

        // User that was archived by the plugin and will be reactivated in cron-job although has as firstname Anonym.
        $reactivatebyplugin2 = $generator->create_user(['username' => 'moreanonym', 'suspended' => '1',
            'firstname' => 'Anonym']);
        $DB->insert_record_raw('tool_cleanupusers', array('id' => $reactivatebyplugin2->id, 'archived' => true,
            'timestamp' => $timestampshortago), true, false, true);
        $DB->insert_record_raw('tool_cleanupusers_archive', array('id' => $reactivatebyplugin2->id,
            'username' => 'reactivatebypluginexception', 'firstname' => 'Anonym',
            'suspended' => 1, 'lastaccess' => $mytimestamp), true, false, true);
        $data['reactivatebyplugin2'] = $reactivatebyplugin2;

        return $data; // Return the user, course and group objects.
    }
}