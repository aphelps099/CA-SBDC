

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
		$.wptheme.initSocialSharingLinks();
		
		// $.wptheme.initExpandableContentBlocks();
		$.wptheme.initTabbedContentBlocks();
		$.wptheme.initTestimonialSliderBlocks();

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


		wptheme.initGalleries = function() {

			

			// var caseStudyGalleryCallback = function(e) {
			// 	var images = $(this).closest('.entry-photos').find('.image').not('.slick-cloned');
			// 	var galleryLinks = [];
			// 	var index = 0;
			// 	images.each(function(i, el) {
			// 		galleryLinks.push({
			// 			title: $('.caption', el).length ? $('.caption', el).text() : '',
			// 			href: $('img.large', el).attr('src'),
			// 			thumbnail: $('img.thumbnail', el).attr('src')
			// 		});
			// 		if($(el).hasClass('slick-current')) index = i;
			// 	});
			// 	blueimp.Gallery(galleryLinks, {
			// 		index: index
			// 	});
			// };
			// $(document).on('click', '.wp-block-crown-blocks-featured-case-studies article .entry-photos .expand-image', caseStudyGalleryCallback);
			// $(document).on('click', '.wp-block-crown-blocks-case-study-index article .entry-photos .expand-image', caseStudyGalleryCallback);
			// $(document).on('click', '.modal.case-study article .entry-photos .expand-image', caseStudyGalleryCallback);

		};


		wptheme.initSliders = function() {

			

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

		};


		wptheme.initExpandableContentBlocks = function() {
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


		wptheme.initTabbedContentBlocks = function() {
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
					adaptiveHeight: true,
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
		};


		wptheme.initTestimonialSliderBlocks = function() {
			$('.wp-block-crown-blocks-testimonial-slider').each(function(i, el) {
				var slider = $('.testimonial-slider-testimonials > .inner', el);
				if(slider.hasClass('slick-initialized')) return;
				var sliderNavContainer = $('<div class="slick-slider-nav"></div>');
				$('.testimonial-slider-testimonials', el).before(sliderNavContainer);
				slider.children().wrap('<div></div>');
				var slickSettings = {
					mobileFirst: true,
					draggable: false,
					dots: false,
					arrows: true,
					fade: true,
					appendArrows: sliderNavContainer,
					adaptiveHeight: true
				};
				slider.on('init', function(e, slick) {
					var block = slick.$slider.closest('.wp-block-crown-blocks-testimonial-slider');
					var nav = $('.slick-slider-nav', block);
					$('.status', nav).remove();
					if(slick.slideCount > 1) {
						nav.append('<span class="status"><span class="current">' + (slick.currentSlide + 1) + '</span> <span class="of">of</span> <span class="total">' + slick.slideCount + '</span></span>')
					}
				}).on('beforeChange', function(e, slick, currentSlide, nextSlide) {
					var block = slick.$slider.closest('.wp-block-crown-blocks-testimonial-slider');
					var nav = $('.slick-slider-nav', block);
					$('.status', nav).remove();
					if(slick.slideCount > 1) {
						nav.append('<span class="status"><span class="current">' + (nextSlide + 1) + '</span> <span class="of">of</span> <span class="total">' + slick.slideCount + '</span></span>')
					}
				}).slick(slickSettings);
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