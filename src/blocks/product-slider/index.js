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
        title: {
            type: 'string',
            default: __('Featured Products', 'blockxpert')
        },
        productsPerSlide: {
            type: 'number',
            default: 3
        },
        autoPlay: {
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
        },
        category: {
            type: 'string',
            default: ''
        },
        orderBy: {
            type: 'string',
            default: 'date'
        },
        order: {
            type: 'string',
            default: 'desc'
        }
    },
    edit: Edit,
    save: () => null // Dynamic block
});