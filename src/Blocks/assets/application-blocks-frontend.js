/**
 * This is the main entry point for Block Editor blocks used for the `WordPress frontend screen`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress frontend screen`.
 */

import { dynamicImport } from '@eightshift/frontend-libs-tailwind/scripts/helpers';

/* ------------------------------------------------------------ */
/* Styles */
/* ------------------------------------------------------------ */

import './styles/frontend.css';

// dynamicImport(require.context('./../components', true, /-frontend\.css$/));

/* ------------------------------------------------------------ */
/* Scripts */
/* ------------------------------------------------------------ */

dynamicImport(require.context('./../components', true, /assets\/index\.js$/));
