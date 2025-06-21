import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('gutenberg-blocks/product-slider', {
    edit: Edit,
    save: () => null, // dynamic block, no static save output
}); 