/* eslint-disable no-param-reassign */
/**
 * Import CSS
 */
import './updates.scss';

/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * WordPress dependencies
 */
const { jQuery: $, ajaxurl } = window;

const { Component } = wp.element;

const { __ } = wp.i18n;

const { addFilter } = wp.hooks;

const { TextControl, Button, Notice, Spinner } = wp.components;

/**
 * WordPress dependencies
 */
const { ghostkitProSettingsData } = window;

let isLicenseActive = !!ghostkitProSettingsData.license_key;

/**
 * Update settings.
 */
class UpdatesSettings extends Component {
  constructor(props) {
    super(props);

    this.state = {
      license: ghostkitProSettingsData.license_key,
      isActive: isLicenseActive,
      isLoading: false,
      message: '',
      messageType: 'success',
    };

    this.requestAjax = this.requestAjax.bind(this);
  }

  requestAjax() {
    const { isActive, license } = this.state;

    const type = isActive ? 'deactivate' : 'activate';

    this.setState({
      isLoading: true,
    });

    $.post(
      ajaxurl,
      {
        action: 'gkt_pro_activation_action',
        ajax_nonce: ghostkitProSettingsData.activation_nonce,
        type,
        license_key: license,
      },
      (response) => {
        const isSuccess = !response;

        // Success.
        if (!response) {
          if (type === 'deactivate') {
            isLicenseActive = false;
            ghostkitProSettingsData.license_key = '';
            response = __('License deactivated.', 'ghostkit-pro');
          } else {
            isLicenseActive = !!license;
            ghostkitProSettingsData.license_key = license;
            response = __('License activated.', 'ghostkit-pro');
          }
        }

        this.setState({
          isLoading: false,
          isActive: isLicenseActive,
          message: response,
          messageType: isSuccess ? 'success' : 'error',
        });
      }
    );
  }

  render() {
    const { isActive, license, message, messageType, isLoading } = this.state;

    const classes = classnames(
      'ghostkit-settings-content-wrapper ghostkit-settings-updates',
      isLoading ? 'ghostkit-settings-updates-loading' : false
    );

    return (
      <div className={classes}>
        {isLoading ? <Spinner /> : ''}
        <h4>{__('PRO Addon License Activation', 'ghostkit-pro')}</h4>
        <p>
          {__(
            "To unlock updates, please enter your license key below. If you don't have a licence key, please see",
            'ghostkit-pro'
            // eslint-disable-next-line react/jsx-one-expression-per-line
          )}{' '}
          <a href="https://ghostkit.io/pricing/" target="_blank" rel="noopener noreferrer">
            {__('details & pricing', 'ghostkit-pro')}
          </a>
          .
        </p>
        <TextControl
          placeholder={__('License Key', 'ghostkit-pro')}
          value={license}
          disabled={isActive}
          onChange={(val) => {
            this.setState({
              license: val,
            });
          }}
        />
        <Button
          isPrimary={!isActive}
          isSecondary={isActive}
          onClick={() => {
            this.requestAjax();
          }}
        >
          {isActive
            ? __('Deactivate License', 'ghostkit-pro')
            : __('Activate License', 'ghostkit-pro')}
        </Button>
        {message ? (
          <Notice status={messageType} isDismissible={false}>
            <p>{message}</p>
          </Notice>
        ) : (
          ''
        )}
      </div>
    );
  }
}

/**
 * Add new Updates page.
 *
 * @param   {Object} pages pages list.
 * @return  {Object} Control.
 */
function addUpdatesSettings(pages) {
  pages.updates = {
    label: __('Updates', 'ghostkit-pro'),
    block: UpdatesSettings,
  };

  return pages;
}

addFilter('ghostkit.settings.pages', 'ghostkit/settings/pages/updates', addUpdatesSettings);
