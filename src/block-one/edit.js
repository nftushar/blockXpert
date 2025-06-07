// Edit.js
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const handleChange = (event) => {
        setAttributes({ content: event.target.value }); // Update content attribute
    };

    return (
        <div {...useBlockProps()}>
            <h2>{__('x Block One', 'gutenberg-blocks')}</h2>
            <p>{__('This is the first example block.', 'gutenberg-blocks')}</p>
            <TextControl
                label={__('Content', 'gutenberg-blocks')}
                value={attributes.content}
                onChange={handleChange} // Update content as user types
                placeholder={__('Enter some content...', 'gutenberg-blocks')}
            />

        </div>
    );
}
