const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    ...defaultConfig,
    entry: {
        // Main entry point
        index: './src/index.js',
        
        // Block-specific entries with optimized structure
        'advanced-post-block/index': './src/blocks/advanced-post-block/index.js',
        'advanced-post-block/editor': './src/blocks/advanced-post-block/editor/Edit.js',
        'advanced-post-block/frontend': './src/blocks/advanced-post-block/frontend/Save.js',
        
        'product-slider/index': './src/blocks/product-slider/index.js',
        'ai-faq/index': './src/blocks/ai-faq/index.js',
        'ai-product-recommendations/index': './src/blocks/ai-product-recommendations/index.js',
    },
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: '[name].js',
        clean: true
    },
    module: {
        rules: [
            ...defaultConfig.module.rules,
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader'
                ]
            }
        ]
    },
    resolve: {
        alias: {
            '@shared': path.resolve(__dirname, 'src/shared'),
            '@blocks': path.resolve(__dirname, 'src/blocks'),
            '@components': path.resolve(__dirname, 'src/shared/components'),
            '@hooks': path.resolve(__dirname, 'src/shared/hooks'),
            '@services': path.resolve(__dirname, 'src/shared/services'),
            '@utils': path.resolve(__dirname, 'src/shared/utils')
        }
    },
    plugins: [
        ...defaultConfig.plugins,
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[id].css'
        })
    ],
    optimization: {
        ...defaultConfig.optimization,
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                shared: {
                    name: 'shared',
                    test: /[\\/]src[\\/]shared[\\/]/,
                    priority: 10,
                    reuseExistingChunk: true
                },
                vendor: {
                    name: 'vendor',
                    test: /[\\/]node_modules[\\/]/,
                    priority: 5,
                    reuseExistingChunk: true
                }
            }
        }
    }
};