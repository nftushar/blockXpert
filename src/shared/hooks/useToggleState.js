/**
 * Custom hook for managing toggle/accordion states
 * Useful for managing multiple expanded items across blocks
 */

import { useState, useCallback } from '@wordpress/element';

/**
 * Hook for managing toggle states (Set-based)
 * @param {Array} initialItems - Initial expanded items
 * @returns {Object} Expanded state and toggle/clear functions
 */
export const useToggleState = (initialItems = []) => {
    const [expanded, setExpanded] = useState(new Set(initialItems));

    const toggle = useCallback((item) => {
        setExpanded((prevExpanded) => {
            const newExpanded = new Set(prevExpanded);
            if (newExpanded.has(item)) {
                newExpanded.delete(item);
            } else {
                newExpanded.add(item);
            }
            return newExpanded;
        });
    }, []);

    const isExpanded = useCallback((item) => {
        return expanded.has(item);
    }, [expanded]);

    const expandAll = useCallback((items) => {
        setExpanded(new Set(items));
    }, []);

    const collapseAll = useCallback(() => {
        setExpanded(new Set());
    }, []);

    const clear = useCallback(() => {
        setExpanded(new Set());
    }, []);

    return {
        expanded,
        toggle,
        isExpanded,
        expandAll,
        collapseAll,
        clear,
    };
};

export default useToggleState;
