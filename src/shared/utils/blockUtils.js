/**
 * Shared utility functions for BlockXpert blocks
 * Common functionality used across multiple blocks
 */

import { __ } from '@wordpress/i18n';

/**
 * Filter array items based on search term
 * @param {Array} items - Items to filter
 * @param {string} searchTerm - Search term
 * @param {Array} searchFields - Fields to search in
 * @returns {Array} Filtered items
 */
export const filterItems = (items, searchTerm, searchFields = ['name', 'title', 'question']) => {
    if (!searchTerm) return items;
    
    const normalized = searchTerm.toLowerCase();
    return items.filter(item => 
        searchFields.some(field => 
            item[field]?.toLowerCase().includes(normalized)
        )
    );
};

/**
 * Group array items by a specific property
 * @param {Array} items - Items to group
 * @param {string} key - Property key to group by
 * @returns {Object} Grouped items object
 */
export const groupBy = (items, key) => {
    return items.reduce((groups, item) => {
        const groupKey = item[key] || 'default';
        if (!groups[groupKey]) {
            groups[groupKey] = [];
        }
        groups[groupKey].push(item);
        return groups;
    }, {});
};

/**
 * Generate unique ID
 * @param {string} prefix - ID prefix
 * @returns {string} Unique ID
 */
export const generateId = (prefix = 'id') => {
    return `${prefix}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
};

/**
 * Debounce function execution
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export const debounce = (func, delay = 300) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
};

/**
 * Sanitize HTML content
 * @param {string} html - HTML to sanitize
 * @returns {string} Sanitized HTML
 */
export const sanitizeHtml = (html) => {
    if (typeof html !== 'string') return '';
    
    const div = document.createElement('div');
    div.textContent = html;
    return div.innerHTML;
};

/**
 * Format error message for display
 * @param {Error|string} error - Error object or message
 * @returns {string} Formatted error message
 */
export const formatErrorMessage = (error) => {
    if (typeof error === 'string') return error;
    if (error?.message) return error.message;
    return __('An unknown error occurred', 'blockxpert');
};

/**
 * Validate required fields
 * @param {Object} data - Data to validate
 * @param {Array} requiredFields - Required field names
 * @returns {Object} Validation result { valid: boolean, errors: string[] }
 */
export const validateRequired = (data, requiredFields) => {
    const errors = [];
    
    requiredFields.forEach(field => {
        if (!data[field] || (typeof data[field] === 'string' && !data[field].trim())) {
            errors.push(`${field} is required`);
        }
    });
    
    return {
        valid: errors.length === 0,
        errors,
    };
};

/**
 * Merge style objects safely
 * @param {...Object} styles - Style objects to merge
 * @returns {Object} Merged style object
 */
export const mergeStyles = (...styles) => {
    return styles.reduce((merged, style) => {
        return { ...merged, ...style };
    }, {});
};

/**
 * Get block attribute with fallback
 * @param {Object} attributes - Block attributes
 * @param {string} key - Attribute key
 * @param {*} fallback - Fallback value
 * @returns {*} Attribute value or fallback
 */
export const getAttributeValue = (attributes, key, fallback = '') => {
    return attributes[key] !== undefined && attributes[key] !== null 
        ? attributes[key] 
        : fallback;
};

export default {
    filterItems,
    groupBy,
    generateId,
    debounce,
    sanitizeHtml,
    formatErrorMessage,
    validateRequired,
    mergeStyles,
    getAttributeValue,
};
