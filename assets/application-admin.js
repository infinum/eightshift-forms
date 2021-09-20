/**
 * This is the main entry point for Application Admin used for the `WordPress admin screen`.
 * This file registers styles and scripts and all other assets only for the admin but not the editor.
 * You would load helpers, scripts, styles for admin here.
 *
 * Usage: `WordPress admin screen`.
 */

// Load Styles
import './styles/application-admin.scss';
import './../src/Blocks/assets/application-admin';

// // Load Scripts
import './scripts/application-admin';
import './../src/Blocks/assets/scripts/application-admin';
