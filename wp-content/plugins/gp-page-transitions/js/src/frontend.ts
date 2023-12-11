/**
 * GP Page Transitions - Frontend Scripts
 */

import $ from 'jquery';

// eslint-disable-next-line prettier/prettier
import type * as Swiper from 'swiper';

// Polyfill for inert attribute
import 'wicg-inert';

// Stylesheets
import './frontend.css';
// eslint-disable-next-line import/no-unresolved
import 'swiper/css';
// eslint-disable-next-line import/no-unresolved
import 'swiper/css/effect-fade';

interface GPPageTransitionsArgs {
	formId: number;
	hasConditionalLogic: boolean;
	enablePageTransitions: boolean;
	enableAutoProgress: boolean;
	hideNextButton: boolean;
	hidePrevButton: boolean;
	enableSoftValidation: boolean;
	validationSelectors: any;
	validationClassForm: string;
	validationClass: string;
	validationMessageContainer: string;
	submission: any;
	pagination: {
		type: string;
		startAtZero: boolean;
		pageCount: number;
		progressIndicators: any;
		pages: any;
		isCustom: any;
		labels: {
			step: string;
			of: string;
		};
	};
	skipSoftValidation: boolean;
	progressBarStartAtZero: boolean;
	transitionSettings: {
		[setting: string]: any;
	};
}

interface GPPageTransitions extends GPPageTransitionsArgs {}

class GPPageTransitions implements GPPageTransitions {
	public formId!: number;
	public swiper!: Swiper.Swiper;
	public rules = {};
	public initialized: boolean = false;
	public sourcePage: number = 0;
	public inputs: { [selector: string]: JQuery<HTMLElement> } = {};
	public functions = {};
	public currentPage!: number;
	public $formElem!: JQuery<HTMLElement>;
	public $currentPage!: JQuery<HTMLElement>;
	public gfPageConditionalLogic: any;
	public percentageInterval?: NodeJS.Timer;

	/**
	 * Observer to resize form body any time the form DOM changes (e.g. adding a row via the list field)
	 */
	public observer: MutationObserver | undefined;

	constructor(args: GPPageTransitionsArgs) {
		Object.assign(this, args);
	}

	init(currentPage: string) {
		// Do not reinitialize if GF is attempting a JS redirect.
		// @ts-ignore
		if (window?.gformRedirect) {
			return;
		}

		// Class is reinitialized on every page load.
		this.initialized = false;

		// @ts-ignore
		delete this.swiper;

		// confirmation page will have no current page specified; no need to init on confirmation
		if (!currentPage) {
			return;
		}

		if (this.submission.hasError) {
			this.sourcePage = this.submission.sourcePage;
		}

		this.currentPage = parseInt(currentPage);
		this.$formElem = $('#gform_' + this.formId); // @todo: might need to change for WC GF Product Add-ons
		this.$currentPage = $(
			'#gform_page_' + this.formId + '_' + this.currentPage
		);

		this.bindEvents();

		this.initialized = true;
	}

	initPageTransitions() {
		if (!this.$formElem || !this.currentPage || !this.pagination.pageCount) {
			return;
		}

		const $formBody = this.$formElem.find('.gform_body'),
			startingIndex = Math.max(0, this.sourcePage - 1), //Math.max( 0, isForward ? this.currentPage - 2 : this.currentPage ),
			currentIndex = this.currentPage - 1;

		// Set the source page *after* we have identified th startingIndex.
		this.sourcePage = this.currentPage;

		$formBody.css({ width: $formBody.width()! });
		$formBody.find('.gform_page').css({ width: $formBody.width()! });

		this.$formElem.addClass('swiper');
		this.$formElem.find('.gform_body').addClass('swiper-wrapper gform-theme__no-reset--el');
		this.$formElem
			.find('.gform_page')
			.attr('style', '')
			.addClass('swiper-slide gform-theme__no-reset--el')

		this.swiper = new window.GPPageTransitionsSwiper.Swiper(
			this.$formElem[0],
			window.gform.applyFilters(
				'gppt_swiper_options',
				$.extend(
					{},
					{
						initialSlide: startingIndex,
						fadeEffect: { crossFade: true },
						modules: [window.GPPageTransitionsSwiper.EffectFade],
						on: {
							beforeTransitionStart: this.beforeTransition,
							slideChangeTransitionEnd: this.afterTransition,
							slideChange: this.slideChange,
						},
						noSwipingClass: 'ts-wrapper',
					},
					this.transitionSettings
				),
				this
			)
		);

		this.conditionalLogicDisableSlides();

			this.swiper.slideTo(this.getCurrentPageSlideIndex());

		this.updateInertAttr();

		/*
		 * If soft validation is disabled, we need to work around an issue where the GF 'submit' handler will look for
		 * any _disabled_ next buttons that are visible (which they will be since they won't be display: none;) and
		 * prevent submission from occurring.
		 */
		if (!this.enableSoftValidation) {
			this.$formElem.on('submit.gppt', () => {
				// Remove any next buttons on pages outside of the current page
				this.$formElem.find('.swiper-slide:not(.swiper-slide-active) .gform_next_button').remove();
			});
		}

		// resize form body anytime form DOM changes (e.g. adding a row via the list field) or if fields are hidden/shown
		this.observer = new MutationObserver((mutations) => {
			mutationLoop: for (let i = 0; i < mutations.length; i++) {
				// find the first non-text node (removed or added) and trigger our resize
				// otherwise resize is fired when we update any text which can be problematic
				if ( mutations[i].type === 'childList' ) {
					const nodes =
						mutations[i].addedNodes.length > 0
							? mutations[i].addedNodes
							: mutations[i].removedNodes;

					for (let j = 0; j < nodes.length; j++) {
						if (nodes[j].nodeType !== 3) {
							this.swiper?.updateAutoHeight(
								this.transitionSettings.speed / 4
							);
							break mutationLoop;
						}
					}
				} else if ( mutations[i].type === 'attributes' && mutations[i].attributeName === 'style' ) {
					this.swiper?.updateAutoHeight(
						this.transitionSettings.speed / 4
					);
				}
			}
		});

		this.observer.observe(this.$formElem[0], {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['style'],
		});

		if (this.submission.hasError && startingIndex !== currentIndex) {
			this.updateProgressIndicator(this.currentPage);
		}

		/**
		 * Listen to scroll events on the Swiper container to always ensure that the scrollTop is 0.
		 *
		 * These scroll events will frequently be triggered by an element being focused.
		 */
		this.$formElem.on('scroll', () => {
			this.$formElem.scrollTop(0);
		});

		// @ts-ignore
		window[`GPPT_${this.formId}`] = self;
	}

	conditionalLogicDisableSlides() {
		const pageCl = this.gfPageConditionalLogic;

		if (!pageCl || !this.swiper) {
			return;
		}

		// Add/remove slides based on visibility
		for (let i = 0; i < pageCl.options.pages.length; i++) {
			const page = pageCl.options.pages[i];

			const $slide = this.$formElem
				.find('.swiper-wrapper')
				.children('.gform_page ')
				.eq(i + 1);

			if (!pageCl.isPageVisible(page)) {
				$slide
					.removeClass(
						'swiper-slide swiper-slide-next slider-slide-prev'
					)
					.addClass('swiper-slide-disabled')
					.css({ display: 'none' });
			} else {
				$slide
					.removeClass('swiper-slide-disabled')
					.addClass('swiper-slide')
					.css({ display: '' });
			}
		}

		this.swiper.updateSlides();
		this.swiper.updateSlidesClasses();
	}

	bindEvents() {
		if (!this.$formElem) {
			return;
		}

		window.gform.addAction(
			'gform_frontend_pages_evaluated',
			(pages: any, formId: number, self: any) => {
				if (formId === this.formId) {
					this.gfPageConditionalLogic = self;
					this.conditionalLogicDisableSlides();

					/*
					 * Ensure Swiper is initialized, so we're not immediately updating the progress indicator on load which
					 * can make for an awkward animation.
					 */
					if (this.swiper) {
						this.updateProgressIndicator(this.currentPage);
					}
				}
			}
		);

		if (this.enablePageTransitions) {
			if (!this.hasConditionalLogic) {
				this.initPageTransitions();
			} else {
				$(document).bind('gform_post_conditional_logic', () => {
					if (!this.swiper && this.$formElem.is(':visible')) {
						this.initPageTransitions();
					}
				});
			}
		}

		if (this.enablePageTransitions && this.enableSoftValidation) {
			const $previousButtons = this.$formElem.find(
					'.gform_previous_button'
				),
				$nextButtons = this.$formElem.find('.gform_next_button'),
				$saveButton = this.$formElem.find('.gform_save_link');

			// Handle clicking the submit button as well as using the enter button, excluding the save and continue buttons.
			this.$formElem.on('submit', (event): void | false => {
				// Bypass submitting if the user clicked the Save & Continue button.
				// eslint-disable-next-line @wordpress/no-global-active-element
				if ( document.activeElement && $(document.activeElement).is('.gform_save_link') ) {
					return;
				}

				const valid = this.validate();

				// If we're on the last page, let the form submit.
				if (valid && this.isOnLastPage()) {
					// Ensure target page number is 0 right before submission for things like honeypot, etc if the
					// last _actual_ page is visible.
					if ( this.$formElem.find('.gform_page:last').is(':visible') ) {
						$(`#gform_target_page_number_${this.formId}`).val(0);
					}

					return;
				}

				// @ts-ignore
				window[`gf_submitting_${this.formId}`] = false;

				this.removeSpinner();
				event.preventDefault();

				if (valid) {
					this.$currentPage.find($nextButtons).filter(':visible').trigger('click');
				} else {
					return false;
				}
			});

			$previousButtons.each((_, el) => {
				$(el)
					.attr('type', 'button')
					.attr('onclick', '')
					.attr('onkeypress', '')
					.on('click keypress', (event) => {
						// previous button on last page is a submit button (yeah, no idea)
						event.preventDefault();

						if (
							event.type === 'click' &&
							// @ts-ignore
							event.originalEvent.detail === 0
						) {
							if (this.validate()) {
								this.$formElem.trigger('nextPage.gppt', [
									this.currentPage + 1,
									this.currentPage,
									this.formId,
								]);
							}
							return;
						}

						this.$formElem.trigger('prevPage.gppt', [
							this.currentPage - 1,
							this.currentPage,
							this.formId,
						]);
					});
			});

			$nextButtons.each((_, el) => {
				$(el)
					.attr('onclick', '')
					.attr('onkeypress', '')
					.on('click', (event) => {
						// We explicitly do not bind to the keypress event here as the form submit handler will catch it.

						if (this.validate()) {
							// If the current page has reached the limit, it's time to submit
							if ( this.isOnLastPage() ){
								$( '#gform_' + this.formId ).submit();

								return;
							}

							this.$formElem.trigger('nextPage.gppt', [
								parseInt(this.currentPage as unknown as string) + 1,
								this.currentPage,
								this.formId,
							]);
						}
					});
			});

			this.$formElem.on(
				'prevPage.gppt',
				(event, newPage, oldPage, formId) => {
					this.currentPage = newPage;
					this.sourcePage = oldPage;
					this.$currentPage = $(
						'#gform_page_' + formId + '_' + this.currentPage
					);

					this.swiper?.slidePrev();
					this.updateProgressIndicator(this.currentPage);
					this.$formElem.trigger('softValidationPageLoad.gppt', [
						newPage,
						oldPage,
						formId,
					]);
				}
			);

			this.$formElem.on(
				'nextPage.gppt',
				(event, newPage, oldPage, formId) => {
					this.currentPage = newPage;
					this.sourcePage = oldPage;
					this.$currentPage = $(
						'#gform_page_' + formId + '_' + this.currentPage
					);

					this.swiper?.slideNext();
					this.updateProgressIndicator(this.currentPage);
					this.$formElem.trigger('softValidationPageLoad.gppt', [
						newPage,
						oldPage,
						formId,
					]);
				}
			);

			/*
			 * Finish initialization for Nested Form fields that were originally hidden. This is only used for soft validation as soft validation does not trigger gform_post_render as of writing.
			 *
			 * gform_post_render maybe the best route to go here at some point, but it caused issues with GPNF where it'd try to reinit Knockout, but Knockout would fail to since the template
			 * HTML would be missing after the original KO init.
			 */
			this.$formElem.on('softValidationPageLoad.gppt', (
				event,
				newPage,
				oldPage,
				formId
			) => {
				// Update the hidden inputs for source and target page numbers.
				$(`#gform_source_page_number_${formId}`).val(this.getCurrentPageRealIndex() + 1);

				/*
				 * Set the target page number to the next expected page. Note, we don't set the target page number to 0
				 * as we ensure it is '0' right before submission if it's truly the last page.
				 */
				$(`#gform_target_page_number_${formId}`).val(this.getCurrentPageRealIndex() + 2);

				// GP Nested Forms compatibility
				for (const k in window) {
					if (
						window.hasOwnProperty(k) &&
						k.indexOf('GPNestedForms_' + formId + '_') === 0
					) {
						const gpnf: any = window[k];

						if (
							gpnf.hasOwnProperty('finalizeInit') &&
							!gpnf.inHiddenPage() &&
							!gpnf.initialized
						) {
							gpnf.finalizeInit();
						}
					}
				}

				// Evaluate CL pages to do things like change the Next button to Submit.
				this.gfPageConditionalLogic?.evaluatePages();
			});
		}

		if (this.enableAutoProgress) {
			this.$formElem.find('.gform_page:not(.gppt-disable-auto-progress)').each((_, el) => {
				const $pageElem = $(el);
				const $fields = $pageElem.find(
					'.gfield.gppt-auto-progress-field'
				);

				$fields.each(
					// eslint-disable-next-line @typescript-eslint/no-shadow
					(_, el) => {
						const $field = $(el);
						const events = ['gpptAutoProgress'];

						let $inputs = $();

						if (
							$field.find('input[value="gf_other_choice"]').length
						) {
							// any radio button except the "other" radio button
							$inputs = $inputs.add(
								$field.find(
									'input[type="radio"][value!="gf_other_choice"]'
								)
							);
							events.push('change');
						} else if ($field.find('.gsurvey-likert').length) {
							$inputs = $inputs.add(
								$field.find(
									'.gsurvey-likert tbody tr:last-child input'
								)
							);
							events.push('change');
						} else if ($field.find('.gsurvey-rating').length) {
							$inputs = $inputs.add(
								$field.find('.gsurvey-rating label')
							);
							events.push('click');
						} else {
							const $currentInputs = $field
								.find('input, select')
								.not('input[type="hidden"]');

							if ($currentInputs.length) {
								/*
								 * If radio field, include all inputs, otherwise only include the last input
								 * in a set of inputs as it could potentially cause the form to progress
								 * too early.
								 */
								if ($currentInputs.is(':radio')) {
									$inputs = $inputs.add($currentInputs);

									// Add "click" event for radio fields in case the user is clicking on an already selected radio button.
									events.push('click');
								} else {
									$inputs = $inputs.add(
										$currentInputs.last()
									);
								}

								// filter out text inputs; they are exclusively handled by input masks via gpptAutoProgress
								if (!$currentInputs.is(':text')) {
									events.push('change');
								}

								// Auto Progress functionality for GP Advanced Phone Field
								const { formId } = this;

								$currentInputs.on('keyup', function () {
									if (!$field.find('.iti').length) {
										return;
									}

									const fieldId = $field?.attr('id')?.split('_')[2];

									// @ts-ignore
									const gpapf = window?.[`gp_advanced_phone_field_${formId}_${fieldId}`];

									if (gpapf?.iti) {
										const {iti} = gpapf;

										// Ensure phone number is valid before auto-progressing
										const value = $(this).val()?.toString().trim();
										const isValid = iti.isValidNumber(value);

										if (isValid) {
											jQuery(this).trigger('gpptAutoProgress');
										}
									}
								});
							}
						}

						if (!$inputs) {
							return;
						}

						$inputs.on(events.join(' '), (event) => {
							const $this = $(event.currentTarget);

							// Save the timeout to the input data so this callback only runs once. For instance,
							// if both change and click are triggered, we only want to run this callback once.
							if ($this.data('gpptAutoProgressTimeout')) {
								return;
							}

							// Move handler to the bottom of the stack to ensure
							// that conditional fields are shown/hidden before we check
							// if this is the last field in the form
							const timeout = window.setTimeout(() => {
								// eslint-disable-next-line @typescript-eslint/no-shadow
								const $field = $this.parents('.gfield');

								if (
									!$pageElem
										.find('.gfield:visible:not(.gfield_html):last')
										.is($field)
								) {
									return;
								}

								if ($pageElem.is('.swiper-disabled')) {
									return;
								}

								const $nextButton = $pageElem.find(
									'.gform_next_button'
								);
								if ($nextButton.length) {
									$nextButton.click();
								} else {
									/* eslint-disable jsdoc/no-undefined-types */
									/**
									 * Filter whether to automatically submit the form on last page after selecting
									 * the last auto-progression-supported input.
									 *
									 * @since 1.0-beta-1.29
									 *
									 * @param {boolean}           autoSubmit Whether to auto-submit.
									 * @param {number}            formId     Current form ID.
									 * @param {JQuery}            $trigger   Input triggering the auto-progression.
									 * @param {GPPageTransitions} instance   Current instance of GP Page Transitions.
									 */
									// eslint-disable-next-line no-lonely-if
									if (
										window.gform.applyFilters(
											'gppt_auto_submit',
											false,
											this.formId,
											$this,
											self
										)
									) {
										$(
											'#gform_submit_button_' +
												this.formId
										).click();
									} else {
										$(
											'#gform_submit_button_' +
												this.formId
										).focus();
									}
								}

								$this.removeData('gpptAutoProgressTimeout');
							}, 0);

							$this.data('gpptAutoProgressTimeout', timeout);
						});
					}
				);
			});

			// trigger a change event on Datepicker selection for auto-progress-enabled Datepicker fields.
			window.gform.addFilter(
				'gform_datepicker_options_pre_init',
				(options: any, formId: number, fieldId: number) => {
					// eslint-disable-next-line eqeqeq
					if (
						formId !== this.formId ||
						!$(`#input_${formId}_${fieldId}`)
							.parents('.gfield')
							.hasClass('gppt-auto-progress-field')
					) {
						return options;
					}

					const onSelect = options.onSelect;

					options.onSelect = function() {
						if (typeof onSelect === 'function') {
							onSelect();
						}
						$(this).trigger('gpptAutoProgress');
					};

					return options;
				}
			);

			// show AJAX spinner on Previous button if not other button is visible (specifically if Next button is hidden)
			window.gform.addFilter(
				'gform_spinner_target_elem',
				($target: JQuery<HTMLElement>) => {
					const selectors = [
						'#gform_submit_button_' + this.formId + ':visible',
						'.gform_next_button:visible',
						'.gform_previous_button:visible',
					];
					for (let i = 0; i < selectors.length; i++) {
						const $newTarget = this.$currentPage.find(selectors[i]);
						if ($newTarget.length > 0) {
							return $newTarget;
						}
					}
					return $target;
				}
			);
		}
	}

	/**
	 * Force remove the spinner if it gets added when progressing pages.
	 *
	 * Useful if using Soft Validation.
	 */
	removeSpinner() {
		const $spinnerTarget = window.gform.applyFilters(
			'gform_spinner_target_elem',
			$(
				'#gform_submit_button_' +
				this.formId +
				', #gform_wrapper_' +
				this.formId +
				' .gform_next_button, #gform_send_resume_link_button_' +
				this.formId
			),
			this.formId
		);

		$spinnerTarget.siblings('.gform_ajax_spinner, .gform-loader').remove();
	}

	validate() {
		const currentSelectors =
			this.validationSelectors[this.getCurrentPageRealIndex() + 1] ?? [];
		let result = true;

		for (let i = 0; i < currentSelectors.length; i++) {
			const selector = currentSelectors[i];
			const $inputs = this.getInput(
				selector.selectors.join(', '),
				true, // Bypass cache to ensure the node exists so we can add an error to it.
			);
			const $parent = $inputs.parents('.gfield');

			let isEmpty = false;

			// Condtionally hidden fields should not fails this validation.
			if (window.gformIsHidden($inputs)) {
				isEmpty = false;
				// Make sure at least one checkbox or radio button is checked.
			} else if ($inputs.is(':checkbox') || $inputs.is(':radio')) {
				isEmpty = $inputs.filter(':checked').length === 0;
				// support for multifile upload fields
			} else if (
				$inputs.is(':file') &&
				window.gfMultiFileUploader.uploaders[
					`gform_multifile_upload_${this.formId}_${selector.id}`
				]
			) {
				const uploader =
					window.gfMultiFileUploader.uploaders[
						`gform_multifile_upload_${this.formId}_${selector.id}`
					];

				const uploaderHiddenInput = $(`#gform_uploaded_files_${this.formId}`);

				try {
					const uploaderFiles = JSON.parse(uploaderHiddenInput.val() as string);

					isEmpty = uploaderFiles?.[`input_${selector.id}`].length <= 0;
				} catch (e) {
					// eslint-disable-next-line no-console
					console.debug(e);
					isEmpty = true;
				}
			} else if (selector.relation === 'any') {
				isEmpty = !$inputs.val();
			} else {
				$.each(
					$inputs,
					// @ts-ignore
					(_, el) => {
						if (!$(el).val() && !$(el).hasClass('gform_hidden')) {
							isEmpty = true;
							return false;
						}
					}
				);
			}

			if (isEmpty) {
				if (!$parent.hasClass(this.validationClass.split(' ')[0])) {
					$parent.addClass(this.validationClass);
					$parent
						.children('.ginput_container')
						.after(
							this.validationMessageContainer.gformFormat(
								selector.validationMessage
							)
						);
				}
				result = false;
			} else if ($parent.hasClass(this.validationClass.split(' ')[0])) {
				$parent.removeClass(this.validationClass);
				$parent
					.children('.ginput_container')
					.next()
					.remove();
			}

			// Bypass soft validation for the Signature field so pages can at least be navigated and the form submitted.
			// Server-side validation will bring the user back to any unfilled Signature fields.
			if ($parent.hasClass('gfield--type-signature')) {
				result = true;
			}
		}

		if (result) {
			this.$formElem
				.parents('.gform_wrapper')
				.removeClass(this.validationClassForm);
		} else {
			this.$formElem
				.parents('.gform_wrapper')
				.addClass(this.validationClassForm);
		}

		// If Soft Validation is to be skipped (GP Live Preview).
		if (this.skipSoftValidation) {
			result = true;
		}

		// Force height to update in case the mutation observer doesn't catch it.
		this.swiper?.updateAutoHeight(
			this.transitionSettings.speed / 4
		);

		return window.gform.applyFilters(
			'gppt_validation_result',
			result,
			this,
			this.formId
		);
	}

	getNamespacedEvents(eventsString: string, namespace: string) {
		const events = eventsString.split(' ');
		const namespacedEvents = [];

		for (let i = 0; i < events.length; i++) {
			namespacedEvents.push(events[i] + '.' + namespace);
		}

		return namespacedEvents.join(' ');
	}

	beforeTransition = (swiper: Swiper.Swiper): void => {
		const curr = swiper.clickedSlide;
		const next = swiper.slides[swiper.activeIndex];

		window.gform.doAction('gppt_before_transition', curr, next, this);
		this.triggerSoftValidationConditionalLogic(next);

		this.updateInertAttr();
	};

	afterTransition = (): void => {
		window.gform.doAction('gppt_after_transition', this);
	};

	/**
	 * Scroll to the top of the form when changing slides/pages.
	 */
	slideChange = (): void => {
		const scrollTop = this.$formElem.parents('.gform_wrapper')?.offset()!.top - 50;
		const currentScrollTop = $(window).scrollTop() ?? 0;

		// Only animate scrolling if the user is beyond the scroll top of the form top.
		if ( currentScrollTop < scrollTop ) {
			return;
		}

		$('html, body').animate(
			{ scrollTop },
			this.transitionSettings.speed
		);
	}

	/**
	 * Add inert attribute on inactive slides to prevent tabbing to inactive tabs due to
	 * https://github.com/nolimits4web/swiper/issues/4006
	 */
	updateInertAttr = (): void => {
		this.$formElem.find('.gform_page:not(.swiper-slide-active)').attr('inert', '');
		this.$formElem.find('.gform_page.swiper-slide-active').removeAttr('inert');
	}

	/**
	 * Since conditional logic relies on visibility, we need to re-trigger conditional logic when transitioning between pages.
	 *
	 * We apply the rules to all fields with conditional logic in the next page.
	 *
	 * @param next HTMLDivElement The next page.
	 */
	triggerSoftValidationConditionalLogic(next: Element) {
		if (!this.enablePageTransitions || !this.enableSoftValidation) {
			return;
		}

		const fieldIdsOnNextPage: any[] = [];

		if (
			!window.gf_form_conditional_logic ||
			!window.gf_form_conditional_logic[this.formId]
		) {
			return;
		}

		const $nextPage = $(next);

		$.each(
			window.gf_form_conditional_logic[this.formId].dependents,
			(fieldId: number) => {
				if (
					$nextPage.find('#field_' + this.formId + '_' + fieldId)
						.length
				) {
					fieldIdsOnNextPage.push(fieldId);
				}
			}
		);

		requestAnimationFrame(() => {
			window.gf_apply_rules(this.formId, fieldIdsOnNextPage);
		});
	}

	updateProgressIndicator(pageNumber: number, speed?: number) {
		if (this.pagination.type === 'none') {
			return;
		}

		const $progressIndicator =
			this.pagination.type === 'steps'
				? $('#gf_page_steps_' + this.formId)
				: $('#gf_progressbar_wrapper_' + this.formId);

		if (typeof speed === 'undefined') {
			speed = this.getProgressIndicatorTransitionSpeed();
		}

		if (this.pagination.isCustom) {
			$progressIndicator.fadeOut(speed!, () => {
				const newProgressIndicator = $(
					this.pagination.progressIndicators[pageNumber - 1]
				);
				$progressIndicator
					.html(newProgressIndicator.html())
					.fadeIn(this.getProgressIndicatorTransitionSpeed());
			});
		} else if (this.pagination.type === 'steps') {
			const $steps = $progressIndicator.find('.gf_step');
			// eslint-disable-next-line @typescript-eslint/no-shadow
			let pageNumber = 0;

			$steps
				.removeClass(
					'gf_step_completed gf_step_active gf_step_next gf_step_pending'
				)
				.each((i, el) => {
					const $step = $(el);

					if ($step.hasClass('gf_step_hidden')) {
						return;
					}

					pageNumber = pageNumber + 1;

					if (pageNumber < this.currentPage) {
						$step.addClass('gf_step_completed');
					} else if (pageNumber === this.currentPage) {
						$step.addClass('gf_step_active');
					} else if (pageNumber === this.currentPage + 1) {
						$step.addClass('gf_step_next');
					} else {
						$step.addClass('gf_step_pending');
					}
				});
		} else {
			const $percentageBar = $progressIndicator.find(
				'.gf_progressbar_percentage'
			);
			const $percentNumber = $percentageBar.children('span');
			const currentPercentage = this.getProgressPercentage(
				this.progressBarStartAtZero
					? this.sourcePage - 1
					: this.sourcePage
			);
			const targetPercentage = this.getProgressPercentage(
				this.progressBarStartAtZero
					? this.currentPage - 1
					: this.currentPage
			);
			const isForward = targetPercentage > currentPercentage;
			const $progressBarTitle = $progressIndicator.find(
				'.gf_progressbar_title'
			);

			const pageName = this.getPaginationPages()[this.currentPage - 1];
			let pageTitleSuffix = '';

			let diffPoints = Math.abs(targetPercentage - currentPercentage);

			if (pageName) {
				pageTitleSuffix = ` &ndash; ${pageName}`;
			}

			$percentageBar
				.width(targetPercentage + '%')
				// @ts-ignore
				.removeClass(/percentbar_\d+/g)
				.addClass(`percentbar_${targetPercentage}`);

			$progressBarTitle.html(
				this.pagination.labels.step +
					` ` +
					`<span class="gf_step_current_page">${this.currentPage}</span>` +
					` ${this.pagination.labels.of} ` +
					`<span class="gf_step_page_count">${this.getVisiblePageCount()}</span>
					${pageTitleSuffix}`
			);

			/*
			 * Prevent multiple intervals from running at once. Unlikely to happen in the real-world, but can happen
			 * with automated tests.
			 */
			if (this.percentageInterval) {
				clearInterval(this.percentageInterval);
			}

			this.percentageInterval = setInterval(() => {
				// eslint-disable-next-line @typescript-eslint/no-shadow
				const currentPercentage =
					targetPercentage -
					(isForward ? diffPoints : 0 - diffPoints);
				diffPoints--;
				$percentNumber.text(currentPercentage + '%');

				if (currentPercentage === targetPercentage) {
					clearInterval(this.percentageInterval);
				}
			}, 1000 / diffPoints);
		}
	}

	getVisiblePageCount(): number {
		const pageCl = this.gfPageConditionalLogic;

		if (pageCl) {
			let pageCount = 1;

			for (let i = 0; i < pageCl.options.pages.length; i++) {
				const page = pageCl.options.pages[i];

				if (pageCl.isPageVisible(page)) {
					pageCount++;
				}
			}

			return pageCount;
		}

		return this.pagination.pageCount;
	}

	isOnLastPage(): boolean {
		return this.currentPage >= Number(this.getVisiblePageCount());
	}

	getPaginationPages() {
		const pageCl = this.gfPageConditionalLogic;

		if (pageCl) {
			const pages = [this.pagination.pages[0]];

			for (let i = 0; i < pageCl.options.pages.length; i++) {
				const page = pageCl.options.pages[i];

				if (pageCl.isPageVisible(page)) {
					pages.push(this.pagination.pages[i + 1]);
				}
			}

			return pages;
		}

		return this.pagination.pages;
	}

	getProgressIndicatorTransitionSpeed() {
		return parseInt(this.transitionSettings.sync) === 1
			? this.transitionSettings.speed / 2
			: this.transitionSettings.speed;
	}

	getProgressPercentage(currentPage: number) {
		return Math.floor((currentPage / this.getVisiblePageCount()) * 100);
	}

	getInput(selector: string, bypassCache?: boolean) {
		if (typeof this.inputs[selector] === 'undefined' || bypassCache) {
			this.inputs[selector] = $(selector);
		}

		return this.inputs[selector];
	}

	getCurrentPageSlideIndex() {
		const $activeSlides = this.$formElem
			.find('.swiper-slide:not(.swiper-slide-disabled');

		const $currentSlide = this.$formElem.find( `#gform_page_${this.formId}_${this.currentPage}` );

		return $activeSlides.index($currentSlide);
	}

	/**
	 * this.currentPage takes conditional page logic into account which can cause lookups by initial page indexes
	 * for things like validation selectors to return the wrong info.
	 *
	 * The way we fetch the "real" index is by using jQuery and getting the page's index in the DOM.
	 */
	getCurrentPageRealIndex() {
		return this.$formElem
			.find('.gform_page')
			.index(this.$formElem.find('.gform_page.swiper-slide-active'));
	}
}

window.GPPageTransitions = GPPageTransitions;
