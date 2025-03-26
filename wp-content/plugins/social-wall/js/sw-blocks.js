"use strict";

(function () {
    var _wp = wp,
        _wp$serverSideRender = _wp.serverSideRender,
        createElement = wp.element.createElement,
        ServerSideRender = _wp$serverSideRender === void 0 ? wp.components.ServerSideRender : _wp$serverSideRender,
        _ref = wp.blockEditor || wp.editor,
        InspectorControls = _ref.InspectorControls,
        _wp$components = wp.components,
        TextareaControl = _wp$components.TextareaControl,
        Button = _wp$components.Button,
        PanelBody = _wp$components.PanelBody,
        Placeholder = _wp$components.Placeholder,
        registerBlockType = wp.blocks.registerBlockType;

    var sbyIcon = createElement('svg', {
        width: 20,
        height: 20,
        viewBox: '0 0 16 16',
        className: 'dashicon'
    }, createElement('path', {
        fill: 'currentColor',
        fillRule: 'evenodd',
        clipRule: 'evenodd',
        d: 'M7.90766 0C11.2227 0 13.9092 3.32243 13.9092 7.42084C13.9092 11.2725 11.537 14.4377 8.50192 14.8061L9.23124 15.6975L7.15257 15.8743L7.48407 14.8245C4.36668 14.5556 1.90494 11.3498 1.90494 7.42084C1.90494 3.32243 4.5926 0 7.90766 0ZM9.6283 5.58918L9.33947 2.60897L7.49599 4.92022L4.85162 3.41264L5.6229 6.16616L2.82779 7.06509L5.43889 8.38209L4.40899 11.1475L7.08626 9.9207L8.41219 12.5003L9.28546 9.61967L12.166 10.1611L10.4561 7.68805L12.621 5.67515L9.6283 5.58918Z'
    }));

    registerBlockType('sb/sw-feed-block', {
        title: 'Social Wall',
        icon: sbyIcon,
        category: 'widgets',
        attributes: {
            noNewChanges: {
                type: 'boolean',
            },
            shortcodeSettings: {
                type: 'string',
            },
            executed: {
                type: 'boolean'
            }
        },
        edit: function edit(props) {
            var _props = props,
                setAttributes = _props.setAttributes,
                _props$attributes = _props.attributes,
                _props$attributes$sho = _props$attributes.shortcodeSettings,
                shortcodeSettings = _props$attributes$sho === void 0 ? sw_block_editor.shortcodeSettings : _props$attributes$sho,
                _props$attributes$cli = _props$attributes.noNewChanges,
                noNewChanges = _props$attributes$cli === void 0 ? true : _props$attributes$cli,
                _props$attributes$exe = _props$attributes.executed,
                executed = _props$attributes$exe === void 0 ? false : _props$attributes$exe;

          props.attributes.shortcodeSettings = shortcodeSettings;

          
          function setState(shortcodeSettingsContent) {
              setAttributes({
                  noNewChanges: false,
                  shortcodeSettings: shortcodeSettingsContent
                });
            }
            
            function previewClick(content) {
                setAttributes({
                    noNewChanges: true,
                    executed: false,
                });
            }
            function afterRender() {
                // no way to run a script after AJAX call to get feed so we just try to execute it on a few intervals
                if (! executed
                    || typeof window.sbyGB === 'undefined') {
                    window.sbyGB = true;
                    setTimeout(function() { if (typeof sb_wall_init !== 'undefined') {sb_wall_init();}},1000);
                    setTimeout(function() { if (typeof sb_wall_init !== 'undefined') {sb_wall_init();}},2000);
                    setTimeout(function() { if (typeof sb_wall_init !== 'undefined') {sb_wall_init();}},3000);
                    setTimeout(function() { if (typeof sb_wall_init !== 'undefined') {sb_wall_init();}},5000);
                    setTimeout(function() { if (typeof sb_wall_init !== 'undefined') {sb_wall_init();}},10000);
                }
                setAttributes({
                    executed: true,
                });
            }

            var jsx = [createElement(InspectorControls, {
                key: "sby-gutenberg-setting-selector-inspector-controls"
            }, createElement(PanelBody, {
                title: sw_block_editor.i18n.addSettings
            }, createElement(TextareaControl, {
                key: "sby-gutenberg-settings",
                className: "sby-gutenberg-settings",
                label: sw_block_editor.i18n.shortcodeSettings,
                help: sw_block_editor.i18n.example + ": feed=\"1\"",
                value: shortcodeSettings,
                onChange: setState
            }), createElement(Button, {
                key: "sby-gutenberg-preview",
                className: "sby-gutenberg-preview",
                onClick: previewClick,
                isDefault: true
            }, sw_block_editor.i18n.preview)))];

            if (noNewChanges) {
                afterRender();
                jsx.push(createElement(ServerSideRender, {
                    key: "feeds-for-youtube/feeds-for-youtube",
                    block: "sb/sw-feed-block",
                    attributes: props.attributes,
                }));
            } else {
                props.attributes.noNewChanges = false;
                jsx.push(createElement(Placeholder, {
                    key: "sby-gutenberg-setting-selector-select-wrap",
                    className: "sby-gutenberg-setting-selector-select-wrap"
                }, createElement(Button, {
                    key: "sby-gutenberg-preview",
                    className: "sby-gutenberg-preview",
                    onClick: previewClick,
                    isDefault: true
                }, sw_block_editor.i18n.preview)));
            }

            return jsx;
        },
        save: function save() {
            return null;
        }
    });
})();
