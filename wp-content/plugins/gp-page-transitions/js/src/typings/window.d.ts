/**
 * Augment Window typings and add in properties provided by Gravity Forms, WordPress, etc.
 */
interface Window {
    jQuery?: JQueryStatic
    gform: any
    gformIsHidden: any
    gfMultiFileUploader: any
    gf_form_conditional_logic: any
    gf_apply_rules: any
    GPPageTransitions: any
    GPPageTransitionsSwiper: any
}

interface String {
    gformFormat: (...format: any[]) => string;
}
