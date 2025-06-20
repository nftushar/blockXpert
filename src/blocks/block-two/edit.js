import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    return (
        <div {...useBlockProps()}>
            <TextControl
                label={__('Content', 'gutenberg-blocks')}
                value={attributes.content}
                onChange={(content) => setAttributes({ content })}
                placeholder={__('Enter your content...', 'gutenberg-blocks')}
            />
        </div>
    );
}
