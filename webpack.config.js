const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  entry: {
    'block-one': './src/blocks/block-one/index.js',
    'block-two': './src/blocks/block-two/index.js',
    'block-three': './src/blocks/block-three/index.js'
  },
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: '[name]/index.js'
  }
};