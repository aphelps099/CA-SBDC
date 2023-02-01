
if ( typeof wp !== 'undefined' ) {

    const { addFilter } = wp.hooks;
    const { assign, merge } = lodash;

    const dark_blue = '#282362';
    const darker_blue = '#1a1558';
    const blue = '#147a93';
    const light_med_blue = '#8b87a9';

    function filterButtonBlockSettings(settings, name) {
        if ( name == 'crown-blocks/button' ) {
            return assign({}, settings, {
                supports: merge(settings.attributes, {
                    color: { type: 'string', default: dark_blue },
                    colorSlug: { type: 'string', default: 'dark-blue' },
                }),
            });
        }
        return settings;
    }
    addFilter(
        'blocks.registerBlockType',
        'crown-blocks/button',
        filterButtonBlockSettings,
    );

}
