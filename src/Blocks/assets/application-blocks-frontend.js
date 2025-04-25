/**
 * This is the main entry point for Block Editor blocks used for the `WordPress frontend screen`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress frontend screen`.
 */

import { dynamicImport } from '@eightshift/frontend-libs/scripts/helpers';

// Styles.
import './styles/blocks-frontend.scss';

dynamicImport(require.context('./../components', true, /-frontend\.scss$/));

// Scripts.
import './scripts/blocks-frontend';
