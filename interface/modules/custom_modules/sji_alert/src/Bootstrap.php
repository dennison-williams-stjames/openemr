<?php
/**
 * Bootstrap custom module skeleton.  This file is an example custom module that can be used
 * to create modules that can be utilized inside the OpenEMR system.  It is NOT intended for
 * production and is intended to serve as the barebone requirements you need to get started
 * writing modules that can be installed and used in OpenEMR.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SJIAlert;
//require_once("interface/globals.php");

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Note the below use statements are importing classes from the OpenEMR core codebase
 */
use OpenEMR\Events\PatientDemographics\RenderEvent;

class Bootstrap
{
	const MODULE_INSTALLATION_PATH = "";
	const MODULE_NAME = "sji-alert";
	/**
	 * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
	 */
	private $eventDispatcher;

	public function __construct(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}

	public function subscribeToEvents()
	{
		$this->eventDispatcher->addListener(RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, [$this, 'addSJIAlert']);
	}

	public function addSJIAlert(RenderEvent $event) {
		$pid = $event->getPid();
		$db = $GLOBALS['adodb']['db'];

		$sql = 
		"SELECT form_sji_alert.date as date,form_sji_alert.alert as alert FROM form_sji_alert " .
		"LEFT JOIN forms on (form_sji_alert.id=forms.form_id) ".
		"WHERE form_sji_alert.pid = " .  $db->qstr($pid) ." ".
		"AND forms.formdir='sji_alert' ".
		"AND forms.deleted=0";
		$result = $db->Execute($sql);
		if ($db->ErrorMsg()) {
		    return error_log($db->ErrorMsg());
		}

		// TODO: If there are any active alerts for the participant 
		// then we should click on a link to the alert pop up with js
		while ($result && !$result->EOF) {
			echo "<a href='/interface/modules/custom_modules/sji_alert/src/alert.php?".
			"pid=". attr_url($pid) .
			"' id='alert_popup' ".
			"style='display: none;' onclick='top.restoreSession()'></a>\n".

			// show the active alert modal
			'<script type="text/javascript">'."\n".
			"dlgopen('', 'aleretreminder', 300, 170, '', false, { ".
			"allowResize: false, ".
			"allowDrag: true, ".
			"dialogId: '', ".
			"type: 'iframe', ".
			'url: $("#alert_popup").attr("href") '.
			"});".
			'</script>'."\n";
			break;
		}        
	}

}
