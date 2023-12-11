/**
 * Styles
 */
import './style.scss';

/**
 * WordPress dependencies
 */
const { Component, Fragment } = wp.element;

const { BaseControl, TextControl, Button, SelectControl, Spinner } = wp.components;

const { MediaUpload } = wp.blockEditor;

const { __ } = wp.i18n;

class FontsCustom extends Component {
  constructor(props) {
    super(props);

    this.state = {
      isLoading: true,
      newName: '',
      newWoff: '',
      newWoff2: '',
      newWeight: '400',
      newStyle: 'normal',
      newError: '',
    };
  }

  componentDidMount() {
    this.setState({ isLoading: false });
  }

  render() {
    const { isLoading } = this.state;

    const { customFonts, updateFonts } = this.props;

    return (
      <div className={isLoading ? 'ghostkit-settings-fonts-loading' : ''}>
        {isLoading ? <Spinner /> : ''}
        <div className="editor-styles-wrapper">
          <ol className="is-style-styled">
            <li>{__('Upload font files in woff and woff2 formats', 'ghostkit-pro')}</li>
            <li>
              {__(
                'Add each font separately for Weight and Style with the same names',
                'ghostkit-pro'
              )}
            </li>
          </ol>
          <div className="ghostkit-settings-fonts-custom-form">
            <TextControl
              label={__('Name', 'ghostkit-pro')}
              value={this.state.newName}
              onChange={(val) => {
                this.setState({
                  newName: val,
                });
              }}
            />
            <BaseControl label={__('Font .woff', 'ghostkit-pro')}>
              <div className="ghostkit-settings-fonts-custom-input-group">
                <TextControl
                  readOnly
                  value={this.state.newWoff}
                  placeholder={__('Select font file...', 'ghostkit-pro')}
                />
                <MediaUpload
                  onSelect={(media) => {
                    this.setState({
                      newWoff: '',
                    });
                    wp.media
                      .attachment(media.id)
                      .fetch()
                      .then((data) => {
                        this.setState({
                          newWoff: data.url,
                        });
                      });
                  }}
                  allowedTypes={['application/x-font-woff']}
                  value={this.state.newWoff}
                  render={({ open }) => (
                    <Button onClick={open} isSecondary>
                      {__('Select Font')}
                    </Button>
                  )}
                />
              </div>
            </BaseControl>
            <BaseControl label={__('Font .woff2', 'ghostkit-pro')}>
              <div className="ghostkit-settings-fonts-custom-input-group">
                <TextControl
                  readOnly
                  value={this.state.newWoff2}
                  placeholder={__('Select font file...', 'ghostkit-pro')}
                />
                <MediaUpload
                  onSelect={(media) => {
                    this.setState({
                      newWoff2: '',
                    });
                    wp.media
                      .attachment(media.id)
                      .fetch()
                      .then((data) => {
                        this.setState({
                          newWoff2: data.url,
                        });
                      });
                  }}
                  allowedTypes={['application/x-font-woff2']}
                  value={this.state.newWoff2}
                  render={({ open }) => (
                    <Button onClick={open} isSecondary>
                      {__('Select Font')}
                    </Button>
                  )}
                />
              </div>
            </BaseControl>
            <SelectControl
              label={__('Weight', 'ghostkit-pro')}
              value={this.state.newWeight}
              onChange={(val) => {
                this.setState({
                  newWeight: val,
                });
              }}
              options={[
                {
                  value: '100',
                  label: __('100', 'ghostkit-pro'),
                },
                {
                  value: '200',
                  label: __('200', 'ghostkit-pro'),
                },
                {
                  value: '300',
                  label: __('300', 'ghostkit-pro'),
                },
                {
                  value: '400',
                  label: __('400 (Regular)', 'ghostkit-pro'),
                },
                {
                  value: '500',
                  label: __('500', 'ghostkit-pro'),
                },
                {
                  value: '600',
                  label: __('600', 'ghostkit-pro'),
                },
                {
                  value: '700',
                  label: __('700', 'ghostkit-pro'),
                },
                {
                  value: '800',
                  label: __('800', 'ghostkit-pro'),
                },
                {
                  value: '900',
                  label: __('900', 'ghostkit-pro'),
                },
              ]}
            />
            <SelectControl
              label={__('Style', 'ghostkit-pro')}
              value={this.state.newStyle}
              onChange={(val) => {
                this.setState({
                  newStyle: val,
                });
              }}
              options={[
                {
                  value: 'normal',
                  label: __('Normal', 'ghostkit-pro'),
                },
                {
                  value: 'italic',
                  label: __('Italic', 'ghostkit-pro'),
                },
              ]}
            />
            {this.state.newError ? (
              <div className="ghostkit-settings-fonts-custom-form-error">{this.state.newError}</div>
            ) : null}
            <Button
              isPrimary
              onClick={() => {
                if (this.state.newName && (this.state.newWoff || this.state.newWoff2)) {
                  updateFonts({
                    custom: {
                      ...customFonts.custom,
                      [Math.random().toString(36).substr(2, 9)]: {
                        slug: this.state.newName,
                        name: this.state.newName,
                        woff: this.state.newWoff,
                        woff2: this.state.newWoff2,
                        weight: this.state.newWeight,
                        style: this.state.newStyle,
                      },
                    },
                  });

                  this.setState({
                    newName: '',
                    newWoff: '',
                    newWoff2: '',
                    newWeight: '400',
                    newStyle: 'normal',
                    newError: '',
                  });
                } else if (!this.state.newName) {
                  this.setState({
                    newError: __('You should specify the `Name` to add new font.', 'ghostkit-pro'),
                  });
                } else if (!this.state.newWoff && !this.state.newWoff2) {
                  this.setState({
                    newError: __(
                      'You should specify the `woff` or `woff2` files to add new font.',
                      'ghostkit-pro'
                    ),
                  });
                }
              }}
            >
              {__('Add Font', 'ghostkit-pro')}
            </Button>
          </div>
        </div>
        {customFonts.custom && Object.keys(customFonts.custom).length ? (
          <Fragment>
            <br />
            <table className="widefat fixed striped">
              <thead>
                <tr>
                  <td>{__('Font Family', 'ghostkit-pro')}</td>
                  <td>{__('Font Weight', 'ghostkit-pro')}</td>
                  <td>{__('Font Style', 'ghostkit-pro')}</td>
                  <td>{__('Actions', 'ghostkit-pro')}</td>
                </tr>
              </thead>
              <tbody>
                {Object.keys(customFonts.custom).map((key) => (
                  <tr key={customFonts.custom[key].name + key}>
                    <td>{customFonts.custom[key].slug}</td>
                    <td>{customFonts.custom[key].weight}</td>
                    <td>{customFonts.custom[key].style}</td>
                    <td>
                      <Button
                        isLink
                        onClick={() => {
                          const result = { ...customFonts.custom };

                          delete result[key];

                          updateFonts({
                            custom: {
                              ...result,
                            },
                          });
                        }}
                      >
                        {__('Remove', 'ghostkit-pro')}
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </Fragment>
        ) : (
          ''
        )}
      </div>
    );
  }
}

export default FontsCustom;
