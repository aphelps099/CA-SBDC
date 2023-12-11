/* eslint-disable no-param-reassign */
/**
 * External dependencies
 */
import gradientParser from 'gradient-parser';

/**
 * Internal dependencies
 */
const cache = {};

/**
 * Check if string is CSS gradient.
 *
 * @param {String} gradient - CSS gradient.
 *
 * @return {Boolean} is gradient.
 */
export function isGradient(gradient) {
  return typeof gradient === 'string' && /^linear-gradient\(/g.test(gradient);
}

/**
 * Parse gradient CSS string.
 *
 * @param {String} color - CSS gradient.
 *
 * @return {Object} - gradient data.
 */
export function parseGradient(color) {
  if (typeof cache[color] === 'undefined') {
    cache[color] = isGradient(color) ? gradientParser.parse(color) : false;
  }

  return cache[color];
}

/**
 * Color data to CSS string.
 *
 * @param {Object} colorStopData - color data.
 *
 * @return {String} - color CSS.
 */
export function colorStopToColor(colorStopData) {
  let result = '';

  if (colorStopData && colorStopData.type) {
    if (colorStopData.type === 'hex') {
      result = `#${colorStopData.value}`;
    } else if (colorStopData.type === 'rgba') {
      result = `rgba(${colorStopData.value[0]}, ${colorStopData.value[1]}, ${colorStopData.value[2]}, ${colorStopData.value[3]})`;
    }
  }

  return result;
}

/**
 * Convert gradient data to CSS gradient string.
 *
 * @param {Object} data - gradient data.
 *
 * @return {String} - CSS gradient.
 */
export function gradientDataToString(data) {
  let result = '';

  data[0].colorStops.forEach((val) => {
    const color = colorStopToColor(val);
    result += `${result ? ', ' : ''}${color} ${val.length.value}${val.length.type}`;
  });

  const gradient = `${data[0].type}(${data[0].orientation.value}deg, ${result})`;

  // save in cache
  if (typeof cache[gradient] === 'undefined') {
    cache[gradient] = isGradient(gradient) ? data : false;
  }

  return gradient;
}

/**
 * Get fallback color from gradient string.
 *
 * @param {String} gradient - gradient CSS.
 *
 * @return {String} - fallback color CSS.
 */
export function fallbackColor(gradient) {
  const gradientData = parseGradient(gradient);

  if (gradientData) {
    const firstColor = colorStopToColor(gradientData[0].colorStops[0]);
    if (firstColor) {
      gradient = firstColor;
    }
  }

  return gradient;
}
