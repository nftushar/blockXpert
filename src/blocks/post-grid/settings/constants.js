/**
 * Advanced Post Block Constants
 * Centralized constants for the Advanced Post Block
 */

export const BLOCK_NAME = 'blockxpert/advanced-post-block';
export const BLOCK_TITLE = 'Advanced Post Block';
export const BLOCK_DESCRIPTION = 'Display posts with advanced layout options and customization';
export const BLOCK_ICON = 'grid-view';
export const BLOCK_CATEGORY = 'blockxpert-blocks';

export const DEFAULT_SETTINGS = {
    layout: 'grid',
    columns: 3,
    postsToShow: 6,
    showExcerpt: true,
    showDate: true,
    showAuthor: true,
    showImage: true,
    showReadMore: true,
    excerptLength: 150,
    imageSize: 'medium',
    postType: 'post',
    categories: [],
    tags: [],
    orderBy: 'date',
    order: 'desc',
    cardSpacing: 20,
    borderRadius: 8,
    showShadows: true
};

export const RESPONSIVE_BREAKPOINTS = {
    mobile: 480,
    tablet: 768,
    desktop: 1024,
    wide: 1200
};

export const CSS_CLASSES = {
    // Block wrapper classes
    blockWrapper: 'blockxpert-advanced-post-block',
    blockLayout: 'blockxpert-apb-layout',
    blockContainer: 'blockxpert-apb-container',
    
    // Layout specific classes
    gridLayout: 'blockxpert-apb-grid',
    masonryLayout: 'blockxpert-apb-masonry',
    sliderLayout: 'blockxpert-apb-slider',
    tickerLayout: 'blockxpert-apb-ticker',
    
    // Post item classes
    postItem: 'blockxpert-apb-post-item',
    postCard: 'blockxpert-apb-post-card',
    postImage: 'blockxpert-apb-post-image',
    postContent: 'blockxpert-apb-post-content',
    postTitle: 'blockxpert-apb-post-title',
    postExcerpt: 'blockxpert-apb-post-excerpt',
    postMeta: 'blockxpert-apb-post-meta',
    postAuthor: 'blockxpert-apb-post-author',
    postDate: 'blockxpert-apb-post-date',
    readMoreLink: 'blockxpert-apb-read-more',
    
    // Editor specific classes
    editorPreview: 'blockxpert-apb-editor-preview',
    editorPlaceholder: 'blockxpert-apb-editor-placeholder',
    editorControls: 'blockxpert-apb-editor-controls',
    
    // Responsive classes
    responsiveMobile: 'blockxpert-apb-mobile',
    responsiveTablet: 'blockxpert-apb-tablet',
    responsiveDesktop: 'blockxpert-apb-desktop'
};

export const ANIMATION_SETTINGS = {
    fadeIn: {
        duration: 300,
        easing: 'ease-in-out'
    },
    slideIn: {
        duration: 400,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
    },
    stagger: {
        delay: 100
    }
};

export const API_ENDPOINTS = {
    posts: '/wp/v2/posts',
    categories: '/wp/v2/categories',
    tags: '/wp/v2/tags',
    media: '/wp/v2/media'
};
