<?php

namespace srag\Plugins\HelpMe\Screenshot;

use ilFormException;
use ilFormPropertyGUI;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ilTemplate;
use srag\DIC\DICTrait;
use srag\DIC\Plugin\Plugin;
use srag\DIC\Plugin\Pluginable;
use srag\DIC\Plugin\PluginInterface;

/**
 * Class ScreenshotsInputGUI
 *
 * @package srag\Plugins\HelpMe\Screenshot
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @since   ILIAS 5.3
 */
class ScreenshotsInputGUI extends ilFormPropertyGUI implements Pluginable {

	use DICTrait;
	const LANG_MODULE_SCREENSHOTSINPUTGUI = "screenshotsinputgui";
	/**
	 * @var bool
	 */
	protected static $init = false;
	/**
	 * @var UploadResult[]
	 */
	protected $screenshots = [];
	/**
	 * @var Plugin|null
	 */
	protected $plugin = NULL;


	/**
	 * ScreenshotsInputGUI constructor
	 *
	 * @param string $title
	 * @param string $post_var
	 */
	public function __construct(string $title = "", string $post_var = "") {
		parent::__construct($title, $post_var);
	}


	/**
	 * @return bool
	 */
	public function checkInput(): bool {
		$this->processScreenshots();

		if ($this->getRequired() && count($this->screenshots) === 0) {
			return false;
		}

		return true;
	}


	/**
	 * @return string
	 */
	public function getJSOnLoadCode(): string {
		$screenshot_tpl = $this->getPlugin()->template(__DIR__ . "/../../templates/screenshot.html", true, true, false);
		$screenshot_tpl->setVariable("TXT_REMOVE_SCREENSHOT", $this->getPlugin()
			->translate("remove_screenshot", self::LANG_MODULE_SCREENSHOTSINPUTGUI));

		return 'il.Screenshots.PAGE_SCREENSHOT_NAME = ' . json_encode($this->getPlugin()
				->translate("page_screenshot", self::LANG_MODULE_SCREENSHOTSINPUTGUI)) . ';
		il.Screenshots.SCREENSHOT_TEMPLATE = ' . json_encode($screenshot_tpl->get()) . ';';
	}


	/**
	 * @return PluginInterface
	 */
	public function getPlugin(): PluginInterface {
		return $this->plugin;
	}


	/**
	 * @return UploadResult[]
	 */
	public function getValue(): array {
		return $this->screenshots;
	}


	/**
	 *
	 */
	public function initJS()/*: void*/ {
		if (self::$init === false) {
			self::$init = true;

			$dir = substr(__DIR__, strlen(ILIAS_ABSOLUTE_PATH) + 1) . "/../..";

			self::dic()->mainTemplate()->addJavaScript($dir . "/js/Screenshots.js", false);
			self::dic()->mainTemplate()->addOnLoadCode($this->getJSOnLoadCode());
		}
	}


	/**
	 * @param ilTemplate $tpl
	 */
	public function insert(ilTemplate $tpl) /*: void*/ {
		$html = $this->render();

		$tpl->setCurrentBlock("prop_generic");
		$tpl->setVariable("PROP_GENERIC", $html);
		$tpl->parseCurrentBlock();
	}


	/**
	 *
	 */
	protected function processScreenshots()/*: void*/ {
		// TODO: Match by post var
		if (!self::dic()->upload()->hasBeenProcessed()) {
			self::dic()->upload()->process();
		}

		if (self::dic()->upload()->hasUploads()) {
			$this->screenshots = array_values(array_filter(self::dic()->upload()->getResults(), function (UploadResult $file): bool {
				return ($file->getStatus()->getCode() === ProcessingStatus::OK);
			}));
		} else {
			$this->screenshots = [];
		}
	}


	/**
	 * @return string
	 */
	protected function render(): string {
		$this->initJS();

		$screenshots_tpl = $this->getPlugin()->template(__DIR__ . "/../../templates/screenshots.html", true, true, false);
		$screenshots_tpl->setVariable("TXT_UPLOAD_SCREENSHOT", $this->getPlugin()
			->translate("upload_screenshot", self::LANG_MODULE_SCREENSHOTSINPUTGUI));
		$screenshots_tpl->setVariable("TXT_TAKE_PAGE_SCREENSHOT", $this->getPlugin()
			->translate("take_page_screenshot", self::LANG_MODULE_SCREENSHOTSINPUTGUI));
		$screenshots_tpl->setVariable("POST_VAR", $this->getPostVar());

		return $screenshots_tpl->get();
	}


	/**
	 * @param PluginInterface $plugin
	 */
	public function setPlugin(PluginInterface $plugin)/*: void*/ {
		$this->plugin = $plugin;
	}


	/**
	 * @param UploadResult[] $screenshots
	 *
	 * @throws ilFormException
	 */
	public function setValue(array $screenshots)/*: void*/ {
		//throw new ilFormException("ScreenshotInputGUI does not support set screenshots!");
	}


	/**
	 * @param array $values
	 *
	 * @throws ilFormException
	 */
	public function setValueByArray($values)/*: void*/ {
		//throw new ilFormException("ScreenshotInputGUI does not support set screenshots!");
	}
}
