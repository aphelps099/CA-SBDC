import { __ } from '@wordpress/i18n';

class SbUtils  {

    /**
     *   Ajax Post Action
    */
     static ajaxPost = ( ajaxHandler, data, callback, topLoader = null, editorNotification = null, notificationsContent = null ) => {

        //Set Form Data body for the Ajax call
        var formData = new FormData();
        for ( var key in data ) {
            formData.append(key, data[key]);
        }
        formData.append( 'nonce', window?.sbsw_admin?.nonce );

        topLoader !== null && topLoader.setLoader( true ); //Show top Bar header loader

        fetch( ajaxHandler, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(response => response.json())
        .then( ( data ) => {

            callback( data ); //CallBack Function
            topLoader !== null && topLoader.setLoader( false ); //Hide top Bar header loader

            //Show Success Notification if Set
            ( editorNotification !== null && notificationsContent?.success )
            && SbUtils.applyNotification( notificationsContent.success , editorNotification );


        } ).catch(error => {
            //Show Error Notification if Set
            ( editorNotification !== null && notificationsContent?.error )
            && SbUtils.applyNotification( notificationsContent.error , editorNotification );

        })
	}

    /**
     *   Generates : CSS Class Names
    */
     static getClassNames = ( props, slug, classesList ) => {
        let classes = [ slug, props.customClass ];
        classesList.forEach(function( classItem ) {
            if( typeof classItem === 'object' ){
                classes.push(slug + '-' + (props[Object.keys(classItem)?.[0]] === undefined ? Object.values(classItem)?.[0] : props[Object.keys(classItem)?.[0]]));
            }
            if( props[classItem] !== undefined ){
                classes.push(slug + '-' + props[classItem]);
            }
        });
        return classes.join(' ');
    }

    /**
     *  Generates : Element React Actions
    */
     static getElementActions = ( props, actionsList, ref ) => {
        let attrs = [];
        actionsList.forEach(function( actItem ) {
            if( props[actItem] !== undefined ){
                attrs[`${actItem}`] = props[actItem];
            }
        });
        return attrs;
    }

    /**
     *   Find Element By ID
    */
     static findElementById = ( objects, searchBy, value ) => {
        let [ objkey, obj ] = Object.entries(objects).find(([key, ob]) => ob[searchBy] === value)
        return obj;
    }

    /*
        Get Feed Styling
    */
    static getFeedStyling = ( feedSettings, customizerData ) => {
        let styles = '';
        customizerData.forEach( ( tab ) => {
            Object.values( tab.sections ).map( ( section ) => {
                styles += SbUtils.getFeedStylingRecursive(section, feedSettings);
            })
        })
        return styles;
    }


    /*
        Get Feed Styling Recursive
    */
    static getFeedStylingRecursive = ( element, feedSettings) => {
        let styles = '';
        element?.controls.forEach(function ( el ) {
            let controlStyle = SbUtils.getSingleControlStyle( el, feedSettings );
            styles += controlStyle !== false ? controlStyle : '';

            //Nested List Elements
            if( el?.controls !== undefined )
                styles += SbUtils.getFeedStylingRecursive(el, feedSettings)
        });
        return styles;
    }


    /*
        Get Single Control Style
    */
    static getSingleControlStyle = ( control, feedSettings ) => {
        let applyStyle = SbUtils.checkControlCondition( control, feedSettings );
        if( control?.style === undefined || applyStyle === false)
            return false

        let styleString = '';
        Object.entries( control.style ).map( ( css ) => {
            let cssValue = SbUtils.createCssStyle( control.type, feedSettings[control?.id] );
            styleString += (cssValue !== null && cssValue !== undefined) ? `${css[0]}{${css[1].replace("{{value}}", cssValue)}}` : '';
        })

        return styleString;
    }


    /**
     *  Check Control Conditions Hide/Show/Dimmed
    */
     static checkControlCondition = ( element, feedSettings ) => {
        if ( element?.condition === undefined ){
            return true;
        } else {
            let isConditionTrue = 0;
			Object.keys( element.condition ).map( (condition, index) => {
				if(element.condition[ condition ].indexOf( feedSettings[ condition ] ) !== -1)
					isConditionTrue += 1
			});
            let showElement = isConditionTrue === Object.keys(element.condition).length;
            return showElement === false && element?.conditionDimmed === true ? 'dimmed' : showElement;
        }
    }

    /*
     *   Create Style
     *   This Will dump the CSS style depending on the Type
    */
    static createCssStyle = ( type, value ) => {

        switch (type) {

            // Create Box Shadow Styling
            case 'boxshadow':
                if( value?.enabled === undefined || value?.enabled === false )
                    return null;
                return `${SbUtils.addCssUnit( value.x )} ${SbUtils.addCssUnit( value.y )} ${SbUtils.addCssUnit( value.blur )} ${SbUtils.addCssUnit( value.spread )} ${value.color}`;

            // Create Box Radius Styling
            case 'borderradius':
                if( value?.enabled === undefined || value?.enabled === false )
                    return null;
                return value.radius + 'px';

             // Create Stroke Styling
            case 'stroke':
                if( value?.enabled === undefined || value?.enabled === false )
                    return null;
                return value.thickness + 'px solid '+ value.color;

            // Create Distance Styling : Margins/Paddings
            case 'distance':
                let sidesList = [ 'top', 'right', 'bottom', 'left'],
                    distances = '';
                sidesList.forEach( side => {
                    distances += SbUtils.checkNotEmpty( value[side] ) ? `${value[side]}px ` : '0px ';
                });
                return distances;

            // Create Font Styling : Family/Weight/Size/height
            case 'font':
                let fontElements = [ 'family', 'weight', 'size', 'height'],
                    fonts = '';
                fontElements.forEach( f => {
                    let includeFont = SbUtils.checkNotEmpty( value[f] ) && value[f] !== 'inherit' ;
                    fonts += includeFont ? `${f === 'height' ? 'line' : 'font' }-${f}:${value[f]}${f === 'size' ? 'px' : '' };` : '';
                });
                return fonts;

            default:
                return value;
        }
    }

    /*
     *   Create Object for Highlighted Sections
     *
    */
    static getHighlightedSection = ( customizerData ) => {
        let highlightedSection = {};
        customizerData.forEach( ( tab ) => {
            Object.values( tab.sections ).map( ( section ) => {
                if( section?.highlight !== undefined ){
                    highlightedSection[ section?.highlight ] = section;
                }
                highlightedSection = {
                    ...highlightedSection,
                    ...SbUtils.getHighlightedSectionRecursive( section )
                }
            })
        })

        return highlightedSection;
    }

     /*
     *   Recursive function to add more Highlighted Sections
     *
    */
     static getHighlightedSectionRecursive = ( element ) => {
        let highlightedSection = {};
        element?.controls.forEach(function ( el ) {
            if( el?.highlight !== undefined ){
                highlightedSection[ el?.highlight ] = el;
            }
            //Nested List Elements
            if( el?.controls !== undefined ){
                highlightedSection = {
                    ...highlightedSection,
                    ...SbUtils.getHighlightedSectionRecursive( el )
                }
            }
        });
        return highlightedSection;
    }

    /**
     *   Prints SVG Icon
    */
     static printIcon = ( iconName, customClass = false, key = false, iconSize = undefined ) => {
        const iconStyle = (iconSize !== undefined && { width : iconSize + 'px'  } ) || null;

        return window?.sbsw_admin?.iconsList[iconName] ?
               <span
                key={key !== false ? key : key}
                className={ ( customClass !== false ? customClass : '' ) + ( iconSize !== undefined ? ' sb-custom-icon-size' : '') }
                style={ iconStyle }
                dangerouslySetInnerHTML={{__html: window.sbsw_admin.iconsList[iconName] }}></span>
                : '';
    }


    /**
     *   Generates : Element HTML Attibutes
    */
     static getElementAttributes = ( props, attributesList, ref ) => {
        let attrs = [];
        attributesList.forEach(function( attrItem ) {
            if( typeof attrItem === 'object' ){
                attrs[`data-${Object.keys(attrItem)?.[0]}`] = props[Object.keys(attrItem)?.[0]] === undefined ? Object.values(attrItem)?.[0] : props[Object.keys(attrItem)?.[0]];
            }
            else if( props[attrItem] !== undefined ){
                if( attrItem === 'disabled' ){
                    attrs[`${attrItem}`] = props[attrItem];
                }else{
                    attrs[`data-${attrItem}`] = props[attrItem];
                }
            }
        });
        return attrs;
    }

    /*
     *   Stringify JSON Objects
     *
    */
    static stringify = ( obj ) => {
        return JSON.stringify(obj, (key, value) => {
            if ( ! isNaN( value ) && ! Array.isArray( value ) && typeof value !== "boolean" && typeof value !== "string" ){
                value = Number(value)
            }
            return value
        })
    }

    /**
     *   Check if Element is Empty/Undefined/Null
    */
     static checkNotEmpty = ( value ) => {
        return value !== undefined && value !== null &&  value?.toString()?.replace(/ /gi,'') !== '';
    }

    //Save Feed Data
    static saveFeedData = ( editorFeedData, editorFeedStyling, editorFeedSettings, sbCustomizer, editorTopLoader, editorNotification,  exit = false, isSettingRef = false, getPosts = false ) => {
        const formData = {
            action : 'sbsw_builder_update',
            update_feed	: true,
            feed_id : editorFeedData.feedData.feed_info.id,
            feed_name : editorFeedData.feedData.feed_info.feed_name,
            feed_style : editorFeedStyling,
            settings : SbUtils.stringify( isSettingRef ? editorFeedSettings.current : editorFeedSettings.feedSettings ),
            get_posts : getPosts
        },
        notificationsContent = {
            success : {
                icon : 'success',
                text : __('Feed saved succesfully', 'sbc-customizer')
            }
        }

        SbUtils.ajaxPost(
            sbCustomizer.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if( getPosts === true ){
                    if( data?.posts ){
                        editorFeedData.setFeedData(  {
                            ...editorFeedData.feedData,
                            posts : data?.posts
                        } );
                    }
                }
                if(exit === true){
                    window.location.href = sbCustomizer.builderUrl;
                }
            },
            editorTopLoader,
            editorNotification,
            notificationsContent
        )
    }


    // Clear Feed Cache
    static clearFeedCache = ( editorFeedData, editorFeedStyling, editorFeedSettings, sbCustomizer, editorTopLoader, editorNotification,  exit = false, isSettingRef = false ) => {
        const formData = {
            action : 'sbsw_clear_feed_cache',
            feed_id : editorFeedData.feedData.feed_info.id,
            feed_name : editorFeedData.feedData.feed_info.feed_name,
            feed_style : editorFeedStyling,
            settings : SbUtils.stringify( isSettingRef ? editorFeedSettings.current : editorFeedSettings.feedSettings ),
        },
        notificationsContent = {
            success : {
                icon : 'success',
                text : __('Cache cleared succesfully', 'sbc-customizer')
            }
        }

        SbUtils.ajaxPost(
            sbCustomizer.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if( data?.data?.feedData?.posts ){
                    editorFeedData.setFeedData(  {
                        ...editorFeedData.feedData,
                        posts : data?.data?.feedData?.posts
                    } );
                }
                if(exit === true){
                    window.location.href = sbCustomizer.builderUrl;
                }
            },
            editorTopLoader,
            editorNotification,
            notificationsContent
        )
    }

    static clearExistingCache = () => {
        const formData = {
            action : 'sbsw_clear_feed_cache'
        };

        SbUtils.ajaxPost(
            window.sbsw_admin.ajax_url,
            formData,
            ( data ) => { //Call Back Function
            }            
        )
    }

    static removeFeedSource = (editorFeedData, editorTopLoader, editorNotification, sbCustomizer, deletePluginData, setDeleteSource) => {
     
        const formData = {
            action : 'sbsw_remove_wall_source',
            update_feed	: true,
            feed_id : editorFeedData.feedData.feed_info.id,
            feed_name : editorFeedData.feedData.feed_info.feed_name,
            feed_plugin : deletePluginData[0],
            feed_plugin_id : deletePluginData[1]['id']
        },
        notificationsContent = {
            success : {
                icon : 'success',
                text : __('Source removed succesfully', 'sbc-customizer')
            }
        }

        SbUtils.ajaxPost(
            sbCustomizer.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if ( data.success ) {
                    editorFeedData.setFeedData(data.data.feedData)
                    setDeleteSource(false)
                }
            },
            editorTopLoader,
            editorNotification,
            notificationsContent
        )
    }

    /**
     * Update feed source from the customizer
     * 
     * @since 2.0
     */
    static updateFeedSource = (editorFeedData, editorTopLoader, editorNotification, sbCustomizer, updateSourceFeed, setAddSourceToWall, sourcePlugin) => {
        let updatedSourceFeed = JSON.stringify(updateSourceFeed[sourcePlugin.id]);
        if ( updatedSourceFeed == undefined ) return;
        const formData = {
            action : 'sbsw_update_wall_source',
            update_feed	: true,
            feed_id : editorFeedData.feedData.feed_info.id,
            wall_plugin : updatedSourceFeed,
        },
        notificationsContent = {
            success : {
                icon : 'success',
                text : __('Source addedd succesfully', 'sbc-customizer')
            }
        }

        SbUtils.ajaxPost(
            sbCustomizer.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if ( data.success ) {
                    editorFeedData.setFeedData(data.data.feedData)
                    setAddSourceToWall(false)
                }
            },
            editorTopLoader,
            editorNotification,
            notificationsContent
        )
    }

    static exportStringToFile = (
        content,
        filename,
        type = "application/json"
      ) => {
        const element = document.createElement("a");
        const file = new Blob([content], {
          type,
        });
        element.href = URL.createObjectURL(file);
        element.download = filename;
        document.body.appendChild(element);
        element.click();
    };

    /**
     * Install plugin from the Sources control
     * 
     * @since 2.0
     */
    static installPlugin = (editorFeedData, editorTopLoader, editorNotification, sbCustomizer, addSourceData, setPluginInstallModal, pluginSuccess, setPluginSuccess, setLoading) => {
        const formData = {
            action : 'sw_install_plugin',
            plugin: addSourceData.plugin,
            downloadPlugin: addSourceData.download_plugin,
            installed: addSourceData.isPluginInstalled
        };

        SbUtils.ajaxPost(
            sbCustomizer.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if ( data.success ) {
                    setPluginSuccess(data);
                    setLoading(false)
                    // editorFeedData.setFeedData(data.data.feedData)
                }
            },
            editorTopLoader,
        )
    }


    /**
     * Feed fly preview
     * 
     * @since 2.0
     */
    static  feedFlyPreview = ( editorFeedData, editorTopLoader, editorNotification, sbCustomizer, settingsRef ) => {
        const formData = {
            action : 'sbsw_fly_preview',
            feedID : editorFeedData?.feedData?.feed_info?.id,
            feedName : editorFeedData?.feedData?.feed_info?.feed_name,
            previewSettings : SbUtils.stringify( settingsRef.current ),
            feedName : editorFeedData?.feedData?.feed_info?.feed_name,
        },
        notificationsContent = {
            success : {
                icon : 'success',
                text : __('Preview updated successfully', 'sb-customizer' )
            }
        }
        
        SbUtils.ajaxPost(
            sbCustomizer.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if( data?.data?.feedData?.posts ){
                    editorFeedData.setFeedData(  {
                        ...editorFeedData.feedData,
                        posts : data?.data?.feedData?.posts
                    } );
                }
            },
            editorTopLoader,
            editorNotification,
            notificationsContent
        )
    }


    /**
     *   Notification Process
     *
    */
    static applyNotification = ( notification, editorNotification ) => {
        editorNotification.setNotification({
            active : true,
            type : notification?.type,
            icon : notification?.icon,
            text : notification?.text
        });
        setTimeout(function(){
			editorNotification.setNotification({
                active : false,
                type : null,
                icon : null,
                text : null
            });
	    }, 3000);
    }
    
    /*
     *   Creates a tooltip & Display it when hover
     *
    */
    static copyToClipBoard = ( text ) => {
        const el = document.createElement('textarea');
		el.className = 'sb-copy-clpboard';
		el.value = text;
		document.body.appendChild(el);
	    el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
    }

    /*
     *   Transform Date & Print Date
    */
    static printDate = ( postDate, feedSettings ) => {
        let originalDate 	= postDate,
			dateOffset 		= new Date(),
			offsetTimezone 	= dateOffset.getTimezoneOffset(),
			periods = [
			    __('second', 'sb-customizer'),
				__('minute', 'sb-customizer'),
                __('hour', 'sb-customizer'),
                __('day', 'sb-customizer'),
                __('week', 'sb-customizer'),
                __('month', 'sb-customizer'),
                __('year', 'sb-customizer')
				],
			periodsPlural = [
				__('seconds', 'sb-customizer'),
				__('minutes', 'sb-customizer'),
				__('hours', 'sb-customizer'),
				__('days', 'sb-customizer'),
				__('weeks', 'sb-customizer'),
				__('months', 'sb-customizer'),
				__('years', 'sb-customizer')
			],
			lengths		 = ["60","60","24","7","4.35","12","10"],
            now 		= dateOffset.getTime()  / 1000,
            newTime 	= originalDate + offsetTimezone,
            printDate 	= '',
            dateFortmat = feedSettings.dateformat,
            agoText 	= __('ago', 'sb-customizer'),
            difference 	= null,
            formatsChoices = {
                '2' : 'F jS, g:i a',
                '3' : 'F jS',
                '4' : 'D F jS',
                '5' : 'l F jS',
                '6' : 'D M jS, Y',
                '7' : 'l F jS, Y',
                '8' : 'l F jS, Y - g:i a',
                '9' : "l M jS, 'y",
                '10' : 'm.d.y',
                '11' : 'm/d/y',
                '12' : 'd.m.y',
                '13' : 'd/m/y',
                '14' : 'd-m-Y, G:i',
                '15' : 'jS F Y, G:i',
                '16' : 'd M Y, G:i',
                '17' : 'l jS F Y, G:i',
                '18' : 'm.d.y - G:i',
                '19' : 'd.m.y - G:i'
            };
			if( formatsChoices[dateFortmat] !== undefined ){
			    printDate = window.date_i18n( formatsChoices[dateFortmat], newTime );
			} else if(dateFortmat === 'custom') {
			    let dateCustom = feedSettings.customdate;
				printDate = window.date_i18n( dateCustom , newTime );
			}
			else{
			    if( now > originalDate ) {
	                difference = now - originalDate;
				}else{
	                difference = originalDate - now;
				}
				for(var j = 0; difference >= lengths[j] && j < lengths.length-1; j++) {
	              	difference /= lengths[j];
	            }
	            difference = Math.round(difference);
	            if(difference !== 1) {
		            periods[j] = periodsPlural[j];
		        }
				printDate = difference + " " + periods[j] + " "+ agoText;
			}
		return printDate;
	}

    static getPlugin = (post) => {
        let pluginName = '';
        if ( 'embed_link' in post && 'open_id' in post ) {
            pluginName = 'tiktok';
        } else if ( 'message' in post ||
            'full_picture' in post || 
            'privacy' in post || 
            'cover' in post || 
            'owner' in post || 
            'picture' in post || 
            'embed_html' in post || 
            'cover_photo' in post
        ) {
            pluginName = 'facebook'
        } else if ( 'snippet' in post ) {
            pluginName = 'youtube'
        } else if ( 'permalink' in post ) {
            pluginName = 'instagram'
        } else if ( 'id_str' in post ) {
            pluginName = 'twitter'
        }
        
        return pluginName;
    }

}

export default SbUtils;