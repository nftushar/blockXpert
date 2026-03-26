/**
 * BlockXpertAPI Service
 * Centralized API management with caching, interceptors, and error handling
 */

import { apiFetch } from '@wordpress/api-fetch';

class BlockXpertAPIClient {
    constructor() {
        this.cache = new Map();
        this.cacheEnabled = true;
        this.cacheExpiry = 5 * 60 * 1000; // 5 minutes default
        this.requestInterceptors = [];
        this.responseInterceptors = [];
        this.errorInterceptors = [];
    }

    /**
     * Add request interceptor
     */
    addRequestInterceptor(interceptor) {
        this.requestInterceptors.push(interceptor);
        return () => {
            this.requestInterceptors = this.requestInterceptors.filter(
                (i) => i !== interceptor
            );
        };
    }

    /**
     * Add response interceptor
     */
    addResponseInterceptor(interceptor) {
        this.responseInterceptors.push(interceptor);
        return () => {
            this.responseInterceptors = this.responseInterceptors.filter(
                (i) => i !== interceptor
            );
        };
    }

    /**
     * Add error interceptor
     */
    addErrorInterceptor(interceptor) {
        this.errorInterceptors.push(interceptor);
        return () => {
            this.errorInterceptors = this.errorInterceptors.filter(
                (i) => i !== interceptor
            );
        };
    }

    /**
     * Generate cache key
     */
    getCacheKey(endpoint, options = {}) {
        return `${endpoint}:${JSON.stringify(options)}`;
    }

    /**
     * Get from cache
     */
    getFromCache(key) {
        if (!this.cacheEnabled) return null;
        
        const cached = this.cache.get(key);
        if (!cached) return null;
        
        // Check expiry
        if (Date.now() > cached.expiry) {
            this.cache.delete(key);
            return null;
        }
        
        return cached.data;
    }

    /**
     * Set cache
     */
    setCache(key, data, ttl = this.cacheExpiry) {
        if (!this.cacheEnabled) return;
        
        this.cache.set(key, {
            data,
            expiry: Date.now() + ttl,
        });
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Enable/disable caching
     */
    setCacheEnabled(enabled) {
        this.cacheEnabled = enabled;
        if (!enabled) {
            this.clearCache();
        }
    }

    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const method = options.method || 'GET';
        const useCache = options.cache !== false && method === 'GET';
        
        // Check cache for GET requests
        if (useCache) {
            const cacheKey = this.getCacheKey(endpoint, options);
            const cached = this.getFromCache(cacheKey);
            if (cached) {
                return cached;
            }
        }

        // Apply request interceptors
        let config = { path: endpoint, ...options };
        for (const interceptor of this.requestInterceptors) {
            config = await interceptor(config);
        }

        try {
            let response = await apiFetch(config);

            // Apply response interceptors
            for (const interceptor of this.responseInterceptors) {
                response = await interceptor(response);
            }

            // Cache successful GET responses
            if (useCache) {
                const cacheKey = this.getCacheKey(endpoint, options);
                this.setCache(cacheKey, response);
            }

            return response;
        } catch (error) {
            // Apply error interceptors
            let processedError = error;
            for (const interceptor of this.errorInterceptors) {
                processedError = await interceptor(processedError);
            }
            throw processedError;
        }
    }

    /**
     * GET request
     */
    get(endpoint, options = {}) {
        return this.request(endpoint, { ...options, method: 'GET' });
    }

    /**
     * POST request
     */
    post(endpoint, data, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'POST',
            data,
            cache: false,
        });
    }

    /**
     * PUT request
     */
    put(endpoint, data, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'PUT',
            data,
            cache: false,
        });
    }

    /**
     * DELETE request
     */
    delete(endpoint, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'DELETE',
            cache: false,
        });
    }

    /**
     * PATCH request
     */
    patch(endpoint, data, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'PATCH',
            data,
            cache: false,
        });
    }
}

// Export singleton instance
export const blockxpertAPI = new BlockXpertAPIClient();

export default blockxpertAPI;
