<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("member_register_start", "pwnedplugin");
$plugins->add_hook("usercp_do_profile_start", "pwnedplugin");
$plugins->add_hook("member_do_register_start", "pwnedplugin");

function pwnedplugin_info()
{
	return array(
		"name"			=> "HIBP Password Check",
		"description"	=> "Use the HaveIBeenPwned API to check whether user passwords have appeared in any known data breaches.",
		"website"		=> "https://github.com/z3rodaycve",
		"author"		=> "z3rodaycve",
		"authorsite"	=> "https://github.com/z3rodaycve",
		"version"		=> "1.0.0",
		"guid" 			=> "",
		"codename"		=> "pwnedplugin",
		"compatibility" => "*"
	);
}

function pwnedplugin_install()
{
	global $db;
	$collation = $db->build_create_table_collation();

    $db->write_query("
        CREATE TABLE `" . TABLE_PREFIX . "plugin_pwned` (
            `isActivated` TINYINT(1) NOT NULL DEFAULT 0 
        ) ENGINE=MyISAM {$collation};
    ");
}

function pwnedplugin_is_installed()
{
	global $db;

    if($db->table_exists("plugin_pwned"))
    {
        return true;
    }
    return false;
}

function pwnedplugin_uninstall()
{
	global $db;
	require_once MYBB_ROOT . "inc/adminfunctions_templates.php";

	if($db->table_exists("plugin_pwned"))
	{
        $db->write_query("DROP TABLE `" . TABLE_PREFIX . "plugin_pwned`");
	}

	$db->delete_query("settinggroups", "name = 'pwnedplugin_settings'");

	$settings = array(
		"pwnedplugin_warning",
		"pwnedplugin_showicon",
		"pwnedplugin_tooltipdescription",
		"pwnedplugin_tooltiplogo"
	);
	$settings = "'" . implode("','", $settings) . "'";
	$db->delete_query("settings", "name IN ({$settings})");

	rebuild_settings();

	$templates = array(
		"pwnedplugin"
	);
	$templates = "'" . implode("','", $templates) . "'";
	$db->delete_query("templates", "title IN ({$templates})");

	find_replace_templatesets("member_register_password", "#".preg_quote('{$pwnedplugin}')."#i", '<input type="password" class="textbox" name="password" id="password" style="width: 100%" />');
}

function pwnedplugin_activate()
{
	global $db;
	require_once MYBB_ROOT . "inc/adminfunctions_templates.php";

	if($db->table_exists("plugin_pwned"))
	{
        $settings_group = array(
			"name" => "pwnedplugin_settings",
			"title" => "HIBP Password Check",
			"description" => "",
			"disporder" => "1",
			"isdefault" => 0
		);
		$db->insert_query("settinggroups", $settings_group);
		$gid = $db->insert_id();

		$settings = array();
		$settings[] = array(
			"name" => "pwnedplugin_warning",
			"title" => "Custom user warn message:",
			"description" => "Define your own custom warning when a breached password is detected.",
			"optionscode" => "text",
			"value" => "Your password was found in a breach. Please choose a different password."
		);
		$settings[] = array(
			"name" => "pwnedplugin_showicon",
			"title" => "Do you want to show the HIBP tooltip in the password input?",
			"description" => "Choose whether to display the HIBP tooltip. <strong>Note:</strong> This setting does not disable the password breach check. To disable the check, you must deactivate the plugin.",
			"optionscode" => "select
				show=Show
				dontshow=Do not show",
			"value" => "show"
		);
		$settings[] = array(
			"name" => "pwnedplugin_tooltipdescription",
			"title" => "Do you want to customize the default HIBP tooltip description?",
			"description" => "You can edit the text shown in the tooltip right here.",
			"optionscode" => "text",
			"value" => "We use HaveIBeenPwned to check for a potentially breached password."
		);
		$settings[] = array(
			"name" => "pwnedplugin_tooltiplogo",
			"title" => "Do you want to change the default logo in the HIBP tooltip?",
			"description" => "Choose whether to display the default HIBP logo, or replace it with your custom logo.",
			"optionscode" => "text",
			"value" => "https://upload.wikimedia.org/wikipedia/commons/2/23/Have_I_Been_Pwned_logo.png"
		);

		$i = 1;
		foreach($settings as $setting)
		{
			$insert = array(
				"name" => $db->escape_string($setting['name']),
				"title" => $db->escape_string($setting['title']),
				"description" => $db->escape_string($setting['description']),
				"optionscode" => $db->escape_string($setting['optionscode']),
				"value" => $db->escape_string($setting['value']),
				"disporder" => intval($i),
				"gid" => intval($gid),
			);
			$db->insert_query("settings", $insert);
			$i++;
		}

		rebuild_settings();

		$templates = array();
		$templates[] = array(
			"title" => "pwnedplugin",
			"template" => "<div class='hibp-password'>
		<input type='password' class='textbox' name='password' id='password' style='width: 100%' />
		<div class='hibp-tooltip'>
			<img src='%logo%' width='16'>
			<span class='hibp-tooltext'>%description%</span>
		</div>
	</div>"
		);
		foreach($templates as $template)
		{
			$insert = array(
				"title" => $db->escape_string($template['title']),
				"template" => $db->escape_string($template['template']),
				"sid" => "-1",
				"version" => "1000",
				"dateline" => TIME_NOW
			);
			$db->insert_query("templates", $insert);
		}

		find_replace_templatesets("member_register_password", "#".preg_quote('<input type="password" class="textbox" name="password" id="password" style="width: 100%" />')."#i", '{$pwnedplugin}');
	}
}

function pwnedplugin_deactivate()
{
	global $db;

	require_once MYBB_ROOT . "inc/adminfunctions_templates.php";

	$db->delete_query("settinggroups", "name = 'pwnedplugin_settings'");

	$settings = array(
		"pwnedplugin_warning",
		"pwnedplugin_showicon",
		"pwnedplugin_tooltipdescription",
		"pwnedplugin_tooltiplogo"
	);
	$settings = "'" . implode("','", $settings) . "'";
	$db->delete_query("settings", "name IN ({$settings})");

	rebuild_settings();

	$templates = array(
		"pwnedplugin"
	);
	$templates = "'" . implode("','", $templates) . "'";
	$db->delete_query("templates", "title IN ({$templates})");

	find_replace_templatesets("member_register_password", "#".preg_quote('{$pwnedplugin}')."#i", '<input type="password" class="textbox" name="password" id="password" style="width: 100%" />');
}

// HIBP Logic

function pwnedplugin() { 
    global $plugins, $mybb, $pwnedplugin, $templates; 

    $plugins->add_hook("datahandler_user_validate", "pwnedplugin_checkhandler"); 

    # Show/Hide Tooltip Handler
    if (trim($mybb->settings['pwnedplugin_showicon']) === 'dontshow') # turns out pwnedplugin_showicon had like 12 random spaces in front of it, so my if statement was never true ~ :D, added trim() to get rid of the whitespaces.
    {   
        $pwnedplugin = '<input type="password" class="textbox" name="password" id="password" style="width: 100%" />';
    }
    else 
    {
        $pwnedplugin = "<div class='hibp-password'>
            <input type='password' class='textbox' name='password' id='password' style='width: 100%' />
            <div class='hibp-tooltip'>
                <img src='%logo%' width='16'>
                <span class='hibp-tooltext'>%description%</span>
            </div>
        </div>";
    }

    # Tooltip Description Handler
    $pwnedplugin = str_replace("%description%", trim($mybb->settings['pwnedplugin_tooltipdescription']), $pwnedplugin);

    # Tooltip Logo Handler
    $pwnedplugin = str_replace("%logo%", trim($mybb->settings['pwnedplugin_tooltiplogo']), $pwnedplugin);

}

function pwnedplugin_checkhandler(&$userhandler)
{
	global $mybb;

	$conf_password = $mybb->get_input('password2');
	$userhandler->data['password'] = $conf_password;
	pwnedplugin_apihandler($conf_password, $userhandler);
}

function pwnedplugin_apihandler($conf_password, &$userhandler)
{
	global $mybb;

	$sha1_confpassword = strtoupper(sha1($conf_password)); # we make the hash uppercase, as API response is in all uppercase

	$sha1_hash = substr($sha1_confpassword, 0, 5); # we use first 5 characters of the SHA1 hash for hibp password looking (using k-anonymity)
	$password_hash = substr($sha1_confpassword, 5, 35); # and then we use the rest of the hash to check against the API response

	$url= 'https://api.pwnedpasswords.com/range/' . $sha1_hash; # docs: https://haveibeenpwned.com/API/v3#PwnedPasswords
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close($ch);

	if (str_contains($result, $password_hash) == true)
	{
		$error = $mybb->settings['pwnedplugin_warning'];
		$userhandler->set_error($error);
	}	
	return true;
}
?>