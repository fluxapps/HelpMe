<?php

namespace srag\GeneratePluginInfosHelper\HelpMe;

use Closure;
use Composer\Config;
use Composer\Script\Event;

/**
 * Class GeneratePluginReadme
 *
 * @package srag\GeneratePluginInfosHelper\HelpMe
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @internal
 */
final class GeneratePluginReadme
{

    const AUTOGENERATED_COMMENT = "Autogenerated from " . self::PLUGIN_COMPOSER_JSON . " - All changes will be overridden if generated again!";
    const PLUGIN_COMPOSER_JSON = "composer.json";
    const PLUGIN_LONG_DESCRIPTION = "doc/DESCRIPTION.md";
    const PLUGIN_README = "README.md";
    const PLUGIN_README_TEMPLATE_FOLDER = __DIR__ . "/../templates/GeneratePluginReadme";
    const PLUGIN_README_TEMPLATE_FOLDER_SUFFIX = "_" . self::PLUGIN_README;
    /**
     * @var self|null
     */
    private static $instance = null;
    /**
     * @var string
     */
    private static $plugin_root = "";
    /**
     * @var Event
     */
    private $event;


    /**
     * GeneratePluginReadme constructor
     *
     * @param Event $event
     */
    private function __construct(Event $event)
    {
        $this->event = $event;
    }


    /**
     * @param Event $event
     *
     * @internal
     */
    public static function generatePluginReadme(Event $event)/*: void*/
    {
        self::$plugin_root = rtrim(Closure::bind(function () : string {
            return $this->baseDir;
        }, $event->getComposer()->getConfig(), Config::class)(), "/");

        self::getInstance($event)->doGeneratePluginReadme();
    }


    /**
     * @param Event $event
     *
     * @return self
     */
    private static function getInstance(Event $event) : self
    {
        if (self::$instance === null) {
            self::$instance = new self($event);
        }

        return self::$instance;
    }


    /**
     *
     */
    private function doGeneratePluginReadme()/*: void*/
    {
        $plugin_composer_json = json_decode(file_get_contents(self::$plugin_root . "/" . self::PLUGIN_COMPOSER_JSON), true);

        if (file_exists(self::$plugin_root . "/" . self::PLUGIN_README)) {
            $old_readme = file_get_contents(self::$plugin_root . "/" . self::PLUGIN_README);
        } else {
            $old_readme = "";
        }

        echo "(Re)generate " . self::PLUGIN_README . "
";

        if (file_exists(self::$plugin_root . "/" . self::PLUGIN_LONG_DESCRIPTION)) {
            $long_description = str_replace("./images/", "./doc/images/", trim(file_get_contents(self::$plugin_root . "/" . self::PLUGIN_LONG_DESCRIPTION)));
        } else {
            $long_description = "";
        }

        $placeholders = [
            "AUTHOR_EMAIL"                   => strval($plugin_composer_json["authors"][0]["email"] ?? ""),
            "AUTHOR_HOMEPAGE"                => strval($plugin_composer_json["authors"][0]["homepage"] ?? ""),
            "AUTHOR_NAME"                    => strval($plugin_composer_json["authors"][0]["name"] ?? ""),
            "AUTOGENERATED_COMMENT"          => self::AUTOGENERATED_COMMENT,
            "GITHUB_REPO"                    => strval($plugin_composer_json["homepage"] ?? "") . ".git",
            "HOMEPAGE"                       => strval($plugin_composer_json["homepage"] ?? ""),
            "KEYWORDS"                       => implode("\n", array_map(function (string $keyword) : string {
                return "- " . $keyword;
            }, (array) $plugin_composer_json["keywords"] ?? [])),
            "ILIAS_PLUGIN_BASE_SLOT_PATH"    => "Customizing/global/plugins/" . strval($plugin_composer_json["extra"]["ilias_plugin"]["slot"] ?? ""),
            "ILIAS_PLUGIN_ID"                => strval($plugin_composer_json["extra"]["ilias_plugin"]["id"] ?? ""),
            "ILIAS_PLUGIN_MAX_ILIAS_VERSION" => strval($plugin_composer_json["extra"]["ilias_plugin"]["ilias_max_version"] ?? ""),
            "ILIAS_PLUGIN_MIN_ILIAS_VERSION" => strval($plugin_composer_json["extra"]["ilias_plugin"]["ilias_min_version"] ?? ""),
            "ILIAS_PLUGIN_NAME"              => strval($plugin_composer_json["extra"]["ilias_plugin"]["name"] ?? ""),
            "ILIAS_PLUGIN_SLOT"              => strval($plugin_composer_json["extra"]["ilias_plugin"]["slot"] ?? ""),
            "LICENSE"                        => strval($plugin_composer_json["license"] ?? ""),
            "LONG_DESCRIPTION"               => $long_description,
            "NAME"                           => strval($plugin_composer_json["name"] ?? ""),
            "PHP_VERSION"                    => strval($plugin_composer_json["require"]["php"] ?? ""),
            "SHORT_DESCRIPTION"              => strval($plugin_composer_json["description"] ?? ""),
            "SUPPORT_LINK"                   => strval($plugin_composer_json["support"]["issues"] ?? ""),
            "VERSION"                        => strval($plugin_composer_json["version"] ?? "")
        ];

        if (!empty($plugin_composer_json["extra"]["generate_plugin_readme_template"] ?? "")) {
            if (!file_exists(
                $template_file = self::PLUGIN_README_TEMPLATE_FOLDER . "/" . $plugin_composer_json["extra"]["generate_plugin_readme_template"] . self::PLUGIN_README_TEMPLATE_FOLDER_SUFFIX)
            ) {
                if (!file_exists($template_file = self::$plugin_root . "/" . $plugin_composer_json["extra"]["generate_plugin_readme_template"] . self::PLUGIN_README_TEMPLATE_FOLDER_SUFFIX)) {
                    echo "Invalid composer.json > extra > generate_plugin_readme_template
 ";
                    die(1);
                }
            }
        } else {
            echo "Please set composer.json > extra > generate_plugin_readme_template
 ";
            die(1);
        }

        echo "Use template " . $template_file . "
";
        $plugin_readme = file_get_contents($template_file);

        foreach ($placeholders as $key => $value) {
            $plugin_readme = str_replace("__" . $key . "__", $value, $plugin_readme);
        }

        if ($old_readme !== $plugin_readme) {
            echo "Store changes in " . self::PLUGIN_README . "
";

            file_put_contents(self::$plugin_root . "/" . self::PLUGIN_README, $plugin_readme);
        } else {
            echo "No changes in " . self::PLUGIN_README . "
";
        }
    }
}
