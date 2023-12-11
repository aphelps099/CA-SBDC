/**
 * Import CSS
 */
import './fonts.scss';

/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

import '../../store';
import FontsAdobe from '../../components/fonts-adobe';
import FontsCustom from '../../components/fonts-custom';

/**
 * WordPress dependencies
 */
const { Component } = wp.element;

const { addFilter } = wp.hooks;

const { TabPanel } = wp.components;

const { compose } = wp.compose;

const { withSelect, withDispatch } = wp.data;

const { apiFetch } = wp;

const { __ } = wp.i18n;

const { merge } = window.lodash;

let reloadPage = false;

class FontsProSettings extends Component {
  /**
   * We should reload page after fonts updated and when we visit the Fonts settings page.
   */
  componentWillUnmount() {
    if (reloadPage) {
      setTimeout(() => {
        window.location.reload();
      }, 0);
    }
  }

  render() {
    const { getIcon, customFonts, updateFonts } = this.props;

    const classes = classnames('ghostkit-settings-content-wrapper ghostkit-settings-fonts');

    return (
      <div className={classes}>
        <TabPanel
          className="ghostkit-settings-fonts-tabs"
          tabs={[
            {
              name: 'adobe',
              title: (
                <span>
                  {getIcon('icon-typography-adobe-fonts', false)}
                  {__('Adobe Fonts', 'ghostkit-pro')}
                </span>
              ),
            },
            {
              name: 'custom',
              title: (
                <span>
                  {getIcon('icon-typography-custom-fonts', false)}
                  {__('Custom Fonts', 'ghostkit-pro')}
                </span>
              ),
            },
          ]}
        >
          {(data) => {
            if (data.name === 'custom') {
              return <FontsCustom customFonts={customFonts} updateFonts={updateFonts} />;
            }

            return <FontsAdobe customFonts={customFonts} updateFonts={updateFonts} />;
          }}
        </TabPanel>
      </div>
    );
  }
}
const ComposeFontsProSettings = compose([
  withSelect((select) => {
    const { getIcon } = select('ghostkit/base/utils').get();

    const customFonts = select('ghostkit/plugins/fonts').getCustomFonts();

    const defaultCustomFonts = {
      adobe: {
        token: false,
        errors: false,
        kits: false,
        kit: false,
        fonts: false,
      },
      custom: false,
    };

    return {
      getIcon,
      customFonts: merge(defaultCustomFonts, customFonts),
    };
  }),
  withDispatch((dispatch) => ({
    updateFonts(value) {
      dispatch('ghostkit/plugins/fonts').setCustomFonts(value);

      apiFetch({
        path: '/ghostkit/v1/update_custom_fonts',
        method: 'POST',
        data: {
          data: value,
        },
      }).then(() => {
        reloadPage = true;
      });
    },
  })),
])(FontsProSettings);

/**
 * Add fonts page settings.
 *
 * @param   {Object} Control JSX control.
 * @param   {Object} props additional props.
 * @return  {Object} Control.
 */
function addFontsProSettings(Control, props) {
  return <ComposeFontsProSettings {...props} />;
}

addFilter('ghostkit.fonts.settings', 'ghostkit/settings/pages/fonts', addFontsProSettings);
