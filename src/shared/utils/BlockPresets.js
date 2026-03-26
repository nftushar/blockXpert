/**
 * Block Presets
 * Pre-configured block attribute sets for quick setup
 */

export const BlockPresets = {
    // Grid Presets
    grid: {
        minimal: {
            layout: 'grid',
            columns: 3,
            gap: 16,
            cardStyle: 'minimal',
            showImage: true,
            showTitle: true,
            showExcerpt: false,
            shadow: 0,
            borderRadius: 0,
        },
        featured: {
            layout: 'grid',
            columns: 3,
            gap: 20,
            cardStyle: 'elevated',
            showImage: true,
            showTitle: true,
            showExcerpt: true,
            showMeta: true,
            shadow: 4,
            borderRadius: 8,
        },
        compact: {
            layout: 'grid',
            columns: 4,
            gap: 12,
            cardStyle: 'outline',
            showImage: true,
            showTitle: true,
            showExcerpt: false,
            shadow: 0,
            borderRadius: 4,
        },
    },

    // List Presets
    list: {
        simple: {
            layout: 'list',
            cardStyle: 'minimal',
            showImage: true,
            showTitle: true,
            showExcerpt: false,
            showMeta: false,
        },
        detailed: {
            layout: 'list',
            cardStyle: 'default',
            showImage: true,
            showTitle: true,
            showExcerpt: true,
            showMeta: true,
            showDate: true,
            showAuthor: true,
        },
    },

    // Masonry Presets
    masonry: {
        gallery: {
            layout: 'masonry',
            columns: 3,
            gap: 16,
            cardStyle: 'minimal',
            showImage: true,
            showTitle: false,
            showExcerpt: false,
        },
        portfolio: {
            layout: 'masonry',
            columns: 3,
            gap: 20,
            cardStyle: 'elevated',
            showImage: true,
            showTitle: true,
            showExcerpt: false,
            shadow: 2,
            borderRadius: 4,
        },
    },

    // Slider Presets
    slider: {
        hero: {
            layout: 'slider',
            cardStyle: 'default',
            showImage: true,
            showTitle: true,
            showExcerpt: true,
            autoplay: true,
            speed: 5000,
            shadow: 8,
            borderRadius: 12,
        },
        carousel: {
            layout: 'slider',
            cardStyle: 'minimal',
            showImage: true,
            showTitle: true,
            autoplay: true,
            speed: 3000,
            shadow: 2,
            borderRadius: 4,
        },
    },
};

/**
 * Get preset by category and name
 */
export function getPreset(category, presetName) {
    if (!BlockPresets[category]) {
        console.warn(`Category ${category} not found`);
        return null;
    }
    if (!BlockPresets[category][presetName]) {
        console.warn(`Preset ${presetName} not found in category ${category}`);
        return null;
    }
    return BlockPresets[category][presetName];
}

/**
 * List available presets
 */
export function listPresets(category) {
    if (category && BlockPresets[category]) {
        return Object.keys(BlockPresets[category]);
    }
    return Object.keys(BlockPresets).reduce((acc, cat) => {
        acc[cat] = Object.keys(BlockPresets[cat]);
        return acc;
    }, {});
}

export default BlockPresets;
