/**
 * Error Boundary Component
 * Catches JavaScript errors anywhere in the child component tree
 */

import { Component } from '@wordpress/element';
import { Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { 
            hasError: false, 
            error: null,
            errorInfo: null 
        };
    }

    static getDerivedStateFromError(error) {
        // Update state so the next render will show the fallback UI
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        // Log error details
        console.error('BlockXpert Error:', error, errorInfo);
        
        this.setState({
            error: error,
            errorInfo: errorInfo
        });

        // Report to error tracking service if available
        if (window.BlockXpert?.errorReporting) {
            window.BlockXpert.errorReporting.report(error, errorInfo);
        }
    }

    render() {
        if (this.state.hasError) {
            return (
                <Notice 
                    status="error" 
                    isDismissible={false}
                    className="blockxpert-error-boundary"
                >
                    <div className="blockxpert-error-content">
                        <h4>{__('Something went wrong with this block.', 'blockxpert')}</h4>
                        <p>{__('Please try refreshing the page or contact support if the problem persists.', 'blockxpert')}</p>
                        
                        {this.props.showDetails && this.state.error && (
                            <details className="blockxpert-error-details">
                                <summary>{__('Error Details', 'blockxpert')}</summary>
                                <pre>{this.state.error.toString()}</pre>
                                {this.state.errorInfo && (
                                    <pre>{this.state.errorInfo.componentStack}</pre>
                                )}
                            </details>
                        )}
                    </div>
                </Notice>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
