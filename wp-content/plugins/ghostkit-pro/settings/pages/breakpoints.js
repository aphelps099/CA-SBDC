/* eslint-disable no-param-reassign */
/**
 * Import CSS
 */
import './breakpoints.scss';

/**
 * External dependencies
 */
import '../../store';
import { debounce } from 'throttle-debounce';
import classnames from 'classnames/dedupe';

/**
 * WordPress dependencies
 */
const { Component, Fragment } = wp.element;

const { addFilter } = wp.hooks;

const { TextControl, Button, Spinner } = wp.components;

const { compose } = wp.compose;

const { withSelect, withDispatch } = wp.data;

const { apiFetch } = wp;

const { __, sprintf } = wp.i18n;

class BreakpointsProSettings extends Component {
  constructor(props) {
    super(props);

    this.state = {
      errors: false,
      breakpoints: null,
      defaultBreakpoints: null,
      isLoading: true,
    };

    this.maybePrepareBreakpointsData = this.maybePrepareBreakpointsData.bind(this);
    this.updateBreakpoints = this.updateBreakpoints.bind(this);
    this.updateBreakpointsDebounce = debounce(100, this.updateBreakpointsDebounce.bind(this));
  }

  componentDidMount() {
    this.maybePrepareBreakpointsData();
    this.setState({ isLoading: false });
  }

  componentDidUpdate() {
    this.maybePrepareBreakpointsData();
  }

  onChangeBreakpoints(mode = 'xs', value = '') {
    const { breakpoints, defaultBreakpoints } = this.state;

    const breakpointsValues = this.getBreakpointValues();

    let prevBreakpoint;
    let nextBreakpoint;

    switch (mode) {
      case 'xs':
      // eslint-disable-next-line default-case-last, no-fallthrough
      default:
        prevBreakpoint = false;
        nextBreakpoint = 'sm';
        break;
      case 'sm':
        prevBreakpoint = 'xs';
        nextBreakpoint = 'md';
        break;
      case 'md':
        prevBreakpoint = 'sm';
        nextBreakpoint = 'lg';
        break;
      case 'lg':
        prevBreakpoint = 'md';
        nextBreakpoint = false;
        break;
    }

    if (value === '') {
      value = defaultBreakpoints[mode];
    } else {
      switch (nextBreakpoint) {
        case false:
          value = value >= 3840 ? defaultBreakpoints[mode] : value;
          break;
        default:
          value =
            value >= breakpointsValues[nextBreakpoint]
              ? breakpointsValues[nextBreakpoint] - 1
              : value;
      }
      switch (prevBreakpoint) {
        case false:
          value = value < 1 ? defaultBreakpoints[mode] : value;
          break;
        default:
          value =
            value <= breakpointsValues[prevBreakpoint]
              ? breakpointsValues[prevBreakpoint] + 1
              : value;
      }
    }

    const newBreakpoints = {
      breakpoints: {
        ...breakpoints,
        [mode]: Number(value),
      },
    };

    this.setState(newBreakpoints);
  }

  getBreakpointValues() {
    const { breakpoints, defaultBreakpoints } = this.state;

    let breakpointsValues = null;

    if (defaultBreakpoints !== null) {
      breakpointsValues = {
        xs: breakpoints !== null ? breakpoints.xs : defaultBreakpoints.xs,
        sm: breakpoints !== null ? breakpoints.sm : defaultBreakpoints.sm,
        md: breakpoints !== null ? breakpoints.md : defaultBreakpoints.md,
        lg: breakpoints !== null ? breakpoints.lg : defaultBreakpoints.lg,
      };
    }

    return breakpointsValues;
  }

  maybePrepareBreakpointsData() {
    const { breakpoints = {} } = this.props;

    if (breakpoints && this.state.defaultBreakpoints === null) {
      this.setState({
        breakpoints: breakpoints.saved_breakpoints || null,
        defaultBreakpoints: breakpoints.default_breakpoints || null,
      });
    }
  }

  updateBreakpointsDebounce(breakpoints) {
    this.setState({ isLoading: true });

    breakpoints = JSON.stringify(breakpoints);

    apiFetch({
      path: '/ghostkit/v1/update_breakpoints',
      method: 'POST',
      data: {
        data: breakpoints,
      },
    }).then((request) => {
      if (request.success === true) {
        const { response } = request;
        if (typeof response !== 'undefined') {
          this.setState({
            errors: false,
            isLoading: false,
          });
        }
      }
      if (request.length > 0) {
        const errors = [];
        Object.keys(request).forEach((key) => {
          if (request[key].data.error) {
            errors.push({
              error_code: request[key].data.error_code,
              error: request[key].data.response,
            });
          }
        });
        if (errors.length > 0) {
          this.setState({
            errors,
            isLoading: false,
          });
        }
      }
    });

    this.props.updateBreakpoints(breakpoints);
  }

  errorsOutput() {
    const { errors } = this.state;

    return errors && Object.keys(errors).length ? (
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
              {errors[key].error}
            </li>
          ))}
        </ul>
      </Fragment>
    ) : (
      ''
    );
  }

  updateBreakpoints(breakpoints) {
    this.setState(
      {
        breakpoints,
      },
      () => {
        this.updateBreakpointsDebounce(breakpoints);
      }
    );
  }

  render() {
    const { breakpoints, defaultBreakpoints, isLoading } = this.state;

    const breakpointsValues = this.getBreakpointValues();

    const classes = classnames(
      'ghostkit-settings-content-wrapper ghostkit-settings-breakpoints',
      isLoading ? 'ghostkit-settings-breakpoints-loading' : false
    );

    return (
      <div className={classes}>
        {isLoading ? <Spinner /> : ''}

        {defaultBreakpoints !== null ? (
          <Fragment>
            {this.errorsOutput()}
            <div className="ghostkit-settings-breakpoints-container">
              <h4>{__('Large', 'ghostkit-pro')}</h4>
              <TextControl
                type="number"
                onChange={(value) => {
                  this.onChangeBreakpoints('lg', value);
                }}
                step={1}
                min={breakpointsValues.md + 1}
                max={3840}
                value={breakpointsValues.lg}
                placeholder={defaultBreakpoints.lg}
                help={sprintf(
                  __('Sets the breakpoint on large screen sizes (Default: %s).', 'ghostkit-pro'),
                  defaultBreakpoints.lg
                )}
              />
            </div>
            <div className="ghostkit-settings-breakpoints-container">
              <h4>{__('Medium', 'ghostkit-pro')}</h4>
              <TextControl
                type="number"
                onChange={(value) => {
                  this.onChangeBreakpoints('md', value);
                }}
                step={1}
                min={breakpointsValues.sm + 1}
                max={breakpointsValues.lg - 1}
                value={breakpointsValues.md}
                placeholder={defaultBreakpoints.md}
                help={sprintf(
                  __('Sets the breakpoint on medium screen sizes (Default: %s).', 'ghostkit-pro'),
                  defaultBreakpoints.md
                )}
              />
            </div>
            <div className="ghostkit-settings-breakpoints-container">
              <h4>{__('Small', 'ghostkit-pro')}</h4>
              <TextControl
                type="number"
                onChange={(value) => {
                  this.onChangeBreakpoints('sm', value);
                }}
                step={1}
                min={breakpointsValues.xs + 1}
                max={breakpointsValues.md - 1}
                value={breakpointsValues.sm}
                placeholder={defaultBreakpoints.sm}
                help={sprintf(
                  __('Sets the breakpoint on small screen sizes (Default: %s).', 'ghostkit-pro'),
                  defaultBreakpoints.sm
                )}
              />
            </div>
            <div className="ghostkit-settings-breakpoints-container">
              <h4>{__('Extra Small', 'ghostkit-pro')}</h4>
              <TextControl
                type="number"
                onChange={(value) => {
                  this.onChangeBreakpoints('xs', value);
                }}
                step={1}
                min={1}
                max={breakpointsValues.sm - 1}
                value={breakpointsValues.xs}
                placeholder={defaultBreakpoints.xs}
                help={sprintf(
                  __(
                    'Sets the breakpoint on extra small screen sizes (Default: %s).',
                    'ghostkit-pro'
                  ),
                  defaultBreakpoints.xs
                )}
              />
            </div>
            <div className="ghostkit-settings-breakpoints-container">
              <Button
                isSecondary
                onClick={() => {
                  this.updateBreakpoints(breakpoints);
                }}
              >
                {__('Save', 'ghostkit-pro')}
              </Button>
            </div>
          </Fragment>
        ) : (
          ''
        )}
      </div>
    );
  }
}

const ComposeBreakpointsProSettings = compose([
  withSelect((select) => {
    let breakpoints = select('ghostkit/plugins/breakpoints').getBreakpoints();
    const savedBreakpoints = breakpoints.value;

    try {
      if (breakpoints) {
        breakpoints = JSON.parse(breakpoints);
        if (typeof savedBreakpoints !== 'undefined') {
          breakpoints.saved_breakpoints = JSON.parse(savedBreakpoints);
        }
      }
      // eslint-disable-next-line no-empty
    } catch (e) {}

    return {
      breakpoints,
    };
  }),
  withDispatch((dispatch) => ({
    updateBreakpoints(value) {
      dispatch('ghostkit/plugins/breakpoints').setBreakpoints({ value });
    },
  })),
])(BreakpointsProSettings);

/**
 * Add breakpoints page settings.
 *
 * @param   {Object} Control JSX control.
 * @param   {Object} props additional props.
 * @return  {Object} Control.
 */
function addBreakpointsProSettings(Control, props) {
  return <ComposeBreakpointsProSettings {...props} />;
}

addFilter(
  'ghostkit.breakpoints.settings',
  'ghostkit/settings/pages/breakpoints',
  addBreakpointsProSettings
);
