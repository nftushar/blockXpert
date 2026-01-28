import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';

registerBlockType('blockxpert/product-slider', {
    edit: Edit,
    save
});
