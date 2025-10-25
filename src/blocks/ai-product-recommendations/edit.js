import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
    PanelBody,
    RangeControl,
    ToggleControl
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();

    return (
        <div {...blockProps}>
            <p>{__('Product Recommend AI placeholder', 'blockxpert')}</p>
        </div>
    );
}