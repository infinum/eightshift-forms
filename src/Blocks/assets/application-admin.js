/**
 * This is the main entry point for admin used for the `WordPress admin screen`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress admin`.
 */

import { dynamicImport } from '@eightshift/frontend-libs-tailwind/scripts/helpers';

// Styles.
import './styles/admin.css';

dynamicImport(require.context('./../components', true, /styles-admin\.css$/));

// Scripts.
import './scripts/admin';
