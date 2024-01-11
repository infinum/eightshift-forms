<?php

/**
 * Main entrypoint route for all routes.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class AbstractUtilsBaseRoute
 */
abstract class AbstractPluginRoute extends AbstractUtilsBaseRoute implements ServiceInterface {} // phpcs:ignore
