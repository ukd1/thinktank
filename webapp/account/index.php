<?php 
// set up
chdir("..");


require_once ("common/init.php");

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
    header("Location: ../index.php");
}

$od = new OwnerDAO($db);

if (isset($_POST['changepass']) && $_POST['changepass'] == 'Change password') {
    $originalpass = $od->getPass($_SESSION['user']);
    $origpass = $originalpass['pwd'];
    if (!$session->pwdCheck($_POST['oldpass'], $origpass)) {
        $errormsg = "Old password does not match or empty.";
    } elseif ($_POST['pass1'] != $_POST['pass2']) {
        $errormsg = "New passwords did not match. Your password has not been changed.";
    } elseif (strlen($_POST['pass1']) < 5) {
        $errormsg = "New password must be at least 5 characters. Your password has not been changed.";
    } else {
        $cryptpass = $session->pwdcrypt($_POST['pass1']);
        $od->updatePassword($_SESSION['user'], $cryptpass);
        $successmsg = "Your password has been updated.";
    }
} 

$s = new SmartyThinkTank();
$s->caching = 0;

$cfg = new Config();
$id = new InstanceDAO($db);
$od = new OwnerDAO($db);
$oid = new OwnerInstanceDAO($db);

$owner = $od->getByEmail($_SESSION['user']);

$s->assign('cfg', $cfg);
$s->assign('owner', $owner);

// grab instance from session variable
$i = unserialize($_SESSION['instance']); 
$s->assign('instance', $i);

if ($owner->is_admin) {
    $owners = $od->getAllOwners();
    foreach ($owners as $o) {
        $instances = $id->getByOwner($o, true);
        $o->setInstances($instances);
    }
    $s->assign('owners', $owners);
}

$s->assign('instances', $id->getByOwner($owner));

/* Begin plugin-specific configuration handling */
$cmi = $webapp->getConfigMenu();
$s->assign('config_menu', $cmi);
if (!isset($_GET['m']) && isset($_GET['p'])) {
    $active_plugin = $_GET['p'];
} else {
    if (isset($_GET['m']) && $_GET['m'] == 'manage') {
        $pld = new PluginDAO($db);
        $installed_plugins = $pld->getInstalledPlugins($THINKTANK_CFG["source_root_path"]);
        $s->assign('installed_plugins', $installed_plugins);
    } else {
        //default to the first plugin on the stack
        $active_plugin = $cmi[0][0];
    }
}
if (isset($active_plugin)) {
    $webapp->configuration($active_plugin);
    array_push($s->template_dir, 'plugins/'.$active_plugin);
    $s->assign('body', $THINKTANK_CFG['source_root_path'].'webapp/plugins/'.$active_plugin.'/templates/'.$active_plugin.'.account.index.tpl');
}
/* End plugin-specific configuration handling */


# clean up
$db->closeConnection($conn);

if (isset($errormsg)) {
    $s->assign('errormsg', $errormsg);
}
if (isset($successmsg)) {
    $s->assign('successmsg', $successmsg);
}

$s->display('account.index.tpl');
?>
