<?php

namespace srag\Plugins\HelpMe\Support;

use ilCronManager;
use ilHelpMeCronPlugin;
use ilHelpMePlugin;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use srag\ActiveRecordConfig\HelpMe\Exception\ActiveRecordConfigException;
use srag\DIC\HelpMe\DICStatic;
use srag\DIC\HelpMe\DICTrait;
use srag\JiraCurl\HelpMe\JiraCurl;
use srag\Plugins\HelpMe\Config\Config;
use srag\Plugins\HelpMe\Job\FetchJiraTicketsJob;
use srag\Plugins\HelpMe\Recipient\Recipient;
use srag\Plugins\HelpMe\Utils\HelpMeTrait;

/**
 * Class Repository
 *
 * @package srag\Plugins\HelpMe\Support
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class Repository {

	use DICTrait;
	use HelpMeTrait;
	const PLUGIN_CLASS_NAME = ilHelpMePlugin::class;
	/**
	 * @var self
	 */
	protected static $instance = null;


	/**
	 * @return self
	 */
	public static function getInstance(): self {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @var bool|null
	 */
	protected $is_enabled_tickets = null;


	/**
	 * Repository constructor
	 */
	private function __construct() {

	}


	/**
	 * @return Factory
	 */
	public function factory(): Factory {
		return Factory::getInstance();
	}


	/**
	 * Get browser infos
	 *
	 * @return string "Browser Version / System Version"
	 */
	public function getBrowserInfos(): string {
		$browser = new Browser();
		$os = new Os();

		$infos = $browser->getName() . (($browser->getVersion() !== Browser::UNKNOWN) ? " " . $browser->getVersion() : "") . " / " . $os->getName()
			. (($os->getVersion() !== Os::UNKNOWN) ? " " . $os->getVersion() : "");

		return $infos;
	}


	/**
	 * @param string $project_url_key
	 *
	 * @return string
	 */
	public function getLink(string $project_url_key = ""): string {
		return ILIAS_HTTP_PATH . "/goto.php?target=uihk_" . ilHelpMePlugin::PLUGIN_ID . (!empty($project_url_key) ? "_" . $project_url_key : "");
	}


	/**
	 * @return JiraCurl
	 *
	 * @throws ActiveRecordConfigException
	 */
	public function initJiraCurl(): JiraCurl {
		$jira_curl = new JiraCurl();

		$jira_curl->setJiraDomain(Config::getField(Config::KEY_JIRA_DOMAIN));

		$jira_curl->setJiraAuthorization(Config::getField(Config::KEY_JIRA_AUTHORIZATION));

		$jira_curl->setJiraUsername(Config::getField(Config::KEY_JIRA_USERNAME));
		$jira_curl->setJiraPassword(Config::getField(Config::KEY_JIRA_PASSWORD));

		$jira_curl->setJiraConsumerKey(Config::getField(Config::KEY_JIRA_CONSUMER_KEY));
		$jira_curl->setJiraPrivateKey(Config::getField(Config::KEY_JIRA_PRIVATE_KEY));
		$jira_curl->setJiraAccessToken(Config::getField(Config::KEY_JIRA_ACCESS_TOKEN));

		return $jira_curl;
	}


	/**
	 * @param bool $checkHasOneProjectAtLeastReadAccess
	 *
	 * @return bool
	 */
	public function isEnabledTickets(bool $checkHasOneProjectAtLeastReadAccess = true): bool {
		if ($this->is_enabled_tickets === null) {
			$this->is_enabled_tickets = (Config::getField(Config::KEY_RECIPIENT) === Recipient::CREATE_JIRA_TICKET
				&& (!$checkHasOneProjectAtLeastReadAccess || self::projects()->hasOneProjectAtLeastReadAccess())
				&& file_exists(__DIR__ . "/../../../../../Cron/CronHook/HelpMeCron/vendor/autoload.php")
				&& DICStatic::plugin(ilHelpMeCronPlugin::class)->getPluginObject()->isActive()
				&& ilCronManager::isJobActive(FetchJiraTicketsJob::CRON_JOB_ID));
		}

		return $this->is_enabled_tickets;
	}
}
