import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import metadata from './block.json';
import './style.scss';

registerBlockType(metadata.name, {
  ...metadata,
  icon: '✨',
  edit: Edit,
  save: Save,
});
