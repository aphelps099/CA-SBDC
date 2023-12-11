/**
 * WordPress dependencies
 */
/**
 * Internal dependencies
 */
import { AnimatedTextPopover } from './animated-text-popover';

const { __ } = wp.i18n;

const { Component, Fragment } = wp.element;

const { PanelBody, ToolbarGroup, Button, ButtonGroup, TextControl } = wp.components;

const { toggleFormat, applyFormat } = wp.richText;

const { RichTextToolbarButton, BlockControls } = wp.blockEditor;

export const name = 'ghostkit-pro/animated-text';

const defaults = {
  parts: [''],
  cursor: '_',
};

export const settings = {
  title: __('Animated Text', 'ghostkit-pro'),
  tagName: 'span',
  className: 'ghostkit-pro-animated-text',
  attributes: {
    parts: 'data-parts',
    cursor: 'data-cursor',
  },
  edit: class AnimatedTextFormat extends Component {
    constructor(props) {
      super(props);

      this.state = {
        openedPopover: false,
      };

      this.getCurrentParts = this.getCurrentParts.bind(this);
      this.toggleFormat = this.toggleFormat.bind(this);
    }

    componentDidUpdate() {
      const { isActive } = this.props;

      const { openedPopover } = this.state;

      // Close popover.
      if (!isActive && openedPopover) {
        // eslint-disable-next-line react/no-did-update-set-state
        this.setState({
          openedPopover: false,
        });
      }
    }

    getCurrentParts() {
      const { activeAttributes: attributes } = this.props;

      let parts = [''];

      if (attributes && attributes.parts) {
        const partsString = attributes.parts;

        try {
          if (partsString) {
            parts = JSON.parse(attributes.parts);
          }
          // eslint-disable-next-line no-empty
        } catch (e) {}
      }

      return parts;
    }

    toggleFormat(val, toggle = true) {
      const { value, onChange } = this.props;

      const attributes = {
        ...defaults,
      };

      if (val) {
        attributes.parts = val;
      } else {
        this.setState({ openedPopover: true });
      }

      if (attributes.parts) {
        try {
          attributes.parts = JSON.stringify(attributes.parts);
          // eslint-disable-next-line no-empty
        } catch (e) {}
      }

      const runFormat = toggle ? toggleFormat : applyFormat;

      onChange(
        runFormat(value, {
          type: name,
          attributes,
        })
      );
    }

    render() {
      const { activeAttributes: attributes, value, isActive, onChange } = this.props;

      const { openedPopover } = this.state;

      let currentParts = [''];
      let cursor = '';

      if (isActive) {
        cursor = attributes && attributes.cursor ? attributes.cursor : cursor;
        currentParts = this.getCurrentParts();

        return (
          <Fragment>
            <BlockControls>
              <ToolbarGroup>
                <Button
                  icon={
                    // eslint-disable-next-line react/jsx-wrap-multilines
                    <svg
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M3.28011 17L4.3483 13.9205H8.89375L9.95625 17H11.8199L7.63239 5.36364H5.60398L1.41648 17H3.28011ZM4.85966 12.4432L6.57557 7.47727H6.66648L8.38239 12.4432H4.85966ZM21.9969 17H14.6048V18.4432H21.9969V17Z"
                        fill="black"
                      />
                    </svg>
                  }
                  onClick={() => {
                    this.setState({
                      openedPopover: !openedPopover,
                    });
                  }}
                />
              </ToolbarGroup>
            </BlockControls>
            {openedPopover ? (
              <AnimatedTextPopover value={value} name={name}>
                <div>
                  <PanelBody>
                    {currentParts.map((part, i) => (
                      <ButtonGroup
                        // eslint-disable-next-line react/no-array-index-key
                        key={`gkt-animated-text-${i}`}
                      >
                        <TextControl
                          label={`String ${i + 1}`}
                          value={part}
                          onChange={(val) => {
                            const newVal = [...currentParts];
                            newVal[i] = val;
                            this.toggleFormat(newVal, false);
                          }}
                        />
                        <Button
                          isTertiary
                          icon={
                            // eslint-disable-next-line react/jsx-wrap-multilines
                            <svg
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                d="M4 6.2H5.77778H20"
                                stroke="black"
                                strokeWidth="1.5"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                              />
                              <path
                                d="M8.57143 6.4V4.7C8.57143 4.24913 8.75204 3.81673 9.07353 3.49792C9.39502 3.17911 9.83106 3 10.2857 3H13.7143C14.1689 3 14.605 3.17911 14.9265 3.49792C15.248 3.81673 15.4286 4.24913 15.4286 4.7V6.4M18 6.4V18.3C18 18.7509 17.8194 19.1833 17.4979 19.5021C17.1764 19.8209 16.7404 20 16.2857 20H7.71429C7.25963 20 6.82359 19.8209 6.5021 19.5021C6.18061 19.1833 6 18.7509 6 18.3V6.4H18Z"
                                stroke="black"
                                strokeWidth="1.5"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                fill="transparent"
                              />
                            </svg>
                          }
                          onClick={() => {
                            if (currentParts.length > 1) {
                              const newVal = [...currentParts];
                              newVal.splice(i, 1);

                              this.toggleFormat(newVal, false);
                            } else {
                              this.toggleFormat('', true);
                            }
                          }}
                        />
                      </ButtonGroup>
                    ))}
                    <Button
                      className="block-editor-button-block-appender"
                      icon={
                        // eslint-disable-next-line react/jsx-wrap-multilines
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          width="24"
                          height="24"
                          role="img"
                          aria-hidden="true"
                          focusable="false"
                        >
                          <path d="M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z" />
                        </svg>
                      }
                      onClick={() => {
                        const newVal = [...currentParts];
                        newVal.push('');
                        this.toggleFormat(newVal, false);
                      }}
                    />
                  </PanelBody>
                  <PanelBody>
                    <TextControl
                      label={__('Cursor', 'ghostkit-pro')}
                      placeholder="_"
                      value={cursor}
                      onChange={(val) => {
                        onChange(
                          applyFormat(value, {
                            type: name,
                            attributes: {
                              ...attributes,
                              cursor: val,
                            },
                          })
                        );
                      }}
                    />
                  </PanelBody>
                  <PanelBody>
                    <p className="description">
                      {__('Text animation will be only visible on Frontend.', 'ghostkit-pro')}
                    </p>
                  </PanelBody>
                  <PanelBody>
                    <Button
                      isLink
                      onClick={() => {
                        this.toggleFormat('', true);
                      }}
                    >
                      {__('Remove Format', 'ghostkit-pro')}
                    </Button>
                  </PanelBody>
                </div>
              </AnimatedTextPopover>
            ) : (
              ''
            )}
          </Fragment>
        );
      }

      return (
        <RichTextToolbarButton
          icon={
            // eslint-disable-next-line react/jsx-wrap-multilines
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M3.28011 17L4.3483 13.9205H8.89375L9.95625 17H11.8199L7.63239 5.36364H5.60398L1.41648 17H3.28011ZM4.85966 12.4432L6.57557 7.47727H6.66648L8.38239 12.4432H4.85966ZM21.9969 17H14.6048V18.4432H21.9969V17Z"
                fill="black"
              />
            </svg>
          }
          title={__('Animated Text', 'ghostkit-pro')}
          onClick={() => {
            this.toggleFormat();
          }}
          isActive={isActive}
        />
      );
    }
  },
};
