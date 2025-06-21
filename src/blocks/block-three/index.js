import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import { save } from './save';

registerBlockType('blockxpert/block-three', {
    title: __('Block three', 'gutenberg-blocks'),
    icon: 'smiley',
    category: 'common',
    attributes: {
        content: {
            type: 'string',
            default: '', // Default value for content
        },
    },
    edit: Edit,   // Edit function for the editor
    save: () => null, // dynamic block
});
