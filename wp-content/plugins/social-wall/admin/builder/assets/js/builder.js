var sbswBuilder;


sbswBuilder = new Vue({
	el: '#sbsw-builder-app',
	http: {
        emulateJSON: true,
        emulateHTTP: true
    },
    data: {
		nonce : sbsw_builder.nonce,
        genericText	: sbsw_builder.genericText,
        svgIcons	: sbsw_builder.svgIcons,
		ajaxHandler : sbsw_builder.ajax_handler,
		builderUrl : sbsw_builder.builder_url,
		customizerSidebarBuilder : sbsw_builder.customizerSidebarBuilder,
        appLoaded: false,
		loadingBar : true,
		customizerFeedDataInitial : null,
		customizerFeedData 	: sbsw_builder.customizerFeedData,
		viewsActive : {
			//Screens where the footer widget is disabled
			footerDiabledScreens : [
				'welcome',
				'selectFeed'
			],
			footerWidget : false,

			// welcome, selectFeed
			pageScreen : 'welcome',

			// feedsType, selectSource, feedsTypeGetProcess
			selectedFeedSection : 'feedsType',

			sourcePopup : false,
			feedtypesPopup : false,
			feedtypesCustomizerPopup : false,
			feedtemplatesPopup : false,
			sourcesListPopup : false,
			connectAccountPopup : false,
			// step_1 [Add New Source] , step_2 [Connect to a user pages/groups], step_3 [Add Manually]
			sourcePopupScreen : 'redirect_1',

			connectAccountStep : 'step_1',

			// creation or customizer
			sourcePopupType : 'creation',
			extensionsPopupElement : false,
			feedTypeElement : null,
			feedTemplateElement : null,
			instanceFeedActive : null,
			clipboardCopiedNotif : false,
			legacyFeedsShown : false,
			editName : false,
			embedPopup : false,
			embedPopupScreen : 'step_1',
			embedPopupSelectedPage : null,

			moderationMode : false,
			// plugin install popup
			installPluginPopup : false,
			installPluginModal: 'facebook'
        },
		customizerScreens : {
			activeTab 		: 'customize',
			printedType 	: {},
			printedTemplate : {},
			activeSection 	: null,
			previewScreen 	: 'desktop',
			sourceExpanded 	: null,
			sourcesChoosed 	: [],
			inputNameWidth 	: '0px',
			activeSectionData 	: null,
			parentActiveSection : null, //For nested Setions
			parentActiveSectionData : null, //For nested Setions
			activeColorPicker : null,
			popupBackButton : ['feedTemplates']
		},
    },
    created: function() {
        var self = this;
		self.appLoaded = true;
		self.loadingBar = false;
    },

    methods: {
		/**
		 * Ajax Post Action
		 *
		 * @since 2.0
		 */
		ajaxPost : function(data, callback){
			var self = this;
			data['nonce'] = self.nonce;
			self.$http.post(self.ajaxHandler,data).then(callback);
		},

		/**
		 * Show & Hide View
		 *
		 * @since 2.0
		 */
		activateView : function(viewName, sourcePopupType = 'creation', ajaxAction = false) {
			var self = this;
			self.viewsActive[viewName] = (self.viewsActive[viewName] == false ) ? true : false;

            if(viewName == 'editName'){
                document.getElementById("sbsw-csz-hd-input").focus();
            }

			sbswBuilder.$forceUpdate();
        },

		updateInputWidth : function(){
			this.customizerScreens.inputNameWidth = ((document.getElementById("sbsw-csz-hd-input").value.length + 6) * 8) + 'px';
		},

		/**
		 * Ajax Action : Save Feed Settings
		 *
		 * @since 2.0
		 */
		saveFeedSettings : function(){
			var self = this,
				sources = [],
				updateFeedData = {
					action : 'sbsw_builder_update',
					update_feed	: 'true',
					feed_id : self.customizerFeedData.feed_info.id,
					feed_name : self.customizerFeedData.feed_info.feed_name,
					settings : self.customizerFeedData.settings
				};
			self.loadingBar = true;
			self.ajaxPost(updateFeedData, function(_ref){
				var data = _ref.data;
                self.loadingBar = false;
				// if(data && data.success === true){
				// 	self.processNotification('feedSaved');
				// 	self.customizerFeedDataInitial = self.customizerFeedData;
				// }else{
				// 	self.processNotification('feedSavedError');
				// }
			});
			sbswBuilder.$forceUpdate();
		},


		/**
		 * Switch customizer tabs
		 * 
		 * @since 2.0
		 */
		switchCustomizerTab : function(tabId){
			var self = this;
			self.customizerScreens.activeTab = tabId;
			self.customizerScreens.activeSection = null;
			self.customizerScreens.activeSectionData = null;
			self.highLightedSection = 'all';
			self.dummyLightBoxScreen = false;
			sbswBuilder.$forceUpdate();
		},

		/**
		 * Switch the customizer section
		 * 
		 * @since 2.0
		 */
		switchCustomizerSection : function(sectionId, section, isNested = false, isBackElements){
			var self = this;
			self.customizerScreens.parentActiveSection = null;
			self.customizerScreens.parentActiveSectionData = null;
			if(isNested){
				self.customizerScreens.parentActiveSection = self.customizerScreens.activeSection;
				self.customizerScreens.parentActiveSectionData = self.customizerScreens.activeSectionData;
			}
			self.customizerScreens.activeSection = sectionId;
			self.customizerScreens.activeSectionData = section;
			sbswBuilder.$forceUpdate();
		},

		checkExtensionActive : function(extension){
			var self = this;
			return self.activeExtensions[extension];
		},

		/**
		 * Show Control
		 *
		 * @since 2.0
		*/
		isControlShown : function( control ){
			var self = this;
			if( control.checkViewDisabled != undefined ){
				return !self.viewsActive[control.checkViewDisabled];
			}
			if( control.checkView != undefined ){
				return !self.viewsActive[control.checkView];
			}

			if(control.checkExtension != undefined && control.checkExtension != false && !self.checkExtensionActive(control.checkExtension)){
				return self.checkExtensionActive(control.checkExtension);
			}

			if(control.conditionDimmed != undefined && self.checkControlCondition(control.conditionDimmed) )
				return self.checkControlCondition(control.conditionDimmed);
			if(control.overrideColorCondition != undefined){
				return self.checkControlOverrideColor( control.overrideColorCondition );
			}

			return ( control.conditionHide != undefined && control.condition != undefined || control.checkExtension != undefined )
				? self.checkControlCondition(control.condition, control.checkExtension)
				: true;
		},

		/**
		 * Check Control Condition
		 *
		 * @since 2.0
		*/
		checkControlCondition : function(conditionsArray = [], checkExtensionActive = false, checkExtensionActiveDimmed = false){
			var self = this,
			isConditionTrue = 0;
			Object.keys(conditionsArray).map(function(condition, index){
				if(conditionsArray[condition].indexOf(self.customizerFeedData.settings[condition]) !== -1)
					isConditionTrue += 1
			});
			var extensionCondition = checkExtensionActive != undefined && checkExtensionActive != false ? self.checkExtensionActive(checkExtensionActive) : true,
				extensionCondition = checkExtensionActiveDimmed != undefined && checkExtensionActiveDimmed != false && !self.checkExtensionActive(checkExtensionActiveDimmed) ? false : extensionCondition;

			return (isConditionTrue == Object.keys(conditionsArray).length) ? ( extensionCondition ) : false;
		},

		/**
		 * Check Color Override Condition
		 *
		 * @since 2.0
		*/
		checkControlOverrideColor : function(overrideConditionsArray = []){
			var self = this,
			isConditionTrue = 0;
			overrideConditionsArray.map(function(condition, index){
				if(self.checkNotEmpty(self.customizerFeedData.settings[condition]) && self.customizerFeedData.settings[condition].replace(/ /gi,'') != '#'){
					isConditionTrue += 1
				}
			});
			return (isConditionTrue >= 1) ? true : false;
		},

		/**
		 * Check if Control Is active
		 *
		 * @since 2.0
		 *
		 * @return boolean
		 */
		 checkActiveControl : function(controlId, enabled){
			var self = this;
			return self.customizerFeedData.settings[controlId] === enabled || self.customizerFeedData.settings[controlId] === enabled.toString();

		},
    }
});