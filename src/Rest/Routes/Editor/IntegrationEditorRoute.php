<?php

/**
 * The class to provide form builder json to block editor for integrations.
 *
 * @package EightshiftForms\Rest\Routes\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor;

use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\MapperInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_Query;
use WP_REST_Request;

/**
 * Class IntegrationEditorRoute
 */
class IntegrationEditorRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/integration-editor/(?P<id>\d+)';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var MapperInterface
	 */
	protected $hubspot;

	/**
	 * Create a new instance.
	 *
	 * @param MapperInterface $hubspot Inject Hubspot which holds Hubspot form data.
	 */
	public function __construct(
		MapperInterface $hubspot
	) {
		$this->hubspot = $hubspot;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * By default allow public access to route.
	 *
	 * @return bool
	 */
	// public function permissionCallback(): bool
	// {
	// 	return true;
	// }

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		if (! \current_user_can(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY)) {
			\rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$formId = $request->get_url_params()['id'] ?? '';
		if (!$formId) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form ID.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$content = $this->getFormContent($formId);
		$formContent = $this->prepareBlocks($this->getFormContent($formId));

		if (!$formContent) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$type = $formContent['type'] ?? '';
		$itemId = $formContent['itemId'] ?? '';
		$fields = $formContent['fields'] ?? [];

		if (!$type) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content integration type block.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		if (!$itemId) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content integration item Id.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$integration = $this->hubspot->getFormBlockGrammarArray($formId, $itemId, $type);
		$integrationBlocks = $this->prepareBlocks($integration);

		if (!$fields) {
			$integrationFields = $integrationBlocks['fields'] ?? [];

			if (!$integrationFields) {
				return \rest_ensure_response([
					'code' => 400,
					'status' => 'error',
					'message' => \esc_html__('Missing form content fields and missing integration fields.', 'eightshift-forms'),
					'update' => false,
					'data' => [],
				]);
			}

			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content integration fields.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}


		$output = $this->diffChanges($integrationBlocks, $formContent);

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		if ($isDeveloperMode) {
			$output['contentOld'] = $content;
			$output['integration'] = $integration;
		}

		// Exit with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => \esc_html__('Form updated.', 'eightshift-forms'),
			'update' => true,
			'data' => $output,
		]);
	}

	/**
	 * Undocumented function
	 *
	 * @param string $formId
	 * @return array
	 */
	private function getFormContent(string $formId): array
	{
		$theQuery = new WP_Query([
			'p' => $formId,
			'post_type' => Forms::POST_TYPE_SLUG,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'any',
		]);

		$form = $theQuery->post;

		wp_reset_postdata();

		if (!$form) {
			return [];
		}

		$blocks = parse_blocks($form->post_content);

		if (!$blocks) {
			return [];
		}

		return isset($blocks[0]) ? $blocks[0] : [];
	}

	/**
	 * Undocumented function
	 *
	 * @param array $blocks
	 * @return array
	 */
	private function prepareBlocks(array $blocks): array
	{
		$output = [
			'type' => '',
			'itemId' => '',
			'fields' => [],
		];

		$blockName = $blocks['innerBlocks'][0]['blockName'] ?? '';

		if (!$blockName) {
			return $output;
		}

		$type = \explode('/', $blockName);
		$type = \end($type);
		$output['type'] = $type;

		$itemId = $blocks['innerBlocks'][0]['attrs'][Components::kebabToCamelCase($type) . "IntegrationId"] ?? '';

		if (!$itemId) {
			return $output;
		}

		$output['itemId'] = \preg_replace('/\\u002d\\u002d/', '--', $itemId);
		$output['fields'] = $blocks;

		return $output;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $integrationBlocks
	 * @param array $contentBlocks
	 * @return array
	 */
	private function diffChanges(array $integrationBlocks, array $contentBlocks): array
	{
		if ($integrationBlocks['type'] !== $contentBlocks['type']) {
			return [];
		}

		if ($integrationBlocks['itemId'] !== $contentBlocks['itemId']) {
			return [];
		}

		$output = [
			'type' => $integrationBlocks['type'],
			'itemId' => $integrationBlocks['itemId'],
			'removed' => [],
			'added' => [],
			'changed' => [],
			'output' => [],
		];
		$diff = $this->prepareDiffCheckOutput($integrationBlocks, $contentBlocks);

		foreach ($diff['fields'] as $key => $block) {
			$content = $block['content'] ?? [];
			$integration = $block['integration'] ?? [];

			// error_log( print_r( ( $block ), true ) );
			

			// Remove item if it is not present on integration, output nothing.
			if (!$integration) {
				$output['removed'][] = $key;
				continue;
			}

			// If field exists on the integration but not on the content add it.
			if (!$content) {
				$output['added'][] = $key;
				$output['output'][] = $integration;
				continue;
			}

			// If field type has changed on integration use the integration one.
			if ($integration['blockName'] !== $content['blockName']) {
				$output['changed'][] = $key;
				$output['output'][] = $integration;
				continue;
			}

			// Check if disabled attrs changed.
			$innerOutput = $content;
			$prefix = Helper::getBlockAttributePrefixByFullBlockName($integration['blockName']);
			$disabledOptions = $integrationBlocks['attrs']["{$prefix}DisabledOptions"] ?? [];

			if ($disabledOptions) {
				foreach ($disabledOptions as $disabledOption) {
					$i = $integrationBlocks['attrs'][$disabledOption] ?? '';
					$c = $content['attrs'][$disabledOption] ?? '';

					// If intregration is missing disabled and protected attribute. This chould be and issue in the mapping of component attributes for integration.
					if (!$i) {
						$output['changed'][] = $key;
						$output['output'][] = $integration;
						break;
					}

					// If content has missing disabled or protected attribute add it from integration.
					if (!$c) {
						$innerOutput['attrs'][$disabledOption] = $i;
						break;
					}

					// If value of attribute in content and intregation is diffrerent do something.
					if ($i !== $c) {
						// If protected attributes name has changed we need to update the whole block. This is an unlikely scenario but it can happen.
						if ($i === "{$prefix}Name" && $c === "{$prefix}Name") {
							$output['changed'][] = $key;
							$output['output'][] = $integration;
							break;
						}

						$innerOutput['attrs'][$disabledOption] = $i;
						continue;
					}
				}
			}

			$output['output'][] = $content;
		}

		if ($diff['removed']) {
			$output['removed'] = [
				...$output['removed'],
				...$diff['removed'],
			];
		}

		$namespace = Components::getSettingsNamespace();

		$integrationOutput = [
			[
				'blockName' => "{$namespace}/" . $output['type'],
				'attrs' => [
					$output['type'] . "IntegrationId" => $output['itemId'],
				],
				'innerContent' => $output['output'],
				'innerHTML' => '',
				'innerBlocks' => $output['output'],
			],
		];

		$output['output'] = [
			'blockName' => "{$namespace}/form-selector",
			'attrs' => [],
			'innerContent' => $integrationOutput,
			'innerHTML' => '',
			'innerBlocks' => $integrationOutput,
		];

		return $output;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $integrationBlocks
	 * @param array $contentBlocks
	 * @return array
	 */
	private function prepareDiffCheckOutput(array $integrationBlocks, array $contentBlocks): array
	{
		$output = [
			'fields' => [],
			'removed' => [],
		];

		$contentBlocks = $this->getFieldsFromBlocks($contentBlocks);

		foreach ($this->getFieldsFromBlocks($integrationBlocks) as $block) {
			$blockName = Helper::getBlockAttributePrefixByFullBlockName($block['blockName']);
			$name = $block['attrs']["{$blockName}Name"] ?? '';

			if (!$name) {
				continue;
			}

			$output['fields'][$name] = [
				'content' => $this->getBlockByAttribute("{$blockName}Name", $name, $contentBlocks),
				'integration' => $block,
			];
		}

		if (!$output['fields']) {
			return $output;
		}

		// Add to remove list if the field exists on the content and not on the integration.
		foreach ($contentBlocks as $block) {
			$blockName = Helper::getBlockAttributePrefixByFullBlockName($block['blockName']);
			$name = $block['attrs']["{$blockName}Name"] ?? '';

			if (!$name) {
				continue;
			}

			if (isset($output['fields'][$name])) {
				continue;
			}

			$output['removed'][] = $name;
		}

		return $output;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $attribute
	 * @param string $value
	 * @param array $blocks
	 * @return array
	 */
	private function getBlockByAttribute(string $attribute, string $value, array $blocks): array
	{
		$output = array_filter(
			$blocks,
			static function($item) use ($attribute, $value) {
				$attributeValue = $item['attrs'][$attribute] ?? '';

				if ($attributeValue === $value) {
					return $item;
				}
			}
		);

		return reset($output) ?: [];
	}

	/**
	 * Undocumented function
	 *
	 * @param array $blocks
	 * @return array
	 */
	private function getFieldsFromBlocks(array $blocks): array
	{
		return $blocks['fields']['innerBlocks'][0]['innerBlocks'] ?? [];
	}
}