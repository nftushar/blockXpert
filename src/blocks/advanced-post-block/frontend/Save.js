import { useBlockProps } from '@wordpress/block-editor';

export const Save = ({ attributes }) => {
    const { layout, columns } = attributes;
    const blockProps = useBlockProps.save({
        className: `layout-${layout || 'grid'} columns-${columns || 3}`
    });

    return (
        <div {...blockProps}>
            <div className="post-grid">
                {/* Content will be rendered server-side */}
            </div>
        </div>
    );
};
