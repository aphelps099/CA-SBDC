/* eslint-disable function-paren-newline */
/* eslint-disable no-param-reassign */
/**
 * WordPress dependencies
 */
const { addFilter } = wp.hooks;
const { Component, cloneElement, Children } = wp.element;

const { __ } = wp.i18n;

const { BaseControl, Button, RangeControl } = wp.components;

const { MediaUpload, RichText } = wp.blockEditor;

const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
const MIN_MARKER_WIDTH = 10;
const MAX_MARKER_WIDTH = 100;

/**
 * Component Class
 */
class MarkerAdditionalOptions extends Component {
  constructor(...args) {
    super(...args);

    this.state = {
      isOpened: false,
    };

    this.onSelectImage = this.onSelectImage.bind(this);
    this.onResetImage = this.onResetImage.bind(this);
  }

  onSelectImage(media) {
    if (!media || !media.url) {
      return;
    }

    const { setMarkerOptions } = this.props;

    setMarkerOptions({
      iconImageID: media.id,
      iconImageURL: media.url,
      iconImageCustomWidth: Math.min(MAX_MARKER_WIDTH, media.width),
      iconImageWidth: media.width,
      iconImageHeight: media.height,
    });
  }

  onResetImage() {
    const { setMarkerOptions } = this.props;

    setMarkerOptions({
      iconImageID: '',
      iconImageURL: '',
      iconImageCustomWidth: '',
      iconImageWidth: '',
      iconImageHeight: '',
    });
  }

  render() {
    const { isOpened } = this.state;

    const {
      marker: { iconImageID, iconImageURL, iconImageCustomWidth, infoWindowText },
      setMarkerOptions,
      getIcon,
    } = this.props;

    let title = __('More Options');
    if (isOpened) {
      title = __('Less Options');
    }

    const previewIcon =
      iconImageID && iconImageURL ? (
        <img src={iconImageURL} width={iconImageCustomWidth} alt="" />
      ) : (
        <img
          src="https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2_hdpi.png"
          width="27"
          alt=""
        />
      );

    return (
      <div className="ghostkit-pro-google-maps-marker-options">
        <Button
          className="ghostkit-pro-google-maps-marker-options-toggle"
          onClick={() => {
            this.setState({
              isOpened: !isOpened,
            });
          }}
          aria-expanded={isOpened}
        >
          {title}
          {/*
                        Firefox + NVDA don't announce aria-expanded because the browser
                        repaints the whole element, so this wrapping span hides that.
                    */}
          <span aria-hidden="true">
            {isOpened ? getIcon('icon-arrow-up') : getIcon('icon-arrow-down')}
          </span>
        </Button>
        {isOpened ? (
          <div className="ghostkit-pro-google-maps-marker-options-content">
            <div className="ghostkit-pro-google-maps-marker-options-content-icon">
              {previewIcon}
              <MediaUpload
                onSelect={this.onSelectImage}
                allowedTypes={['image']}
                value={iconImageID && iconImageURL ? iconImageID : false}
                render={({ open }) => (
                  <Button isSecondary onClick={open}>
                    {__('Change Icon')}
                  </Button>
                )}
              />
            </div>
            {iconImageCustomWidth ? (
              <div>
                <Button
                  className="ghostkit-google-maps-icon-reset"
                  isSmall
                  onClick={this.onResetImage}
                >
                  {__('Reset Icon to Default')}
                </Button>
              </div>
            ) : (
              ''
            )}
            {iconImageCustomWidth ? (
              <div>
                <RangeControl
                  label={__('Marker Width')}
                  value={iconImageCustomWidth}
                  onChange={(val) => setMarkerOptions({ iconImageCustomWidth: val })}
                  min={MIN_MARKER_WIDTH}
                  max={MAX_MARKER_WIDTH}
                />
              </div>
            ) : (
              ''
            )}
            <BaseControl
              label={__('Info Window Text')}
              className="ghostkit-pro-google-maps-marker-options-content-info-window-text"
            >
              <RichText
                value={infoWindowText}
                multiline
                placeholder={__('Write text…')}
                onChange={(val) => {
                  setMarkerOptions({ infoWindowText: val });
                }}
                onRemove={() => {
                  setMarkerOptions({ infoWindowText: '' });
                }}
                keepPlaceholderOnFocus
              />
            </BaseControl>
          </div>
        ) : (
          ''
        )}
      </div>
    );
  }
}

const MarkerAdditionalOptionsWithSelect = withSelect((select) => {
  const { getIcon } = select('ghostkit/base/utils').get();

  return {
    getIcon,
  };
})(MarkerAdditionalOptions);

/**
 * Add Google Maps marker additional options
 *
 * @param {Object} Control JSX control.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addMarkerAdditionalOptions(Control, props) {
  if (
    props.props.name === 'ghostkit/google-maps' &&
    props.attribute === 'additionalMarkerOptions'
  ) {
    return <MarkerAdditionalOptionsWithSelect {...props} />;
  }

  return Control;
}

/**
 * Add Google Maps marker additional options
 *
 * @param {Object} Marker JSX element.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function previewMapMarker(Marker, props) {
  if (props.attribute === 'mapMarker' && props.marker) {
    const { iconImageID, iconImageURL, iconImageCustomWidth, iconImageWidth, iconImageHeight } =
      props.marker;
    if (
      props.marker &&
      iconImageID &&
      iconImageURL &&
      iconImageCustomWidth &&
      iconImageWidth &&
      iconImageHeight
    ) {
      const iconImageCustomHeight = iconImageCustomWidth * (iconImageHeight / iconImageWidth);

      Marker = Children.map(Marker, (child) =>
        cloneElement(child, {
          icon: {
            url: iconImageURL,
            scaledSize: new window.google.maps.Size(iconImageCustomWidth, iconImageCustomHeight),
          },
        })
      );
    }
  }

  return Marker;
}

/**
 * Add Google Maps marker additional data attributes
 *
 * @param {Object} Element Save element.
 * @param {Object} props  Block properties.
 * @param {Object} attributes  Block attributes.
 *
 * @return {Object} Filtered props applied to save element.
 */
function additionalMarkerData(Element, props, attributes) {
  if (props.name === 'ghostkit/google-maps') {
    const { markers } = attributes;

    if (markers && markers.length) {
      // each map block
      Element = Children.map(Element, (mapBlock) => {
        if (mapBlock.props && mapBlock.props.children) {
          // each marker
          return cloneElement(mapBlock, {
            children: Children.map(mapBlock.props.children, (mapMarker, index) => {
              const {
                iconImageID,
                iconImageURL,
                iconImageCustomWidth,
                iconImageWidth,
                iconImageHeight,
                infoWindowText,
              } = markers[index];

              const thereIsIcon =
                iconImageID &&
                iconImageURL &&
                iconImageCustomWidth &&
                iconImageWidth &&
                iconImageHeight;

              if (thereIsIcon || infoWindowText) {
                const newProps = {};
                let newChild = mapMarker.props.children || [];

                if (thereIsIcon) {
                  const iconImageCustomHeight =
                    iconImageCustomWidth * (iconImageHeight / iconImageWidth);
                  newProps['data-icon-url'] = iconImageURL;
                  newProps['data-icon-width'] = iconImageCustomWidth;
                  newProps['data-icon-height'] = iconImageCustomHeight;
                }

                if (infoWindowText && !RichText.isEmpty(infoWindowText)) {
                  newChild = [
                    ...newChild,
                    ...[
                      <div
                        key="ghostkit-pro-google-maps-marker-info-window-text"
                        className="ghostkit-pro-google-maps-marker-info-window-text"
                        style={{ display: 'none' }}
                      >
                        <RichText.Content value={infoWindowText} />
                      </div>,
                    ],
                  ];
                }

                return cloneElement(mapMarker, newProps, newChild);
              }
              return mapMarker;
            }),
          });
        }
        return mapBlock;
      });
    }
  }

  return Element;
}

addFilter(
  'ghostkit.editor.controls',
  'ghostkit-pro/editor/addMarkerAdditionalOptions',
  addMarkerAdditionalOptions
);
addFilter('ghostkit.editor', 'ghostkit-pro/editor/previewMapMarker', previewMapMarker);
addFilter(
  'ghostkit.blocks.googleMaps.markerData',
  'ghostkit-pro/blocks/markerData',
  additionalMarkerData
);
addFilter('blocks.getSaveElement', 'ghostkit-pro/google_maps/markerData', additionalMarkerData);
