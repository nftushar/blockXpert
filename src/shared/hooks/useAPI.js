/**
 * useAPI Hook
 * Centralized API management with error handling and loading states
 */

import { useState, useCallback } from '@wordpress/element';
import { apiFetch } from '@wordpress/api-fetch';

export const useAPI = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const fetch = useCallback(async (endpoint, options = {}) => {
        setLoading(true);
        setError(null);
        
        try {
            const response = await apiFetch({
                path: endpoint,
                ...options
            });
            return response;
        } catch (err) {
            const errorMessage = err.message || 'An error occurred while fetching data';
            setError(errorMessage);
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    const post = useCallback(async (endpoint, data) => {
        return fetch(endpoint, {
            method: 'POST',
            data
        });
    }, [fetch]);

    const put = useCallback(async (endpoint, data) => {
        return fetch(endpoint, {
            method: 'PUT',
            data
        });
    }, [fetch]);

    const del = useCallback(async (endpoint) => {
        return fetch(endpoint, {
            method: 'DELETE'
        });
    }, [fetch]);

    const clearError = useCallback(() => {
        setError(null);
    }, []);

    return { 
        fetch, 
        post, 
        put, 
        del, 
        loading, 
        error, 
        clearError 
    };
};
