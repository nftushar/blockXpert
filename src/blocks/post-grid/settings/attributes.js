/**
 * Advanced Post Block Attributes Configuration
 * Defines all block attributes with their types, defaults, and validation
 */

export const BLOCK_ATTRIBUTES = {
    // Layout Settings
    layout: {
        type: 'string',
        default: 'grid'
    },
    columns: {
        type: 'number',
        default: 3,
        minimum: 1,
        maximum: 6
    },
    postsToShow: {
        type: 'number',
        default: 6,
        minimum: 1,
        maximum: 20
    },
    
    // Display Settings
    showExcerpt: {
        type: 'boolean',
        default: true
    },
    showDate: {
        type: 'boolean',
        default: true
    },
    showAuthor: {
        type: 'boolean',
        default: true
    },
    showImage: {
        type: 'boolean',
        default: true
    },
    showReadMore: {
        type: 'boolean',
        default: true
    },
    
    // Content Settings
    excerptLength: {
        type: 'number',
        default: 150,
        minimum: 50,
        maximum: 500
    },
    imageSize: {
        type: 'string',
        default: 'medium'
    },
    
    // Query Settings
    postType: {
        type: 'string',
        default: 'post'
    },
    categories: {
        type: 'array',
        default: []
    },
    tags: {
        type: 'array',
        default: []
    },
    orderBy: {
        type: 'string',
        default: 'date'
    },
    order: {
        type: 'string',
        default: 'desc'
    },
    
    // Styling Settings
    cardSpacing: {
        type: 'number',
        default: 20,
        minimum: 0,
        maximum: 50
    },
    borderRadius: {
        type: 'number',
        default: 8,
        minimum: 0,
        maximum: 20
    },
    showShadows: {
        type: 'boolean',
        default: true
    }
};

export const LAYOUT_OPTIONS = [
    { 
        label: 'Grid Layout', 
        value: 'grid',
        description: 'Display posts in a responsive grid'
    },
    { 
        label: 'Masonry Layout', 
        value: 'masonry',
        description: 'Pinterest-style masonry layout'
    },
    { 
        label: 'Slider Layout', 
        value: 'slider',
        description: 'Carousel slider with navigation'
    },
    { 
        label: 'Ticker Layout', 
        value: 'ticker',
        description: 'Horizontal scrolling ticker'
    }
];

export const IMAGE_SIZE_OPTIONS = [
    { label: 'Thumbnail', value: 'thumbnail' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' },
    { label: 'Full Size', value: 'full' }
];

export const ORDER_BY_OPTIONS = [
    { label: 'Date', value: 'date' },
    { label: 'Title', value: 'title' },
    { label: 'Random', value: 'rand' },
    { label: 'Comment Count', value: 'comment_count' },
    { label: 'Menu Order', value: 'menu_order' }
];

export const ORDER_OPTIONS = [
    { label: 'Descending', value: 'desc' },
    { label: 'Ascending', value: 'asc' }
];

// Export function for getting default supports
export const getDefaultSupports = () => ({
    align: ['wide', 'full'],
    spacing: {
        padding: true,
        margin: true
    },
    color: {
        background: true,
        text: true,
        link: true
    },
    typography: {
        fontSize: true,
        lineHeight: true,
        fontFamily: true,
        fontWeight: true
    }
});
