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
            <p>{__('AI Product Recommendations placeholder', 'blockxpert')}</p>
        </div>
    );
}