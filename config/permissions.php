<?php

/**
 * Role and permission configuration.
 *
 * Permissions use hierarchical dotted keys: granting "1" implies "1.1", "1.1.2", etc.
 * Granting "1.*" is equivalent to "1". The wildcard "*" grants every permission (admin).
 *
 * Roles: 0 = admin, 1 = supervisor, 2 = cleaner.
 */
return [

    'roles' => [

        0 => [
            '*',
        ],

        1 => [
            '2.1', '2.2', '2.3',
            '3.1', '3.2', '3.3', '3.6',
            '4.1', '4.2', '4.3', '4.5', '4.6',
            '5.1', '5.2',
            '6.1', '6.2',
            '7.1', '7.2',
            '8.1', '8.2',
        ],

        2 => [
            '1.3',
            '3.1',
            '4.1', '4.4',
            '6.1',
        ],

    ],

    'permissions' => [

        '1'   => 'Settings',
        '1.1' => 'Settings > Users',
        '1.2' => 'Settings > Roles & Permissions',
        '1.3' => 'Settings > Own Profile',
        '1.4' => 'Settings > Organization',

        '2'   => 'Personnel',
        '2.1' => 'Personnel > View',
        '2.2' => 'Personnel > Create',
        '2.3' => 'Personnel > Edit',
        '2.4' => 'Personnel > Delete',

        '3'   => 'Properties',
        '3.1' => 'Properties > View',
        '3.2' => 'Properties > Create',
        '3.3' => 'Properties > Edit',
        '3.4' => 'Properties > Categories',
        '3.5' => 'Properties > Tags',
        '3.6' => 'Properties > Assignments',

        '4'   => 'Tasks',
        '4.1' => 'Tasks > View',
        '4.2' => 'Tasks > Create',
        '4.3' => 'Tasks > Assign',
        '4.4' => 'Tasks > Update Status',
        '4.5' => 'Tasks > Approve',
        '4.6' => 'Tasks > Cancel / Reopen',
        '4.7' => 'Tasks > Task Types',
        '4.8' => 'Tasks > Checklists',

        '5'   => 'Shifts',
        '5.1' => 'Shifts > View',
        '5.2' => 'Shifts > Manage',

        '6'   => 'Attendance',
        '6.1' => 'Attendance > View',
        '6.2' => 'Attendance > Correct',

        '7'   => 'Reports',
        '7.1' => 'Reports > View',
        '7.2' => 'Reports > Export',

        '8'   => 'Incidents',
        '8.1' => 'Incidents > View',
        '8.2' => 'Incidents > Manage',

        '9'   => 'Audit',
        '9.1' => 'Audit > View',

    ],

];
