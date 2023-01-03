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
use EightshiftForms\Form\AbstractFormBuilder;
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

		$content = $this->prepareContentBlocks($this->getFormContent($formId));

		if (!$content) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$contentType = $content['type'] ?? '';
		$contentItemId = $content['itemId'] ? Helper::unserializeAttributes($content['itemId']) : '';
		$contentFields = $content['fields'] ?? [];

		if (!$contentType) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content integration type block.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		if (!$contentItemId) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Missing form content integration item Id.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$integration = $this->hubspot->getFormBlockGrammarArray($formId, $contentItemId);
		$integrationFields = $integration['fields'] ?? [];

		if (!$contentFields) {
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

		if ($integration['type'] !== $content['type']) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Integration type is different than content type.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		if ($integration['itemId'] !== $content['itemId']) {
			return \rest_ensure_response([
				'code' => 400,
				'status' => 'error',
				'message' => \esc_html__('Integration item ID is different than content item ID.', 'eightshift-forms'),
				'update' => false,
				'data' => [],
			]);
		}

		$output = $this->diffChanges($integration, $content);

		if ($output['output']) {
			$a = $this->updateBlockContent($formId, $output['output']);

			error_log( print_r( ( $a ), true ) );
			
		}

		// Exit with success.
		return \rest_ensure_response([
			'code' => 200,
			'status' => 'success',
			'message' => \esc_html__('Form updated.', 'eightshift-forms'),
			'update' => true,
			'data' => $this->diffChanges($integration, $content),
		]);
	}

	/**
	 * Undocumented function
	 *
	 * @param array $integrationBlocks
	 * @param array $contentBlocks
	 * @return array
	 */
	private function diffChanges(array $integration, array $content): array
	{
		$diff = $this->prepareContentBlocksForCheck($content['fields']['innerBlocks'][0]['innerBlocks'] ?? [], $this->prepareIntegrationBlocksForCheck($integration['fields']));

		$output = [
			'type' => $integration['type'],
			'itemId' => $integration['itemId'],
			'removed' => [],
			'added' => [],
			'replaced' => [],
			'changed' => [],
			'output' => [],
		];

		foreach ($diff as $key => $block) {
			$changes = $this->diffInnerChanges($block['integration'] ?? [], $block['content'] ?? [], $key);

			if ($changes['removed']) {
				$output['removed'][] = $changes['removed'];
			}
			if ($changes['added']) {
				$output['added'][] = $changes['added'];
			}
			if ($changes['replaced']) {
				$output['replaced'][] = $changes['replaced'];
			}
			if ($changes['changed']) {
				$output['changed'][] = $changes['changed'];
			}
			if ($changes['output']) {
				$output['output'][$key] = $changes['output'];
			}
		}

		$blocksOutput = $this->reconstructFieldsOutput($output['output'], $output['type'], $output['itemId']);
		$output['output'] = serialize_blocks($blocksOutput);

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		if ($isDeveloperMode) {
			$output['outputPure'] = $blocksOutput;
			$output['diff'] = $diff;
		}

		return $output;
	}

	private function diffInnerChanges(array $integration, array $content, string $key): array
	{
		$output = [
			'removed' => [],
			'added' => [],
			'replaced' => [],
			'changed' => [],
			'output' => [],
		];

		// Remove item if block is not present on integration, output nothing.
		if (!$integration) {
			$output['removed'] = $key;
			return $output;
		}

		// If field exists on the integration but not on the content add it.
		if (!$content) {
			$output['added'] = $key;
			$output['output'] = $integration;
			return $output;
		}

		// If field type has changed on integration use the integration one.
		if ($integration['component'] !== $content['component']) {
			$output['replaced'] = $key;
			$output['output'] = $integration;
			return $output;
		}

		// Check if disabled attrs changed.
		$innerOutput = $content;
		$prefix = $integration['component'];
		$disabledOptions = $integration['attrs']["{$prefix}DisabledOptions"] ?? [];

		if ($disabledOptions) {
			foreach ($disabledOptions as $disabledOption) {
				$i = $integration['attrs'][$disabledOption] ?? '';
				$c = $content['attrs'][$disabledOption] ?? '';

				// If intregration is missing disabled or protected attribute. Thhere chould be and issue in the mapping of component attributes for integration.
				if (!$i) {
					$output['replaced'] = $key;
					$output['output'] = $integration;
					break;
				}

				// If content has missing disabled or protected attribute add it from integration.
				if (!$c) {
					$output['changed'][$key][] = $disabledOption;
					$innerOutput['attrs'][$disabledOption] = $i;
					break;
				}

				// If values of attribute in content and intregation are diffrerent do something.
				if ($i !== $c) {
					// If protected attribute name has changed we need to update the whole block. This is an unlikely scenario but it can happen.
					if ($i === "{$prefix}Name" && $c === "{$prefix}Name") {
						$output['replaced'] = $key;
						$output['output'] = $integration;
						break;
					}

					// Output the changed value.
					$output['changed'][$key][] = $disabledOption;
					$innerOutput['attrs'][$disabledOption] = $i;
					continue;
				}
			}
		}

		$output['output'] = $content;

		return $output;
	}

	private function prepareIntegrationBlocksForCheck(array $blocks): array
	{
		$output = [];

		$nestedKeys = array_flip(AbstractFormBuilder::NESTED_KEYS);
		$namespace = Components::getSettingsNamespace();

		foreach ($blocks as $key => $block) {
			$blockTypeOriginal = $block['component'] ?? '';

			if (!$blockTypeOriginal) {
				continue;
			}

			$blockType = Components::kebabToCamelCase($blockTypeOriginal, '-');
			$blockName = "{$blockType}Name";

			$name = $block[$blockName] ?? '';

			if (!$name) {
				continue;
			}

			$output[$name]['integration']  = [
				'namespace' => $namespace,
				'component' => $blockTypeOriginal,
				'prefix' => $blockType . ucfirst($blockType),
				'attrs' => $this->prepareBlockAttributes($block, $blockType),
				'parent' => '',
				'order' => $key,
			];

			$innerBlocks = \array_intersect_key($block, $nestedKeys);

			if ($innerBlocks) {
				foreach (\reset($innerBlocks) as $innerKey => $innerBlock) {
					$blockInnerTypeOriginal = $innerBlock['component'] ?? '';

					if (!$blockInnerTypeOriginal) {
						continue;
					}

					$blockInnerType = Components::kebabToCamelCase($blockInnerTypeOriginal, '-');

					$output["{$name}---{$innerKey}"]['integration']  = [
						'namespace' => $namespace,
						'component' => $blockInnerTypeOriginal,
						'prefix' => $blockInnerType . ucfirst($blockInnerType),
						'attrs' => $this->prepareBlockAttributes($innerBlock, $blockInnerType),
						'parent' => $name,
						'order' => $innerKey,
					];
				}
			}
		}

		return $output;
	}

	private function prepareContentBlocksForCheck(array $blocks, $integration): array
	{
		$output = $integration;

		foreach ($blocks as $key => $block) {
			$blockTypeOriginal = $block['blockName'] ?? '';

			if (!$blockTypeOriginal) {
				continue;
			}

			$blockType = $this->getBlockAttributePrefixByFullBlockName($blockTypeOriginal);
			$blockName = $blockType['prefix']. "Name";

			if (!$block['attrs']) {
				continue;
			}

			$name = $block['attrs'][$blockName] ?? '';

			if (!$name) {
				continue;
			}

			$block['attrs'] = array_filter($block['attrs']);

			$output[$name]['content']  = [
				'namespace' => $blockType['namespace'],
				'component' => $blockType['component'],
				'prefix' => $blockType['prefix'],
				'attrs' => $block['attrs'],
				'parent' => '',
				'order' => $key,
			];

			if (isset($block['innerBlocks'])) {
				foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
					$blockInnerType = $innerBlock['blockName'] ?? '';

					if (!$blockInnerType) {
						continue;
					}

					$blockInnerType = $this->getBlockAttributePrefixByFullBlockName($blockInnerType);

					$output["{$name}---{$innerKey}"]['content'] = [
						'namespace' => $blockInnerType['namespace'],
						'component' => $blockInnerType['component'],
						'prefix' => $blockInnerType['prefix'],
						'attrs' => $innerBlock['attrs'],
						'parent' => $name,
						'order' => $innerKey,
					];
				}
			}
		}

		return $output;
	}

	private function reconstructFieldsOutput(array $data, string $type, string $itemId): array
	{
		$fieldsOutput = [];

		foreach ($data as $key => $value) {
			// TO DO: remove this.
			if ($value['component'] === 'rich-text') {
				continue;
			}

			if (!$value['parent']) {
				$fieldsOutput[$key] = [
					'blockName' => $value['namespace'] . '/' . $value['component'],
					'attrs' => $value['attrs'],
					'innerBlocks' => [],
					'innerContent' => [],
				];
			} else {
				$innerOutput = [
					'blockName' => $value['namespace'] . '/' . $value['component'],
					'attrs' => $value['attrs'],
					'innerBlocks' => [],
					'innerContent' => [],
				];

				$fieldsOutput[$value['parent']]['innerBlocks'][] = $innerOutput;
				$fieldsOutput[$value['parent']]['innerContent'][] = $innerOutput;
			}
		}

		$fieldsOutput = array_values($fieldsOutput);

		$namespace = Components::getSettingsNamespace();

		$innerBlock = [
			[
				'blockName' => "{$namespace}/" . $type,
				'attrs' => [
					$type . "IntegrationId" => $itemId,
				],
				'innerBlocks' => $fieldsOutput,
				'innerContent' => $fieldsOutput,
			],
		];

		return [
			[
				'blockName' => "{$namespace}/form-selector",
				'attrs' => [],
				'innerBlocks' => $innerBlock,
				'innerContent' => $innerBlock,
			],
		];
	}

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

	private function updateBlockContent(string $id, $content)
	{
		return wp_update_post([
			'ID' => $id,
			'post_content' => wp_slash($content),
		 ]);
	}

	private function prepareContentBlocks(array $blocks): array
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

		$output['itemId'] = $itemId;
		$output['fields'] = $blocks;

		return $output;
	}

	private function prepareBlockAttributes($attributes, $component) {
		$output = [];

		$nestedKeys = array_flip(AbstractFormBuilder::NESTED_KEYS);

		foreach ($attributes as $key => $value) {
			if ($key === 'component') {
				continue;
			}

			if (!$value) {
				continue;
			}

			if (isset($nestedKeys[$key])) {
				continue;
			}

			if ($key === "{$component}DisabledOptions") {
				$value = array_values(array_map(
					static function($item) use ($component) {
						return "{$component}" . ucfirst($item);
					},
					$value
				));
			}

			$output[$component . ucfirst($key)] = $value;
		}

		return $output;
	}

	/**
	 * Get Block attribute prefix by full block name.
	 *
	 * @param string $blockName Block name to check.
	 *
	 * @return string
	 */
	private function getBlockAttributePrefixByFullBlockName(string $name): array
	{
		$block = \explode('/', $name);
		$blockName = \end($block);

		$component = Components::kebabToCamelCase($blockName, '-');

		return [
			'namespace' => $block[0],
			'component' => $blockName,
			'prefix' => "{$component}" . ucfirst($component),
		];
	}
}
