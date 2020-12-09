

(function($) {

	var stickyHeaderScrollTracker = 0;


	$(document).ready(function() {

		$.wptheme.initSiteAnnouncement();
		$.wptheme.initHeader();
		$.wptheme.initMobileMenu();
		$.wptheme.initGalleries();
		$.wptheme.initSliders();
		$.wptheme.initModals();
		$.wptheme.initOdometers();
		$.wptheme.initExpandableContent();
		$.wptheme.initCoreValuesBlocks();
		$.wptheme.initCaseStudyIndexBlocks();
		$.wptheme.initTeamMemberIndexBlocks();

	});


	$(window).load(function() {

		

	});


	$.wptheme = (function(wptheme) {


		wptheme.initSiteAnnouncement = function() {

			$(document).on('click', '#site-announcement button.dismiss', function(e) {
				var announcement = $('#site-announcement');
				announcement.removeClass('shown');
				setTimeout(function() { announcement.addClass('active'); }, 500);
				var id = announcement.data('announcement-id');
				setCookie('site_announcement_' + id, 'dismissed', 365);
			});

			var adjustAnnouncement = function() {
				var announcement = $('#site-announcement');
				if(announcement.length && announcement.hasClass('active')) {
					var height = announcement.outerHeight();
					announcement.css({ marginTop: -height });
				}
			};

			var announcement = $('#site-announcement');
			if(announcement.length) {
				var id = announcement.data('announcement-id');
				var status = getCookie('site_announcement_' + id);
				if(status != 'dismissed') {
					announcement.addClass('active');
					setTimeout(function() { announcement.addClass('shown'); }, 500);
					adjustAnnouncement();
					$(window).on('load resize', adjustAnnouncement);
				}
			}
			
		};


		wptheme.initHeader = function() {

			var header = $('#header');
			var first_section = $('#main-content').children().first();
			if(first_section.is('.wp-block-crown-blocks-container.text-color-light.alignfull')) header.addClass('text-color-light');
			header.addClass('loaded');

			var adjustSubMenus = function() {
				var windowWidth = $('body').width();
				var gap = 10;
				$('#header-primary-navigation .sub-menu:not(.mega-sub-menu)').each(function(i, el) {
					var subMenu = $(el);
					var isSubSubMenu = subMenu.parent().closest('.sub-menu').length;
					subMenu.css({ marginLeft: 0 });
					if(isSubSubMenu) subMenu.removeClass('drop-left');
					if(subMenu.offset().left + subMenu.outerWidth() > windowWidth - gap) {
						if(isSubSubMenu) {
							subMenu.addClass('drop-left');
						} else {
							subMenu.css({ marginLeft: (windowWidth - (subMenu.offset().left + subMenu.outerWidth()) - gap) + 'px' });
						}
					}
				});
			};

			adjustSubMenus();
			$(window).on('load', adjustSubMenus);
			$(window).on('resize', adjustSubMenus);

			// // activate sub-menus on hover
			// $('#header-primary-navigation .menu-item').on('mouseenter', function(e) {
			// 	var menuItem = $(this);
			// 	var subMenu = $('> .sub-menu', menuItem);
			// 	if(subMenu.length) {
			// 		menuItem.addClass('active');
			// 	}
			// }).on('mouseleave', function(e) {
			// 	var menuItem = $(this);
			// 	if(menuItem.hasClass('active')) {
			// 		menuItem.removeClass('active');
			// 	}
			// });

			// activate sub-menus on click
			$('#header-primary-navigation a').on('click', function(e) {
				var menuItem = $(this).closest('.menu-item');
				var subMenu = $('> .sub-menu', menuItem);
				$('#header-primary-navigation .menu-item.active').not($(this).parents('.menu-item.active')).removeClass('active');
				if(subMenu.length && !menuItem.hasClass('active')) {
					e.preventDefault();
					menuItem.addClass('active');
				}
			});
			$(document).on('click', function(e) {
				if($(e.target).closest('#header-primary-navigation-menu').length) return;
				$('#header-primary-navigation .menu-item.active').removeClass('active');
			});

			var updateHeaderStatus = function() {
				var header = $('#header');
				var scrollTop = $(window).scrollTop();
				var threshold = 0;
				if($('body').width() < 601 && $('#wpadminbar').length) threshold += $('#wpadminbar').outerHeight();
				if($('#site-announcement.active.shown').length) threshold += $('#site-announcement.active.shown').outerHeight();
				if(scrollTop > threshold && !header.hasClass('is-sticky')) {
					header.addClass('is-sticky');
				} else if(scrollTop <= threshold && header.hasClass('is-sticky')) {
					header.removeClass('is-sticky');
				}
				if(scrollTop > stickyHeaderScrollTracker && scrollTop > threshold + 300 && !$('#header-primary-navigation-menu > .menu-item.active').length && !header.hasClass('is-minified')) {
					header.addClass('is-minified');
				} else if(scrollTop <= stickyHeaderScrollTracker && header.hasClass('is-minified')) {
					header.removeClass('is-minified');
				}
				stickyHeaderScrollTracker = scrollTop;
			};
			updateHeaderStatus();
			$(window).on('load scroll', updateHeaderStatus);

		};


		wptheme.initMobileMenu = function() {

			$(document).on('click', '#mobile-menu-toggle', function(e) {
				var body = $('body');
				if(body.is('.mobile-menu-active')) {
					$(document).trigger('close-mobile-menu');
				} else {
					body.addClass('mobile-menu-active');
				}
			});

			$(document).on('touchstart', 'body.mobile-menu-active #page', function(e) {
				if($(e.target).closest('#mobile-menu-toggle').length) return;
				$(document).trigger('close-mobile-menu');
			});

			$(document).on('close-mobile-menu', function() {
				var body = $('body');
				body.removeClass('mobile-menu-active');
			});

			$('#mobile-menu-primary-navigation .menu-item').each(function(i, el) {
				var menuItem = $(this);
				var subMenu = $('> .sub-menu', menuItem);
				if(subMenu.length) {
					menuItem.addClass('menu-item-has-sub-menu');
					menuItem.append('<button type="button" class="toggle">Toggle</button>');
				}
			});

			$('#mobile-menu-primary-navigation').on('click', 'button.toggle', function(e) {
				var menuItem = $(this).closest('.menu-item');
				var subMenu = $('> .sub-menu', menuItem);
				if(!menuItem.hasClass('active')) {
					menuItem.addClass('active');
					var startHeight = subMenu.outerHeight();
					subMenu.css({ height: 'auto' });
					var endHeight = subMenu.outerHeight();
					subMenu.css({ height: startHeight });
					setTimeout(function() { subMenu.css({ height: endHeight }); }, 10);
					setTimeout(function() { subMenu.css({ height: 'auto' }); }, 210);
				} else {
					menuItem.removeClass('active');
					var startHeight = subMenu.outerHeight();
					var endHeight = 0;
					subMenu.css({ height: startHeight });
					setTimeout(function() { subMenu.css({ height: endHeight }); }, 10);
				}
			});

			$('#mobile-menu-primary-navigation .menu-item-has-sub-menu.current-menu-item, #mobile-menu-primary-navigation .menu-item-has-sub-menu.current-menu-ancestor').each(function(i, el) {
				$('> .toggle', el).trigger('click');
			});

		};


		wptheme.initCTAs = function() {

			$(document).on('click', '#footer .call-to-action button.contact-form-toggle', function(e) {
				e.preventDefault();
				// var cta = $(this).closest('.call-to-action');
				// var container = $('.contents > .inner', cta);
				// var teaser = $('.teaser', cta);
				// var contactForm = $('.contact-form', cta);
				// container.height(container.outerHeight());
				// cta.addClass('contact-form-active');
				// container.height(contactForm.outerHeight());
				// $('input', contactForm).first().focus();
				// setTimeout(function() {
				// 	teaser.hide();
				// 	contactForm.css({ position: 'static' });
				// 	container.css({ height: 'auto' });
				// }, 1000);
				wptheme.openPrimaryCtaModal();
			});

			$(document).on('click', 'a', function(e) {
				if($(this).attr('href').substr(0, 1) == '#' && $(this).attr('href').length > 1) {
					if($(this).attr('href') == '#primary-cta-modal') {
						wptheme.openPrimaryCtaModal();
					} else {
						var modal = $($(this).attr('href'));
						if(modal.length && modal.hasClass('modal')) {
							e.preventDefault();
							modal.modal({});
						}
					}
				}
			});

			$(document).on('click', '.wp-block-crown-blocks-call-to-action button.contact-form-toggle', function(e) {
				e.preventDefault();
				var cta = $(this).closest('.wp-block-crown-blocks-call-to-action');
				if($(this).data('toggle') == 'modal') {
					if(!$(this).data('target')) {
						var modal = $('.modal', cta);
						if(modal.length) {
							var modalId = modal.attr('id');
							if(!modalId) {
								modalId = 'call-to-action-modal-' + new Date().getTime();
								modal.attr('id', modalId);
							}
							$('body').append(modal);
							$(this).data('toggle', 'modal').data('target', '#' + modalId);
							modal.modal({});
						}
					} else {
						var modal = $($(this).data('target'));
						modal.modal({});
					}
				}
			});

			var stickyCta = $('#site-sticky-cta');
			if(stickyCta.length) {
				if(!getCookie('sticky-cta-' + stickyCta.data('hash') + '-dismissed')) {
					stickyCta.addClass('enabled');
				}
			}
			$(document).on('click', '#site-sticky-cta button.cta-dismiss', function(e) {
				var stickyCta = $('#site-sticky-cta');
				stickyCta.addClass('dismissed');
				setCookie('sticky-cta-' + stickyCta.data('hash') + '-dismissed', 1, 30);
			});
			$(window).on('scroll', function(e) {
				var scrollTop = $(window).scrollTop();
				if(scrollTop > 400) {
					$('#site-sticky-cta.enabled:not(.active)').addClass('active');
				}
			});
			$(document).on('click', '#site-sticky-cta button.contact-form-toggle', function(e) {
				e.preventDefault();
				var cta = $(this).closest('#site-sticky-cta');
				if(!$(this).data('target')) {
					var modal = $('.modal', cta);
					if(modal.length) {
						var modalId = modal.attr('id');
						if(!modalId) {
							modalId = 'call-to-action-modal-' + new Date().getTime();
							modal.attr('id', modalId);
						}
						$('body').append(modal);
						$(this).data('toggle', 'modal').data('target', '#' + modalId);
						modal.modal({});
					}
				} else {
					var modal = $($(this).data('target'));
					modal.modal({});
				}
			});

		};

		wptheme.openPrimaryCtaModal = function() {
			var cta = $('#footer .call-to-action');
			var modalButton = $('button.contact-form-toggle', cta);
			if(modalButton.length) {
				if(!modalButton.data('target')) {
					var modal = $('.modal', cta);
					if(modal.length) {
						var modalId = modal.attr('id');
						if(!modalId) {
							modalId = 'call-to-action-modal-' + new Date().getTime();
							modal.attr('id', modalId);
						}
						$('body').append(modal);
						modalButton.data('toggle', 'modal').data('target', '#' + modalId);
						modal.modal({});
					}
				} else {
					var modal = $(modalButton.data('target'));
					modal.modal({});
				}
			}
		};


		wptheme.initGalleries = function() {

			var caseStudyGalleryCallback = function(e) {
				var images = $(this).closest('.entry-photos').find('.image').not('.slick-cloned');
				var galleryLinks = [];
				var index = 0;
				images.each(function(i, el) {
					galleryLinks.push({
						title: $('.caption', el).length ? $('.caption', el).text() : '',
						href: $('img.large', el).attr('src'),
						thumbnail: $('img.thumbnail', el).attr('src')
					});
					if($(el).hasClass('slick-current')) index = i;
				});
				blueimp.Gallery(galleryLinks, {
					index: index
				});
			};
			$(document).on('click', '.wp-block-crown-blocks-featured-case-studies article .entry-photos .expand-image', caseStudyGalleryCallback);
			$(document).on('click', '.wp-block-crown-blocks-case-study-index article .entry-photos .expand-image', caseStudyGalleryCallback);
			$(document).on('click', '.modal.case-study article .entry-photos .expand-image', caseStudyGalleryCallback);

			var nextMosiacGalleryBlockSlide = function(block) {
				var currentSlide = block.data('currentSlide');
				currentSlide = typeof currentSlide !== 'undefined' ? currentSlide : 0;
				var items = $('.blocks-gallery-item', block);
				var nextItem = items.eq(5);
				var currentSlot = items.eq(currentSlide);
				$('figure', nextItem).addClass('new');
				currentSlot.append($('figure', nextItem));
				$('.blocks-gallery-grid', block).append(nextItem);
				setTimeout(function() {
					$('figure', currentSlot).last().removeClass('new');
				}, 100);
				setTimeout(function() {
					nextItem.append($('figure', currentSlot).first());
				}, 1100);
				currentSlide++;
				if(currentSlide >= 5) currentSlide = 0;
				block.data('currentSlide', currentSlide);
			}
			$('.wp-block-gallery.is-style-mosaic-1').each(function(i, el) {
				var block = $(el);
				var items = $('.blocks-gallery-item', block);
				if(items.length > 5) {
					window.setInterval(nextMosiacGalleryBlockSlide, 3000, block);
				}
			});

		};


		wptheme.initSliders = function() {

			$('.wp-block-crown-blocks-recent-posts.display-as-thumbnails').each(function(i, el) {
				var slider = $('.post-feed > .inner', el);
				slider.on('beforeChange', function(e, slick, currentSlide, nextSlide) {
					var currentSlideEl = $('.slick-slide.slick-active', slick.$slideTrack);
					if(nextSlide == 0) currentSlideEl.next('.slick-cloned').addClass('slick-cloned-active');
					if(nextSlide == slick.slideCount - 1) currentSlideEl.prev('.slick-cloned').addClass('slick-cloned-active');
				}).on('afterChange', function(e, slick, currentSlide) {
					$('.slick-slide.slick-cloned-active', slick.$slideTrack).removeClass('slick-cloned-active');
				});
			});
			$(window).on('load resize orientationchange', function() {
				var windowWidth = $('body').width();
				$('.wp-block-crown-blocks-recent-posts.display-as-thumbnails').each(function(i, el) {
					var slider = $('.post-feed > .inner', el);
					if($(el).hasClass('display-as-slider-mobile') && slider.hasClass('slick-initialized')) return;
					if(windowWidth >= 576 || $(el).hasClass('display-as-slider-mobile')) {
						if(!slider.hasClass('slick-initialized')) {
							var itemCount = parseInt($('.post-feed', el).data('item-count'));
							var slickSettings = {
								mobileFirst: true,
								draggable: false,
								dots: false,
								arrows: true,
								slidesToShow: 1,
								slidesToScroll: 1,
								centerMode: true,
								centerPadding: '25px',
								prevArrow: '<button type="button" class="slick-prev"><svg class="bi bi-arrow-left-short" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.854 4.646a.5.5 0 0 1 0 .708L5.207 8l2.647 2.646a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 0 1 .708 0z"/><path fill-rule="evenodd" d="M4.5 8a.5.5 0 0 1 .5-.5h6.5a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"/></svg> <span class="label">Previous</span></button>',
								nextArrow: '<button type="button" class="slick-next"><span class="label">Next</span> <svg class="bi bi-arrow-right-short" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.146 4.646a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.793 8 8.146 5.354a.5.5 0 0 1 0-.708z"/><path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H4.5A.5.5 0 0 1 4 8z"/></svg></button>',
								responsive: [
									{ breakpoint: 576 - 1,  settings: { slidesToShow: 2, slidesToScroll: 2, centerMode: false, centerPadding: '0px' } }
								]
							};
							if(itemCount >= 3) slickSettings.responsive.push({ breakpoint: 992 - 1,  settings: { slidesToShow: 3, slidesToScroll: 3, centerMode: false, centerPadding: '0px' } });
							if(itemCount >= 4) slickSettings.responsive.push({ breakpoint: 1200 - 1, settings: { slidesToShow: 4, slidesToScroll: 4, centerMode: false, centerPadding: '0px' } });
							slider.slick(slickSettings);
						}
					} else {
						if(slider.hasClass('slick-initialized')) {
							slider.slick('unslick');
						}
					}
				});
			});

			// var windowWidth = $('body').width();
			// var centerPadding = 30;
			// if(windowWidth >= 768) centerPadding = 60;
			// if(windowWidth >= 900 + (60 * 2) - (20 * 2)) centerPadding = ((windowWidth - 900) / 2) - 20;
			$('.wp-block-crown-blocks-testimonial-slider').each(function(i, el) {
				var slider = $('.testimonial-slider-testimonials > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				slider.children().wrap('<div></div>');
				var slickSettings = {
					mobileFirst: true,
					draggable: true,
					dots: true,
					arrows: false,
					slidesToShow: 1,
					slidesToScroll: 1,
					centerMode: true,
					variableWidth: true,
					centerPadding: 0,
					// centerPadding: centerPadding + 'px',
				};
				slider.slick(slickSettings);
				slider.on('beforeChange', function(e, slick, currentSlide, nextSlide) {
					var currentSlideEl = $('.slick-slide.slick-active', slick.$slideTrack);
					if(nextSlide == 0) currentSlideEl.next('.slick-cloned').addClass('slick-cloned-active');
					if(nextSlide == slick.slideCount - 1) currentSlideEl.prev('.slick-cloned').addClass('slick-cloned-active');
				}).on('afterChange', function(e, slick, currentSlide) {
					$('.slick-slide.slick-cloned-active', slick.$slideTrack).removeClass('slick-cloned-active');
				}).on('setPosition', function(event, slick) {
					var slides = $('.slick-slide', slick.$slider);
					slides.css({ height: 'auto' });
					slides.css({ height: slick.$slideTrack.height() });
					if(slick.$slider.width() >= 840) {
						slick.$slider.slick('slickSetOption', 'variableWidth', true);
						slick.$slider.removeClass('flush-edge');
					} else {
						slick.$slider.slick('slickSetOption', 'variableWidth', false);
						slick.$slider.addClass('flush-edge');
					}
				});
			});
			// $(window).on('load resize orientationchange', function() {
			// 	var windowWidth = $('body').width();
			// 	var centerPadding = 30;
			// 	if(windowWidth >= 768) centerPadding = 60;
			// 	if(windowWidth >= 900 + (60 * 2) - (20 * 2)) centerPadding = ((windowWidth - 900) / 2) - 20;
			// 	$('.wp-block-crown-blocks-testimonial-slider .testimonial-slider-testimonials > .inner').slick('slickSetOption', 'centerPadding', centerPadding + 'px');
			// });

			$('.wp-block-crown-blocks-tabbed-content').each(function(i, el) {
				var slider = $('.tabbed-content-tabs > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var sliderNavContainer = $('<div class="slick-slider-nav"></div>');
				$('.tabbed-content-tabs', el).before(sliderNavContainer);
				slider.children().wrap('<div></div>');
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: true,
					arrows: false,
					fade: true,
					appendDots: sliderNavContainer,
					adaptiveHeight: false,
					customPaging: function(slider, pageIndex) {
						var tabTitleEl = $('> .wp-block-crown-blocks-tabbed-content-tab > .inner > .tab-title', slider.$slides[pageIndex]);
						var title = tabTitleEl ? tabTitleEl.text() : '';
						var tab = $('<button></button');
						tab.append('<span class="index">' + padNumber(pageIndex + 1, 2) + '<span>');
						if(title != '') tab.append(' <span class="label">' + title + '<span>');
						tab.append(' <span class="indicator"><span></span></span>');
						return tab;
					}
				};
				slider.slick(slickSettings);
			});
			$(document).on('mouseenter mousemove', '.wp-block-crown-blocks-tabbed-content .slick-slider-nav button', function(e) {
				var li = $(this).parent();
				if(!li.hasClass('slick-active')) {
					var slideIndex = li.index();
					var slider = $(this).closest('.slick-slider-nav').siblings('.tabbed-content-tabs').children('.slick-slider');
					slider.slick('slickGoTo', slideIndex);
				}
			});

			$('.wp-block-crown-blocks-container-featured-image').each(function(i, el) {
				var slider = $('.container-featured-image .slider', el);
				if(slider.hasClass('slick-initialized')) return;
				if(slider.children().length <= 1) return;
				var sliderNavContainer = $('<div class="slick-slider-nav"></div>');
				$('.container-contents', el).prepend(sliderNavContainer);
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: true,
					arrows: false,
					fade: true,
					autoplay: true,
					appendDots: sliderNavContainer,
					// customPaging: function(slider, pageIndex) {
					// 	var tabTitleEl = $('> .wp-block-crown-blocks-tabbed-content-tab > .inner > .tab-title', slider.$slides[pageIndex]);
					// 	var title = tabTitleEl ? tabTitleEl.text() : '';
					// 	var tab = $('<button></button');
					// 	tab.append('<span class="index">' + padNumber(pageIndex + 1, 2) + '<span>');
					// 	if(title != '') tab.append(' <span class="label">' + title + '<span>');
					// 	tab.append(' <span class="indicator"><span></span></span>');
					// 	return tab;
					// }
				};
				slider.slick(slickSettings);
			});
			var adjustContainerFeaturedImageSliderNav = function() {
				if($('body').width() < 768) return;
				$('.wp-block-crown-blocks-container-featured-image.has-wave-overlay .container-contents .slick-slider-nav').each(function(i, el) {
					var container = $(el).closest('.wp-block-crown-blocks-container-featured-image');
					var containerTop = container.offset().top;
					var containerHeight = container.height();
					var imageLeft = container.hasClass('featured-image-left');
					$('.slick-dots li').each(function(j, el2) {
						var li = $(el2);
						var percentTop = 1 - ((li.offset().top - containerTop) / containerHeight);
						var offset = 0;
						if(imageLeft) {
							offset = ((-Math.cos(percentTop * 1.4 * Math.PI) + 1) / 2) * (64 / 535) * -containerHeight - 3;
						} else {
							offset = ((Math.cos(percentTop * 1.4 * Math.PI) + 1) / 2) * (64 / 535) * containerHeight - 0;
						}
						li.css({ left: offset });
					});
				});
			};
			adjustContainerFeaturedImageSliderNav();
			$(window).on('load', adjustContainerFeaturedImageSliderNav);
			$(window).on('resize', adjustContainerFeaturedImageSliderNav);

			$('.wp-block-crown-blocks-featured-case-studies article.has-multiple-photos, .wp-block-crown-blocks-case-study-index article.has-multiple-photos').each(function(i, el) {
				var slider = $('.entry-photos .slider', el);
				if(slider.hasClass('slick-initialized')) return;
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: false,
					arrows: true,
					fade: false,
					autoplay: false
				};
				slider.slick(slickSettings);
			});

			$('.wp-block-crown-blocks-featured-case-study-logos .post-feed').each(function(i, el) {
				var slider = $('> .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var slickSettings = {
					mobileFirst: true,
					draggable: true,
					dots: false,
					arrows: true,
					fade: false,
					autoplay: false,
					slidesToShow: 3,
					slidesToScroll: 3,
					responsive: [
						{ breakpoint: 576 - 1, settings: { slidesToShow: 5, slidesToScroll: 5 } },
						{ breakpoint: 768 - 1, settings: { slidesToShow: 6, slidesToScroll: 6 } }
					]
				};
				slider.slick(slickSettings);
			});

			var setActiveMapPin = function(slick, slide) {
				var $nextSlide = slick.$slides.eq(slide);
				var mapPin = $nextSlide.data('map-pin');
				if(mapPin) {
					var block = slick.$slider.closest('.wp-block-crown-blocks-locations-map');
					var pins = $('.map-wrapper .map #pins-primary path', block);
					pins.removeClass('active');
					pins.filter('#' + mapPin).addClass('active');
				}
			};
			$(window).on('load resize orientationchange', function() {
				var windowWidth = $('body').width();
				$('.wp-block-crown-blocks-locations-map').each(function(i, el) {
					var slider = $('.locations-wrapper .locations', el);
					if(windowWidth >= 992) {
						if(!slider.hasClass('slick-initialized')) {
							var slickSettings = {
								mobileFirst: true,
								draggable: true,
								dots: false,
								arrows: true,
								fade: false,
								autoplay: false
							};
							slider.on('setPosition', function(event, slick) {
								var slides = $('.slick-slide', slick.$slider);
								slides.css({ height: 'auto' });
								slides.css({ height: slick.$slideTrack.height() });
							}).on('init', function(e, slick) {
								setActiveMapPin(slick, 0);
							}).on('beforeChange', function(e, slick, currentSlide, nextSlide) {
								setActiveMapPin(slick, nextSlide);
							}).slick(slickSettings);
							$(el).on('click', '.map-wrapper .map #pins-primary path', function(e) {
								var pin = $(this).attr('id');
								var block = $(this).closest('.wp-block-crown-blocks-locations-map');
								var slick = $('.locations-wrapper .locations', block).slick('getSlick');
								var targetSlide = slick.$slides.filter('.map-' + pin);
								if(targetSlide.length) {
									slick.$slider.slick('slickGoTo', targetSlide.index() - 1);
								}
							});
						}
					} else {
						if(slider.hasClass('slick-initialized')) {
							slider.slick('unslick');
						}
					}
				});
			});

		};


		wptheme.initModals = function() {

			$(document).on('click', 'a', function(e) {
				if(!$(this).attr('href')) return;
				if((matches = $(this).attr('href').match(/^#case-study-modal-(\d+)$/))) {
					e.preventDefault();
					var modal = $($(this).attr('href') + '.modal');
					if(modal.length) {
						modal.modal();
					} else {
						var id = parseInt(matches[1]);
						modal = $('<div class="modal fade case-study"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body"></div></div></div></div>');
						modal.attr('id', 'case-study-modal-' + id);
						modal.addClass('loading');
						$('.modal-content', modal).prepend('<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>');
						$('.modal-body', modal).html('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>');
						$('body').append(modal);
						modal.modal();
						$.get(crownThemeData.ajaxUrl, { action: 'the_case_study', id: id }, function(response) {
							var modal = $('#case-study-modal-' + response.id);
							if(modal.length) {
								modal.removeClass('loading');
								$('.modal-body', modal).html(response.html);
								if($('article.has-photos', modal).length) modal.addClass('has-photos');
								setTimeout(function() { 
									if($('article.has-multiple-photos', modal).length) {
										var slider = $('.entry-photos .slider', modal);
										if(slider.hasClass('slick-initialized')) return;
										var slickSettings = {
											mobileFirst: true,
											draggable: false,
											dots: false,
											arrows: true,
											fade: false,
											autoplay: false
										};
										slider.slick(slickSettings);
									}
								}, 500);
								setTimeout(function() { $(window).trigger('resize'); }, 1000);
							}
						}, 'json');
					}
				}
			});

			$(document).on('click', 'a', function(e) {
				if(!$(this).attr('href')) return;
				if((matches = $(this).attr('href').match(/^#team-member-modal-(\d+)$/))) {
					e.preventDefault();
					var modal = $($(this).attr('href') + '.modal');
					if(modal.length) {
						modal.modal();
					} else {
						var id = parseInt(matches[1]);
						modal = $('<div class="modal fade team-member"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body"></div></div></div></div>');
						modal.attr('id', 'team-member-modal-' + id);
						modal.addClass('loading');
						$('.modal-content', modal).prepend('<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>');
						$('.modal-body', modal).html('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>');
						$('body').append(modal);
						modal.modal();
						$.get(crownThemeData.ajaxUrl, { action: 'the_team_member', id: id }, function(response) {
							var modal = $('#team-member-modal-' + response.id);
							if(modal.length) {
								modal.removeClass('loading');
								$('.modal-body', modal).html(response.html);
							}
						}, 'json');
					}
				}
			});

		};


		wptheme.initOdometers = function() {

			$('.odometer-statistic').each(function(i, el) {
				var value = $('.stat-value', el);
				$(el).addClass('fade-in');
				if(value.length && (matches = value.text().match(/([^\d]*)([\d\.,]+)(.*)/))) {

					value.data('odometer-final-value', matches[2]);

					var preOd = $('<span class="pre">' + matches[1] + '</span>');
					var odometer = $('<span class="odometer">' + matches[2] + '</span>');
					var postOd = $('<span class="post">' + matches[3] + '</span>');
					value.html('');
					value.append(preOd).append(odometer).append(postOd);
					
					var od = new Odometer({ el: odometer[0], value: '0'.padStart(5, 0) });

				}
			});

			var animateStats = function() {
				var scrollTop = $(window).scrollTop();
				var windowHeight = $(window).height();
				$('.odometer-statistic.fade-in:not(.animated)').each(function(i, el) {
					var value = $('.stat-value', el);
					if(value.offset().top <= scrollTop + (windowHeight * .9)) {
						$(el).addClass('animated');
						$('.odometer', value).text(value.data('odometer-final-value'));
					}
				});
			};
			animateStats();
			$(window).on('load scroll', animateStats);

		};


		wptheme.initSocialSharingLinks = function() {

			$(document).on('click', '.social-sharing-links a', function(e) {
				var link = $(this);
				var li = link.parent();
				var ul = li.parent();
				if(li.hasClass('print')) {
					e.preventDefault();
					window.print();
				} else if(li.is('.facebook, .twitter, .linkedin, .google-plus, .pinterest, .houzz')) {
					e.preventDefault();
					var winWidth = 600;
					var winHeight = 400;
					if(li.hasClass('google-plus')) { winWidth = 512; }
					if(li.hasClass('pinterest')) { winWidth = 750; }
					if(li.hasClass('houzz')) { winWidth = 800; winHeight = 460; }
					var winTop = (screen.height / 2) - (winHeight / 2) - (screen.height * 0.1);
					var winLeft = (screen.width / 2) - (winWidth / 2);
					window.open($(this).attr('href'), 'Share Link', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
				}
			});

			$(window).on('load resize orientationchange', function() {
				$('body.single #main-article > .social-sharing-links').each(function(i, el) {
					var top = 0;
					var pageHeader = $('#main-content > .wp-block-crown-blocks-promo.alignfull:first');
					if(pageHeader.length) {
						top = pageHeader.outerHeight();
					}
					$(el).css({ top: top }).addClass('initialized');
				});
			});

		};


		wptheme.initGatedContent = function() {

			var gc = $('#main-gated-content');
			if(gc.length) {
				preview = $('.gated-content-preview', gc);
				var header = $('#main-content > .wp-block-crown-blocks-promo.alignfull:first-child', preview);
				if(header.length) {
					gc.before(header);
				}
			}

		};


		wptheme.initExpandableContent = function() {
			$(document).on('click', '.wp-block-crown-blocks-expandable-content .expandable-content-toggle button', function() {
				var container = $(this).closest('.wp-block-crown-blocks-expandable-content');
				var contents = $('> .inner > .expandable-content-contents', container);
				container.css({ minHeight: container.outerHeight() });
				container.addClass('expanded');
				contents.css({ height: $('> .inner', contents).outerHeight() });
				setTimeout(function() {
					contents.css({ height: 'auto' });
				}, 500);
			});
		};


		wptheme.initCoreValuesBlocks = function() {
			$(document).on('mouseenter', '.wp-block-crown-blocks-core-values-item', function(e) {
				var container = $(this).closest('.wp-block-crown-blocks-core-values');
				container.addClass('value-is-active');
			}).on('mouseleave', '.wp-block-crown-blocks-core-values-item', function(e) {
				var container = $(this).closest('.wp-block-crown-blocks-core-values');
				container.removeClass('value-is-active');
			});
		};


		wptheme.initCaseStudyIndexBlocks = function() {

			$(document).on('change', '.wp-block-crown-blocks-case-study-index form.filters select', function(e) {
				var form = $(this).closest('form');
				form.trigger('submit');
			});

		};


		wptheme.initTeamMemberIndexBlocks = function() {

			$(document).on('change', '.wp-block-crown-blocks-team-member-index form.filters input[type=radio]', function(e) {
				var form = $(this).closest('form');
				form.trigger('submit');
			});

			$(document).on('change', '.wp-block-crown-blocks-team-member-index form.filters select', function(e) {
				var form = $(this).closest('form');
				form.trigger('submit');
			});

		};


		wptheme.smoothScrollToElement = function(element, speed, offset) {
			speed = typeof speed !== 'undefined' ? speed : 1000;
			offset = typeof offset !== 'undefined' ? offset : 0;
			if(element.length > 0) {
				var margin = parseInt(element.css('margin-top'));
				wptheme.smoothScrollToPos(element.offset().top - (margin > 0 ? margin : 0), speed, offset);
			}
		};
		wptheme.smoothScrollToPos = function(y, speed, offset) {
			speed = typeof speed !== 'undefined' ? speed : 1000;
			offset = typeof offset !== 'undefined' ? offset : 0;
			var fixedHeaderOffset = 0;
			$('html, body').stop(true).animate({ scrollTop: y - fixedHeaderOffset + offset }, speed, 'easeOutExpo');
		};


		return wptheme;
		
	})({});

})(jQuery);



function getQueryStringValue(key) {  
	return unescape(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + escape(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));  
}

function setCookie(name, value, days, path) {
	path = typeof path !== 'undefined' ? path : '/';
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "")  + expires + "; path=" + path;
}
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function padNumber(number, length) {
	var str = '' + number;
	while(str.length < length) str = '0' + str;
	return str;
}