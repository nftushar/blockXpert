const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    ...defaultConfig,
    entry: (function () {
        // Auto-discover blocks under src/blocks and build convenient entries per block
        const fs = require('fs');
        const baseEntries = {
            index: './src/index.js'
        };
        const blocksDir = path.resolve(__dirname, 'src/blocks');
        if (fs.existsSync(blocksDir)) {
            fs.readdirSync(blocksDir, { withFileTypes: true }).forEach(dirent => {
                if (!dirent.isDirectory()) return;
                const name = dirent.name;
                const candidates = [
                    { key: `${name}/index`, file: `./src/blocks/${name}/index.js` },
                    { key: `${name}/view`, file: `./src/blocks/${name}/view.js` },
                    { key: `${name}/editor`, file: `./src/blocks/${name}/edit.js` },
                    { key: `${name}/editor`, file: `./src/blocks/${name}/editor.js` },
                    { key: `${name}/editor`, file: `./src/blocks/${name}/editor.css` },
                    { key: `${name}/style-index`, file: `./src/blocks/${name}/style.scss` },
                    { key: `${name}/frontend`, file: `./src/blocks/${name}/frontend/Save.js` },
                ];
                candidates.forEach(c => {
                    const absolute = path.resolve(__dirname, c.file.replace(/^\.\//, ''));
                    if (fs.existsSync(absolute)) {
                        baseEntries[c.key] = c.file;
                    }
                });
            });
        }
        return baseEntries;
    })(),
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