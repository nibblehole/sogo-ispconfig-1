<?php

/*
 * Copyright (C) 2015 Christian M. Jensen
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
 * @author Christian M. Jensen <christian@cmjscripter.net>
 * @copyright 2014-2015 Christian M. Jensen
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3
 */

$tform_def_file = "form/sogo_domains.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->auth->check_module_permissions('mail');

$app->uses('tpl,tform,functions,sogo_helper');
$app->load('tform_actions');

class tform_action extends tform_actions {

    static private $edit_permissions = array();
    static private $load_domain_id = null;
    static private $load_domain_name = null;
    static private $domain_config_exists = null;
    static private $domain_config_index = null;

    /** @global app $app */
    public function onLoad() {
        global $app;
//        $this->_load_domain_id();
//        self::$domain_config_exists = $app->sogo_helper->domainSOGoConfigExists(self::$load_domain_id);
//        self::$domain_config_index = (int) $app->sogo_helper->getDomainConfigIndex(self::$load_domain_id);
        
        
        $tmp_sogo_domain = $app->db->queryOneRecord('SELECT `sogo_id`,`domain_id`,`domain_name`,`server_id`,`server_name` FROM `sogo_domains` WHERE `sogo_id`=' . intval($_REQUEST["id"]));
        self::$load_domain_id = $tmp_sogo_domain['domain_id'];
        self::$load_domain_name = $tmp_sogo_domain['domain_name'];
        //self::$domain_config_exists=true;
        
        // @todo ��maybe allow dedicated SOGo server like ISPConfig do with web and dns, will make the auto create config more doable??

//        if (!isset($_SESSION['s']['module']['sogo_tmp']['domain_id']) ||
//                (isset($_SESSION['s']['module']['sogo_tmp']['domain_id']) && (
//                $_SESSION['s']['module']["sogo_conifg_domain_id"] != $_SESSION['s']['module']['sogo_tmp']['domain_id'] ||
//                $_SESSION['s']['module']['sogo_tmp']['domain_id'] != intval(self::$load_domain_id)))) {
//
//            $result = $app->db->queryOneRecord('SELECT `domain_id`,`server_id`,`domain` FROM `mail_domain` WHERE `domain_id`=' . intval(self::$load_domain_id));
//            // $this->__domain_id
//            $_SESSION['s']['module']['sogo_tmp']['domain_id'] = $result['domain_id'];
//            // $this->__domain_name
//            $_SESSION['s']['module']['sogo_tmp']['domain'] = $result['domain'];
//            // $this->__server_id
//            $_SESSION['s']['module']['sogo_tmp']['server_id'] = $result['server_id'];
//            $result2 = $app->db->queryOneRecord('SELECT `server_name` FROM `server` WHERE `server_id`=' . intval($result['server_id']));
//            // $this->__server_name
//            $_SESSION['s']['module']['sogo_tmp']['server_name'] = $result2['server_name'];
//        }
//        
//        //* if no config for domain exists set id = 0 to create a new
//        if (self::$load_domain_id != 0 && !self::$domain_config_exists) {
//            if (!isset($_SESSION['s']['module']['sogo_tmp']['domain_id']) && !isset($_SESSION['s']['module']['sogo_tmp']['server_id'])) {
//                //* domain do not exists.!
//                die("HEADER_REDIRECT:mail/sogo_mail_domain_list.php?msg=DOMAINNOTFOUND");
//            } else {
//                //* domain exists but no config exists yet
//                $_REQUEST["id"] = 0;
//            }
//        } else if (self::$load_domain_id != 0 && self::$domain_config_index != 0 && self::$domain_config_exists) {
//            //* server config found, redirect to get correct vars page loaded
//            if (!isset($_REQUEST["id"])) {
//                die("HEADER_REDIRECT:mail/sogo_mail_domains_edit.php?id=" . self::$domain_config_index . '&domain_id=' . self::$load_domain_id);
//            }
//        } else {
//            //* nothing is valid
//            die("HEADER_REDIRECT:mail/sogo_mail_domain_list.php?msg=INVALIDDATA");
//        }
//        $_SESSION['s']['module']["sogo_conifg_domain_id"] = self::$load_domain_id;
//        if (!$app->sogo_helper->configExists($_SESSION['s']['module']['sogo_tmp']['server_id']) && !$app->sogo_helper->configExistsByDomain(self::$load_domain_id)) {
//            global $tform_def_file;
//            $app->tform->loadFormDef($tform_def_file);
//            if (!$app->auth->is_admin()) {
//                //* none admin, show general error message, log actual error as warning 
//                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
//                $app->log("SOGo server configuration is missing on server: #" . $_SESSION['s']['module']['sogo_tmp']['server_id']
//                        . ', User: #' . $app->auth->get_user_id()
//                        . " Tried to load domain: #" . self::$load_domain_id, LOGLEVEL_WARN);
//            } else {
//                //* is admin show actual error, NO sogo configuration
//                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND'];
//            }
//            $app->error($msg);
//        } else {
            $_uid = $app->auth->get_user_id();
            $_cid = $app->sogo_helper->get_client_id();
            if ($app->auth->has_clients($_uid))
                self::$edit_permissions = $app->sogo_helper->getResellerConfigPermissions($_cid);
            else
                self::$edit_permissions = $app->sogo_helper->getClientConfigPermissions($_cid);

            parent::onLoad();
//        }
    }

    /** @global app $app */
    public function onShow() {
        global $app;
//        $this->_load_domain_id();
//
//        if (!isset($_SESSION['s']['module']['sogo_tmp']['domain'])) {
//            $result = $app->db->queryOneRecord('SELECT `domain_id`,`server_id`,`domain` FROM `mail_domain` WHERE `domain_id`=' . self::$load_domain_id);
//            //* replace var "{DOMAINNAME}" in query string
//            if (isset($result['domain'])) {
//                $_SESSION['s']['module']['sogo_tmp']['domain'] = $result['domain'];
//                $_SESSION['s']['module']['sogo_tmp']['domain_id'] = $result['domain_id'];
//                $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace('{DOMAINNAME}', $result['domain'], $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']);
                $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace('{DOMAINNAME}', self::$load_domain_name, $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']);
//            }
//        } else {
//            $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring'] = str_replace('{DOMAINNAME}', $_SESSION['s']['module']['sogo_tmp']['domain'], $app->tform->formDef["tabs"]['domain']['fields']['SOGoSuperUsernames']['datasource']['querystring']);
//        }

        //* i like this to be translated WHY IS THIS NOT DONE IN CORE FILES..!..! :-(
        foreach ($app->tform->formDef["tabs"]['domain']['fields'] as $key => & $value) {
            if (isset($value['value']) && is_array($value['value'])) {
                foreach ($value['value'] as $innerkey => & $innervalue) {
                    if (isset($app->tform->wordbook[$innerkey])) {
                        //* using the key of the field to translate
                        $innervalue = $app->tform->wordbook[$innerkey];
                    } else if (isset($app->tform->wordbook[$innervalue])) {
                        //* using the value of the field to translate
                        $innervalue = $app->tform->wordbook[$innervalue];
                    }
                }
            }
        }

        parent::onShow();
    }
    
    /** @global app $app */
    public function onShowEnd() {
        global $app;
        if (!$app->auth->is_admin()) {
            if (isset(self::$edit_permissions) & count(self::$edit_permissions) > 0)
                $app->tpl->setVar(self::$edit_permissions);
            //* imap stuff
            $app->tpl->setVar('show_imap_section', $this->_show_imap_section());
            //* sieve stuff
            $app->tpl->setVar('show_sieve_section', $this->_show_sieve_section());
            //* smtp stuff
            $app->tpl->setVar('show_smtp_section', $this->_show_smtp_section());
        }
        parent::onShowEnd();
    }

    public function onInsert() /* onBeforeInsert() */ {
        global $app;
        //* if not admin.
        if (!$app->auth->is_admin()) {
//            if (!isset($_SESSION['s']['module']['sogo_tmp']['domain_id']) ||
//                    !isset($_SESSION['s']['module']['sogo_tmp']['domain']) ||
//                    !isset($_SESSION['s']['module']['sogo_tmp']['server_id']) ||
//                    !isset($_SESSION['s']['module']['sogo_tmp']['server_name'])) {
//                //* something whent wrong, session is not isset
//                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
//                $app->log("SOGo:Mail:onUpdate: Session values missing :: sogo_tmp = " . print_r($_SESSION['s']['module']['sogo_tmp'], true), LOGLEVEL_WARN);
//                $app->error($msg);
//                exit;
//            }
//            $this->dataRecord['domain_id'] = $_SESSION['s']['module']['sogo_tmp']['domain_id'];
//            $this->dataRecord['domain_name'] = $_SESSION['s']['module']['sogo_tmp']['domain'];
//            $this->dataRecord['server_id'] = $_SESSION['s']['module']['sogo_tmp']['server_id'];
//            $this->dataRecord['server_name'] = $_SESSION['s']['module']['sogo_tmp']['server_name'];

            //echo "<pre>BEFORE(" . $_SESSION['s']['module']['sogo_tmp']['server_id'] . "):Count:(".count($this->dataRecord).")::\n\n" . print_r($this->dataRecord, true) . "</pre>";
            if ($app->sogo_helper->configExists($this->dataRecord['server_id'])) {
                //* get server defaults
                $domain_config_fileds = $app->sogo_helper->getDomainConfigFields();
                if ($server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex($this->dataRecord['server_id'])))
                    foreach ($domain_config_fileds as $key => $value)
                        if (!isset($this->dataRecord[$key]) && isset($server_config[$key])) {
                            if ($key == "SOGoCustomXML")
                                continue;
                            $this->dataRecord[$key] = $server_config[$key];
                        }
            } else {
                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
                //* if we get here as user it's an error as this check is done in onLoad()
                $app->log("SOGo server configuration is missing on server: #" .$this->dataRecord['server_id']
                        . ', User: #' . $app->auth->get_user_id()
                        . " Tried to load domain: #" . $this->dataRecord['domain_id'], LOGLEVEL_ERROR);
                $app->error($msg);
                exit;
            }
            //die("<pre>AFTER(" . $_SESSION['s']['module']['sogo_tmp']['server_id'] . "):Count:(".count($this->dataRecord).")::\n\n" . print_r($this->dataRecord, true) . "</pre>");
        }
        parent::onInsert();
        //parent::onBeforeInsert();
    }

    public function onUpdate()/* onBeforeUpdate() */ {
        global $app;
        //* if not admin.
        if (!$app->auth->is_admin()) {
//            if (!isset($_SESSION['s']['module']['sogo_tmp']['domain_id']) ||
//                    !isset($_SESSION['s']['module']['sogo_tmp']['domain']) ||
//                    !isset($_SESSION['s']['module']['sogo_tmp']['server_id']) ||
//                    !isset($_SESSION['s']['module']['sogo_tmp']['server_name'])) {
//                //* something whent wrong, session is not isset
//                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
//                $app->log("SOGo:Mail:onUpdate: Session values missing :: sogo_tmp = " . print_r($_SESSION['s']['module']['sogo_tmp'], true), LOGLEVEL_WARN);
//                $app->error($msg);
//                exit;
//            }
//            $this->dataRecord['domain_id'] = $_SESSION['s']['module']['sogo_tmp']['domain_id'];
//            $this->dataRecord['domain_name'] = $_SESSION['s']['module']['sogo_tmp']['domain'];
//            $this->dataRecord['server_id'] = $_SESSION['s']['module']['sogo_tmp']['server_id'];
//            $this->dataRecord['server_name'] = $_SESSION['s']['module']['sogo_tmp']['server_name'];

            //echo "<pre>BEFORE(" . $_SESSION['s']['module']['sogo_tmp']['server_id'] . "):Count:(" . count($this->dataRecord) . ")::\n\n" . print_r($this->dataRecord, true) . "</pre>";
            if ($app->sogo_helper->configExists($this->dataRecord['server_id'])) {
                //* get server defaults
                $domain_config_fileds = $app->sogo_helper->getDomainConfigFields();
                if ($server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex($this->dataRecord['server_id'])))
                    foreach ($domain_config_fileds as $key => $value)
                        if (!isset($this->dataRecord[$key]) && isset($server_config[$key])) {
                            if ($key == "SOGoCustomXML")
                                continue;
                            $this->dataRecord[$key] = $server_config[$key];
                        }
            }else {
                $msg = $app->tform->wordbook['SOGO_SERVER_CONFIG_NOT_FOUND2'];
                //* if we get here as user it's an error as this check is done in onLoad()
                $app->log("SOGo server configuration is missing on server: #" . $this->dataRecord['server_id']
                        . ', User: #' . $app->auth->get_user_id()
                        . " Tried to load domain: #" . $this->dataRecord['domain'], LOGLEVEL_ERROR);
                $app->error($msg);
                exit;
            }
            //die("<pre>AFTER(" . $_SESSION['s']['module']['sogo_tmp']['server_id'] . "):Count:(" . count($this->dataRecord) . ")::\n\n" . print_r($this->dataRecord, true) . "</pre>");
        }
        parent::onUpdate();
        //parent::onBeforeUpdate();
    }

    /** @global app $app */
//    public function onShowNew() {
//        global $app;
//        if ($app->sogo_helper->configExists($_SESSION['s']['module']['sogo_tmp']['server_id'])) {
//            $server_config = $app->db->queryOneRecord("SELECT * FROM `sogo_config` WHERE `sogo_id`=" . $app->sogo_helper->getConfigIndex($_SESSION['s']['module']['sogo_tmp']['server_id']));
//            //* on new copy all default values from server config if exists
//            foreach ($app->tform->formDef["tabs"] as $key => & $value) {
//                foreach ($value['fields'] as $key => & $value) {
//                    if ($key == "sogo_id" || $key == "sys_userid" || $key == "sys_groupid" || $key == "sys_perm_user" || $key == "sys_perm_group" || $key == "sys_perm_other" || $key == "server_id" || $key == "server_name" || $key == "domain_id" || $key == "domain_name" || $key == "SOGoCustomXML") {
//                        continue;
//                    } else {
//                        $value['default'] = (isset($server_config[$key]) ? $server_config[$key] : $value['default']);
//                    }
//                }
//            }
//        }
//        parent::onShowNew();
//    }

//    public function onAfterInsert() {
//        $this->__fixDomainOwner();
//        parent::onAfterInsert();
//    }

    public function onAfterUpdate() {
        $this->__fixDomainOwner();
        parent::onAfterUpdate();
    }

    /** @global app $app */
    private function __fixDomainOwner() {
        global $app;
        //$this->_load_domain_id();

        $result = $app->db->queryOneRecord('SELECT `sys_userid`,`sys_groupid`,`sys_perm_user`,`sys_perm_group`,`sys_perm_other` FROM `mail_domain` WHERE `domain_id`=' . intval(self::$load_domain_id));
        if (isset($result['sys_userid']) && isset($result['sys_groupid']) && isset($result['sys_perm_user']) && isset($result['sys_perm_group']) && isset($result['sys_perm_other'])) {
//            self::$domain_config_index = (int) $app->sogo_helper->getDomainConfigIndex(self::$load_domain_id);
            $app->db->query("UPDATE `sogo_domains` SET "
                    . "`sys_userid` = '" . intval($result['sys_userid']) . "', "
                    . "`sys_groupid` = '" . intval($result['sys_groupid']) . "', "
                    . "`sys_perm_user` = '{$result['sys_perm_user']}', "
                    . "`sys_perm_group` = '{$result['sys_perm_group']}', "
                    . "`sys_perm_other` = '{$result['sys_perm_other']}' "
                    . "WHERE `sogo_id` ='" . /*self::$domain_config_index*/ $this->id . "' AND `domain_id` ='" . self::$load_domain_id . "';");
        }
    }

//    private function _load_domain_id() {
//        if (self::$load_domain_id == NULL || self::$load_domain_id <= 0) {
//            self::$load_domain_id = (isset($_REQUEST["domain_id"]) ? intval($_REQUEST["domain_id"]) : NULL);
//            if (self::$load_domain_id == NULL || self::$load_domain_id <= 0) {
//                self::$load_domain_id = (isset($_SESSION['s']['module']["sogo_conifg_domain_id"]) ? intval($_SESSION['s']['module']["sogo_conifg_domain_id"]) : NULL);
//            }
//            if (self::$load_domain_id == NULL || self::$load_domain_id <= 0) {
//                self::$load_domain_id = (isset($_SESSION['s']['module']['sogo_tmp']['domain_id']) ? intval($_SESSION['s']['module']['sogo_tmp']['domain_id']) : 0);
//            }
//        }
//    }

    private function _show_smtp_section() {
        if (isset(self::$edit_permissions['permission_smtp_server']) && self::$edit_permissions['permission_smtp_server'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mailing_mechanism']) && self::$edit_permissions['permission_mailing_mechanism'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mail_spool_path']) && self::$edit_permissions['permission_mail_spool_path'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mail_custom_from_enabled']) && self::$edit_permissions['permission_mail_custom_from_enabled'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_smtp_authentication_type']) && self::$edit_permissions['permission_smtp_authentication_type'] == 'y')
            return 1;
        return 0;
    }

    private function _show_sieve_section() {
        if (isset(self::$edit_permissions['permission_sieve_filter_forward']) && self::$edit_permissions['permission_sieve_filter_forward'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_filter_vacation']) && self::$edit_permissions['permission_sieve_filter_vacation'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_server']) && self::$edit_permissions['permission_sieve_server'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_filter_enable_disable']) && self::$edit_permissions['permission_sieve_filter_enable_disable'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_sieve_folder_encoding']) && self::$edit_permissions['permission_sieve_folder_encoding'] == 'y')
            return 1;
        return 0;
    }

    private function _show_imap_section() {
        if (isset(self::$edit_permissions['permission_imap_server']) && self::$edit_permissions['permission_imap_server'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_conforms_imapext']) && self::$edit_permissions['permission_imap_conforms_imapext'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_acl_style']) && self::$edit_permissions['permission_imap_acl_style'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_folder_drafts']) && self::$edit_permissions['permission_imap_folder_drafts'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_folder_trash']) && self::$edit_permissions['permission_imap_folder_trash'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_imap_folder_sent']) && self::$edit_permissions['permission_imap_folder_sent'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_subscription_folder_format']) && self::$edit_permissions['permission_subscription_folder_format'] == 'y')
            return 1;
        if (isset(self::$edit_permissions['permission_mail_auxiliary_accounts']) && self::$edit_permissions['permission_mail_auxiliary_accounts'] == 'y')
            return 1;
        return 0;
    }

}

$app->tform_action = new tform_action();
$app->tform_action->onLoad();
