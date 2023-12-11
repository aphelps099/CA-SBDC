/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  InspectorControls,
  __experimentalColorGradientSettingsDropdown: ColorGradientSettingsDropdown,
  __experimentalUseMultipleOriginColorsAndGradients: useMultipleOriginColorsAndGradients,
} = wp.blockEditor;

export default function ColorControls(props) {
  const { attributes, setAttributes, clientId } = props;
  const { color, backgroundColor, backgroundGradient } = attributes;

  const colorGradientSettings = useMultipleOriginColorsAndGradients();

  return colorGradientSettings.hasColorsOrGradients ? (
    <InspectorControls group="color">
      <ColorGradientSettingsDropdown
        __experimentalIsRenderedInSidebar
        settings={[
          {
            colorValue: color,
            label: __('Icon', 'ghostkit'),
            onColorChange: (val) => {
              setAttributes({ color: val });
            },
            isShownByDefault: true,
            resetAllFilter: () => ({
              color: undefined,
            }),
          },
          {
            colorValue: backgroundColor,
            gradientValue: backgroundGradient,
            label: __('Background', 'ghostkit'),
            onColorChange: (val) => {
              setAttributes({ backgroundColor: val });
            },
            onGradientChange: (val) => {
              setAttributes({ backgroundGradient: val });
            },
            isShownByDefault: true,
            resetAllFilter: () => ({
              backgroundColor: undefined,
              backgroundGradient: undefined,
            }),
          },
        ]}
        panelId={clientId}
        {...colorGradientSettings}
      />
    </InspectorControls>
  ) : null;
}
