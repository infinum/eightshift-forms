/**
 * This is the main entry point for admin all used for the `WordPress admin screen`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress admin all`.
 */

import { dynamicImport } from '@eightshift/frontend-libs-tailwind/scripts/helpers';

// Styles.
import './styles/admin-all.css';

dynamicImport(require.context('./../components', true, /styles-admin-all\.css$/));
