/**
 * Post Preview Component
 * Preview component for the editor interface
 */

import { __ } from '@wordpress/i18n';
import { Placeholder, Spinner } from '@wordpress/components';
import { CSS_CLASSES } from '../../settings/constants';
import PostCard from '../../frontend/components/PostCard';

export default function PostPreview({ 
    attributes, 
    posts, 
    loading, 
    error, 
    layoutClasses, 
    responsiveClasses 
}) {
    const { layout, columns, postsToShow } = attributes;

    if (loading) {
        return (
            <div className="blockxpert-apb-editor-preview">
                <div className="blockxpert-apb-loading">
                    <Spinner />
                    <p>{__('Loading posts...', 'blockxpert')}</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="blockxpert-apb-editor-preview">
                <Placeholder
                    icon="warning"
                    label={__('Error Loading Posts', 'blockxpert')}
                    instructions={error}
                />
            </div>
        );
    }

    if (!posts || posts.length === 0) {
        return (
            <div className="blockxpert-apb-editor-preview">
                <Placeholder
                    icon="admin-post"
                    label={__('No Posts Found', 'blockxpert')}
                    instructions={__('No posts match your current criteria. Try adjusting your settings.', 'blockxpert')}
                />
            </div>
        );
    }

    const previewPosts = posts.slice(0, Math.min(postsToShow, 6)); // Limit preview to 6 posts

    return (
        <div className="blockxpert-apb-editor-preview">
            <div className="blockxpert-apb-preview-header">
                <h4 className="blockxpert-apb-preview-title">
                    {__('Preview:', 'blockxpert')} {layout} layout
                </h4>
                <p className="blockxpert-apb-preview-info">
                    {__('Showing', 'blockxpert')} {previewPosts.length} {__('of', 'blockxpert')} {posts.length} {__('posts', 'blockxpert')}
                </p>
            </div>
            
            <div className={`${CSS_CLASSES.blockContainer} ${layoutClasses} ${responsiveClasses}`}>
                {previewPosts.map((post, index) => (
                    <div 
                        key={post.id} 
                        className={CSS_CLASSES.postItem}
                        style={{ 
                            animationDelay: `${index * 100}ms`,
                            '--blockxpert-apb-item-index': index
                        }}
                    >
                        <PostCard 
                            post={post} 
                            attributes={attributes} 
                            index={index}
                        />
                    </div>
                ))}
            </div>
            
            {posts.length > previewPosts.length && (
                <div className="blockxpert-apb-preview-footer">
                    <p className="blockxpert-apb-preview-more">
                        {__('And', 'blockxpert')} {posts.length - previewPosts.length} {__('more posts...', 'blockxpert')}
                    </p>
                </div>
            )}
        </div>
    );
}
