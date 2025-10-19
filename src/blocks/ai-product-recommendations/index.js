import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import './style.scss';

registerBlockType('blockxpert/ai-product-recommendations', {
    title: __('AI Product Recommendations', 'blockxpert'),
    description: __('Display AI-powered product recommendations.', 'blockxpert'),
    category: 'blockxpert',
    icon: 'star-filled',
    supports: {
        html: false,
        align: ['wide', 'full']
    },
    attributes: {
        productsToShow: {
            type: 'number',
            default: 4
        },
        layout: {
            type: 'string',
            default: 'grid'
        },
        columns: {
            type: 'number',
            default: 2
        },
        showPrice: {
            type: 'boolean',
            default: true
        },
        showRating: {
            type: 'boolean',
            default: true
        },
        showDescription: {
            type: 'boolean',
            default: true
        }
    },
    edit: Edit,
    save: () => null // Dynamic block
});
