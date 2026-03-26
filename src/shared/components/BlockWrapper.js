/**
 * BlockWrapper Component
 * Provides common block functionality and layout
 */

import { useBlockProps } from '@wordpress/block-editor';
import { Spinner } from '@wordpress/components';
import './BlockWrapper.scss';

export function BlockWrapper({
    children,
    loading = false,
    error = null,
    className = '',
    blockPropsConfig = {},
}) {
    const blockProps = useBlockProps({
        className: `blockxpert-block-wrapper ${className}`,
        ...blockPropsConfig,
    });

    if (error) {
        return (
            <div {...blockProps}>
                <div className="blockxpert-error-notice">
                    <p className="components-notice is-error">
                        {error}
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div {...blockProps}>
            {loading && (
                <div className="blockxpert-loading">
                    <Spinner />
                </div>
            )}
            {!loading && children}
        </div>
    );
}

export default BlockWrapper;
