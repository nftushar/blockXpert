import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('blockxpert/ai-product-recommendations', {
    edit: Edit,
    save: () => null, // dynamic block, no static save output
}); 