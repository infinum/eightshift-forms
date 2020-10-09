<?php
/**
 * The Filters class, used for defining available filters
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

/**
 * The Filters class, used for defining available filters.
 */
class Filters {
  const DYNAMICS_CRM            = 'eightshift_forms/dynamics_info';
  const BUCKAROO                = 'eightshift_forms/buckaroo';
  const ALLOWED_BLOCKS          = 'eightshift_forms/allowed_blocks';
  const GENERAL                 = 'eightshift_forms/general_info';
  const PREFILL_GENERIC_MULTI   = 'eightshift_forms/prefill/multi';
  const PREFILL_GENERIC_SINGLE  = 'eightshift_forms/prefill/single';
  const AUTHORIZATION_GENERATOR = 'eightshift_forms/authorization_generator';
}
