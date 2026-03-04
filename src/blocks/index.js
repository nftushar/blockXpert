const blockModules = require.context('./', true, /^\.\/[^/]+\/index\.js$/);

blockModules.keys().forEach((modulePath) => {
    blockModules(modulePath);
});