const path = require('path');

// Eightshift blocks Config.
const blockConfig = require('../vendor/infinum/eightshift-blocks/webpack/config');

// Create Theme/Plugin config variable.
// Define path to the project from the WordPress root. This is used to output the correct path to the manifest.json.
const configData = getConfig('wp-content/plugins/eightshift-forms'); // eslint-disable-line no-use-before-define

// Export config to use in other Webpack files.
module.exports = {
  proxyUrl: 'dev.infinum.co',
  absolutePath: configData.absolutePath,
  relativePathFiles: configData.relativePathFiles,
  absolutePathFiles: configData.absolutePathFiles,
  outputPath: configData.outputPath,
  publicPath: configData.publicPath,
  assetsPath: configData.assetsPath,
  fontsPath: configData.fontsPath,
  assetsEntry: configData.assetsEntry,
  assetsAdminEntry: configData.assetsAdminEntry,
  blocksEntry: configData.blocksEntry,
  blocksEditorEntry: configData.blocksEditorEntry,
};

// Generate all paths required for Webpack build to work.
function getConfig(assetsPath) {

  // Clear all shalshes and set project path the correct way.
  const relativePath = assetsPath.replace(/^\/|\/$/g, '');

  // Create absolute path from the projects relative path.
  const absolutePath = `${path.resolve(`/${__dirname}`, '..')}`;

  // Define projects relative path for file locations.
  const relativePathFiles = `${relativePath}/src/blocks`;

  // Define projects absolute path for file locations.
  const absolutePathFiles = `${absolutePath}/src/blocks`;

  // Load Blocks configuration.
  const blocks = blockConfig('', absolutePath);

  return {
    absolutePath,
    relativePathFiles,
    absolutePathFiles,

    // Output files absolute location.
    outputPath: `${absolutePath}/public`,

    // Output files relative location, added before every output file in manifes.json. Should start and end with "/".
    publicPath: `/${relativePath}/public/`,

    // Source files relative locations.
    assetsPath: `/${relativePathFiles}/assets`,
    fontsPath: `/${relativePathFiles}/assets/fonts`,

    // Source files entries absolute locations.
    assetsEntry: `${absolutePathFiles}/assets/application.js`,
    assetsAdminEntry: `${absolutePathFiles}/assets/application-admin.js`,
    blocksEntry: blocks.blocksEntry,
    blocksEditorEntry: blocks.blocksEditorEntry,
  };
}
