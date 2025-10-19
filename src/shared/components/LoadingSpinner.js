/**
 * Loading Spinner Component
 * Reusable loading indicator with customizable options
 */

import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function LoadingSpinner({ 
    message = __('Loading...', 'blockxpert'),
    size = 'default',
    className = '',
    showMessage = true
}) {
    const spinnerClasses = [
        'blockxpert-loading-spinner',
        `blockxpert-loading-spinner--${size}`,
        className
    ].filter(Boolean).join(' ');

    return (
        <div className={spinnerClasses}>
            <Spinner size={size} />
            {showMessage && (
                <p className="blockxpert-loading-message">
                    {message}
                </p>
            )}
        </div>
    );
}
