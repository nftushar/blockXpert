import { createContext, useContext, useReducer, useCallback, useEffect } from '@wordpress/element';

// Create context
export const BlockXpertContext = createContext();

// Action types
export const ACTIONS = {
    // API state
    SET_LOADING: 'SET_LOADING',
    SET_ERROR: 'SET_ERROR',
    CLEAR_ERROR: 'CLEAR_ERROR',
    
    // Blocks state
    SET_ACTIVE_BLOCKS: 'SET_ACTIVE_BLOCKS',
    SET_BLOCK_CONFIG: 'SET_BLOCK_CONFIG',
    
    // Settings state
    SET_SETTINGS: 'SET_SETTINGS',
    UPDATE_SETTING: 'UPDATE_SETTING',
    
    // Cache state
    SET_CACHE_ENABLED: 'SET_CACHE_ENABLED',
    CLEAR_CACHE: 'CLEAR_CACHE',
};

// Initial state
const initialState = {
    loading: false,
    error: null,
    activeBlocks: [],
    blockConfigs: {},
    settings: {},
    cacheEnabled: true,
    lastUpdated: null,
};

// Reducer
function blockxpertReducer(state, action) {
    switch (action.type) {
        case ACTIONS.SET_LOADING:
            return { ...state, loading: action.payload };
        
        case ACTIONS.SET_ERROR:
            return { ...state, error: action.payload, loading: false };
        
        case ACTIONS.CLEAR_ERROR:
            return { ...state, error: null };
        
        case ACTIONS.SET_ACTIVE_BLOCKS:
            return { ...state, activeBlocks: action.payload, lastUpdated: Date.now() };
        
        case ACTIONS.SET_BLOCK_CONFIG:
            return {
                ...state,
                blockConfigs: {
                    ...state.blockConfigs,
                    [action.payload.blockName]: action.payload.config,
                },
            };
        
        case ACTIONS.SET_SETTINGS:
            return { ...state, settings: action.payload, lastUpdated: Date.now() };
        
        case ACTIONS.UPDATE_SETTING:
            return {
                ...state,
                settings: {
                    ...state.settings,
                    [action.payload.key]: action.payload.value,
                },
            };
        
        case ACTIONS.SET_CACHE_ENABLED:
            return { ...state, cacheEnabled: action.payload };
        
        case ACTIONS.CLEAR_CACHE:
            return {
                ...state,
                blockConfigs: {},
                settings: {},
            };
        
        default:
            return state;
    }
}

/**
 * BlockXpert Provider Component
 */
export function BlockXpertProvider({ children }) {
    const [state, dispatch] = useReducer(blockxpertReducer, initialState);
    
    // Memoized action creators
    const actions = {
        setLoading: useCallback((loading) => {
            dispatch({ type: ACTIONS.SET_LOADING, payload: loading });
        }, []),
        
        setError: useCallback((error) => {
            dispatch({ type: ACTIONS.SET_ERROR, payload: error });
        }, []),
        
        clearError: useCallback(() => {
            dispatch({ type: ACTIONS.CLEAR_ERROR });
        }, []),
        
        setActiveBlocks: useCallback((blocks) => {
            dispatch({ type: ACTIONS.SET_ACTIVE_BLOCKS, payload: blocks });
        }, []),
        
        setBlockConfig: useCallback((blockName, config) => {
            dispatch({
                type: ACTIONS.SET_BLOCK_CONFIG,
                payload: { blockName, config },
            });
        }, []),
        
        setSettings: useCallback((settings) => {
            dispatch({ type: ACTIONS.SET_SETTINGS, payload: settings });
        }, []),
        
        updateSetting: useCallback((key, value) => {
            dispatch({
                type: ACTIONS.UPDATE_SETTING,
                payload: { key, value },
            });
        }, []),
        
        setCacheEnabled: useCallback((enabled) => {
            dispatch({ type: ACTIONS.SET_CACHE_ENABLED, payload: enabled });
        }, []),
        
        clearCache: useCallback(() => {
            dispatch({ type: ACTIONS.CLEAR_CACHE });
        }, []),
    };
    
    const contextValue = {
        state,
        dispatch,
        actions,
    };
    
    return (
        <BlockXpertContext.Provider value={contextValue}>
            {children}
        </BlockXpertContext.Provider>
    );
}

/**
 * Hook to use BlockXpert context
 */
export function useBlockXpert() {
    const context = useContext(BlockXpertContext);
    
    if (!context) {
        throw new Error(
            'useBlockXpert must be used within BlockXpertProvider'
        );
    }
    
    return context;
}

/**
 * Hook to use only the state
 */
export function useBlockXpertState() {
    const { state } = useBlockXpert();
    return state;
}

/**
 * Hook to use only the actions
 */
export function useBlockXpertActions() {
    const { actions } = useBlockXpert();
    return actions;
}
