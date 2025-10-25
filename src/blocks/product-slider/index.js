import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import './style.scss';

registerBlockType('blockxpert/product-slider', {
    title: __('Product Slider', 'blockxpert'),
    description: __('Display products in a beautiful slider.', 'blockxpert'),
    category: 'blockxpert',
    icon: 'slides',
    supports: {
        html: false,
        align: ['wide', 'full']
    },
    attributes: {
        productsToShow: {
            type: 'number',
            default: 6
        },
        categories: {
            type: 'array',
            default: []
        },
        slidesPerView: {
            type: 'number',
            default: 3
        },
        autoplay: {
            type: 'boolean',
            default: true
        },
        showNavigation: {
            type: 'boolean',
            default: true
        },
        showPagination: {
            type: 'boolean',
            default: true
        }
    },
    edit: Edit,
    save: () => null // Dynamic block
});