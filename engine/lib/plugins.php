<?php
/**
 * Elgg plugins library
 * Contains functions for managing plugins
 */

use Elgg\Filesystem\Directory;


/**
 * Tells \ElggPlugin::start() to include the start.php file.
 */
define('ELGG_PLUGIN_INCLUDE_START', 1);

/**
 * Tells \ElggPlugin::start() to automatically register the plugin's views.
 */
define('ELGG_PLUGIN_REGISTER_VIEWS', 2);

/**
 * Tells \ElggPlugin::start() to automatically register the plugin's languages.
 */
define('ELGG_PLUGIN_REGISTER_LANGUAGES', 4);

/**
 * Tells \ElggPlugin::start() to automatically register the plugin's classes.
 */
define('ELGG_PLUGIN_REGISTER_CLASSES', 8);

/**
 * Tells \ElggPlugin::start() to automatically register the plugin's actions.
 */
define('ELGG_PLUGIN_REGISTER_ACTIONS', 16);

/**
 * Tells \ElggPlugin::start() to automatically register the plugin's actions.
 */
define('ELGG_PLUGIN_REGISTER_WIDGETS', 32);

/**
 * Prefix for plugin setting names
 *
 * @todo Can't namespace these because many plugins directly call
 * private settings via $entity->$name.
 */
//define('ELGG_PLUGIN_SETTING_PREFIX', 'plugin:setting:');

/**
 * Prefix for plugin user setting names
 */
define('ELGG_PLUGIN_USER_SETTING_PREFIX', 'plugin:user_setting:');

/**
 * Internal settings prefix
 *
 * @todo This could be resolved by promoting \ElggPlugin to a 5th type.
 */
define('ELGG_PLUGIN_INTERNAL_PREFIX', 'elgg:internal:');

/**
 * Discovers plugins in the plugins_path setting and creates \ElggPlugin
 * entities for them if they don't exist.  If there are plugins with entities
 * but not actual files, will disable the \ElggPlugin entities and mark as inactive.
 * The \ElggPlugin object holds config data, so don't delete.
 *
 * @return bool
 * @since 1.8.0
 * @access private
 */
function _elgg_generate_plugin_entities() {
	return _elgg_services()->plugins->generateEntities();
}

/**
 * Cache a reference to this plugin by its ID
 *
 * @param \ElggPlugin $plugin
 *
 * @access private
 */
function _elgg_cache_plugin_by_id(\ElggPlugin $plugin) {
	return _elgg_services()->plugins->cache($plugin);
}

/**
 * Returns an \ElggPlugin object with the path $path.
 *
 * @param string $plugin_id The id (dir name) of the plugin. NOT the guid.
 * @return \ElggPlugin|null
 * @since 1.8.0
 */
function elgg_get_plugin_from_id($plugin_id) {
	return _elgg_services()->plugins->get($plugin_id);
}

/**
 * Returns if a plugin exists in the system.
 *
 * @warning This checks only plugins that are registered in the system!
 * If the plugin cache is outdated, be sure to regenerate it with
 * {@link _elgg_generate_plugin_objects()} first.
 *
 * @param string $id The plugin ID.
 * @since 1.8.0
 * @return bool
 */
function elgg_plugin_exists($id) {
	return _elgg_services()->plugins->exists($id);
}

/**
 * Returns the highest priority of the plugins
 *
 * @return int
 * @since 1.8.0
 * @access private
 */
function _elgg_get_max_plugin_priority() {
	return _elgg_services()->plugins->getMaxPriority();
}

/**
 * Returns if a plugin is active for a current site.
 *
 * @param string $plugin_id The plugin ID
 * @since 1.8.0
 * @return bool
 */
function elgg_is_active_plugin($plugin_id) {
	return _elgg_services()->plugins->isActive($plugin_id);
}

/**
 * Returns an ordered list of plugins
 *
 * @param string $status The status of the plugins. active, inactive, or all.
 * @return \ElggPlugin[]
 * @since 1.8.0
 */
function elgg_get_plugins($status = 'active') {
	return _elgg_services()->plugins->find($status);
}

/**
 * Namespaces a string to be used as a private setting name for a plugin.
 *
 * For user_settings, two namespaces are added: a user setting namespace and the
 * plugin id.
 *
 * For internal (plugin priority), there is a single internal namespace added.
 *
 * @param string $type The type of setting: user_setting or internal.
 * @param string $name The name to namespace.
 * @param string $id   The plugin's ID to namespace with.  Required for user_setting.
 * @return string
 * @since 1.8.0
 * @access private
 */
function _elgg_namespace_plugin_private_setting($type, $name, $id = null) {
	return _elgg_services()->plugins->namespacePrivateSetting($type, $name, $id);
}

/**
 * Deletes all cached data on plugins being provided.
 *
 * @return boolean
 * @since 1.9.0
 * @access private
 */
function _elgg_invalidate_plugins_provides_cache() {
	return _elgg_services()->plugins->invalidateProvidesCache();
}

/**
 * Checks if a plugin is currently providing $type and $name, and optionally
 * checking a version.
 *
 * @param string $type       The type of the provide
 * @param string $name       The name of the provide
 * @param string $version    A version to check against
 * @param string $comparison The comparison operator to use in version_compare()
 *
 * @return array An array in the form array(
 * 	'status' => bool Does the provide exist?,
 * 	'value' => string The version provided
 * )
 * @since 1.8.0
 * @access private
 */
function _elgg_check_plugins_provides($type, $name, $version = null, $comparison = 'ge') {
	return _elgg_services()->plugins->checkProvides($type, $name, $version, $comparison);
}

/**
 * Returns an array of parsed strings for a dependency in the
 * format: array(
 * 	'type'			=>	requires, conflicts, or provides.
 * 	'name'			=>	The name of the requirement / conflict
 * 	'value'			=>	A string representing the expected value: <1, >=3, !=enabled
 * 	'local_value'	=>	The current value, ("Not installed")
 * 	'comment'		=>	Free form text to help resovle the problem ("Enable / Search for plugin <link>")
 * )
 *
 * @param array $dep An \ElggPluginPackage dependency array
 * @return array
 * @since 1.8.0
 * @access private
 */
function _elgg_get_plugin_dependency_strings($dep) {
	return _elgg_services()->plugins->getDependencyStrings($dep);
}

/**
 * Returns an array of all plugin user settings for a user.
 *
 * @param int    $user_guid  The user GUID or 0 for the currently logged in user.
 * @param string $plugin_id  The plugin ID (Required)
 * @param bool   $return_obj Return settings as an object? This can be used to in reusable
 *                           views where the settings are passed as $vars['entity'].
 * @return array
 * @since 1.8.0
 * @see \ElggPlugin::getAllUserSettings()
 */
function elgg_get_all_plugin_user_settings($user_guid = 0, $plugin_id = null, $return_obj = false) {
	return _elgg_services()->plugins->getAllUserSettings($user_guid, $plugin_id, $return_obj);
}

/**
 * Set a user specific setting for a plugin.
 *
 * @param string $name      The name. Note: cannot be "title".
 * @param mixed  $value     The value.
 * @param int    $user_guid The user GUID or 0 for the currently logged in user.
 * @param string $plugin_id The plugin ID (Required)
 *
 * @return bool
 * @since 1.8.0
 * @see \ElggPlugin::setUserSetting()
 */
function elgg_set_plugin_user_setting($name, $value, $user_guid = 0, $plugin_id = null) {
	return _elgg_services()->plugins->setUserSetting($name, $value, $user_guid, $plugin_id);
}

/**
 * Unsets a user-specific plugin setting
 *
 * @param string $name      Name of the setting
 * @param int    $user_guid The user GUID or 0 for the currently logged in user.
 * @param string $plugin_id The plugin ID (Required)
 *
 * @return bool
 * @since 1.8.0
 * @see \ElggPlugin::unsetUserSetting()
 */
function elgg_unset_plugin_user_setting($name, $user_guid = 0, $plugin_id = null) {
	return _elgg_services()->plugins->unsetUserSetting($name, $user_guid, $plugin_id);
}

/**
 * Get a user specific setting for a plugin.
 *
 * @param string $name      The name of the setting.
 * @param int    $user_guid The user GUID or 0 for the currently logged in user.
 * @param string $plugin_id The plugin ID (Required)
 * @param mixed  $default   The default value to return if none is set
 *
 * @return mixed
 * @since 1.8.0
 * @see \ElggPlugin::getUserSetting()
 */
function elgg_get_plugin_user_setting($name, $user_guid = 0, $plugin_id = null, $default = null) {
	return _elgg_services()->plugins->getUserSetting($name, $user_guid, $plugin_id, $default);
}

/**
 * Set a setting for a plugin.
 *
 * @param string $name      The name of the setting - note, can't be "title".
 * @param mixed  $value     The value.
 * @param string $plugin_id The plugin ID (Required)
 *
 * @return bool
 * @since 1.8.0
 * @see \ElggPlugin::setSetting()
 */
function elgg_set_plugin_setting($name, $value, $plugin_id) {
	return _elgg_services()->plugins->setSetting($name, $value, $plugin_id);
}

/**
 * Get setting for a plugin.
 *
 * @param string $name      The name of the setting.
 * @param string $plugin_id The plugin ID (Required)
 * @param mixed  $default   The default value to return if none is set
 *
 * @return mixed
 * @since 1.8.0
 * @see \ElggPlugin::getSetting()
 */
function elgg_get_plugin_setting($name, $plugin_id, $default = null) {
	return _elgg_services()->plugins->getSetting($name, $plugin_id, $default);
}

/**
 * Unsets a plugin setting.
 *
 * @param string $name      The name of the setting.
 * @param string $plugin_id The plugin ID (Required)
 *
 * @return bool
 * @since 1.8.0
 * @see \ElggPlugin::unsetSetting()
 */
function elgg_unset_plugin_setting($name, $plugin_id) {
	return _elgg_services()->plugins->unsetSetting($name, $plugin_id);
}

/**
 * Unsets all plugin settings for a plugin.
 *
 * @param string $plugin_id The plugin ID (Required)
 *
 * @return bool
 * @since 1.8.0
 * @see \ElggPlugin::unsetAllSettings()
 */
function elgg_unset_all_plugin_settings($plugin_id) {
	return _elgg_services()->plugins->unsetAllSettings($plugin_id);
}

/**
 * Returns entities based upon plugin user settings.
 * Takes all the options for {@link elgg_get_entities_from_private_settings()}
 * in addition to the ones below.
 *
 * @param array $options Array in the format:
 *
 * 	plugin_id => STR The plugin id. Required.
 *
 * 	plugin_user_setting_names => null|ARR private setting names
 *
 * 	plugin_user_setting_values => null|ARR metadata values
 *
 * 	plugin_user_setting_name_value_pairs => null|ARR (
 *                                         name => 'name',
 *                                         value => 'value',
 *                                         'operand' => '=',
 *                                        )
 * 	                             Currently if multiple values are sent via
 *                               an array (value => array('value1', 'value2')
 *                               the pair's operand will be forced to "IN".
 *
 * 	plugin_user_setting_name_value_pairs_operator => null|STR The operator to use for combining
 *                                        (name = value) OPERATOR (name = value); default AND
 *
 * @return mixed int If count, int. If not count, array. false on errors.
 * @since 1.8.0
 */
function elgg_get_entities_from_plugin_user_settings(array $options = []) {
	return _elgg_services()->plugins->getEntitiesFromUserSettings($options);
}

/**
 * Runs unit tests for plugin API.
 *
 * @param string $hook   unit_test
 * @param string $type   system
 * @param mixed  $value  Array of tests
 * @param mixed  $params Params
 *
 * @return array
 * @access private
 */
function _elgg_plugins_test($hook, $type, $value, $params) {
	global $CONFIG;
	$value[] = $CONFIG->path . 'engine/tests/ElggCorePluginsAPITest.php';
	return $value;
}

/**
 * Initialize the plugin system
 *
 * @return void
 * @access private
 */
function _elgg_plugins_init() {

	if (elgg_is_admin_logged_in()) {
		elgg_register_ajax_view('object/plugin/full');
		elgg_register_ajax_view('object/plugin/details');
	}

	elgg_register_plugin_hook_handler('unit_test', 'system', '_elgg_plugins_test');

	/**
	 * @see \Elgg\Database\Plugins::invalidateIsActiveCache
	 */
	$svc = _elgg_services()->plugins;
	elgg_register_event_handler('deactivate', 'plugin', [$svc, 'invalidateIsActiveCache']);
	elgg_register_event_handler('activate', 'plugin', [$svc, 'invalidateIsActiveCache']);

	elgg_register_action("plugins/settings/save", '', 'admin');
	elgg_register_action("plugins/usersettings/save");

	elgg_register_action('admin/plugins/activate', '', 'admin');
	elgg_register_action('admin/plugins/deactivate', '', 'admin');
	elgg_register_action('admin/plugins/activate_all', '', 'admin');
	elgg_register_action('admin/plugins/deactivate_all', '', 'admin');

	elgg_register_action('admin/plugins/set_priority', '', 'admin');
}

return function(\Elgg\EventsService $events, \Elgg\HooksRegistrationService $hooks) {
	$events->registerHandler('init', 'system', '_elgg_plugins_init');
};
