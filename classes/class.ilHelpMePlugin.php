<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;

/**
 * HelpMe Plugin
 */
class ilHelpMePlugin extends ilUserInterfaceHookPlugin {

	const PLUGIN_ID = "srsu";
	const PLUGIN_NAME = "HelpMe";
	/**
	 * @var ilHelpMePlugin
	 */
	protected static $instance = NULL;


	/**
	 * @return ilHelpMePlugin
	 */
	static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @var ilDB
	 */
	protected $db;


	public function __construct() {
		parent::__construct();

		global $DIC;

		$this->db = $DIC->database();
	}


	function getPluginName() {
		return self::PLUGIN_NAME;
	}


	/**
	 * Get browser infos
	 *
	 * @return string "Browser Version / System Version"
	 */
	function getBrowserInfos() {
		$browser = new Browser();
		$os = new Os();

		$infos = $browser->getName() . (($browser->getVersion() !== Browser::UNKNOWN) ? " " . $browser->getVersion() : "") . " / " . $os->getName()
			. (($os->getVersion() !== Os::UNKNOWN) ? " " . $os->getVersion() : "");

		return $infos;
	}


	protected function beforeUninstall() {
		$this->db->dropTable(ilHelpMeConfig::TABLE_NAME, false);

		$this->db->dropTable(ilHelpMeConfigPriority::TABLE_NAME, false);

		$this->db->dropTable(ilHelpMeConfigRole::TABLE_NAME, false);

		return true;
	}
}
