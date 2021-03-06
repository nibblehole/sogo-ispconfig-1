<?php

/*
 * Copyright (C) 2014  Christian M. Jensen
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author Christian M. Jensen <christian@cmjscripter.net>
 *  @copyright 2014 Christian M. Jensen
 *  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$tform_def_file = "form/sogo_config.tform.php";
$list_def_file = "list/sogo_server.list.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('admin');
if (method_exists($app->auth, 'check_security_permissions')) {
    $app->auth->check_security_permissions('admin_allow_server_services');
} else {
    if (!$app->auth->is_admin())
        die('only allowed for administrators.');
}

$app->load("tform_actions");

class tform_action extends tform_actions {

    /** @global app $app */
    public function onAfterDelete() {
        global $app;
        //* remove module config
        $tmp = $app->db->queryOneRecord("SELECT * FROM `sogo_module` WHERE `server_id`=" . intval($this->dataRecord['server_id']));
        if (isset($tmp['smid']) && method_exists($app->db, 'datalogDelete'))
            $app->db->datalogDelete('sogo_module', 'smid', $tmp['smid']);
        parent::onAfterDelete();
    }

}
$app->tform_action= new tform_action();
$app->tform_action->onDelete();
