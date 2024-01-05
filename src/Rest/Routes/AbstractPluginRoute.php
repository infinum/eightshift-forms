<?php

/**
 * Main entrypoint route for all routes.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class AbstractBaseRoute
 */
abstract class AbstractPluginRoute extends AbstractBaseRoute implements ServiceInterface {} // phpcs:ignore
