/**
 * Improved Block Example - Post Grid (Refactored)
 * 
 * This example shows how to refactor existing blocks using the new utilities.
 * Copy this pattern to improve other blocks.
 */

import React from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Import improved utilities
import { blockRegister } from '@shared/utils/blockRegister';
import { useBlockAttributes } from '@shared/hooks/useBlockAttributes';
import { BlockWrapper } from '@shared/components/BlockWrapper';
import {
    LayoutSettings,
    DisplaySettings,
    QuerySettings,
    StyleSettings,
} from '@shared/components/BlockSettings';
import { getPreset } from '@shared/utils/BlockPresets';

/**
 * Block Edit Component
 */
function PostGridEdit({ attributes, setAttributes }) {
    // Use enhanced attribute management
    const attrs = useBlockAttributes(attributes, setAttributes);

    // Fetch data (replace with your actual data fetching logic)
    const { posts, loading, error } = useFetchPosts(attributes);

    // Apply preset if provided
    const applyPreset = (presetName) => {
        const preset = getPreset('grid', presetName);
        if (preset) {
            attrs.setMultipleAttributes(preset);
        }
    };

    return (
        <>
            {/* Inspector Panel - Settings */}
            <InspectorControls>
                {/* Preset Selector */}
                <PanelBody title={__('Quick Setup', 'blockxpert')} initialOpen={true}>
                    <SelectControl
                        label={__('Choose Preset', 'blockxpert')}
                        options={[
                            { label: __('None', 'blockxpert'), value: '' },
                            { label: __('Minimal', 'blockxpert'), value: 'minimal' },
                            { label: __('Featured', 'blockxpert'), value: 'featured' },
                            { label: __('Compact', 'blockxpert'), value: 'compact' },
                        ]}
                        onChange={applyPreset}
                    />
                </PanelBody>

                {/* Layout Settings */}
                <LayoutSettings 
                    attributes={attributes} 
                    updateAttribute={attrs.setAttribute}
                />

                {/* Display Settings */}
                <DisplaySettings 
                    attributes={attributes} 
                    updateAttribute={attrs.setAttribute}
                />

                {/* Query Settings */}
                <QuerySettings 
                    attributes={attributes} 
                    updateAttribute={attrs.setAttribute}
                />

                {/* Style Settings */}
                <StyleSettings 
                    attributes={attributes} 
                    updateAttribute={attrs.setAttribute}
                />
            </InspectorControls>

            {/* Block Preview */}
            <BlockWrapper
                loading={loading}
                error={error}
                className={`post-grid layout-${attributes.layout}`}
            >
                <div className="blockxpert-grid" style={{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${attributes.columns || 3}, 1fr)`,
                    gap: `${attributes.gap || 16}px`,
                }}>
                    {posts.map((post) => (
                        <PostCard
                            key={post.id}
                            post={post}
                            attributes={attributes}
                        />
                    ))}
                </div>
            </BlockWrapper>
        </>
    );
}

/**
 * Post Card Component
 */
function PostCard({ post, attributes }) {
    return (
        <div
            className={`blockxpert-card blockxpert-card--${attributes.cardStyle || 'default'}`}
            style={{
                borderRadius: `${attributes.borderRadius || 0}px`,
                boxShadow: attributes.shadow > 0 
                    ? `0 ${attributes.shadow}px ${attributes.shadow * 2}px rgba(0,0,0,0.1)`
                    : 'none',
            }}
        >
            {attributes.showImage && post.featured_image && (
                <div className="blockxpert-card__image">
                    <img src={post.featured_image} alt={post.title} />
                </div>
            )}

            <div className="blockxpert-card__content">
                {attributes.showTitle && (
                    <h3 className="blockxpert-card__title">
                        {post.title}
                    </h3>
                )}

                {attributes.showExcerpt && post.excerpt && (
                    <p className="blockxpert-card__excerpt">
                        {post.excerpt}
                    </p>
                )}

                {attributes.showMeta && (
                    <div className="blockxpert-card__meta">
                        {attributes.showDate && (
                            <span className="blockxpert-card__date">
                                {new Date(post.date).toLocaleDateString()}
                            </span>
                        )}

                        {attributes.showAuthor && post.author && (
                            <span className="blockxpert-card__author">
                                {post.author.name}
                            </span>
                        )}

                        {attributes.showCategory && post.categories && (
                            <span className="blockxpert-card__category">
                                {post.categories[0]?.name}
                            </span>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

/**
 * Hook for fetching posts
 */
function useFetchPosts(attributes) {
    const [posts, setPosts] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);

    React.useEffect(() => {
        const fetchPosts = async () => {
            try {
                setLoading(true);
                const response = await fetch(
                    `/wp-json/wp/v2/posts?per_page=${attributes.postsToShow || 12}`
                );
                if (!response.ok) throw new Error('Failed to fetch');
                const data = await response.json();
                setPosts(data);
                setError(null);
            } catch (err) {
                setError(err.message);
                setPosts([]);
            } finally {
                setLoading(false);
            }
        };

        fetchPosts();
    }, [attributes.postsToShow, attributes.orderBy, attributes.order]);

    return { posts, loading, error };
}

/**
 * Register Block
 */
blockRegister('blockxpert/improved-post-grid', {
    title: __('Improved Post Grid', 'blockxpert'),
    description: __('Display posts in a customizable grid with presets', 'blockxpert'),
    icon: 'grid',
    keywords: ['posts', 'grid', 'layout', 'blog'],
    
    attributes: {
        // Layout
        layout: { type: 'string', default: 'grid' },
        columns: { type: 'number', default: 3 },
        gap: { type: 'number', default: 16 },
        
        // Query
        postsToShow: { type: 'number', default: 12 },
        orderBy: { type: 'string', default: 'date' },
        order: { type: 'string', default: 'desc' },
        
        // Display
        showImage: { type: 'boolean', default: true },
        showTitle: { type: 'boolean', default: true },
        showExcerpt: { type: 'boolean', default: false },
        showMeta: { type: 'boolean', default: false },
        showDate: { type: 'boolean', default: false },
        showAuthor: { type: 'boolean', default: false },
        showCategory: { type: 'boolean', default: false },
        
        // Styling
        cardStyle: { type: 'string', default: 'default' },
        borderRadius: { type: 'number', default: 0 },
        shadow: { type: 'number', default: 0 },
    },
    
    edit: PostGridEdit,
    save: () => null, // Dynamic block
});

export default PostGridEdit;
