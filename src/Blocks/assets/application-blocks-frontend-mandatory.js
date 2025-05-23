/**
 * This is the main entry point for Block Editor blocks used for the `WordPress frontend screen`.
 * This file registers styles and scripts. But files that are mandatory to load if the user disables the options in admin.
 *
 * Usage: `WordPress frontend screen`.
 */

import { dynamicImport } from '@eightshift/frontend-libs-tailwind/scripts/helpers';

// Images.
import './images/blocks-frontend-mandatory';

import '../../../tailwind.css';

dynamicImport(require.context('./../components', true, /-frontend-mandatory\.css$/));
dynamicImport(require.context('./../custom', true, /-frontend-mandatory\.css$/));
