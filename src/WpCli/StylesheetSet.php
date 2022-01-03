<?php

/**
 * The file that holds WpCli command to copy stylessheet set from form to your project.
 *
 * @package EightshiftForms\WpCli
 */

declare(strict_types=1);

namespace EightshiftForms\WpCli;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * StylesheetSet class.
 */
class StylesheetSet implements ServiceInterface
{
	/**
	 * Register method for WPCLI command
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('cli_init', [$this, 'registerCommand']);
	}

	/**
	 * Register actual command in WP-CLI.
	 *
	 * @return void
	 */
	public function registerCommand(): void
	{
		\WP_CLI::add_command(
			Config::getProjectName() . ' ' . $this->getCommandName(),
			get_class($this),
			$this->getDocs()
		);
	}

	/**
	 * Get WPCLI command name.
	 *
	 * @return string
	 */
	public function getCommandName(): string
	{
		return 'copy_stylesheet_set';
	}

	/**
	 * Return WPCLI command documentation.
	 *
	 * @return array<string, mixed>
	 */
	public function getDocs(): array
	{
		return [
			'shortdesc' => 'This stylesheet set is used to give you a headstart in providing your own style to the form.',
			'synopsis' => [
				[
					'type' => 'assoc',
					'name' => 'additional-path',
					'description' => 'Set additional path relative from the active theme. Example. src/Block/components/',
					'optional' => true,
				],
			],
		];
	}

	// @phpstan-ignore-next-line
	public function __invoke(array $args, array $argsAsoc) // phpcs:ignore
	{
		$path = $argsAsoc['additional-path'] ?? '';

		$targetPath = __DIR__ . DIRECTORY_SEPARATOR . Config::getProjectName();
		$destinationPath = get_template_directory() . DIRECTORY_SEPARATOR . $path;
		$destinationPathWithFolder = $destinationPath . DIRECTORY_SEPARATOR . Config::getProjectName();

		if (file_exists($destinationPathWithFolder)) {
			\WP_CLI::error(
				"You have tried to move stylesheet set to this path '{$destinationPathWithFolder}'. The folder all-ready exists please choose another one and run the command again."
			);
		}

		// Create folder in project if missing.
		system("mkdir -p {$destinationPath}" . DIRECTORY_SEPARATOR); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_system

		// Move block/component to project folder.
		system("cp -R {$targetPath} {$destinationPath}" . DIRECTORY_SEPARATOR); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_system

		\WP_CLI::success("We have moved stylesheet set to this folder '{$destinationPathWithFolder}', have fun styling your forms.");
	}
}
