export function apiFetch(request) {
  return {
    type: 'API_FETCH',
    request,
  };
}

export function setBreakpoints(data) {
  return {
    type: 'SET_BREAKPOINTS',
    data,
  };
}

export function updateBreakpoints(data) {
  return {
    type: 'UPDATE_BREAKPOINTS',
    data,
  };
}
