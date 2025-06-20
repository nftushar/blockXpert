import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';

registerBlockType('gutenberg-blocks/block-two', {
    edit,
    save,
});
