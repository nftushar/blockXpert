import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import { save } from './save';

registerBlockType('gutenberg-blocks/block-one', {
    title: __('Block One', 'gutenberg-blocks'),
    icon: 'smiley',
    category: 'common',
    attributes: {
        content: {
            type: 'string',
            default: '', // Default value for content
        },
    },
    edit: Edit,   // Edit function for the editor
    save: save,   // Save function for front-end rendering
});
