import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('blockxpert/product-slider', {
    edit: Edit,
    save: () => null, // dynamic block, no static save output
}); 