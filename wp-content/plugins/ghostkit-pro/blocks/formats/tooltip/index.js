/**
 * Internal dependencies
 */
import { TooltipPopover } from './tooltip-popover';
import { initTooltips } from './api';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component, Fragment } = wp.element;

const { ToolbarGroup, Button, TextareaControl } = wp.components;

const { toggleFormat, applyFormat } = wp.richText;

const { RichTextToolbarButton, BlockControls } = wp.blockEditor;

export const name = 'ghostkit-pro/tooltip';

export const settings = {
  title: __('Tooltip', 'ghostkit-pro'),
  tagName: 'span',
  className: 'ghostkit-pro-tooltip',
  attributes: {
    label: 'aria-label',
  },
  edit: class TooltipFormat extends Component {
    constructor(props) {
      super(props);

      this.state = {
        openedPopover: false,
      };

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

    toggleFormat(val, toggle = true) {
      const { value, onChange } = this.props;

      const attributes = {};

      if (val) {
        attributes.label = val;
      } else {
        this.setState({ openedPopover: true });
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
      const { activeAttributes: attributes, value, isActive } = this.props;

      const { openedPopover } = this.state;

      let label = '';

      if (isActive) {
        label = attributes && attributes.label ? attributes.label : label;

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
                        d="M18.2222 4.72223H5.77778C4.79594 4.72223 4 5.51817 4 6.50001V16.2222C4 17.2041 4.79594 18 5.77778 18L9.5 18L12 20.5L14.5 18L18.2222 18C19.2041 18 20 17.2041 20 16.2222V6.50001C20 5.51817 19.2041 4.72223 18.2222 4.72223Z"
                        stroke="black"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        fill="transparent"
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
              <TooltipPopover value={value} name={name}>
                <div>
                  <TextareaControl
                    label={__('Tooltip Text', 'ghostkit-pro')}
                    value={label}
                    onChange={(val) => {
                      this.toggleFormat(val, !val);
                    }}
                  />
                </div>
              </TooltipPopover>
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
                d="M18.2222 4.72223H5.77778C4.79594 4.72223 4 5.51817 4 6.50001V16.2222C4 17.2041 4.79594 18 5.77778 18L9.5 18L12 20.5L14.5 18L18.2222 18C19.2041 18 20 17.2041 20 16.2222V6.50001C20 5.51817 19.2041 4.72223 18.2222 4.72223Z"
                stroke="black"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                fill="transparent"
              />
            </svg>
          }
          title={__('Tooltip', 'ghostkit-pro')}
          onClick={() => {
            this.toggleFormat();
          }}
          isActive={isActive}
        />
      );
    }
  },
};

// Tooltip - Popper.js
initTooltips({
  getContainer($button) {
    return $button.closest('.editor-styles-wrapper');
  },
});
