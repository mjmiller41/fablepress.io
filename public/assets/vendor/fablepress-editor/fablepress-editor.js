/**
 * FablePress Zen Composer - Editor Wrapper and Initializer
 * Coordinates loading core plugins and third-party extensions.
 */
(function() {
    // Ensure namespace exists
    window.FableEditor = window.FableEditor || {};

    /**
     * Initializes the editor on a target DOM element.
     * 
     * @param {Object} config Configurations for initialization
     * @param {HTMLElement} config.el The target DOM element container
     * @param {string} config.initialValue The initial Markdown content
     * @param {string} [config.placeholder="Start typing your story..."] Placeholder text
     * @param {string} [config.height="600px"] Editor container height
     * @param {Function} [config.onUploadImage] Image upload handler (optional)
     * @param {Function} [config.onChange] Change callback event (optional)
     * @returns {Object} The ZenComposer Editor instance
     */
    window.FableEditor.init = function(config) {
        if (!config.el) {
            console.error("FableEditor: Target element ('el') is required.");
            return null;
        }

        // 1. Load default core plugins
        const defaultPlugins = [];
        
        // Add color syntax if available
        if (window.zenComposer && window.zenComposer.Editor && window.zenComposer.Editor.plugin && window.zenComposer.Editor.plugin.colorSyntax) {
            defaultPlugins.push(window.zenComposer.Editor.plugin.colorSyntax);
        }
        
        // Add table cell merging if available
        if (window.zenComposer && window.zenComposer.Editor && window.zenComposer.Editor.plugin && window.zenComposer.Editor.plugin.tableMergedCell) {
            defaultPlugins.push(window.zenComposer.Editor.plugin.tableMergedCell);
        }
        
        // Add code syntax highlight if available
        if (window.zenComposer && window.zenComposer.Editor && window.zenComposer.Editor.plugin && window.zenComposer.Editor.plugin.codeSyntaxHighlight) {
            defaultPlugins.push(window.zenComposer.Editor.plugin.codeSyntaxHighlight);
        }

        // 2. Fetch custom developer-registered plugins from window.FableEditorPlugins hook
        const customPlugins = window.FableEditorPlugins || [];

        // Combine core and custom plugins
        const activePlugins = [...defaultPlugins, ...customPlugins];

        // 3. Setup hooks (e.g. Image Upload Hook)
        const activeHooks = {};
        if (config.onUploadImage) {
            activeHooks.addImageBlobHook = config.onUploadImage;
        }

        // 4. Construct editor instance
        const editorInstance = new zenComposer.Editor({
            el: config.el,
            initialValue: config.initialValue || '',
            placeholder: config.placeholder || 'Start writing your story in Markdown or plain text...',
            height: config.height || 'calc(100vh - 200px)',
            initialEditType: 'wysiwyg',
            previewStyle: 'vertical',
            theme: 'default',
            plugins: activePlugins,
            hooks: activeHooks,
            customHTMLRenderer: {
                image(node, context) {
                    const destination = node.destination || (node.literal && node.literal.destination) || '';
                    const altText = node.altText || (node.literal && node.literal.altText) || '';
                    
                    context.skipChildren();
                    
                    let width = '';
                    let height = '';
                    
                    if (destination) {
                        const hashIndex = destination.indexOf('#');
                        const queryIndex = destination.indexOf('?');
                        let queryStr = '';
                        
                        if (hashIndex !== -1) {
                            queryStr = destination.substring(hashIndex + 1);
                        } else if (queryIndex !== -1) {
                            queryStr = destination.substring(queryIndex + 1);
                        }
                        
                        if (queryStr) {
                            const params = {};
                            queryStr.split('&').forEach(part => {
                                const pair = part.split('=');
                                if (pair[0]) params[pair[0]] = pair[1] || '';
                            });
                            
                            const w = params.width || params.w;
                            const h = params.height || params.h;
                            
                            if (w) width = w;
                            if (h) height = h;
                            
                            if (!width) {
                                const match = queryStr.match(/^(\d+)(x(\d+))?$/);
                                if (match) {
                                    width = match[1];
                                    if (match[3]) height = match[3];
                                }
                            }
                        }
                    }
                    
                    const attributes = {
                        src: destination,
                        alt: altText || ''
                    };
                    
                    if (width) {
                        attributes.width = width.match(/[^0-9]/) ? width : width + 'px';
                        attributes.style = `width: ${attributes.width}; max-width: 100%; height: ${height ? (height.match(/[^0-9]/) ? height : height + 'px') : 'auto'};`;
                    }
                    if (height) {
                        attributes.height = height.match(/[^0-9]/) ? height : height + 'px';
                    }
                    
                    return {
                        type: 'openTag',
                        tagName: 'img',
                        attributes: attributes,
                        isSelfClosing: true
                    };
                }
            },
            toolbarItems: [
                ['heading', 'bold', 'italic', 'strike'],
                ['hr', 'quote'],
                ['ul', 'ol', 'task', 'indent', 'outdent'],
                ['table', 'image', 'link'],
                ['code', 'codeblock']
            ]
        });

        // 5. Setup custom change triggers
        if (config.onChange) {
            editorInstance.on('change', function() {
                config.onChange(editorInstance.getMarkdown());
            });
        }

        // Expose instance on wrapper namespace
        window.FableEditor.activeInstance = editorInstance;

        return editorInstance;
    };
})();
