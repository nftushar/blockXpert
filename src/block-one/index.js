import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import 'style-loader!./style.css';



registerBlockType('gutenberg-blocks/block-one', {
  title: 'Block One',
  category: 'widgets',
  edit: Edit,
  save
});