    // save.js
    import { __ } from '@wordpress/i18n';


    export function save( { attributes } ) {
        console.log(attributes); // Check the content attribute
    
        if (!attributes.content) {
            return (
                <div>
                    <h2>{__('1 Block One', 'gutenberg-blocks')}</h2>
                    <p>{__('This is the first example block.', 'gutenberg-blocks')}</p>
                    <p>{__('No content entered.', 'gutenberg-blocks')}</p> {/* Fallback message */}
                </div>
            );
        }
    
        return (
            <div>
                <h2>{__('Block One', 'gutenberg-blocks')}</h2>
                <p>{__('This is the first example block.', 'gutenberg-blocks')}</p>
                <p>{attributes.content}</p> {/* Display the content on the front-end */}
            </div>
        );
    }
    
