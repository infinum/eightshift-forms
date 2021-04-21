<?php

/**
 * Define rule for a form view.
 *
 * @package EightshiftForms\View
 */

declare(strict_types=1);

namespace EightshiftForms\View;

/**
 * Define rule for a form view.
 */
class FormView
{

  /**
   * Add extra allowed tags specifically for forms.
   *
   * @param  array $allowedTags Already allowed tags.
   * @return array
   */
	public static function extraAllowedTags($allowedTags): array
	{
		$allowedTags['form'] = [
			'class'                            => 1,
			'id'                               => 1,
			'action'                           => 1,
			'method'                           => 1,
			'target'                           => 1,
			'accept-charset'                   => 1,
			'autocapitalize'                   => 1,
			'autocomplete'                     => 1,
			'name'                             => 1,
			'rel'                              => 1,
			'enctype'                          => 1,
			'novalidate'                       => 1,
			'data-is-form-complex'             => 1,
			'data-redirect-on-success'         => 1,
			'data-form-type'                   => 1,
			'data-form-types-complex'          => 1,
			'data-form-types-complex-redirect' => 1,
			'data-dynamics-crm-entity'         => 1,
			'data-buckaroo-service'            => 1,
		];

	  // Append additional allowed tags.
		$allowedTags['input']['type']                = 1;
		$allowedTags['input']['class']               = 1;
		$allowedTags['input']['id']                  = 1;
		$allowedTags['input']['required']            = 1;
		$allowedTags['input']['checked']             = 1;
		$allowedTags['input']['tabindex']            = 1;
		$allowedTags['input']['pattern']             = 1;
		$allowedTags['input']['data-opens-popup']    = 1;
		$allowedTags['input']['data-do-not-send']    = 1;
		$allowedTags['input']['oninput']             = 1;
		$allowedTags['input']['min']                 = 1;
		$allowedTags['input']['max']                 = 1;
		$allowedTags['input']['maxlength']           = 1;
		$allowedTags['input']['aria-labelledby']     = 1;
		$allowedTags['input']['aria-describedby']    = 1;
		$allowedTags['input']['disabled']            = 1;
		$allowedTags['textarea']['required']         = 1;
		$allowedTags['textarea']['data-do-not-send'] = 1;
		$allowedTags['textarea']['aria-labelledby']  = 1;
		$allowedTags['textarea']['aria-describedby'] = 1;
		$allowedTags['textarea']['disabled']         = 1;
		$allowedTags['textarea']['placeholder']      = 1;
		$allowedTags['select']['required']           = 1;
		$allowedTags['select']['data-do-not-send']   = 1;
		$allowedTags['select']['aria-describedby']   = 1;
		$allowedTags['select']['aria-labelledby']    = 1;
		$allowedTags['select']['disabled']           = 1;
		$allowedTags['button']['aria-label']         = 1;
		$allowedTags['button']['role']               = 1;
		$allowedTags['button']['aria-describedby']   = 1;
		$allowedTags['button']['aria-labelledby']    = 1;
		$allowedTags['button']['disabled']           = 1;
		$allowedTags['radio']['aria-describedby']    = 1;
		$allowedTags['radio']['aria-labelledby']     = 1;
		$allowedTags['radio']['disabled']            = 1;

		return $allowedTags;
	}

  /**
   * Returns an array of tags for wp_kses(). Less strict than the usual wp_kses_post().
   */
	public static function allowedTags(): array
	{
		return self::extraAllowedTags(wp_kses_allowed_html('post'));
	}
}
