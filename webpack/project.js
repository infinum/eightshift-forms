/* eslint-disable import/no-extraneous-dependencies*/

// Other build files.
const config = require('./config');

// Plugins.
const CopyWebpackPlugin = require('copy-webpack-plugin');

// Main Webpack build setup - Website.
const project = {
  context: config.appPath,
  entry: {
    esFromsApplication: [config.assetsEntry],
    esFromsApplicationAdmin: [config.assetsAdminEntry],
    esFromsApplicationBlocks: [config.blocksEntry],
    esFromsApplicationBlocksEditor: [config.blocksEditorEntry],
  },
  output: {
    path: config.outputPath,
    publicPath: config.publicPath,
    filename: '[name]-[hash].js',
  },

  plugins: [

    // Copy files to new destination.
    new CopyWebpackPlugin([

      // Find jQuery in node_modules and copy it to public folder
      {
        from: `${config.absolutePath}/node_modules/jquery/dist/jquery.min.js`,
        to: config.output,
      },
    ]),
  ],
};

// Define what output will export for specific build.
module.exports = project;
