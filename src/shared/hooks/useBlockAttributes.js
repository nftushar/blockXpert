/**
 * Custom hook for managing block attributes
 * Centralizes attribute management and state synchronization
 */

import { useCallback } from '@wordpress/element';

/**
 * Hook for managing block attributes with helper methods
 * @param {Object} attributes - Block attributes
 * @param {Function} setAttributes - WordPress setAttributes function
 * @returns {Object} Attributes with helper methods
 */
export const useBlockAttributes = (attributes, setAttributes) => {
    const updateAttribute = useCallback((key, value) => {
        setAttributes({ [key]: value });
    }, [setAttributes]);

    const updateNestedAttribute = useCallback((parentKey, childKey, value) => {
        setAttributes({
            [parentKey]: {
                ...attributes[parentKey],
                [childKey]: value,
            },
        });
    }, [attributes, setAttributes]);

    const updateArrayItem = useCallback((arrayKey, index, field, value) => {
        const updatedArray = [...attributes[arrayKey]];
        updatedArray[index] = {
            ...updatedArray[index],
            [field]: value,
        };
        setAttributes({ [arrayKey]: updatedArray });
    }, [attributes, setAttributes]);

    const removeArrayItem = useCallback((arrayKey, index) => {
        const updatedArray = attributes[arrayKey].filter((_, i) => i !== index);
        setAttributes({ [arrayKey]: updatedArray });
    }, [attributes, setAttributes]);

    const addArrayItem = useCallback((arrayKey, item) => {
        setAttributes({
            [arrayKey]: [...(attributes[arrayKey] || []), item],
        });
    }, [attributes, setAttributes]);

    return {
        ...attributes,
        updateAttribute,
        updateNestedAttribute,
        updateArrayItem,
        removeArrayItem,
        addArrayItem,
    };
};

export default useBlockAttributes;
