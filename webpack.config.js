/* eslint-disable import/no-dynamic-require, global-require */

const path = require('path');

/**
 * This is a main entrypoint for Webpack config.
 * All the settings are pulled from node_modules/@eightshift/frontend-libs/webpack.
 * We are loading mostly used configuration but you can always override or turn off the default setup and provide your own.
 * Please referer to Eightshift-libs wiki for details.
 */
module.exports = (env, argv) => {

	const projectConfig = {
		config: {
			projectDir: __dirname, // Current project directory absolute path.
			projectUrl: 'eightshift.com', // Used for providing browsersync functionality.
			projectPath: 'wp-content/plugins/eightshift-forms', // Project path relative to project root.
		},
		overrides: [
			'browserSyncPlugin',
			'application',
			'applicationAdmin',
			'applicationBlocks',
		],
	};

	// Generate Webpack config for this project using options object.
	const project = require('./node_modules/@eightshift/frontend-libs/webpack')(argv.mode, projectConfig);

	return {
		// Load all projects config from eightshift-frontend-libs.
		...project,

		output: {
			// Load all output config from eightshift-frontend-libs.
			...project.output,
			library: 'EightshiftForms',
		},

		entry: {
			...project.entry,
			applicationAdmin: path.join(projectConfig.config.projectDir, '/src/Blocks/assets/application-admin.js'),
			applicationBlocksFrontendMandatory: path.join(projectConfig.config.projectDir, '/src/Blocks/assets/application-blocks-frontend-mandatory.js'),
		},
	};
};
