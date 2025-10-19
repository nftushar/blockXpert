/**
 * Advanced Post Block Main Entry Point
 * Optimized structure with clear separation of concerns
 */

// Import styles
import './styles/editor.scss';
import './styles/frontend.scss';

// Import editor components
import Edit from './editor/Edit';

// Import frontend components
import Save from './frontend/Save';
import View from './frontend/View';

// Import settings
import { BLOCK_ATTRIBUTES, getDefaultSupports } from './settings/attributes';
import { BLOCK_NAME, BLOCK_TITLE, BLOCK_ICON, BLOCK_CATEGORY } from './settings/constants';

// Register the block
import { registerBlockType } from '@wordpress/blocks';

registerBlockType(BLOCK_NAME, {
    title: BLOCK_TITLE,
    icon: BLOCK_ICON,
    category: BLOCK_CATEGORY,
    description: 'Display posts with advanced layout options and customization',
    
    // Block attributes
    attributes: BLOCK_ATTRIBUTES,
    
    // Block supports
    supports: getDefaultSupports(),
    
    // Editor component
    edit: Edit,
    
    // Frontend save component (for dynamic blocks, this is usually null)
    save: () => null,
    
    // Server-side rendering function (for dynamic blocks)
    // render: View, // Uncomment if using server-side rendering
    
    // Block variations
    variations: [
        {
            name: 'grid-layout',
            title: 'Grid Layout',
            description: 'Display posts in a responsive grid',
            icon: 'grid-view',
            attributes: {
                layout: 'grid',
                columns: 3
            }
        },
        {
            name: 'masonry-layout',
            title: 'Masonry Layout',
            description: 'Pinterest-style masonry layout',
            icon: 'layout',
            attributes: {
                layout: 'masonry',
                columns: 3
            }
        },
        {
            name: 'slider-layout',
            title: 'Slider Layout',
            description: 'Carousel slider with navigation',
            icon: 'slides',
            attributes: {
                layout: 'slider',
                columns: 3,
                autoPlay: true,
                showNavigation: true
            }
        }
    ],
    
    // Block keywords for search
    keywords: [
        'posts',
        'blog',
        'content',
        'grid',
        'masonry',
        'slider',
        'layout'
    ],
    
    // Block example
    example: {
        attributes: {
            layout: 'grid',
            columns: 3,
            postsToShow: 6,
            showExcerpt: true,
            showDate: true,
            showAuthor: true,
            showImage: true
        }
    }
});