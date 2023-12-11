/**
 * Internal dependencies
 */
import { isGradient, parseGradient, colorStopToColor } from './gradient-parser';

/**
 * Generate SVG gradient
 *
 * @param {String} gradient css.
 * @param {String} id for svg.
 * @param {String} selector css selector to apply gradient.
 *
 * @return {Object} svg result.
 */
export function SVGGradientGenerator(gradient, id, selector) {
  let result = '';
  const colorGradient = parseGradient(gradient);

  function getSVGContent(colors) {
    let resultContent = '';

    colors.forEach((item, k) => {
      const color = colorStopToColor(item);
      let offset = 0;

      if (item.length) {
        offset = item.length.value;
      }

      // don't create JSX element as Gutenberg save
      // for some reason don't save 'stopColor' attribute as 'stop-color'
      resultContent += `<stop offset=${offset}% stop-color="${color}" key="${k}" />`;
    });

    return resultContent;
  }

  if (colorGradient && colorGradient[0]) {
    let angle = 0;

    if (colorGradient[0].orientation) {
      angle = parseInt(colorGradient[0].orientation.value, 10);

      // change angle value to work like CSS linear-gradient
      angle -= 90;
      if (angle < 0) {
        angle += 360;
      }
      if (angle > 360) {
        angle -= 360;
      }
    }

    result = `
            <div style="position: absolute; left: -9999px;">
                <svg width="0" height="0">
                    <linearGradient
                        id="${id}"
                        gradientTransform="rotate(${angle} 0.5 0.5)"
                    >${getSVGContent(colorGradient[0].colorStops)}</linearGradient>
                </svg>
                <style>
                    ${selector} svg path[stroke="currentColor"],
                    ${selector} svg[stroke="currentColor"] {
                        stroke: url(#${id});
                    }
                    ${selector} svg path[fill="currentColor"],
                    ${selector} svg[fill="currentColor"] {
                        fill: url(#${id});
                    }
                </style>
            </div>
        `;
  }

  return result;
}

/**
 * Generate SVG gradient
 *
 * @param {String} gradient css.
 * @param {String} id for svg.
 * @param {String} selector css selector to apply gradient.
 *
 * @return {Object} svg result.
 */
export function SVGGradientGeneratorBlock(gradient, id, selector) {
  if (gradient && isGradient(gradient)) {
    return `
            <div class="ghostkit-pro-gradient-mask" style="position: absolute; left: -9999px;" data-gradient-style="${gradient}" data-gradient-id="${id}" data-gradient-selector="${selector}"></div>
        `;
  }

  return '';
}
