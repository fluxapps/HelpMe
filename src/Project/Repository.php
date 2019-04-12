<?php

namespace srag\Plugins\HelpMe\Project;

use ilHelpMePlugin;
use srag\DIC\HelpMe\DICTrait;
use srag\Plugins\HelpMe\Utils\HelpMeTrait;

/**
 * Class Repository
 *
 * @package srag\Plugins\HelpMe\Project
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
	 * Repository constructor
	 */
	private function __construct() {

	}


	/**
	 * @param Project $project
	 */
	public function deleteProject(Project $project)/*: void*/ {
		$project->delete();
	}


	/**
	 * @return Factory
	 */
	public function factory(): Factory {
		return Factory::getInstance();
	}


	/**
	 * @param Project $project
	 * @param string  $issue_type
	 *
	 * @return string
	 */
	public function getFixVersionForIssueType(Project $project, string $issue_type): string {
		foreach ($project->getProjectIssueTypes() as $issue_type_) {
			if ($issue_type_["issue_type"] === $issue_type) {
				return $issue_type_["fix_version"];
			}
		}

		return "";
	}


	/**
	 * @param Project $project
	 *
	 * @return array
	 */
	public function getIssueTypesOptions(Project $project): array {
		$options = [];

		foreach ($project->getProjectIssueTypes() as $issue_type) {
			$options[$issue_type["issue_type"]] = $issue_type["issue_type"];
		}

		return $options;
	}


	/**
	 * @return Project[]
	 */
	public function getProjects(): array {
		return Project::orderBy("project_name", "ASC")->get();
	}


	/**
	 * @return array
	 */
	public function getProjectsArray(): array {
		return Project::orderBy("project_name", "ASC")->getArray();
	}


	/**
	 * @param int $project_id
	 *
	 * @return Project|null
	 */
	public function getProjectById(int $project_id)/*: ?Project*/ {
		/**
		 * @var Project|null $project
		 */

		$project = Project::where([ "project_id" => $project_id ])->first();

		return $project;
	}


	/**
	 * @param string $project_key
	 *
	 * @return Project|null
	 */
	public function getProjectByKey(string $project_key)/*: ?Project*/ {
		/**
		 * @var Project|null $project
		 */

		$project = Project::where([ "project_key" => $project_key ])->first();

		return $project;
	}


	/**
	 * @param string $project_url_key
	 *
	 * @return Project|null
	 */
	public function getProjectByUrlKey(string $project_url_key)/*: ?Project*/ {
		/**
		 * @var Project|null $project
		 */

		$project = Project::where([ "project_url_key" => $project_url_key ])->first();

		return $project;
	}


	/**
	 * @return array
	 */
	public function getProjectsOptions(): array {
		return array_reduce($this->getProjects(), function (array $projects, Project $project): array {
			$projects[$project->getProjectUrlKey()] = $project->getProjectName();

			return $projects;
		}, []);
	}


	/**
	 * @return bool
	 */
	public function hasOneProjectAtLeastReadAccess(): bool {
		// TODO Check at least one project has read access
		return true;
	}


	/**
	 * @param Project $project
	 */
	public function storeInstance(Project $project)/*: void*/ {
		$project->store();
	}
}
