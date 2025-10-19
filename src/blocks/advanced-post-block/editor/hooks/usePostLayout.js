import { useState, useEffect } from '@wordpress/element';

export const usePostLayout = (attributes) => {
    const { layout = 'grid', columns = 3 } = attributes || {};
    
    return {
        gridColumns: layout === 'list' ? 1 : columns,
        gapSize: '2rem',
    };
};
