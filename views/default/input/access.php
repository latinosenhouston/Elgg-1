<?php

	/**
	 * Elgg access level input
	 * Displays a pulldown input field
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider Ltd
	 * @copyright Curverider Ltd 2008-2009
	 * @link http://elgg.org/
	 * 
	 * @uses $vars['value'] The current value, if any
	 * @uses $vars['js'] Any Javascript to enter into the input tag
	 * @uses $vars['internalname'] The name of the input field
	 * 
	 */

	if (isset($vars['class'])) $class = $vars['class'];
	if (!$class) $class = "input-access";
	
	if (!array_key_exists('value', $vars) || $vars['value'] == ACCESS_DEFAULT)
		$vars['value'] = get_default_access();
			

		if ((!isset($vars['options'])) || (!is_array($vars['options'])))
		{
			$vars['options'] = array();
			$vars['options'] = get_write_access_array();
		}
		
		if (is_array($vars['options']) && sizeof($vars['options']) > 0) {	 
			 
?>

<select name="<?php echo $vars['internalname']; ?>" <?php if (isset($vars['js'])) echo $vars['js']; ?> <?php if ((isset($vars['disabled'])) && ($vars['disabled'])) echo ' disabled="yes" '; ?> class="<?php echo $class; ?>">
<?php

    foreach($vars['options'] as $key => $option) {
        if ($key != $vars['value']) {
            echo "<option value=\"{$key}\">". htmlentities($option, null, 'UTF-8') ."</option>";
        } else {
            echo "<option value=\"{$key}\" selected=\"selected\">". htmlentities($option, null, 'UTF-8') ."</option>";
        }
    }

?> 
</select>

<?php

		}		

?>