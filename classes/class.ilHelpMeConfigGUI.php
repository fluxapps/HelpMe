<?php
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * HelpMe Config GUI
 *
 * @property ilHelpMePlugin $pl
 */
class ilHelpMeConfigGUI extends ilPluginConfigGUI {

	use srag\DIC\DICTrait;
	const PLUGIN_CLASS_NAME = ilHelpMePlugin::class;
	const CMD_CONFIGURE = "configure";
	const CMD_UPDATE_CONFIGURE = "updateConfigure";


	/**
	 *
	 */
	public function __construct() {

	}


	/**
	 *
	 * @param string $cmd
	 */
	public function performCommand($cmd) {
		$next_class = self::dic()->ctrl()->getNextClass($this);

		switch ($next_class) {
			default:
				switch ($cmd) {
					case self::CMD_CONFIGURE:
					case self::CMD_UPDATE_CONFIGURE:
						$this->$cmd();
						break;

					default:
						break;
				}
				break;
		}
	}


	/**
	 *
	 * @return ilHelpMeConfigFormGUI
	 */
	protected function getConfigurationForm() {
		$form = new ilHelpMeConfigFormGUI($this);

		return $form;
	}


	/**
	 * @param string $html
	 */
	protected function show($html) {
		self::dic()->tpl()->setContent($html);
	}


	/**
	 *
	 */
	protected function configure() {
		$form = $this->getConfigurationForm();

		$this->show($form->getHTML());
	}


	/**
	 *
	 */
	protected function updateConfigure() {
		$form = $this->getConfigurationForm();
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			$this->show($form->getHTML());

			return;
		}

		$form->updateConfig();

		ilUtil::sendSuccess(self::t("srsu_configuration_saved"));

		$this->show($form->getHTML());
	}
}
