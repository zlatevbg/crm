function ckeditorSetup(config) {
    {{-- if (typeof CKFinder == 'object') {
        CKFinder.basePath = CKEDITOR.basePath.replace('ckeditor', 'ckfinder');

        CKFinder.config({
            configPath: '', // don't load config.js
            defaultDisplayDate: false,
            defaultDisplayFileName: false,
            defaultDisplayFileSize: false,
            defaultSortBy: 'date',
            defaultSortByOrder: 'desc',
            editImageAdjustments: [
                'brightness', 'clip', 'contrast', 'exposure', 'gamma', 'hue', 'noise', 'saturation', 'sepia', 'sharpen', 'stackBlur', 'vibrance',
            ],
            editImagePresets: [
                'clarity', 'concentrate', 'crossProcess', 'glowingSun', 'grungy', 'hazyDays', 'hemingway', 'herMajesty', 'jarques', 'lomo', 'love', 'nostalgia', 'oldBoot', 'orangePeel', 'pinhole', 'sinCity', 'sunrise', 'vintage',
            ],
            // connectorInfo: 'token=7901a26e4bc422aef54eb45', // Additional (GET) parameters to send to the server with each request.
        });

        CKFinder.setupCKEditor();
    } else {
        $.extend(config, {
            removePlugins: 'filebrowser,filetools,uploadfile,uploadimage,uploadwidget',
        });
    } --}}

    var defaultConfig = {
        customConfig: '', // don't load config.js
        disableNativeSpellChecker: false,
        forcePasteAsPlainText: true,
        // embed_provider: '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}',
        removePlugins: '',
        extraPlugins: 'placeholder_elements',
        autosave: {
            delay: 10, // seconds
            messageType: 'statusbar',
            saveOnDestroy: true,
            saveDetectionSelectors: 'button[type="submit"]',
        },
        // autoGrow_maxHeight: 400,
        autoGrow_minHeight: 200,
        autoGrow_onStartup: true,
        extraAllowedContent: '',
        disallowedContent: '',
        // colorButton_colors: 'ffffff,000000,364f9d,0e76bc,3a99d3,6ebbe8,f4fbfe,f6f6f6',
        // contentsCss: 'https://sky.mespil.ie/css/sky/main.css',
        fontSize_sizes: 'Tiny/0.75rem;Small/0.875rem;Default/1rem;Big/1.25rem;Huge/1.5rem',
        font_names: 'Default/-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol',
        format_tags: 'p;h1;h2;h3;h4;h5;h6',
        justifyClasses: ['text-left', 'text-center', 'text-right', 'text-justify'],
        removeButtons: 'Cut,Copy,Anchor',
        removeDialogTabs: '',
        stylesSet: [ // see the styles.js
            { name: 'Strong Emphasis', element: 'strong' },
            { name: 'Emphasis', element: 'em' },
        ],
        placeholder_elements: {
            css: '.cke_placeholder_element { background: #ffff00; } a .cke_placeholder_element { text-decoration: underline }',
            draggable: true,
            placeholders: [
                { label: 'Name', value: 'NAME' },
                { label: 'First Name', value: 'FIRST_NAME' },
                { label: 'Last Name', value: 'LAST_NAME' },
                { label: 'Company', value: 'COMPANY' },
                { label: 'E-mail', value: 'EMAIL' },
                { label: 'Image', value: 'IMAGE' },
            ],
            startDelimiter: '[[',
            endDelimiter: ']]',
            uiType: 'combo',
        },
        toolbarGroups: [
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
            { name: 'colors', groups: [ 'colors' ] },
            { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
            { name: 'document', groups: [ 'doctools', 'mode', 'document' ] },
            { name: 'styles', groups: [ 'styles' ] },
            { name: 'forms', groups: [ 'forms' ] },
            { name: 'custom', groups: [ 'custom' ] },
            { name: 'links', groups: [ 'links' ] },
            { name: 'insert', groups: [ 'insert' ] },
            { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
            { name: 'tools', groups: [ 'tools' ] },
            { name: 'others', groups: [ 'others' ] },
            { name: 'about', groups: [ 'about' ] }
        ],
    };

    $.extend(defaultConfig, config);

    CKEDITOR.replaceAll(function(textarea, config) {
        if ($(textarea).hasClass('ckeditor')) {
            $.extend(config, defaultConfig);
        } else {
            return false;
        }
    });
}

var globalCKEditorConfig = globalCKEditorConfig || {};
ckeditorSetup(globalCKEditorConfig);
