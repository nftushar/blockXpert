import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';

registerBlockType('gutenberg-blocks/block-three', {
    title: __('Block Three', 'gutenberg-blocks'),
    description: __('Second example block', 'gutenberg-blocks'),
    category: 'common',
    icon: 'admin-site',
    edit: Edit,
    save: () => null // Dynamic block
});