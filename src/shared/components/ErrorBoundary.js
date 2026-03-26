/**
 * Enhanced Error Boundary Component
 * Catches JavaScript errors with recovery and logging
 */

import { Component } from '@wordpress/element';
import { Notice, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { 
            hasError: false, 
            error: null,
            errorInfo: null,
            errorCount: 0,
            showDetails: false,
        };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        console.error('BlockXpert Error:', error, errorInfo);
        
        this.setState(prevState => ({
            error,
            errorInfo,
            errorCount: prevState.errorCount + 1,
        }));

        // Report to error tracking
        if (window.BlockXpert?.errorReporting) {
            window.BlockXpert.errorReporting.report(error, errorInfo);
        }

        // Call error callback if provided
        if (this.props.onError) {
            this.props.onError(error, errorInfo);
        }
    }

    handleReset = () => {
        this.setState({
            hasError: false,
            error: null,
            errorInfo: null,
        });

        if (this.props.onReset) {
            this.props.onReset();
        }
    };

    toggleDetails = () => {
        this.setState(prevState => ({
            showDetails: !prevState.showDetails,
        }));
    };

    render() {
        if (this.state.hasError) {
            const isDev = window?.blockxpertConfig?.isDevelopment || false;
            
            return (
                <Notice 
                    status="error" 
                    isDismissible={true}
                    className="blockxpert-error-boundary"
                    onRemove={this.handleReset}
                >
                    <div className="blockxpert-error-content">
                        <h4>{__('Something went wrong with this block.', 'blockxpert')}</h4>
                        <p>{__('Please try refreshing the page or contact support if the problem persists.', 'blockxpert')}</p>
                        <p style={{ fontSize: '12px', color: '#555' }}>
                            {__('Error count: ', 'blockxpert')} {this.state.errorCount}
                        </p>
                        
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
