<?php

/**
 * NotionBuilder Oauth class.
 *
 * @package EightshiftForms\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Notionbuilder;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Oauth\AbstractOauth;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * OauthNotionbuilder class.
 */
class OauthNotionbuilder extends AbstractOauth
{
	/**
	 * Get authorization URL based on the provider Id.
	 *
	 * @return string
	 */
	public function getOauthAuthorizeUrl(): string
	{
		$clientId = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientIdNotionBuilder(), SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_CLIENT_ID);
		$clientSlug = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSlugNotionBuilder(), SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_CLIENT_SLUG);

		return add_query_arg(
			[
				'response_type' => 'code',
				'client_id' => $clientId,
				'redirect_uri' => $this->getRedirectUri(SettingsNotionbuilder::SETTINGS_TYPE_KEY),
			],
			"https://{$clientSlug}.nationbuilder.com/oauth/authorize"
		);
	}
}
