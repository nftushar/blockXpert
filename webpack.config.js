const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

/**
 * Enhanced Webpack Configuration for BlockXpert
 * Features:
 * - Auto-discovery of blocks
 * - Optimized code splitting
 * - Minification and compression
 * - Source maps for debugging
 * - CSS/SCSS support
 * - Path aliases for clean imports
 */

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
    ...defaultConfig,
    mode: isProduction ? 'production' : 'development',
    devtool: isProduction ? 'source-map' : 'eval-source-map',
    
    /**
     * Auto-discover entry points from blocks
     */
    entry: (function () {
        const fs = require('fs');
        const baseEntries = {
            // Main editor entry
            index: './src/index.js'
        };
        
        const blocksDir = path.resolve(__dirname, 'src/blocks');
        if (fs.existsSync(blocksDir)) {
            fs.readdirSync(blocksDir, { withFileTypes: true }).forEach(dirent => {
                if (!dirent.isDirectory()) return;
                
                const blockName = dirent.name;
                const blockPath = path.join(blocksDir, blockName);
                
                // Define entry points for each block
                const entryPoints = [
                    // Block registration entry
                    { key: `${blockName}/index`, file: `./src/blocks/${blockName}/index.js` },
                    // Frontend/View entry (for dynamic blocks)
                    { key: `${blockName}/view`, file: `./src/blocks/${blockName}/view.js` },
                    // Editor entry (if separate from index)
                    { key: `${blockName}/editor`, file: `./src/blocks/${blockName}/editor.js` },
                ];
                
                // Check for style files
                const styleFiles = [
                    './src/blocks/${blockName}/styles/editor.scss',
                    './src/blocks/${blockName}/styles/frontend.scss',
                    './src/blocks/${blockName}/style.scss'
                ];
                
                entryPoints.forEach(entry => {
                    const absolute = path.resolve(__dirname, entry.file.replace(/^\.\//, ''));
                    if (fs.existsSync(absolute)) {
                        baseEntries[entry.key] = entry.file;
                    }
                });
            });
        }
        
        return baseEntries;
    })(),
    
    module: {
        rules: [
            ...defaultConfig.module.rules,
            // SCSS/SASS Support
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: !isProduction,
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: !isProduction,
                        }
                    }
                ]
            },
            // CSS Support
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader'
                ]
            }
        ]
    },
    
    /**
     * Path aliases for cleaner imports
     */
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            '@': path.resolve(__dirname, 'src'),
            '@shared': path.resolve(__dirname, 'src/shared'),
            '@blocks': path.resolve(__dirname, 'src/blocks'),
            '@components': path.resolve(__dirname, 'src/shared/components'),
            '@hooks': path.resolve(__dirname, 'src/shared/hooks'),
            '@services': path.resolve(__dirname, 'src/shared/services'),
            '@utils': path.resolve(__dirname, 'src/shared/utils'),
            '@types': path.resolve(__dirname, 'src/shared/types')
        },
        extensions: ['.js', '.jsx', '.ts', '.tsx', '.scss', '.css']
    },
    
    /**
     * Plugins
     */
    plugins: [
        ...defaultConfig.plugins,
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[id].css'
        })
    ],
    
    /**
     * Optimization
     */
    optimization: {
        minimize: isProduction,
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    compress: {
                        drop_console: isProduction,
                        passes: isProduction ? 3 : 1
                    },
                    mangle: isProduction,
                    format: {
                        comments: !isProduction,
                    }
                },
                extractComments: isProduction,
            })
        ],
        
        /**
         * Code splitting strategy
         */
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                // Shared components and utilities
                shared: {
                    name: 'shared',
                    test: /[\\/]src[\\/]shared[\\/]/,
                    priority: 20,
                    reuseExistingChunk: true,
                    minChunks: 2
                },
                // WordPress packages
                wpPackages: {
                    name: 'wp-packages',
                    test: /[\\/]node_modules[\\/]@wordpress[\\/]/,
                    priority: 15,
                    reuseExistingChunk: true,
                    minChunks: 2
                },
                // Vendor packages
                vendor: {
                    name: 'vendor',
                    test: /[\\/]node_modules[\\/]/,
                    priority: 10,
                    reuseExistingChunk: true,
                    minChunks: 2
                },
                // Common code between blocks
                common: {
                    name: 'common',
                    minChunks: 2,
                    priority: 5,
                    reuseExistingChunk: true,
                    enforce: true
                }
            }
        },
        
        runtimeChunk: {
            name: 'runtime'
        }
    },
    
    /**
     * Performance hints
     */
    performance: {
        maxEntrypointSize: 512000,
        maxAssetSize: 512000
    },
    
    /**
     * Output configuration
     */
    output: {
        ...defaultConfig.output,
        path: path.resolve(__dirname, 'build'),
        filename: '[name].js',
        chunkFilename: '[name].[contenthash].js'
    }
};