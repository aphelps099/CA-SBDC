/**
 * Internal dependencies
 */
import * as actions from './actions';

export function* getBreakpoints() {
  const query = '/ghostkit/v1/get_breakpoints/';
  const data = yield actions.apiFetch({ path: query });
  return actions.setBreakpoints(data);
}
