const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  ...defaultConfig,
  entry: {
    'block-one/index': './src/blocks/block-one/index.js',
    'block-one/style-index': './src/blocks/block-one/style.scss',
    'block-one/view': './src/blocks/block-one/view.js',
    'block-two/index': './src/blocks/block-two/index.js',
    'block-two/style-index': './src/blocks/block-two/style.scss',
    'block-two/view': './src/blocks/block-two/view.js',
    'block-three/index': './src/blocks/block-three/index.js',
    'block-three/style-index': './src/blocks/block-three/style.scss',
    'block-three/view': './src/blocks/block-three/view.js',
    'product-slider/index': './src/blocks/product-slider/index.js',
    'product-slider/style-index': './src/blocks/product-slider/style.scss',
    'product-slider/view': './src/blocks/product-slider/view.js',
    'ai-faq/index': './src/blocks/ai-faq/index.js',
    'ai-faq/style-index': './src/blocks/ai-faq/style.scss',
    'ai-faq/view': './src/blocks/ai-faq/view.js',
    'ai-product-recommendations/index': './src/blocks/ai-product-recommendations/index.js',
    'ai-product-recommendations/style-index': './src/blocks/ai-product-recommendations/style.scss',
    'ai-product-recommendations/view': './src/blocks/ai-product-recommendations/view.js',
    'pdf-invoice/index': './src/blocks/pdf-invoice/index.js',
    'pdf-invoice/style-index': './src/blocks/pdf-invoice/style.scss',
    'pdf-invoice/view': './src/blocks/pdf-invoice/view.js',
  },
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: '[name].js',
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
        if (pathData.chunk.name && pathData.chunk.name.endsWith('index')) {
          const block = pathData.chunk.name.split('/')[0];
          return `${block}/index.js`;
        }
        return '[name].js';
      },
    }),
  ],
};