import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('blockxpert/ai-faq', {
    edit: Edit,
    save: () => null, // dynamic block, no static save output
}); 