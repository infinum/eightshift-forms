<?php

/**
 * Class that holds Countries list.
 *
 * @package EightshiftForms\Countries
 */

declare(strict_types=1);

namespace EightshiftForms\Countries;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * Countries class.
 */
class Countries implements CountriesInterface
{
	/**
	 * Get countries data set depending on the provided filter and default set.
	 *
	 * @param bool $useFullOutput Used to output limited output used for seetings and output.
	 *
	 * @return array<string, mixed>
	 */
	public function getCountriesDataSet(bool $useFullOutput = true): array
	{
		$countries = $this->getCountriesList();

		$output = [
			'default' => [
				'label' => \__('Default', 'eightshift-forms'),
				'slug' => 'default',
				'items' => $countries,
				'codes' => \array_map(
					static function ($item) {
						return [
							'label' => $item[0],
							'value' => $item[1],
						];
					},
					$countries
				)
			]
		];

		$alternative = [];
		$filterName = UtilsHooksHelper::getFilterName(['block', 'country', 'alternativeDataSet']);
		if (\has_filter($filterName)) {
			$alternative = \apply_filters($filterName, []);
		}

		$alternativeOutput = [];

		if ($alternative) {
			foreach ($alternative as $value) {
				$label = $value['label'] ?? '';
				$slug = $value['slug'] ?? '';
				$removed = isset($value['remove']) ? \array_flip($value['remove']) : [];
				$onlyUse = isset($value['onlyUse']) ? \array_flip($value['onlyUse']) : [];
				$changed = $value['change'] ?? [];

				if (!$label || !$slug) {
					continue;
				}

				$slug = \strtolower(\str_replace(' ', '-', $slug));

				$alternativeOutput[$slug] = [
					'label' => $label,
					'slug' => $slug,
					'items' => $countries,
				];

				$itemOutput = [];

				foreach ($alternativeOutput[$slug]['items'] as $key => $item) {
					$countryCode = $item[1] ? \strtolower($item[1]) : '';

					// Only use.
					if ($onlyUse && !isset($onlyUse[$countryCode])) {
						continue;
					}

					// Remove item from list.
					if (isset($removed[$countryCode])) {
						continue;
					}

					// // Change label in the list.
					foreach ($changed as $changedKey => $changedValue) {
						if ($countryCode === $changedKey) {
							$item[0] = $changedValue;
						}
					}

					$itemOutput[] = $item;
				}


				$alternativeOutput[$slug]['items'] = $itemOutput;
			}
		}

		$output = \array_merge(
			$alternativeOutput,
			$output,
		);

		if ($useFullOutput) {
			return $output;
		}

		return [
			'label' => $output['default']['label'],
			'slug' => $output['default']['slug'],
			'items' => \array_values(\array_map(
				static function ($item) {
					return [
						'label' => $item['label'],
						'value' => $item['slug'],
					];
				},
				$output
			)),
			'codes' => $output['default']['codes'],
		];
	}

	/**
	 * Get countries list.
	 *
	 * @return array<mixed> List of countries.
	 */
	private function getCountriesList(): array
	{
		return [
			[
				\__('Afghanistan', 'eightshift-forms'),
				'af',
				'93'
			],
			[
				\__('Åland Islands', 'eightshift-forms'),
				'ax',
				'358'
			],
			[
				\__('Albania', 'eightshift-forms'),
				'al',
				'355'
			],
			[
				\__('Algeria', 'eightshift-forms'),
				'dz',
				'213'
			],
			[
				\__('American Samoa', 'eightshift-forms'),
				'as',
				'1684'
			],
			[
				\__('Andorra', 'eightshift-forms'),
				'ad',
				'376'
			],
			[
				\__('Angola', 'eightshift-forms'),
				'ao',
				'244'
			],
			[
				\__('Anguilla', 'eightshift-forms'),
				'ai',
				'1264'
			],
			[
				\__('Antarctica', 'eightshift-forms'),
				'aq',
				'672'
			],
			[
				\__('Antigua and Barbuda', 'eightshift-forms'),
				'ag',
				'1268'
			],
			[
				\__('Argentina', 'eightshift-forms'),
				'ar',
				'54'
			],
			[
				\__('Armenia', 'eightshift-forms'),
				'am',
				'374'
			],
			[
				\__('Aruba', 'eightshift-forms'),
				'aw',
				'297'
			],
			[
				\__('Australia', 'eightshift-forms'),
				'au',
				'61'
			],
			[
				\__('Austria', 'eightshift-forms'),
				'at',
				'43'
			],
			[
				\__('Azerbaijan', 'eightshift-forms'),
				'az',
				'994'
			],
			[
				\__('Bahamas', 'eightshift-forms'),
				'bs',
				'1242'
			],
			[
				\__('Bahrain', 'eightshift-forms'),
				'bh',
				'973'
			],
			[
				\__('Bangladesh', 'eightshift-forms'),
				'bd',
				'880'
			],
			[
				\__('Barbados', 'eightshift-forms'),
				'bb',
				'1246'
			],
			[
				\__('Belarus', 'eightshift-forms'),
				'by',
				'375'
			],
			[
				\__('Belgium', 'eightshift-forms'),
				'be',
				'32'
			],
			[
				\__('Belize', 'eightshift-forms'),
				'bz',
				'501'
			],
			[
				\__('Benin', 'eightshift-forms'),
				'bj',
				'229'
			],
			[
				\__('Bermuda', 'eightshift-forms'),
				'bm',
				'1441'
			],
			[
				\__('Bhutan', 'eightshift-forms'),
				'bt',
				'975'
			],
			[
				\__('Bolivia', 'eightshift-forms'),
				'bo',
				'591'
			],
			[
				\__('Bonaire', 'eightshift-forms'),
				'bq',
				'5997'
			],
			[
				\__('Bosnia and Herzegovina', 'eightshift-forms'),
				'ba',
				'387'
			],
			[
				\__('Botswana', 'eightshift-forms'),
				'bw',
				'267'
			],
			[
				\__('Brazil', 'eightshift-forms'),
				'br',
				'55'
			],
			[
				\__('British Indian Ocean Territory', 'eightshift-forms'),
				'io',
				'246'
			],
			[
				\__('Brunei Darussalam', 'eightshift-forms'),
				'bn',
				'673'
			],
			[
				\__('Bulgaria', 'eightshift-forms'),
				'bg',
				'359'
			],
			[
				\__('Burkina Faso', 'eightshift-forms'),
				'bf',
				'226'
			],
			[
				\__('Burundi', 'eightshift-forms'),
				'bi',
				'257'
			],
			[
				\__('Cambodia', 'eightshift-forms'),
				'kh',
				'855'
			],
			[
				\__('Cameroon', 'eightshift-forms'),
				'cm',
				'237'
			],
			[
				\__('Canada', 'eightshift-forms'),
				'ca',
				'1'
			],
			[
				\__('Cabo Verde', 'eightshift-forms'),
				'cv',
				'238'
			],
			[
				\__('Cayman Islands', 'eightshift-forms'),
				'ky',
				'1345'
			],
			[
				\__('Central African Republic', 'eightshift-forms'),
				'cf',
				'236'
			],
			[
				\__('Chad', 'eightshift-forms'),
				'td',
				'235'
			],
			[
				\__('Chile', 'eightshift-forms'),
				'cl',
				'56'
			],
			[
				\__('China', 'eightshift-forms'),
				'cn',
				'86'
			],
			[
				\__('Christmas Island', 'eightshift-forms'),
				'cx',
				'61'
			],
			[
				\__('Cocos (Keeling) Island', 'eightshift-forms'),
				'cc',
				'61'
			],
			[
				\__('Colombia', 'eightshift-forms'),
				'co',
				'57'
			],
			[
				\__('Comoros', 'eightshift-forms'),
				'km',
				'269'
			],
			[
				\__('Congo', 'eightshift-forms'),
				'cg',
				'242'
			],
			[
				\__('Congo (Democratic Republic)', 'eightshift-forms'),
				'cd',
				'243'
			],
			[
				\__('Cook Islands', 'eightshift-forms'),
				'ck',
				'682'
			],
			[
				\__('Costa Rica', 'eightshift-forms'),
				'cr',
				'506'
			],
			[
				\__("Côte d'Ivoire", 'eightshift-forms'),
				'ci',
				'225'
			],
			[
				\__('Croatia', 'eightshift-forms'),
				'hr',
				'385'
			],
			[
				\__('Curaçao', 'eightshift-forms'),
				'cw',
				'599'
			],
			[
				\__('Cyprus', 'eightshift-forms'),
				'cy',
				'357'
			],
			[
				\__('Czech Republic', 'eightshift-forms'),
				'cz',
				'420'
			],
			[
				\__('Denmark', 'eightshift-forms'),
				'dk',
				'45'
			],
			[
				\__('Djibouti', 'eightshift-forms'),
				'dj',
				'253'
			],
			[
				\__('Dominica', 'eightshift-forms'),
				'dm',
				'1767'
			],
			[
				\__('Dominican Republic', 'eightshift-forms'),
				'do',
				'1'
			],
			[
				\__('Ecuador', 'eightshift-forms'),
				'ec',
				'593'
			],
			[
				\__('Egypt', 'eightshift-forms'),
				'eg',
				'20'
			],
			[
				\__('El Salvador', 'eightshift-forms'),
				'sv',
				'503'
			],
			[
				\__('Equatorial Guinea', 'eightshift-forms'),
				'gq',
				'240'
			],
			[
				\__('Eritrea', 'eightshift-forms'),
				'er',
				'291'
			],
			[
				\__('Estonia', 'eightshift-forms'),
				'ee',
				'372'
			],
			[
				\__('Eswatini', 'eightshift-forms'),
				'sz',
				'268'
			],
			[
				\__('Ethiopia', 'eightshift-forms'),
				'et',
				'251'
			],
			[
				\__('Falkland Islands', 'eightshift-forms'),
				'fk',
				'500'
			],
			[
				\__('Faroe Islands', 'eightshift-forms'),
				'fo',
				'298'
			],
			[
				\__('Fiji', 'eightshift-forms'),
				'fj',
				'679'
			],
			[
				\__('Finland', 'eightshift-forms'),
				'fi',
				'358'
			],
			[
				\__('France', 'eightshift-forms'),
				'fr',
				'33'
			],
			[
				\__('French Guiana', 'eightshift-forms'),
				'gf',
				'594'
			],
			[
				\__('French Polynesia', 'eightshift-forms'),
				'pf',
				'689'
			],
			[
				\__('French Southern Territories', 'eightshift-forms'),
				'tf',
				'262'
			],
			[
				\__('Gabon', 'eightshift-forms'),
				'ga',
				'241'
			],
			[
				\__('Gambia', 'eightshift-forms'),
				'gm',
				'220'
			],
			[
				\__('Georgia', 'eightshift-forms'),
				'ge',
				'995'
			],
			[
				\__('Germany', 'eightshift-forms'),
				'de',
				'49'
			],
			[
				\__('Ghana', 'eightshift-forms'),
				'gh',
				'233'
			],
			[
				\__('Gibraltar', 'eightshift-forms'),
				'gi',
				'350'
			],
			[
				\__('Greece', 'eightshift-forms'),
				'gr',
				'30'
			],
			[
				\__('Greenland', 'eightshift-forms'),
				'gl',
				'299'
			],
			[
				\__('Grenada', 'eightshift-forms'),
				'gd',
				'1473'
			],
			[
				\__('Guadeloupe', 'eightshift-forms'),
				'gp',
				'590'
			],
			[
				\__('Guam', 'eightshift-forms'),
				'gu',
				'1'
			],
			[
				\__('Guatemala', 'eightshift-forms'),
				'gt',
				'502'
			],
			[
				\__('Guernsey', 'eightshift-forms'),
				'gg',
				'44'
			],
			[
				\__('Guinea', 'eightshift-forms'),
				'gn',
				'224'
			],
			[
				\__('Guinea-Bissau', 'eightshift-forms'),
				'gw',
				'245'
			],
			[
				\__('Guyana', 'eightshift-forms'),
				'gy',
				'592'
			],
			[
				\__('Haiti', 'eightshift-forms'),
				'ht',
				'509'
			],
			[
				\__('Holy See', 'eightshift-forms'),
				'va',
				'39'
			],
			[
				\__('Honduras', 'eightshift-forms'),
				'hn',
				'504'
			],
			[
				\__('Hong Kong', 'eightshift-forms'),
				'hk',
				'852'
			],
			[
				\__('Hungary', 'eightshift-forms'),
				'hu',
				'36'
			],
			[
				\__('Iceland', 'eightshift-forms'),
				'is',
				'354'
			],
			[
				\__('India', 'eightshift-forms'),
				'in',
				'91'
			],
			[
				\__('Indonesia', 'eightshift-forms'),
				'id',
				'62'
			],
			[
				\__('Iraq', 'eightshift-forms'),
				'iq',
				'964'
			],
			[
				\__('Ireland', 'eightshift-forms'),
				'ie',
				'353'
			],
			[
				\__('Isle of Man', 'eightshift-forms'),
				'im',
				'44'
			],
			[
				\__('Israel', 'eightshift-forms'),
				'il',
				'972'
			],
			[
				\__('Italy', 'eightshift-forms'),
				'it',
				'39'
			],
			[
				\__('Jamaica', 'eightshift-forms'),
				'jm',
				'1'
			],
			[
				\__('Japan', 'eightshift-forms'),
				'jp',
				'81'
			],
			[
				\__('Jersey', 'eightshift-forms'),
				'je',
				'44'
			],
			[
				\__('Jordan', 'eightshift-forms'),
				'jo',
				'962'
			],
			[
				\__('Kazakhstan', 'eightshift-forms'),
				'kz',
				'7'
			],
			[
				\__('Kenya', 'eightshift-forms'),
				'ke',
				'254'
			],
			[
				\__('Kiribati', 'eightshift-forms'),
				'ki',
				'686'
			],
			[
				\__('Korea, the Republic of', 'eightshift-forms'),
				'kr',
				'82'
			],
			[
				\__('Kuwait', 'eightshift-forms'),
				'kw',
				'965'
			],
			[
				\__('Kyrgyzstan', 'eightshift-forms'),
				'kg',
				'996'
			],
			[
				\__("Lao People's Democratic Republic", 'eightshift-forms'),
				'la',
				'856'
			],
			[
				\__('Latvia', 'eightshift-forms'),
				'lv',
				'371'
			],
			[
				\__('Lebanon', 'eightshift-forms'),
				'lb',
				'961'
			],
			[
				\__('Lesotho', 'eightshift-forms'),
				'ls',
				'266'
			],
			[
				\__('Liberia', 'eightshift-forms'),
				'lr',
				'231'
			],
			[
				\__('Libya', 'eightshift-forms'),
				'ly',
				'218'
			],
			[
				\__('Liechtenstein', 'eightshift-forms'),
				'li',
				'423'
			],
			[
				\__('Lithuania', 'eightshift-forms'),
				'lt',
				'370'
			],
			[
				\__('Luxembourg', 'eightshift-forms'),
				'lu',
				'352'
			],
			[
				\__('Macao', 'eightshift-forms'),
				'mo',
				'853'
			],
			[
				\__('Madagascar', 'eightshift-forms'),
				'mg',
				'261'
			],
			[
				\__('Malawi', 'eightshift-forms'),
				'mw',
				'265'
			],
			[
				\__('Malaysia', 'eightshift-forms'),
				'my',
				'60'
			],
			[
				\__('Maldives', 'eightshift-forms'),
				'mv',
				'960'
			],
			[
				\__('Mali', 'eightshift-forms'),
				'ml',
				'223'
			],
			[
				\__('Malta', 'eightshift-forms'),
				'mt',
				'356'
			],
			[
				\__('Marshall Islands', 'eightshift-forms'),
				'mh',
				'692'
			],
			[
				\__('Martinique', 'eightshift-forms'),
				'mq',
				'596'
			],
			[
				\__('Mauritania', 'eightshift-forms'),
				'mr',
				'222'
			],
			[
				\__('Mauritius', 'eightshift-forms'),
				'mu',
				'230'
			],
			[
				\__('Mayotte', 'eightshift-forms'),
				'yt',
				'262'
			],
			[
				\__('Mexico', 'eightshift-forms'),
				'mx',
				'52'
			],
			[
				\__('Micronesia (Federated States of)', 'eightshift-forms'),
				'fm',
				'691'
			],
			[
				\__('Moldova', 'eightshift-forms'),
				'md',
				'373'
			],
			[
				\__('Monaco', 'eightshift-forms'),
				'mc',
				'377'
			],
			[
				\__('Mongolia', 'eightshift-forms'),
				'mn',
				'976'
			],
			[
				\__('Montenegro', 'eightshift-forms'),
				'me',
				'382'
			],
			[
				\__('Montserrat', 'eightshift-forms'),
				'ms',
				'1664'
			],
			[
				\__('Morocco', 'eightshift-forms'),
				'ma',
				'212'
			],
			[
				\__('Mozambique', 'eightshift-forms'),
				'mz',
				'258'
			],
			[
				\__('Myanmar', 'eightshift-forms'),
				'mm',
				'95'
			],
			[
				\__('Namibia', 'eightshift-forms'),
				'na',
				'264'
			],
			[
				\__('Nauru', 'eightshift-forms'),
				'nr',
				'674'
			],
			[
				\__('Nepal', 'eightshift-forms'),
				'np',
				'977'
			],
			[
				\__('Netherlands', 'eightshift-forms'),
				'nl',
				'31'
			],
			[
				\__('New Caledonia', 'eightshift-forms'),
				'nc',
				'687'
			],
			[
				\__('New Zealand', 'eightshift-forms'),
				'nz',
				'64'
			],
			[
				\__('Nicaragua', 'eightshift-forms'),
				'ni',
				'505'
			],
			[
				\__('Niger', 'eightshift-forms'),
				'ne',
				'227'
			],
			[
				\__('Nigeria', 'eightshift-forms'),
				'ng',
				'234'
			],
			[
				\__('Niue', 'eightshift-forms'),
				'nu',
				'683'
			],
			[
				\__('Norfolk Island', 'eightshift-forms'),
				'nf',
				'672'
			],
			[
				\__('North Macedonia', 'eightshift-forms'),
				'mk',
				'389'
			],
			[
				\__('Northern Mariana Islands', 'eightshift-forms'),
				'mp',
				'1670'
			],
			[
				\__('Norway', 'eightshift-forms'),
				'no',
				'47'
			],
			[
				\__('Oman', 'eightshift-forms'),
				'om',
				'968'
			],
			[
				\__('Pakistan', 'eightshift-forms'),
				'pk',
				'92'
			],
			[
				\__('Palau', 'eightshift-forms'),
				'pw',
				'680'
			],
			[
				\__('Palestine', 'eightshift-forms'),
				'ps',
				'970'
			],
			[
				\__('Panama', 'eightshift-forms'),
				'pa',
				'507'
			],
			[
				\__('Papua New Guinea', 'eightshift-forms'),
				'pg',
				'675'
			],
			[
				\__('Paraguay', 'eightshift-forms'),
				'py',
				'595'
			],
			[
				\__('Peru', 'eightshift-forms'),
				'pe',
				'51'
			],
			[
				\__('Philippines', 'eightshift-forms'),
				'ph',
				'63'
			],
			[
				\__('Pitcairn', 'eightshift-forms'),
				'pn',
				'872'
			],
			[
				\__('Poland', 'eightshift-forms'),
				'pl',
				'48'
			],
			[
				\__('Portugal', 'eightshift-forms'),
				'pt',
				'351'
			],
			[
				\__('Puerto Rico', 'eightshift-forms'),
				'pr',
				'1'
			],
			[
				\__('Qatar', 'eightshift-forms'),
				'qa',
				'974'
			],
			[
				\__('Réunion', 'eightshift-forms'),
				're',
				'262'
			],
			[
				\__('Romania', 'eightshift-forms'),
				'ro',
				'40'
			],
			[
				\__('Russian Federation', 'eightshift-forms'),
				'ru',
				'7'
			],
			[
				\__('Rwanda', 'eightshift-forms'),
				'rw',
				'250'
			],
			[
				\__('Saba', 'eightshift-forms'),
				'bg-sa',
				'5994'
			],
			[
				\__('Saint Barthélemy', 'eightshift-forms'),
				'bl',
				'590'
			],
			[
				\__('Saint Helena', 'eightshift-forms'),
				'sh',
				'290'
			],
			[
				\__('Saint Kitts and Nevis', 'eightshift-forms'),
				'kn',
				'1869'
			],
			[
				\__('Saint Lucia', 'eightshift-forms'),
				'lc',
				'1758'
			],
			[
				\__('Saint Martin (French part)', 'eightshift-forms'),
				'mf',
				'590'
			],
			[
				\__('Saint Pierre and Miquelon', 'eightshift-forms'),
				'pm',
				'508'
			],
			[
				\__('Saint Vincent and The Grenadines', 'eightshift-forms'),
				'vc',
				'1784'
			],
			[
				\__('Samoa', 'eightshift-forms'),
				'ws',
				'685'
			],
			[
				\__('San Marino', 'eightshift-forms'),
				'sm',
				'378'
			],
			[
				\__('Sao Tome and Principe', 'eightshift-forms'),
				'st',
				'239'
			],
			[
				\__('Saudi Arabia', 'eightshift-forms'),
				'sa',
				'966'
			],
			[
				\__('Senegal', 'eightshift-forms'),
				'sn',
				'221'
			],
			[
				\__('Serbia', 'eightshift-forms'),
				'rs',
				'381'
			],
			[
				\__('Seychelles', 'eightshift-forms'),
				'sc',
				'248'
			],
			[
				\__('Sierra Leone', 'eightshift-forms'),
				'sl',
				'232'
			],
			[
				\__('Singapore', 'eightshift-forms'),
				'sg',
				'65'
			],
			[
				\__('Sint Maarten', 'eightshift-forms'),
				'sx',
				'1721'
			],
			[
				\__('Slovakia', 'eightshift-forms'),
				'sk',
				'421'
			],
			[
				\__('Slovenia', 'eightshift-forms'),
				'si',
				'386'
			],
			[
				\__('Solomon Islands', 'eightshift-forms'),
				'sb',
				'677'
			],
			[
				\__('Somalia', 'eightshift-forms'),
				'so',
				'252'
			],
			[
				\__('South Africa', 'eightshift-forms'),
				'za',
				'27'
			],
			[
				\__('South Georgia and the South Sandwich Islands', 'eightshift-forms'),
				'gs',
				'500'
			],
			[
				\__('South Sudan', 'eightshift-forms'),
				'ss',
				'211'
			],
			[
				\__('Spain', 'eightshift-forms'),
				'es',
				'34'
			],
			[
				\__('Sri Lanka', 'eightshift-forms'),
				'lk',
				'94'
			],
			[
				\__('Sudan', 'eightshift-forms'),
				'sd',
				'249'
			],
			[
				\__('Suriname', 'eightshift-forms'),
				'sr',
				'597'
			],
			[
				\__('Svalbard', 'eightshift-forms'),
				'sj',
				'47'
			],
			[
				\__('Sweden', 'eightshift-forms'),
				'se',
				'46'
			],
			[
				\__('Switzerland', 'eightshift-forms'),
				'ch',
				'41'
			],
			[
				\__('Taiwan, China', 'eightshift-forms'),
				'tw',
				'886'
			],
			[
				\__('Tajikistan', 'eightshift-forms'),
				'tj',
				'992'
			],
			[
				\__('Tanzania', 'eightshift-forms'),
				'tz',
				'255'
			],
			[
				\__('Thailand', 'eightshift-forms'),
				'th',
				'66'
			],
			[
				\__('Timor-Leste', 'eightshift-forms'),
				'tl',
				'670'
			],
			[
				\__('Togo', 'eightshift-forms'),
				'tg',
				'228'
			],
			[
				\__('Tokelau', 'eightshift-forms'),
				'tk',
				'690'
			],
			[
				\__('Tonga', 'eightshift-forms'),
				'to',
				'676'
			],
			[
				\__('Trinidad and Tobago', 'eightshift-forms'),
				'tt',
				'1868'
			],
			[
				\__('Tunisia', 'eightshift-forms'),
				'tn',
				'216'
			],
			[
				\__('Turkey', 'eightshift-forms'),
				'tr',
				'90'
			],
			[
				\__('Turkmenistan', 'eightshift-forms'),
				'tm',
				'993'
			],
			[
				\__('Turks and Caicos Islands', 'eightshift-forms'),
				'tc',
				'1649'
			],
			[
				\__('Tuvalu', 'eightshift-forms'),
				'tv',
				'688'
			],
			[
				\__('Uganda', 'eightshift-forms'),
				'ug',
				'256'
			],
			[
				\__('Ukraine', 'eightshift-forms'),
				'ua',
				'380'
			],
			[
				\__('United Arab Emirates', 'eightshift-forms'),
				'ae',
				'971'
			],
			[
				\__('United Kingdom', 'eightshift-forms'),
				'gb',
				'44'
			],
			[
				\__('United States', 'eightshift-forms'),
				'us',
				'1'
			],
			[
				\__('United States Minor Outlying Islands', 'eightshift-forms'),
				'um',
				'699'
			],
			[
				\__('Uruguay', 'eightshift-forms'),
				'uy',
				'598'
			],
			[
				\__('Uzbekistan', 'eightshift-forms'),
				'uz',
				'998'
			],
			[
				\__('Vanuatu', 'eightshift-forms'),
				'vu',
				'678'
			],
			[
				\__('Venezuela', 'eightshift-forms'),
				've',
				'58'
			],
			[
				\__('Vietnam', 'eightshift-forms'),
				'vn',
				'84'
			],
			[
				\__('Virgin Islands (British)', 'eightshift-forms'),
				'vg',
				'1284'
			],
			[
				\__('Virgin Islands (U.S.)', 'eightshift-forms'),
				'vi',
				'1340'
			],
			[
				\__('Wallis and Futuna', 'eightshift-forms'),
				'wf',
				'681'
			],
			[
				\__('Western Sahara', 'eightshift-forms'),
				'eh',
				'212'
			],
			[
				\__('Yemen', 'eightshift-forms'),
				'ye',
				'967'
			],
			[
				\__('Zambia', 'eightshift-forms'),
				'zm',
				'260'
			],
			[
				\__('Zimbabwe', 'eightshift-forms'),
				'zw',
				'263'
			]
		];
	}
}
