/**
 * Boot data printed by UTS\Menu::app_data() as `window.utsAdmin`.
 */
const data = window.utsAdmin || {};

export default data;

/**
 * Whether the current user can open an app page (slug), e.g. 'uts-builder'.
 *
 * @param {string} slug Page slug.
 * @return {boolean} Whether the page is available.
 */
export const canOpen = ( slug ) => !! ( data.pages && data.pages[ slug ] );
