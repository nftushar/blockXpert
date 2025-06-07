import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('gutenberg-blocks/block-two', {
    edit: Edit,
    save: () => null,
    icon: 'star-filled'
});