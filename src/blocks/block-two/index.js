import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('blockxpert/block-two', {
    edit: Edit,
    save: () => null, // dynamic block, no static save output
});
