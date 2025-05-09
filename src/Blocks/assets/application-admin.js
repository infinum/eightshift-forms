/**
 * This is the main entry point for admin used for the `WordPress admin screen`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress admin`.
 */

import { dynamicImport } from '@eightshift/frontend-libs/scripts/helpers';

// Styles.
import './styles/admin.scss';

dynamicImport(require.context('./../components', true, /-admin\.scss$/));

// Scripts.
import './scripts/admin';
