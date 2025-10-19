/**
 * Advanced Post Block Editor Component
 * Main editor component that orchestrates all editor functionality
 */

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { BLOCK_TITLE } from '../settings/constants';
import LayoutControls from './Controls/LayoutControls';
import DisplayControls from './Controls/DisplayControls';
import PostControls from './Controls/PostControls';
import PostPreview from './Preview/PostPreview';
import { usePostLayout } from './hooks/usePostLayout';
import { usePostData } from './hooks/usePostData';

export default function AdvancedPostBlockEdit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: 'blockxpert-advanced-post-block-editor'
    });

    const {
        layout,
        columns,
        postsToShow,
        showExcerpt,
        showDate,
        showAuthor,
        showImage,
        postType,
        categories,
        orderBy,
        order
    } = attributes;

    // Custom hooks for data management
    const { posts, loading, error } = usePostData({
        postType,
        categories,
        orderBy,
        order,
        postsToShow
    });

    const { 
        getLayoutClasses, 
        getResponsiveClasses,
        isLayoutSupported 
    } = usePostLayout(layout, columns);

    return (
        <div {...blockProps}>
            <InspectorControls>
                <LayoutControls
                    attributes={attributes}
                    setAttributes={setAttributes}
                />
                
                <DisplayControls
                    attributes={attributes}
                    setAttributes={setAttributes}
                />
                
                <PostControls
                    attributes={attributes}
                    setAttributes={setAttributes}
                />
            </InspectorControls>

            <div className="blockxpert-apb-editor-preview">
                <PostPreview
                    attributes={attributes}
                    posts={posts}
                    loading={loading}
                    error={error}
                    layoutClasses={getLayoutClasses()}
                    responsiveClasses={getResponsiveClasses()}
                />
            </div>
        </div>
    );
}
