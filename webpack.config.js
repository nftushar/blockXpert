const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  ...defaultConfig,
  entry: {
    index: './src/index.js',
    'product-slider/style-index': './src/blocks/product-slider/style.scss',
    'product-slider/view': './src/blocks/product-slider/view.js',
    'ai-faq/style-index': './src/blocks/ai-faq/style.scss',
    'ai-faq/view': './src/blocks/ai-faq/view.js',
    'ai-product-recommendations/style-index': './src/blocks/ai-product-recommendations/style.scss',
    'ai-product-recommendations/view': './src/blocks/ai-product-recommendations/view.js',
  },
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: '[name].js',
  },
  externals: {
    '@wordpress/blocks': 'wp.blocks',
    '@wordpress/block-editor': 'wp.blockEditor',
    '@wordpress/components': 'wp.components',
    '@wordpress/element': 'wp.element',
    '@wordpress/i18n': 'wp.i18n',
  },
  plugins: [
    ...defaultConfig.plugins,
    new MiniCssExtractPlugin({
      filename: (pathData) => {
        if (pathData.chunk.name && pathData.chunk.name.endsWith('style-index')) {
          const block = pathData.chunk.name.split('/')[0];
          return `${block}/style-index.css`;
        }
        if (pathData.chunk.name && pathData.chunk.name.endsWith('view')) {
          const block = pathData.chunk.name.split('/')[0];
          return `${block}/view.js`;
        }
        return '[name].js';
      },
    }),
  ],
};