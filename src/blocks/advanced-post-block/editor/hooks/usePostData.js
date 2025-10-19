import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export const usePostData = (attributes) => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const {
        postType = 'post',
        postsToShow = 6,
        categories = [],
        tags = [],
        authors = [],
        orderBy = 'date',
        order = 'desc',
        offset = 0
    } = attributes;

    useEffect(() => {
        const fetchPosts = async () => {
            try {
                setLoading(true);
                setError(null);

                const queryParams = new URLSearchParams({
                    per_page: postsToShow,
                    orderby: orderBy,
                    order: order,
                    offset: offset,
                    _embed: 1
                });

                // Add taxonomy filters
                if (categories.length) {
                    queryParams.append('categories', categories.join(','));
                }

                if (tags.length) {
                    queryParams.append('tags', tags.join(','));
                }

                // Add author filter
                if (authors.length) {
                    queryParams.append('author', authors.join(','));
                }

                const response = await apiFetch({
                    path: `/wp/v2/${postType}s?${queryParams.toString()}`
                });

                setPosts(response);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchPosts();
    }, [postType, postsToShow, categories, tags, authors, orderBy, order, offset]);

    return { posts, loading, error };
};
