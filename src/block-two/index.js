import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('gutenberg-blocks/block-three', {
    edit: Edit,
    save: () => null,
    icon: 'star-filled'
});