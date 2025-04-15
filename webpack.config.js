const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module.rules,
      {
        test: /\.scss$/,
        use: [
          'style-loader',
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              implementation: require('sass')
            }
          }
        ]
      }
    ]
  }
};