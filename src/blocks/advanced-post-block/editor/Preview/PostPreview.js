import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';

export const PostPreview = ({ posts, loading, error, attributes, layoutConfig }) => {
    if (loading) {
        return (
            <div className="loading-spinner">
                <Spinner />
            </div>
        );
    }

    if (error) {
        return (
            <div className="error-message">
                {__('Error loading posts:', 'blockxpert')} {error}
            </div>
        );
    }

    if (!posts?.length) {
        return (
            <div className="no-posts">
                {__('No posts found.', 'blockxpert')}
            </div>
        );
    }

    // Ensure we have layout configuration with defaults
    const { gridColumns = 3, gapSize = '2rem' } = layoutConfig || {};

    return (
        <div className="post-grid">
            {posts.map(post => (
                <article key={post.id} className="post-item">
                    {post._embedded?.['wp:featuredmedia']?.[0] && (
                        <div className="post-thumbnail">
                            <img 
                                src={post._embedded['wp:featuredmedia'][0].source_url}
                                alt={post._embedded['wp:featuredmedia'][0].alt_text || ''}
                            />
                        </div>
                    )}
                    <div className="post-content">
                        <h3 className="post-title">
                            <a href={post.link}>{post.title.rendered}</a>
                        </h3>
                        <div className="post-meta">
                            <span className="post-date">
                                {new Date(post.date).toLocaleDateString()}
                            </span>
                        </div>
                        <div 
                            className="post-excerpt"
                            dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }}
                        />
                    </div>
                </article>
            ))}
        </div>
    );
};
