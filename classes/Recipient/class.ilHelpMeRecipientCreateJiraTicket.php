<?php

/**
 * Class ilHelpMeRecipientCreateJiraTicket
 */
class ilHelpMeRecipientCreateJiraTicket extends ilHelpMeRecipient {

	/**
	 * @var ilJiraCurl
	 */
	protected $jiraCurl;
	/**
	 * @var string
	 */
	protected $issue_key;


	/**
	 * ilHelpMeRecipientCreateJiraTicket constructor
	 *
	 * @param ilHelpMeSupport $support
	 * @param ilHelpMeConfig  $config
	 */
	public function __construct(ilHelpMeSupport $support, ilHelpMeConfig $config) {
		parent::__construct($support, $config);

		$this->jiraCurl = new ilJiraCurl();

		$this->jiraCurl->setJiraDomain($config->getJiraDomain());

		$this->jiraCurl->setJiraAuthorization($config->getJiraAuthorization());

		$this->jiraCurl->setJiraUsername($config->getJiraUsername());
		$this->jiraCurl->setJiraPassword($config->getJiraPassword());

		$this->jiraCurl->setJiraConsumerKey($config->getJiraConsumerKey());
		$this->jiraCurl->setJiraPrivateKey($config->getJiraPrivateKey());
		$this->jiraCurl->setJiraAccessToken($config->getJiraAccessToken());
	}


	/**
	 * Send support to recipient
	 *
	 * @return bool
	 */
	public function sendSupportToRecipient(): bool {
		return ($this->createJiraTicket() && $this->addScreenshoots() && $this->sendConfirmationMail());
	}


	/**
	 * Create Jira ticket
	 *
	 * @return bool
	 */
	protected function createJiraTicket(): bool {
		$issue_key = $this->jiraCurl->createJiraIssueTicket($this->config->getJiraProjectKey(), $this->config->getJiraIssueType(), $this->support->getSubject(), $this->support->getBody("jira"));

		if ($issue_key === false) {
			return false;
		}

		$this->issue_key = $issue_key;

		return true;
	}


	/**
	 * Add screenshots to Jira ticket
	 *
	 * @return bool
	 */
	protected function addScreenshoots(): bool {
		foreach ($this->support->getScreenshots() as $screenshot) {
			if (!$this->jiraCurl->addAttachmentToIssue($this->issue_key, $screenshot["name"], $screenshot["type"], $screenshot["tmp_name"])) {
				return false;
			}
		}

		return true;
	}
}
