/**
 * usePostData Hook
 * Manages post data fetching and state management
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { apiFetch } from '@wordpress/api-fetch';
import { API_ENDPOINTS } from '../../settings/constants';

export const usePostData = (queryParams) => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [totalPosts, setTotalPosts] = useState(0);

    const {
        postType = 'post',
        categories = [],
        tags = [],
        orderBy = 'date',
        order = 'desc',
        postsToShow = 6,
        search = ''
    } = queryParams;

    const buildQueryParams = useCallback(() => {
        const params = {
            per_page: postsToShow,
            orderby: orderBy,
            order: order,
            _embed: true
        };

        if (categories.length > 0) {
            params.categories = categories.join(',');
        }

        if (tags.length > 0) {
            params.tags = tags.join(',');
        }

        if (search) {
            params.search = search;
        }

        return params;
    }, [postType, categories, tags, orderBy, order, postsToShow, search]);

    const fetchPosts = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const queryParams = buildQueryParams();
            const queryString = new URLSearchParams(queryParams).toString();
            const endpoint = `${API_ENDPOINTS.posts}?${queryString}`;

            const response = await apiFetch({ path: endpoint });
            
            setPosts(response);
            setTotalPosts(response.length);
        } catch (err) {
            setError(err.message || 'Failed to fetch posts');
            console.error('Error fetching posts:', err);
        } finally {
            setLoading(false);
        }
    }, [buildQueryParams]);

    const refetchPosts = useCallback(() => {
        fetchPosts();
    }, [fetchPosts]);

    const loadMorePosts = useCallback(async () => {
        if (loading || posts.length >= totalPosts) return;

        setLoading(true);
        try {
            const queryParams = buildQueryParams();
            queryParams.offset = posts.length;
            
            const queryString = new URLSearchParams(queryParams).toString();
            const endpoint = `${API_ENDPOINTS.posts}?${queryString}`;

            const response = await apiFetch({ path: endpoint });
            
            setPosts(prevPosts => [...prevPosts, ...response]);
        } catch (err) {
            setError(err.message || 'Failed to load more posts');
        } finally {
            setLoading(false);
        }
    }, [posts.length, buildQueryParams, loading, totalPosts]);

    const searchPosts = useCallback(async (searchTerm) => {
        setLoading(true);
        setError(null);

        try {
            const queryParams = buildQueryParams();
            queryParams.search = searchTerm;
            
            const queryString = new URLSearchParams(queryParams).toString();
            const endpoint = `${API_ENDPOINTS.posts}?${queryString}`;

            const response = await apiFetch({ path: endpoint });
            
            setPosts(response);
        } catch (err) {
            setError(err.message || 'Failed to search posts');
        } finally {
            setLoading(false);
        }
    }, [buildQueryParams]);

    const getPostById = useCallback((postId) => {
        return posts.find(post => post.id === postId);
    }, [posts]);

    const getPostsByCategory = useCallback((categoryId) => {
        return posts.filter(post => 
            post.categories && post.categories.includes(categoryId)
        );
    }, [posts]);

    const getPostsByTag = useCallback((tagId) => {
        return posts.filter(post => 
            post.tags && post.tags.includes(tagId)
        );
    }, [posts]);

    // Fetch posts when dependencies change
    useEffect(() => {
        fetchPosts();
    }, [fetchPosts]);

    return {
        posts,
        loading,
        error,
        totalPosts,
        refetchPosts,
        loadMorePosts,
        searchPosts,
        getPostById,
        getPostsByCategory,
        getPostsByTag
    };
};
