/**
 * This is the main entry point for admin scripts used for the `WordPress admin`.
 * This file registers blocks dynamically using `registerBlocks` helper method.
 * File names must follow the naming convention to be able to run dynamically.
 *
 * `src/blocks/custom/block_name/admin/index.js`.
 *
 * Usage: `WordPress admin `.
 */

 import { dynamicImport } from '@eightshift/frontend-libs/scripts/helpers';

// Find all blocks and require assets index.js inside it.
dynamicImport(require.context('./../../components', true, /assets-admin\/index\.js$/));
