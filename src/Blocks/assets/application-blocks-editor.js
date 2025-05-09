/**
 * This is the main entry point for Block Editor blocks used for the `WordPress admin editor`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress admin editor`.
 */

import { dynamicImport } from '@eightshift/frontend-libs/scripts/helpers';

// Images.
import './images/blocks-editor';

// Styles.
import './styles/blocks-editor.scss';

dynamicImport(require.context('./../components', true, /-editor\.scss$/));
dynamicImport(require.context('./../custom', true, /-editor\.scss$/));

// Scripts.
import './scripts/blocks-editor';
