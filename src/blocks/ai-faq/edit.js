import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    RangeControl, 
    ToggleControl, 
    SelectControl,
    Placeholder,
    Button,
    TextareaControl,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
    const [loading, setLoading] = useState(false);
    const [aiResponse, setAiResponse] = useState('');
    const [editingQuestion, setEditingQuestion] = useState(null);
    const [expandedQuestions, setExpandedQuestions] = useState(new Set());
    const [searchTerm, setSearchTerm] = useState('');

    const {
        title,
        aiEnabled,
        maxQuestions,
        autoGenerate,
        showSearch,
        accordionStyle,
        theme,
        questions,
        apiKey,
        model
    } = attributes;

    const toggleQuestion = (index) => {
        const newExpanded = new Set(expandedQuestions);
        if (newExpanded.has(index)) {
            newExpanded.delete(index);
        } else {
            newExpanded.add(index);
        }
        setExpandedQuestions(newExpanded);
    };

    const addQuestion = () => {
        const newQuestion = {
            question: '',
            answer: '',
            id: Date.now()
        };
        setAttributes({ questions: [...questions, newQuestion] });
        setEditingQuestion(newQuestion.id);
    };

    const updateQuestion = (index, field, value) => {
        const updatedQuestions = [...questions];
        updatedQuestions[index] = { ...updatedQuestions[index], [field]: value };
        setAttributes({ questions: updatedQuestions });
    };

    const deleteQuestion = (index) => {
        const updatedQuestions = questions.filter((_, i) => i !== index);
        setAttributes({ questions: updatedQuestions });
    };

    const generateAIQuestions = async () => {
        if (!apiKey) {
            alert(__('Please enter your OpenAI API key in the block settings.', 'blockxpert'));
            return;
        }

        setLoading(true);
        try {
            const response = await fetch('/wp-json/blockxpert/v1/generate-faq', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify({
                    apiKey,
                    model,
                    maxQuestions,
                    context: 'Generate relevant FAQ questions and answers for a website'
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.questions) {
                    setAttributes({ questions: data.questions });
                    setAiResponse(__('FAQ questions generated successfully!', 'blockxpert'));
                } else {
                    setAiResponse(__('Failed to generate questions. Please check your API key.', 'blockxpert'));
                }
            } else {
                setAiResponse(__('Error generating questions. Please try again.', 'blockxpert'));
            }
        } catch (error) {
            console.error('Error generating FAQ:', error);
            setAiResponse(__('Error generating questions. Please try again.', 'blockxpert'));
        } finally {
            setLoading(false);
        }
    };

    const filteredQuestions = questions.filter(q => 
        q.question.toLowerCase().includes(searchTerm.toLowerCase()) ||
        q.answer.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div {...useBlockProps({ className: `ai-faq-block theme-${theme}` })}>
            <InspectorControls>
                <PanelBody title={__('FAQ Settings', 'blockxpert')} initialOpen={true}>
                    <TextControl
                        label={__('FAQ Title', 'blockxpert')}
                        value={title}
                        onChange={(title) => setAttributes({ title })}
                        placeholder={__('Enter FAQ title...', 'blockxpert')}
                    />
                    
                    <ToggleControl
                        label={__('Enable AI Generation', 'blockxpert')}
                        checked={aiEnabled}
                        onChange={(aiEnabled) => setAttributes({ aiEnabled })}
                    />
                    
                    <ToggleControl
                        label={__('Auto Generate Questions', 'blockxpert')}
                        checked={autoGenerate}
                        onChange={(autoGenerate) => setAttributes({ autoGenerate })}
                    />
                    
                    <ToggleControl
                        label={__('Show Search Bar', 'blockxpert')}
                        checked={showSearch}
                        onChange={(showSearch) => setAttributes({ showSearch })}
                    />
                </PanelBody>
                
                <PanelBody title={__('AI Settings', 'blockxpert')} initialOpen={false}>
                    <TextControl
                        label={__('OpenAI API Key', 'blockxpert')}
                        value={apiKey}
                        onChange={(apiKey) => setAttributes({ apiKey })}
                        type="password"
                        placeholder={__('Enter your OpenAI API key...', 'blockxpert')}
                    />
                    
                    <SelectControl
                        label={__('AI Model', 'blockxpert')}
                        value={model}
                        options={[
                            { label: 'GPT-3.5 Turbo', value: 'gpt-3.5-turbo' },
                            { label: 'GPT-4', value: 'gpt-4' },
                            { label: 'GPT-4 Turbo', value: 'gpt-4-turbo-preview' }
                        ]}
                        onChange={(model) => setAttributes({ model })}
                    />
                    
                    <RangeControl
                        label={__('Maximum Questions', 'blockxpert')}
                        value={maxQuestions}
                        onChange={(maxQuestions) => setAttributes({ maxQuestions })}
                        min={1}
                        max={20}
                    />
                    
                    {aiEnabled && (
                        <Button
                            isPrimary
                            onClick={generateAIQuestions}
                            isBusy={loading}
                            disabled={!apiKey}
                        >
                            {loading ? __('Generating...', 'blockxpert') : __('Generate AI Questions', 'blockxpert')}
                        </Button>
                    )}
                </PanelBody>
                
                <PanelBody title={__('Display Settings', 'blockxpert')} initialOpen={false}>
                    <SelectControl
                        label={__('Accordion Style', 'blockxpert')}
                        value={accordionStyle}
                        options={[
                            { label: __('Expandable', 'blockxpert'), value: 'expandable' },
                            { label: __('Always Open', 'blockxpert'), value: 'always-open' },
                            { label: __('Single Open', 'blockxpert'), value: 'single-open' }
                        ]}
                        onChange={(accordionStyle) => setAttributes({ accordionStyle })}
                    />
                    
                    <SelectControl
                        label={__('Theme', 'blockxpert')}
                        value={theme}
                        options={[
                            { label: __('Light', 'blockxpert'), value: 'light' },
                            { label: __('Dark', 'blockxpert'), value: 'dark' },
                            { label: __('Minimal', 'blockxpert'), value: 'minimal' }
                        ]}
                        onChange={(theme) => setAttributes({ theme })}
                    />
                </PanelBody>
            </InspectorControls>

            <div className="ai-faq-editor">
                <h2 className="faq-title">{title || __('AI FAQ', 'blockxpert')}</h2>
                
                {aiResponse && (
                    <Notice 
                        status={aiResponse.includes('successfully') ? 'success' : 'error'}
                        onRemove={() => setAiResponse('')}
                    >
                        {aiResponse}
                    </Notice>
                )}
                
                {showSearch && (
                    <div className="faq-search">
                        <TextControl
                            placeholder={__('Search questions...', 'blockxpert')}
                            value={searchTerm}
                            onChange={setSearchTerm}
                        />
                    </div>
                )}
                
                {questions.length === 0 ? (
                    <Placeholder
                        icon="editor-help"
                        label={__('No FAQ Questions', 'blockxpert')}
                        instructions={__('Add your first FAQ question or generate AI-powered questions.', 'blockxpert')}
                    >
                        <div className="faq-placeholder-actions">
                            <Button
                                isPrimary
                                onClick={addQuestion}
                            >
                                {__('Add Question', 'blockxpert')}
                            </Button>
                            
                            {aiEnabled && (
                                <Button
                                    onClick={generateAIQuestions}
                                    isBusy={loading}
                                    disabled={!apiKey}
                                >
                                    {__('Generate AI Questions', 'blockxpert')}
                                </Button>
                            )}
                        </div>
                    </Placeholder>
                ) : (
                    <div className="faq-questions">
                        {filteredQuestions.map((question, index) => (
                            <div key={question.id || index} className="faq-question">
                                {editingQuestion === question.id ? (
                                    <div className="faq-edit-form">
                                        <TextControl
                                            label={__('Question', 'blockxpert')}
                                            value={question.question}
                                            onChange={(value) => updateQuestion(index, 'question', value)}
                                            placeholder={__('Enter your question...', 'blockxpert')}
                                        />
                                        <TextareaControl
                                            label={__('Answer', 'blockxpert')}
                                            value={question.answer}
                                            onChange={(value) => updateQuestion(index, 'answer', value)}
                                            placeholder={__('Enter your answer...', 'blockxpert')}
                                            rows={4}
                                        />
                                        <div className="faq-edit-actions">
                                            <Button
                                                isPrimary
                                                onClick={() => setEditingQuestion(null)}
                                            >
                                                {__('Save', 'blockxpert')}
                                            </Button>
                                            <Button
                                                onClick={() => {
                                                    setEditingQuestion(null);
                                                    deleteQuestion(index);
                                                }}
                                            >
                                                {__('Delete', 'blockxpert')}
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="faq-question-content">
                                        <div 
                                            className="faq-question-header"
                                            onClick={() => toggleQuestion(index)}
                                        >
                                            <h3 className="faq-question-text">{question.question || __('Untitled Question', 'blockxpert')}</h3>
                                            <div className="faq-question-actions">
                                                <Button
                                                    isSmall
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        setEditingQuestion(question.id);
                                                    }}
                                                >
                                                    {__('Edit', 'blockxpert')}
                                                </Button>
                                                <span className="faq-toggle-icon">
                                                    {expandedQuestions.has(index) ? '−' : '+'}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        {expandedQuestions.has(index) && (
                                            <div className="faq-answer">
                                                <p>{question.answer || __('No answer provided.', 'blockxpert')}</p>
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        ))}
                        
                        <div className="faq-actions">
                            <Button
                                isPrimary
                                onClick={addQuestion}
                            >
                                {__('Add Question', 'blockxpert')}
                            </Button>
                            
                            {aiEnabled && (
                                <Button
                                    onClick={generateAIQuestions}
                                    isBusy={loading}
                                    disabled={!apiKey}
                                >
                                    {__('Generate More AI Questions', 'blockxpert')}
                                </Button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
} 