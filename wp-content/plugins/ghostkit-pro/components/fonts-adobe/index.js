/* eslint-disable function-paren-newline */
/* eslint-disable no-param-reassign */
/**
 * Styles
 */
import './style.scss';

/**
 * External dependencies
 */
import { debounce } from 'throttle-debounce';

/**
 * WordPress dependencies
 */
const { Component, Fragment } = wp.element;

const { TextControl, Button, SelectControl, Spinner } = wp.components;

const { apiFetch } = wp;

const { __ } = wp.i18n;

class FontsAdobe extends Component {
  constructor(props) {
    super(props);

    this.state = {
      isLoading: true,
    };

    this.maybeUpdateTypekitFonts = debounce(100, this.maybeUpdateTypekitFonts.bind(this));
  }

  componentDidMount() {
    this.setState({ isLoading: false });
  }

  // eslint-disable-next-line class-methods-use-this
  getFontWeightAndStyle(fontWeight, mode = 'table') {
    let fontStyle = 'normal';

    if (fontWeight.indexOf('i') === 0) {
      fontWeight = fontWeight.replace(/i/g, '');
      fontStyle = 'italic';
      if (mode === 'settings') {
        fontWeight += '00i';
      }
    } else if (fontWeight !== '') {
      fontWeight = fontWeight.replace(/n/g, '');
      if (mode === 'settings') {
        fontWeight += '00';
      }
    }

    if (mode === 'table') {
      fontWeight += '00';
    }

    return {
      fontWeight,
      fontStyle,
    };
  }

  maybeUpdateTypekitFonts() {
    const { customFonts, updateFonts } = this.props;

    this.setState({ isLoading: true });

    apiFetch({
      path: '/ghostkit/v1/get_typekit',
      method: 'POST',
      data: {
        data: {
          token: customFonts.adobe.token,
          kit: customFonts.adobe.kit,
        },
      },
    }).then((request) => {
      if (request.success === true) {
        const { response } = request;
        if (typeof response !== 'undefined') {
          updateFonts({
            adobe: {
              ...response,
            },
          });

          this.setState({ isLoading: false });
        }
      }
    });
  }

  render() {
    const { isLoading } = this.state;

    const { customFonts, updateFonts } = this.props;

    const { token, errors, kits, kit, fonts } = customFonts.adobe;

    return (
      <div className={isLoading ? 'ghostkit-settings-fonts-loading' : ''}>
        {isLoading ? <Spinner /> : ''}
        <div className="editor-styles-wrapper">
          <ol className="is-style-styled">
            <li>
              {__('Log in to your', 'ghostkit-pro')}
              &nbsp;
              <a target="_blank" rel="noopener noreferrer" href="https://fonts.adobe.com/">
                {__('Adobe Fonts account', 'ghostkit-pro')}
              </a>
            </li>
            <li>
              {__('To get your API key, go to', 'ghostkit-pro')}
              &nbsp;
              <a
                target="_blank"
                rel="noopener noreferrer"
                href="https://fonts.adobe.com/account/tokens"
              >
                {__('this link', 'ghostkit-pro')}
              </a>
            </li>
            <li>{__('Copy your API key:', 'ghostkit-pro')}</li>
          </ol>
          <div className="ghostkit-settings-fonts-adobe-token-form">
            <TextControl
              value={token || ''}
              onChange={(opt) => {
                updateFonts({
                  adobe: {
                    token: opt,
                  },
                });
              }}
            />
            <Button
              isSecondary
              onClick={() => {
                updateFonts({
                  adobe: {
                    ...this.props.customFonts.adobe,
                  },
                });
                this.maybeUpdateTypekitFonts();
              }}
            >
              {kits && Object.keys(kits).length
                ? __('Refresh Fonts', 'ghostkit-pro')
                : __('Get Projects', 'ghostkit-pro')}
            </Button>
          </div>
        </div>
        {errors && Object.keys(errors).length ? (
          <Fragment>
            <h3>{__('Errors', 'ghostkit-pro')}</h3>
            <ul>
              {Object.keys(errors).map((key) => (
                <li
                  key={key}
                  className="ghostkit-alert"
                  style={{
                    borderLeftColor: '#e44b4b',
                  }}
                >
                  {errors[key]}
                </li>
              ))}
            </ul>
          </Fragment>
        ) : (
          ''
        )}
        {kits && Object.keys(kits).length ? (
          <Fragment>
            <br />
            <SelectControl
              label={__('Web Project', 'ghostkit-pro')}
              value={kit || ''}
              onChange={(opt) => {
                updateFonts({
                  adobe: {
                    kit: opt,
                  },
                });
                this.maybeUpdateTypekitFonts();
              }}
              options={[
                {
                  value: '',
                  label: __('-- Select Web Project --', 'ghostkit-pro'),
                },
                ...kits,
              ]}
            />
          </Fragment>
        ) : (
          ''
        )}
        {fonts && fonts.families && Object.keys(fonts.families).length ? (
          <table className="widefat fixed striped">
            <thead>
              <tr>
                <td>{__('Name', 'ghostkit-pro')}</td>
                <td>{__('Font Family', 'ghostkit-pro')}</td>
                <td>{__('Font Weight', 'ghostkit-pro')}</td>
                <td>{__('Font Style', 'ghostkit-pro')}</td>
              </tr>
            </thead>
            <tbody>
              {Object.keys(fonts.families).map((key) =>
                Object.keys(fonts.families[key].variations).map((variationsKey) => {
                  const fontWeight = fonts.families[key].variations[variationsKey];
                  const fontOptions = this.getFontWeightAndStyle(fontWeight);

                  return (
                    <tr key={variationsKey}>
                      <td>{fonts.families[key].name}</td>
                      <td>{fonts.families[key].slug}</td>
                      <td>{fontOptions.fontWeight}</td>
                      <td>{fontOptions.fontStyle}</td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        ) : (
          ''
        )}
      </div>
    );
  }
}

export default FontsAdobe;
