/**
 * This is the main entry point for admin scripts used for the `WordPress admin`.
 * This file registers blocks dynamically using `registerBlocks` helper method.
 * File names must follow the naming convention to be able to run dynamically.
 *
 * `src/blocks/custom/block_name/admin/index.js`.
 *
 * Usage: `WordPress admin `.
 */

import { dynamicImport } from '@eightshift/frontend-libs-tailwind/scripts/helpers';

// Find all blocks and require assets index.js inside it.
dynamicImport(require.context('./../../components', true, /assets-admin\/index\.js$/));
dynamicImport(require.context('./../../custom', true, /assets-admin\/index\.js$/));

// Output all frontend-only styles.
dynamicImport(require.context('./../../components', true, /styles-admin.css$/));
dynamicImport(require.context('./../../custom', true, /styles-admin.css$/));
