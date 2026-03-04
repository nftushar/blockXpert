/**
 * Register BlockXpert block category on the client-side
 * This ensures the category appears in the block inserter UI
 */

console.log( '[BlockXpert] register-block-category.js loaded' );

// Method 1: Use setCategories if available (WordPress 5.8+)
if ( typeof wp !== 'undefined' && wp.blocks && typeof wp.blocks.setCategories === 'function' ) {
    console.log( '[BlockXpert] Method 1: Using wp.blocks.setCategories' );
    
    try {
        const currentCategories = wp.blocks.getCategories();
        console.log( '[BlockXpert] Current categories:', currentCategories.map( c => c.slug ) );
        
        const blockxpertExists = currentCategories.some( c => c.slug === 'blockxpert' );
        
        if ( ! blockxpertExists ) {
            console.log( '[BlockXpert] BlockXpert category not found, adding it' );
            const newCategories = [
                ...currentCategories,
                {
                    slug: 'blockxpert',
                    title: 'BlockXpert',
                    icon: null
                }
            ];
            wp.blocks.setCategories( newCategories );
            console.log( '[BlockXpert] Category added via setCategories' );
        } else {
            console.log( '[BlockXpert] BlockXpert category already exists' );
        }
    } catch ( e ) {
        console.error( '[BlockXpert] Error in Method 1:', e );
    }
}

// Method 2: Use data store if available
else if ( typeof wp !== 'undefined' && wp.data && typeof wp.data.dispatch === 'function' ) {
    console.log( '[BlockXpert] Method 2: Using wp.data dispatch' );
    
    try {
        const blockEditorStore = wp.data.select( 'core/editor' ) || wp.data.select( 'core/block-editor' );
        if ( blockEditorStore && typeof blockEditorStore.getEditorSettings === 'function' ) {
            const settings = blockEditorStore.getEditorSettings();
            const categories = settings.categories || [];
            console.log( '[BlockXpert] Editor categories:', categories.map( c => c.slug ) );
            
            const blockxpertExists = categories.some( c => c.slug === 'blockxpert' );
            if ( ! blockxpertExists ) {
                const newCategories = [
                    ...categories,
                    { slug: 'blockxpert', title: 'BlockXpert', icon: null }
                ];
                wp.data.dispatch( 'core/editor' ).updateEditorSettings( { categories: newCategories } );
                console.log( '[BlockXpert] Category added via dispatch' );
            }
        }
    } catch ( e ) {
        console.error( '[BlockXpert] Error in Method 2:', e );
    }
}

else {
    console.warn( '[BlockXpert] No suitable API found for registering category' );
    console.log( '[BlockXpert] Available:', { 'wp.blocks': typeof wp?.blocks, 'wp.data': typeof wp?.data } );
}


