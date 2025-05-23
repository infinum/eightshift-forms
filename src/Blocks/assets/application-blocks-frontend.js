/**
 * This is the main entry point for Block Editor blocks used for the `WordPress frontend screen`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress frontend screen`.
 */

import { dynamicImport } from '@eightshift/frontend-libs/scripts/helpers';

dynamicImport(require.context('./../components', true, /-frontend\.css$/));

// Scripts.
import './scripts/blocks-frontend';
