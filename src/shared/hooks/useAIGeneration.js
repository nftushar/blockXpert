/**
 * Custom hook for AI-powered content generation
 * Handles API calls, loading states, and error management
 */

import { useState, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Hook for managing AI generation requests
 * @param {string} endpoint - API endpoint for generation
 * @param {Object} options - Configuration options
 * @returns {Object} Loading state, response, and handler function
 */
export const useAIGeneration = (endpoint, options = {}) => {
    const [isLoading, setIsLoading] = useState(false);
    const [response, setResponse] = useState('');
    const [error, setError] = useState(null);

    const generate = useCallback(async (payload) => {
        if (!payload) {
            setError('No payload provided');
            return null;
        }

        setIsLoading(true);
        setError(null);
        setResponse('');

        try {
            const result = await apiFetch({
                path: endpoint,
                method: 'POST',
                data: payload,
                ...options,
            });

            setResponse(__('Generated successfully!', 'blockxpert'));
            return result;
        } catch (err) {
            const errorMessage = err.message || __('Error generating content', 'blockxpert');
            setError(errorMessage);
            setResponse(__('Error: ' + errorMessage, 'blockxpert'));
            console.error('AI Generation Error:', err);
            return null;
        } finally {
            setIsLoading(false);
        }
    }, [endpoint, options]);

    const clearResponse = useCallback(() => {
        setResponse('');
        setError(null);
    }, []);

    return {
        isLoading,
        response,
        error,
        generate,
        clearResponse,
    };
};

export default useAIGeneration;
