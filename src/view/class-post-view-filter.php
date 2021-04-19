<?php
/**
 * File for modifying allowed tags for kses.
 *
 * @package EightshiftForms\View
 */

declare( strict_types=1 );

namespace EightshiftForms\View;

use Eightshift_Libs\Core\Service;
use EightshiftForms\View\Form_View;

/**
 * The project config class.
 */
class Post_View_Filter implements Service {

  /**
   * Registers class filters / actions.
   *
   * @return void
   */
  public function register() : void {
    add_filter( 'wp_kses_allowed_html', array( $this, 'modify_kses_post_tags' ), 30, 1 );
  }

  /**
   * Modifies allowed tags in wp_kses_post()
   *
   * @param  array $allowed_tags Array of allowed tags.
   * @return array
   */
  public function modify_kses_post_tags( array $allowed_tags ): array {
    $allowed_tags = array_merge( $allowed_tags, Form_View::extra_allowed_tags( $allowed_tags ) );

    return $allowed_tags;
  }
}
