import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <h2>{__('Block Two', 'gutenberg-blocks')}</h2>
            <p>{__('This is the third example block.', 'gutenberg-blocks')}</p>
        </div>
    );
}