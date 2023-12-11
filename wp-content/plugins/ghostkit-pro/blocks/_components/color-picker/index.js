/* eslint-disable no-param-reassign */
/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

import { isGradient } from '../../_utils/gradient-parser';

/**
 * WordPress dependencies
 */
const WPColorPicker = wp.components.ColorPicker;

const { Component, Fragment } = wp.element;

const { __ } = wp.i18n;

const { Dropdown, Tooltip, BaseControl, GradientPicker } = wp.components;

const { ColorPalette } = wp.blockEditor;

const { withSelect } = wp.data;

/**
 * Component Class
 */
class ColorPicker extends Component {
  constructor(props) {
    super(props);

    this.state = {
      type: 'solid',
      value: props.value,
    };

    this.getValue = this.getValue.bind(this);
    this.getPicker = this.getPicker.bind(this);
    this.getColorPickerComponent = this.getColorPickerComponent.bind(this);

    if (isGradient(props.value)) {
      this.state.type = 'linear-gradient';
    }
  }

  getValue(newState = false) {
    let { value, type } = this.state;

    if (newState && newState.value) {
      value = newState.value;
    }
    if (newState && newState.type) {
      type = newState.type;
    }

    if (type === 'linear-gradient') {
      if (isGradient(value)) {
        return value;
      }

      return 'linear-gradient(135deg, #0366d6 0%, #03b5d8 100%)';
    }
    if (type === 'solid') {
      return value;
    }

    return '';
  }

  // eslint-disable-next-line class-methods-use-this
  getPicker(value, alpha, onChange, index) {
    return (
      <Fragment>
        <WPColorPicker
          color={value}
          onChangeComplete={(color) => {
            let colorString;

            if (typeof color.rgb === 'undefined' || color.rgb.a === 1) {
              colorString = color.hex;
            } else {
              // return it like Object to easily convert it to gradient color object.
              colorString = color.rgb;
            }

            onChange(colorString);
          }}
          disableAlpha={!alpha}
          key={index}
        />
        <BaseControl
          label={__('Color palette')}
          className="ghostkit-component-color-picker-palette"
        >
          <ColorPalette
            value={value}
            onChange={(color) => onChange(color)}
            disableCustomColors
            key={index}
          />
        </BaseControl>
      </Fragment>
    );
  }

  getColorPickerComponent() {
    const { onChange, alpha = false, ToggleGroup } = this.props;

    const { value } = this.state;

    return (
      <div className="ghostkit-component-color-picker">
        <ToggleGroup
          value={this.state.type}
          options={[
            {
              value: 'solid',
              label: __('Solid', 'ghostkit-pro'),
            },
            {
              value: 'linear-gradient',
              label: __('Gradient', 'ghostkit-pro'),
            },
          ]}
          onChange={(type) => {
            const newValue = this.getValue({
              type,
            });
            this.setState({
              type,
              value: newValue,
            });
            onChange(newValue);
          }}
          isBlock
        />
        {this.state.type === 'linear-gradient' ? (
          <GradientPicker
            value={value}
            onChange={(val) => {
              this.setState({
                value: val,
              });
              onChange(val);
            }}
          />
        ) : (
          this.getPicker(value, alpha, (val) => {
            if (typeof val === 'object') {
              val = `rgba(${val.r}, ${val.g}, ${val.b}, ${val.a})`;
            }

            this.setState({
              value: val,
            });
            onChange(val);
          })
        )}
      </div>
    );
  }

  render() {
    const {
      label,
      className,
      help,
      useDropdown = true,
      hint = __('Custom Color Picker', 'ghostkit-pro'),
    } = this.props;

    if (!useDropdown) {
      return this.getColorPickerComponent();
    }

    const { value } = this.state;

    return (
      <BaseControl
        label={label}
        help={help}
        className={classnames(
          'ghostkit-component-color-picker-wrapper ghostkit-pro-component-color-picker-wrapper',
          className
        )}
      >
        <Dropdown
          position="bottom left"
          className="ghostkit-component-color-picker__dropdown"
          contentClassName="ghostkit-component-color-picker__dropdown-content"
          renderToggle={({ isOpen, onToggle }) => (
            <Tooltip text={hint}>
              <button
                type="button"
                aria-expanded={isOpen}
                className="ghostkit-component-color-toggle"
                onClick={onToggle}
                aria-label={hint}
              >
                <span style={{ background: value || '' }} />
              </button>
            </Tooltip>
          )}
          renderContent={() => this.getColorPickerComponent()}
        />
      </BaseControl>
    );
  }
}

const ColorPickerWithSelect = withSelect((select) => {
  const { ToggleGroup } = select('ghostkit/base/components').get();

  return {
    ToggleGroup,
  };
})(ColorPicker);

export default ColorPickerWithSelect;
