<?php

/**
 * Class that holds Countries list.
 *
 * @package EightshiftForms\Countries
 */

declare(strict_types=1);

namespace EightshiftForms\Countries;

use EightshiftForms\Helpers\HooksHelpers;

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
		$countries = \apply_filters(
			HooksHelpers::getFilterName(['block', 'country', 'modifyDataSet']),
			$this->getCountriesList()
		);

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
							'unlocalized-label' => $item[3] ?? $item[0],
							'code' => $item[1],
							'phone' => $item[2],
						];
					},
					$countries
				)
			]
		];

		$alternative = [];
		$filterName = HooksHelpers::getFilterName(['block', 'country', 'alternativeDataSet']);
		if (\has_filter($filterName)) {
			$alternative = \apply_filters($filterName, []);
		}

		$alternativeOutput = [];

		if ($alternative) {
			foreach ($alternative as $value) {
				$label = $value['label'] ?? '';
				$slug = $value['slug'] ?? '';
				if (!$label || !$slug) {
					continue;
				}

				$slug = \strtolower(\str_replace(' ', '-', $slug));
				$alternativeOutput[$slug] = [
					'label' => $label,
					'slug' => $slug,
					'items' => [],
				];

				$removed = $value['remove'] ?? [];
				$onlyUse = $value['onlyUse'] ?? [];
				$changed = $value['change'] ?? [];

				foreach ($countries as $item) {
					$countryCode = \strtolower($item[1]);

					if ((!empty($onlyUse) && !\in_array($countryCode, $onlyUse, true)) || \in_array($countryCode, $removed, true)) {
						continue;
					}

					if (\array_key_exists($countryCode, $changed)) {
						$item[0] = $changed[$countryCode];
					}

					$alternativeOutput[$slug]['items'][] = $item;
				}
			}
		}

		$output = \array_merge(
			$alternativeOutput,
			$output,
		);

		$output = $this->customOrder($output);

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
	 * Custom order countries.
	 *
	 * @param array<string, mixed> $output Output array.
	 *
	 * @return array<string, mixed>
	 */
	private function customOrder(array $output): array
	{
		$data = [];
		$filterName = HooksHelpers::getFilterName(['block', 'country', 'customOrder']);
		if (\has_filter($filterName)) {
			$data = \apply_filters($filterName, []);
		}

		if (!$data) {
			return $output;
		}

		foreach ($data as $key => $value) {
			if (!isset($output[$key])) {
				continue;
			}

			$items = $output[$key]['items'] ?? [];

			if (!$items) {
				continue;
			}

			$ordered = [];
			$remaining = [];

			foreach ($items as $item) {
				$code = $item[1] ?? '';

				if (!$code) {
					continue;
				}

				// Check if the second element matches any key in the provided keys.
				if (\in_array($item[1], $value, true)) {
					$ordered[] = $item;
				} else {
					$remaining[] = $item;
				}
			}

			$output[$key]['items'] = \array_merge($ordered, $remaining);
		}

		return $output;
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
				'93',
				'Afghanistan'
			],
			[
				\__('Åland Islands', 'eightshift-forms'),
				'ax',
				'358',
				'Åland Islands'
			],
			[
				\__('Albania', 'eightshift-forms'),
				'al',
				'355',
				'Albania'
			],
			[
				\__('Algeria', 'eightshift-forms'),
				'dz',
				'213',
				'Algeria'
			],
			[
				\__('American Samoa', 'eightshift-forms'),
				'as',
				'1684',
				'American Samoa'
			],
			[
				\__('Andorra', 'eightshift-forms'),
				'ad',
				'376',
				'Andorra'
			],
			[
				\__('Angola', 'eightshift-forms'),
				'ao',
				'244',
				'Angola'
			],
			[
				\__('Anguilla', 'eightshift-forms'),
				'ai',
				'1264',
				'Anguilla'
			],
			[
				\__('Antarctica', 'eightshift-forms'),
				'aq',
				'672',
				'Antarctica'
			],
			[
				\__('Antigua and Barbuda', 'eightshift-forms'),
				'ag',
				'1268',
				'Antigua and Barbuda'
			],
			[
				\__('Argentina', 'eightshift-forms'),
				'ar',
				'54',
				'Argentina'
			],
			[
				\__('Armenia', 'eightshift-forms'),
				'am',
				'374',
				'Armenia'
			],
			[
				\__('Aruba', 'eightshift-forms'),
				'aw',
				'297',
				'Aruba'
			],
			[
				\__('Australia', 'eightshift-forms'),
				'au',
				'61',
				'Australia'
			],
			[
				\__('Austria', 'eightshift-forms'),
				'at',
				'43',
				'Austria'
			],
			[
				\__('Azerbaijan', 'eightshift-forms'),
				'az',
				'994',
				'Azerbaijan'
			],
			[
				\__('Bahamas', 'eightshift-forms'),
				'bs',
				'1242',
				'Bahamas'
			],
			[
				\__('Bahrain', 'eightshift-forms'),
				'bh',
				'973',
				'Bahrain'
			],
			[
				\__('Bangladesh', 'eightshift-forms'),
				'bd',
				'880',
				'Bangladesh'
			],
			[
				\__('Barbados', 'eightshift-forms'),
				'bb',
				'1246',
				'Barbados'
			],
			[
				\__('Belarus', 'eightshift-forms'),
				'by',
				'375',
				'Belarus'
			],
			[
				\__('Belgium', 'eightshift-forms'),
				'be',
				'32',
				'Belgium'
			],
			[
				\__('Belize', 'eightshift-forms'),
				'bz',
				'501',
				'Belize'
			],
			[
				\__('Benin', 'eightshift-forms'),
				'bj',
				'229',
				'Benin'
			],
			[
				\__('Bermuda', 'eightshift-forms'),
				'bm',
				'1441',
				'Bermuda'
			],
			[
				\__('Bhutan', 'eightshift-forms'),
				'bt',
				'975',
				'Bhutan'
			],
			[
				\__('Bolivia', 'eightshift-forms'),
				'bo',
				'591',
				'Bolivia'
			],
			[
				\__('Bonaire', 'eightshift-forms'),
				'bq',
				'5997',
				'Bonaire'
			],
			[
				\__('Bosnia and Herzegovina', 'eightshift-forms'),
				'ba',
				'387',
				'Bosnia and Herzegovina'
			],
			[
				\__('Botswana', 'eightshift-forms'),
				'bw',
				'267',
				'Botswana'
			],
			[
				\__('Brazil', 'eightshift-forms'),
				'br',
				'55',
				'Brazil'
			],
			[
				\__('British Indian Ocean Territory', 'eightshift-forms'),
				'io',
				'246',
				'British Indian Ocean Territory'
			],
			[
				\__('Brunei Darussalam', 'eightshift-forms'),
				'bn',
				'673',
				'Brunei Darussalam'
			],
			[
				\__('Bulgaria', 'eightshift-forms'),
				'bg',
				'359',
				'Bulgaria'
			],
			[
				\__('Burkina Faso', 'eightshift-forms'),
				'bf',
				'226',
				'Burkina Faso'
			],
			[
				\__('Burundi', 'eightshift-forms'),
				'bi',
				'257',
				'Burundi'
			],
			[
				\__('Cambodia', 'eightshift-forms'),
				'kh',
				'855',
				'Cambodia'
			],
			[
				\__('Cameroon', 'eightshift-forms'),
				'cm',
				'237',
				'Cameroon'
			],
			[
				\__('Canada', 'eightshift-forms'),
				'ca',
				'1',
				'Canada'
			],
			[
				\__('Cabo Verde', 'eightshift-forms'),
				'cv',
				'238',
				'Cabo Verde'
			],
			[
				\__('Cayman Islands', 'eightshift-forms'),
				'ky',
				'1345',
				'Cayman Islands'
			],
			[
				\__('Central African Republic', 'eightshift-forms'),
				'cf',
				'236',
				'Central African Republic'
			],
			[
				\__('Chad', 'eightshift-forms'),
				'td',
				'235',
				'Chad'
			],
			[
				\__('Chile', 'eightshift-forms'),
				'cl',
				'56',
				'Chile'
			],
			[
				\__('China', 'eightshift-forms'),
				'cn',
				'86',
				'China'
			],
			[
				\__('Christmas Island', 'eightshift-forms'),
				'cx',
				'61',
				'Christmas Island'
			],
			[
				\__('Cocos (Keeling) Island', 'eightshift-forms'),
				'cc',
				'61',
				'Cocos (Keeling) Island'
			],
			[
				\__('Colombia', 'eightshift-forms'),
				'co',
				'57',
				'Colombia'
			],
			[
				\__('Comoros', 'eightshift-forms'),
				'km',
				'269',
				'Comoros'
			],
			[
				\__('Congo', 'eightshift-forms'),
				'cg',
				'242',
				'Congo'
			],
			[
				\__('Congo (Democratic Republic)', 'eightshift-forms'),
				'cd',
				'243',
				'Congo (Democratic Republic)'
			],
			[
				\__('Cook Islands', 'eightshift-forms'),
				'ck',
				'682',
				'Cook Islands'
			],
			[
				\__('Costa Rica', 'eightshift-forms'),
				'cr',
				'506',
				'Costa Rica'
			],
			[
				\__("Côte d'Ivoire", 'eightshift-forms'),
				'ci',
				'225'
			],
			[
				\__('Croatia', 'eightshift-forms'),
				'hr',
				'385',
				'Croatia'
			],
			[
				\__('Curaçao', 'eightshift-forms'),
				'cw',
				'599',
				'Curaçao'
			],
			[
				\__('Cyprus', 'eightshift-forms'),
				'cy',
				'357',
				'Cyprus'
			],
			[
				\__('Czech Republic', 'eightshift-forms'),
				'cz',
				'420',
				'Czech Republic'
			],
			[
				\__('Denmark', 'eightshift-forms'),
				'dk',
				'45',
				'Denmark'
			],
			[
				\__('Djibouti', 'eightshift-forms'),
				'dj',
				'253',
				'Djibouti'
			],
			[
				\__('Dominica', 'eightshift-forms'),
				'dm',
				'1767',
				'Dominica'
			],
			[
				\__('Dominican Republic', 'eightshift-forms'),
				'do',
				'1',
				'Dominican Republic'
			],
			[
				\__('Ecuador', 'eightshift-forms'),
				'ec',
				'593',
				'Ecuador'
			],
			[
				\__('Egypt', 'eightshift-forms'),
				'eg',
				'20',
				'Egypt'
			],
			[
				\__('El Salvador', 'eightshift-forms'),
				'sv',
				'503',
				'El Salvador'
			],
			[
				\__('Equatorial Guinea', 'eightshift-forms'),
				'gq',
				'240',
				'Equatorial Guinea'
			],
			[
				\__('Eritrea', 'eightshift-forms'),
				'er',
				'291',
				'Eritrea'
			],
			[
				\__('Estonia', 'eightshift-forms'),
				'ee',
				'372',
				'Estonia'
			],
			[
				\__('Eswatini', 'eightshift-forms'),
				'sz',
				'268',
				'Eswatini'
			],
			[
				\__('Ethiopia', 'eightshift-forms'),
				'et',
				'251',
				'Ethiopia'
			],
			[
				\__('Falkland Islands', 'eightshift-forms'),
				'fk',
				'500',
				'Falkland Islands'
			],
			[
				\__('Faroe Islands', 'eightshift-forms'),
				'fo',
				'298',
				'Faroe Islands'
			],
			[
				\__('Fiji', 'eightshift-forms'),
				'fj',
				'679',
				'Fiji'
			],
			[
				\__('Finland', 'eightshift-forms'),
				'fi',
				'358',
				'Finland'
			],
			[
				\__('France', 'eightshift-forms'),
				'fr',
				'33',
				'France'
			],
			[
				\__('French Guiana', 'eightshift-forms'),
				'gf',
				'594',
				'French Guiana'
			],
			[
				\__('French Polynesia', 'eightshift-forms'),
				'pf',
				'689',
				'French Polynesia'
			],
			[
				\__('French Southern Territories', 'eightshift-forms'),
				'tf',
				'262',
				'French Southern Territories'
			],
			[
				\__('Gabon', 'eightshift-forms'),
				'ga',
				'241',
				'Gabon'
			],
			[
				\__('Gambia', 'eightshift-forms'),
				'gm',
				'220',
				'Gambia'
			],
			[
				\__('Georgia', 'eightshift-forms'),
				'ge',
				'995',
				'Georgia'
			],
			[
				\__('Germany', 'eightshift-forms'),
				'de',
				'49',
				'Germany'
			],
			[
				\__('Ghana', 'eightshift-forms'),
				'gh',
				'233',
				'Ghana'
			],
			[
				\__('Gibraltar', 'eightshift-forms'),
				'gi',
				'350',
				'Gibraltar'
			],
			[
				\__('Greece', 'eightshift-forms'),
				'gr',
				'30',
				'Greece'
			],
			[
				\__('Greenland', 'eightshift-forms'),
				'gl',
				'299',
				'Greenland'
			],
			[
				\__('Grenada', 'eightshift-forms'),
				'gd',
				'1473',
				'Grenada'
			],
			[
				\__('Guadeloupe', 'eightshift-forms'),
				'gp',
				'590',
				'Guadeloupe'
			],
			[
				\__('Guam', 'eightshift-forms'),
				'gu',
				'1',
				'Guam'
			],
			[
				\__('Guatemala', 'eightshift-forms'),
				'gt',
				'502',
				'Guatemala'
			],
			[
				\__('Guernsey', 'eightshift-forms'),
				'gg',
				'44',
				'Guernsey'
			],
			[
				\__('Guinea', 'eightshift-forms'),
				'gn',
				'224',
				'Guinea'
			],
			[
				\__('Guinea-Bissau', 'eightshift-forms'),
				'gw',
				'245',
				'Guinea-Bissau'
			],
			[
				\__('Guyana', 'eightshift-forms'),
				'gy',
				'592',
				'Guyana'
			],
			[
				\__('Haiti', 'eightshift-forms'),
				'ht',
				'509',
				'Haiti'
			],
			[
				\__('Holy See', 'eightshift-forms'),
				'va',
				'39',
				'Holy See'
			],
			[
				\__('Honduras', 'eightshift-forms'),
				'hn',
				'504',
				'Honduras'
			],
			[
				\__('Hong Kong', 'eightshift-forms'),
				'hk',
				'852',
				'Hong Kong'
			],
			[
				\__('Hungary', 'eightshift-forms'),
				'hu',
				'36',
				'Hungary'
			],
			[
				\__('Iceland', 'eightshift-forms'),
				'is',
				'354',
				'Iceland'
			],
			[
				\__('India', 'eightshift-forms'),
				'in',
				'91',
				'India'
			],
			[
				\__('Indonesia', 'eightshift-forms'),
				'id',
				'62',
				'Indonesia'
			],
			[
				\__('Iraq', 'eightshift-forms'),
				'iq',
				'964',
				'Iraq'
			],
			[
				\__('Ireland', 'eightshift-forms'),
				'ie',
				'353',
				'Ireland'
			],
			[
				\__('Isle of Man', 'eightshift-forms'),
				'im',
				'44',
				'Isle of Man'
			],
			[
				\__('Israel', 'eightshift-forms'),
				'il',
				'972',
				'Israel'
			],
			[
				\__('Italy', 'eightshift-forms'),
				'it',
				'39',
				'Italy'
			],
			[
				\__('Jamaica', 'eightshift-forms'),
				'jm',
				'1',
				'Jamaica'
			],
			[
				\__('Japan', 'eightshift-forms'),
				'jp',
				'81',
				'Japan'
			],
			[
				\__('Jersey', 'eightshift-forms'),
				'je',
				'44',
				'Jersey'
			],
			[
				\__('Jordan', 'eightshift-forms'),
				'jo',
				'962',
				'Jordan'
			],
			[
				\__('Kazakhstan', 'eightshift-forms'),
				'kz',
				'7',
				'Kazakhstan'
			],
			[
				\__('Kenya', 'eightshift-forms'),
				'ke',
				'254',
				'Kenya'
			],
			[
				\__('Kiribati', 'eightshift-forms'),
				'ki',
				'686',
				'Kiribati'
			],
			[
				\__('Korea, the Republic of', 'eightshift-forms'),
				'kr',
				'82',
				'Korea, the Republic of'
			],
			[
				\__('Kuwait', 'eightshift-forms'),
				'kw',
				'965',
				'Kuwait'
			],
			[
				\__('Kyrgyzstan', 'eightshift-forms'),
				'kg',
				'996',
				'Kyrgyzstan'
			],
			[
				\__("Lao People's Democratic Republic", 'eightshift-forms'),
				'la',
				'856'
			],
			[
				\__('Latvia', 'eightshift-forms'),
				'lv',
				'371',
				'Latvia'
			],
			[
				\__('Lebanon', 'eightshift-forms'),
				'lb',
				'961',
				'Lebanon'
			],
			[
				\__('Lesotho', 'eightshift-forms'),
				'ls',
				'266',
				'Lesotho'
			],
			[
				\__('Liberia', 'eightshift-forms'),
				'lr',
				'231',
				'Liberia'
			],
			[
				\__('Libya', 'eightshift-forms'),
				'ly',
				'218',
				'Libya'
			],
			[
				\__('Liechtenstein', 'eightshift-forms'),
				'li',
				'423',
				'Liechtenstein'
			],
			[
				\__('Lithuania', 'eightshift-forms'),
				'lt',
				'370',
				'Lithuania'
			],
			[
				\__('Luxembourg', 'eightshift-forms'),
				'lu',
				'352',
				'Luxembourg'
			],
			[
				\__('Macao', 'eightshift-forms'),
				'mo',
				'853',
				'Macao'
			],
			[
				\__('Madagascar', 'eightshift-forms'),
				'mg',
				'261',
				'Madagascar'
			],
			[
				\__('Malawi', 'eightshift-forms'),
				'mw',
				'265',
				'Malawi'
			],
			[
				\__('Malaysia', 'eightshift-forms'),
				'my',
				'60',
				'Malaysia'
			],
			[
				\__('Maldives', 'eightshift-forms'),
				'mv',
				'960',
				'Maldives'
			],
			[
				\__('Mali', 'eightshift-forms'),
				'ml',
				'223',
				'Mali'
			],
			[
				\__('Malta', 'eightshift-forms'),
				'mt',
				'356',
				'Malta'
			],
			[
				\__('Marshall Islands', 'eightshift-forms'),
				'mh',
				'692',
				'Marshall Islands'
			],
			[
				\__('Martinique', 'eightshift-forms'),
				'mq',
				'596',
				'Martinique'
			],
			[
				\__('Mauritania', 'eightshift-forms'),
				'mr',
				'222',
				'Mauritania'
			],
			[
				\__('Mauritius', 'eightshift-forms'),
				'mu',
				'230',
				'Mauritius'
			],
			[
				\__('Mayotte', 'eightshift-forms'),
				'yt',
				'262',
				'Mayotte'
			],
			[
				\__('Mexico', 'eightshift-forms'),
				'mx',
				'52',
				'Mexico'
			],
			[
				\__('Micronesia (Federated States of)', 'eightshift-forms'),
				'fm',
				'691',
				'Micronesia (Federated States of)'
			],
			[
				\__('Moldova', 'eightshift-forms'),
				'md',
				'373',
				'Moldova'
			],
			[
				\__('Monaco', 'eightshift-forms'),
				'mc',
				'377',
				'Monaco'
			],
			[
				\__('Mongolia', 'eightshift-forms'),
				'mn',
				'976',
				'Mongolia'
			],
			[
				\__('Montenegro', 'eightshift-forms'),
				'me',
				'382',
				'Montenegro'
			],
			[
				\__('Montserrat', 'eightshift-forms'),
				'ms',
				'1664',
				'Montserrat'
			],
			[
				\__('Morocco', 'eightshift-forms'),
				'ma',
				'212',
				'Morocco'
			],
			[
				\__('Mozambique', 'eightshift-forms'),
				'mz',
				'258',
				'Mozambique'
			],
			[
				\__('Myanmar', 'eightshift-forms'),
				'mm',
				'95',
				'Myanmar'
			],
			[
				\__('Namibia', 'eightshift-forms'),
				'na',
				'264',
				'Namibia'
			],
			[
				\__('Nauru', 'eightshift-forms'),
				'nr',
				'674',
				'Nauru'
			],
			[
				\__('Nepal', 'eightshift-forms'),
				'np',
				'977',
				'Nepal'
			],
			[
				\__('Netherlands', 'eightshift-forms'),
				'nl',
				'31',
				'Netherlands'
			],
			[
				\__('New Caledonia', 'eightshift-forms'),
				'nc',
				'687',
				'New Caledonia'
			],
			[
				\__('New Zealand', 'eightshift-forms'),
				'nz',
				'64',
				'New Zealand'
			],
			[
				\__('Nicaragua', 'eightshift-forms'),
				'ni',
				'505',
				'Nicaragua'
			],
			[
				\__('Niger', 'eightshift-forms'),
				'ne',
				'227',
				'Niger'
			],
			[
				\__('Nigeria', 'eightshift-forms'),
				'ng',
				'234',
				'Nigeria'
			],
			[
				\__('Niue', 'eightshift-forms'),
				'nu',
				'683',
				'Niue'
			],
			[
				\__('Norfolk Island', 'eightshift-forms'),
				'nf',
				'672',
				'Norfolk Island'
			],
			[
				\__('North Macedonia', 'eightshift-forms'),
				'mk',
				'389',
				'North Macedonia'
			],
			[
				\__('Northern Mariana Islands', 'eightshift-forms'),
				'mp',
				'1670',
				'Northern Mariana Islands'
			],
			[
				\__('Norway', 'eightshift-forms'),
				'no',
				'47',
				'Norway'
			],
			[
				\__('Oman', 'eightshift-forms'),
				'om',
				'968',
				'Oman'
			],
			[
				\__('Pakistan', 'eightshift-forms'),
				'pk',
				'92',
				'Pakistan'
			],
			[
				\__('Palau', 'eightshift-forms'),
				'pw',
				'680',
				'Palau'
			],
			[
				\__('Palestine', 'eightshift-forms'),
				'ps',
				'970',
				'Palestine'
			],
			[
				\__('Panama', 'eightshift-forms'),
				'pa',
				'507',
				'Panama'
			],
			[
				\__('Papua New Guinea', 'eightshift-forms'),
				'pg',
				'675',
				'Papua New Guinea'
			],
			[
				\__('Paraguay', 'eightshift-forms'),
				'py',
				'595',
				'Paraguay'
			],
			[
				\__('Peru', 'eightshift-forms'),
				'pe',
				'51',
				'Peru'
			],
			[
				\__('Philippines', 'eightshift-forms'),
				'ph',
				'63',
				'Philippines'
			],
			[
				\__('Pitcairn', 'eightshift-forms'),
				'pn',
				'872',
				'Pitcairn'
			],
			[
				\__('Poland', 'eightshift-forms'),
				'pl',
				'48',
				'Poland'
			],
			[
				\__('Portugal', 'eightshift-forms'),
				'pt',
				'351',
				'Portugal'
			],
			[
				\__('Puerto Rico', 'eightshift-forms'),
				'pr',
				'1',
				'Puerto Rico'
			],
			[
				\__('Qatar', 'eightshift-forms'),
				'qa',
				'974',
				'Qatar'
			],
			[
				\__('Réunion', 'eightshift-forms'),
				're',
				'262',
				'Réunion'
			],
			[
				\__('Romania', 'eightshift-forms'),
				'ro',
				'40',
				'Romania'
			],
			[
				\__('Russian Federation', 'eightshift-forms'),
				'ru',
				'7',
				'Russian Federation'
			],
			[
				\__('Rwanda', 'eightshift-forms'),
				'rw',
				'250',
				'Rwanda'
			],
			[
				\__('Saba', 'eightshift-forms'),
				'bg-sa',
				'5994',
				'Saba'
			],
			[
				\__('Saint Barthélemy', 'eightshift-forms'),
				'bl',
				'590',
				'Saint Barthélemy'
			],
			[
				\__('Saint Helena', 'eightshift-forms'),
				'sh',
				'290',
				'Saint Helena'
			],
			[
				\__('Saint Kitts and Nevis', 'eightshift-forms'),
				'kn',
				'1869',
				'Saint Kitts and Nevis'
			],
			[
				\__('Saint Lucia', 'eightshift-forms'),
				'lc',
				'1758',
				'Saint Lucia'
			],
			[
				\__('Saint Martin (French part)', 'eightshift-forms'),
				'mf',
				'590',
				'Saint Martin (French part)'
			],
			[
				\__('Saint Pierre and Miquelon', 'eightshift-forms'),
				'pm',
				'508',
				'Saint Pierre and Miquelon'
			],
			[
				\__('Saint Vincent and The Grenadines', 'eightshift-forms'),
				'vc',
				'1784',
				'Saint Vincent and The Grenadines'
			],
			[
				\__('Samoa', 'eightshift-forms'),
				'ws',
				'685',
				'Samoa'
			],
			[
				\__('San Marino', 'eightshift-forms'),
				'sm',
				'378',
				'San Marino'
			],
			[
				\__('Sao Tome and Principe', 'eightshift-forms'),
				'st',
				'239',
				'Sao Tome and Principe'
			],
			[
				\__('Saudi Arabia', 'eightshift-forms'),
				'sa',
				'966',
				'Saudi Arabia'
			],
			[
				\__('Senegal', 'eightshift-forms'),
				'sn',
				'221',
				'Senegal'
			],
			[
				\__('Serbia', 'eightshift-forms'),
				'rs',
				'381',
				'Serbia'
			],
			[
				\__('Seychelles', 'eightshift-forms'),
				'sc',
				'248',
				'Seychelles'
			],
			[
				\__('Sierra Leone', 'eightshift-forms'),
				'sl',
				'232',
				'Sierra Leone'
			],
			[
				\__('Singapore', 'eightshift-forms'),
				'sg',
				'65',
				'Singapore'
			],
			[
				\__('Sint Maarten', 'eightshift-forms'),
				'sx',
				'1721',
				'Sint Maarten'
			],
			[
				\__('Slovakia', 'eightshift-forms'),
				'sk',
				'421',
				'Slovakia'
			],
			[
				\__('Slovenia', 'eightshift-forms'),
				'si',
				'386',
				'Slovenia'
			],
			[
				\__('Solomon Islands', 'eightshift-forms'),
				'sb',
				'677',
				'Solomon Islands'
			],
			[
				\__('Somalia', 'eightshift-forms'),
				'so',
				'252',
				'Somalia'
			],
			[
				\__('South Africa', 'eightshift-forms'),
				'za',
				'27',
				'South Africa'
			],
			[
				\__('South Georgia and the South Sandwich Islands', 'eightshift-forms'),
				'gs',
				'500',
				'South Georgia and the South Sandwich Islands'
			],
			[
				\__('South Sudan', 'eightshift-forms'),
				'ss',
				'211',
				'South Sudan'
			],
			[
				\__('Spain', 'eightshift-forms'),
				'es',
				'34',
				'Spain'
			],
			[
				\__('Sri Lanka', 'eightshift-forms'),
				'lk',
				'94',
				'Sri Lanka'
			],
			[
				\__('Sudan', 'eightshift-forms'),
				'sd',
				'249',
				'Sudan'
			],
			[
				\__('Suriname', 'eightshift-forms'),
				'sr',
				'597',
				'Suriname'
			],
			[
				\__('Svalbard', 'eightshift-forms'),
				'sj',
				'47',
				'Svalbard'
			],
			[
				\__('Sweden', 'eightshift-forms'),
				'se',
				'46',
				'Sweden'
			],
			[
				\__('Switzerland', 'eightshift-forms'),
				'ch',
				'41',
				'Switzerland'
			],
			[
				\__('Taiwan, China', 'eightshift-forms'),
				'tw',
				'886',
				'Taiwan, China'
			],
			[
				\__('Tajikistan', 'eightshift-forms'),
				'tj',
				'992',
				'Tajikistan'
			],
			[
				\__('Tanzania', 'eightshift-forms'),
				'tz',
				'255',
				'Tanzania'
			],
			[
				\__('Thailand', 'eightshift-forms'),
				'th',
				'66',
				'Thailand'
			],
			[
				\__('Timor-Leste', 'eightshift-forms'),
				'tl',
				'670',
				'Timor-Leste'
			],
			[
				\__('Togo', 'eightshift-forms'),
				'tg',
				'228',
				'Togo'
			],
			[
				\__('Tokelau', 'eightshift-forms'),
				'tk',
				'690',
				'Tokelau'
			],
			[
				\__('Tonga', 'eightshift-forms'),
				'to',
				'676',
				'Tonga'
			],
			[
				\__('Trinidad and Tobago', 'eightshift-forms'),
				'tt',
				'1868',
				'Trinidad and Tobago'
			],
			[
				\__('Tunisia', 'eightshift-forms'),
				'tn',
				'216',
				'Tunisia'
			],
			[
				\__('Turkey', 'eightshift-forms'),
				'tr',
				'90',
				'Turkey'
			],
			[
				\__('Turkmenistan', 'eightshift-forms'),
				'tm',
				'993',
				'Turkmenistan'
			],
			[
				\__('Turks and Caicos Islands', 'eightshift-forms'),
				'tc',
				'1649',
				'Turks and Caicos Islands'
			],
			[
				\__('Tuvalu', 'eightshift-forms'),
				'tv',
				'688',
				'Tuvalu'
			],
			[
				\__('Uganda', 'eightshift-forms'),
				'ug',
				'256',
				'Uganda'
			],
			[
				\__('Ukraine', 'eightshift-forms'),
				'ua',
				'380',
				'Ukraine'
			],
			[
				\__('United Arab Emirates', 'eightshift-forms'),
				'ae',
				'971',
				'United Arab Emirates'
			],
			[
				\__('United Kingdom', 'eightshift-forms'),
				'gb',
				'44',
				'United Kingdom'
			],
			[
				\__('United States', 'eightshift-forms'),
				'us',
				'1',
				'United States'
			],
			[
				\__('United States Minor Outlying Islands', 'eightshift-forms'),
				'um',
				'699',
				'United States Minor Outlying Islands'
			],
			[
				\__('Uruguay', 'eightshift-forms'),
				'uy',
				'598',
				'Uruguay'
			],
			[
				\__('Uzbekistan', 'eightshift-forms'),
				'uz',
				'998',
				'Uzbekistan'
			],
			[
				\__('Vanuatu', 'eightshift-forms'),
				'vu',
				'678',
				'Vanuatu'
			],
			[
				\__('Venezuela', 'eightshift-forms'),
				've',
				'58',
				'Venezuela'
			],
			[
				\__('Vietnam', 'eightshift-forms'),
				'vn',
				'84',
				'Vietnam'
			],
			[
				\__('Virgin Islands (British)', 'eightshift-forms'),
				'vg',
				'1284',
				'Virgin Islands (British)'
			],
			[
				\__('Virgin Islands (U.S.)', 'eightshift-forms'),
				'vi',
				'1340',
				'Virgin Islands (U.S.)'
			],
			[
				\__('Wallis and Futuna', 'eightshift-forms'),
				'wf',
				'681',
				'Wallis and Futuna'
			],
			[
				\__('Western Sahara', 'eightshift-forms'),
				'eh',
				'212',
				'Western Sahara'
			],
			[
				\__('Yemen', 'eightshift-forms'),
				'ye',
				'967',
				'Yemen'
			],
			[
				\__('Zambia', 'eightshift-forms'),
				'zm',
				'260',
				'Zambia'
			],
			[
				\__('Zimbabwe', 'eightshift-forms'),
				'zw',
				'263',
				'Zimbabwe'
			]
		];
	}
}
