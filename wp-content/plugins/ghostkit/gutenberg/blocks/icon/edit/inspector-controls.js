/**
 * Internal dependencies
 */
import IconPicker from '../../../components/icon-picker';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { PanelBody, __experimentalUnitControl: UnitControl } = wp.components;
const { InspectorControls } = wp.blockEditor;

export default function EditInspectorControls({ attributes, setAttributes }) {
  const { icon, width } = attributes;

  return (
    <InspectorControls>
      <PanelBody>
        <IconPicker
          label={__('Icon', 'ghostkit')}
          value={icon}
          onChange={(value) => setAttributes({ icon: value })}
          insideInspector
        />
        <UnitControl
          label={__('Width', 'ghostkit')}
          placeholder={__('Auto', 'ghostkit')}
          value={width}
          onChange={(val) => setAttributes({ width: val })}
          labelPosition="edge"
          __unstableInputWidth="70px"
          min={0}
        />
      </PanelBody>
    </InspectorControls>
  );
}
