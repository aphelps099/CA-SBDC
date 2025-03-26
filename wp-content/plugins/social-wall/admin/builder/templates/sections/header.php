<div class="sbsw-header sbsw-csz-header sbsw-fs">
	<div class="sbsw-csz-header-insider">
		<button class="sbsw-btn sbsw-btn-grey sb-button-standard ctf-small-chevron" data-icon="left" @click.prevent.default="window.location = builderUrl">
            <svg width="6" height="8" viewBox="0 0 6 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.27203 0.94L4.33203 0L0.332031 4L4.33203 8L5.27203 7.06L2.2187 4L5.27203 0.94Z" fill="#141B38"/>
            </svg>
            <span>{{genericText.backAllFeeds}}</span>
		</button>
		<div class="sbsw-csz-hd-name" :data-edit="viewsActive.editName">
			<input id="sbsw-csz-hd-input" v-model="customizerFeedData.feed_info.feed_name" type="text" :style="'width:' + customizerScreens.inputNameWidth + ';'" :onfocus="updateInputWidth()" :onkeypress="updateInputWidth()">
			<span class="sb-bold sb-standard-p" v-if="!viewsActive['editName']">{{customizerFeedData.feed_info.feed_name}}</span>
			<button class="sbsw-csz-name-ed-btn" v-html="viewsActive.editName ? svgIcons['checkmarklarge'] : svgIcons['edit']" @click.prevent.default="activateView('editName')"></button>
		</div>

		<div class="sbsw-csz-hd-actions">
			<a class="sbsw-btn sbsw-btn-grey sb-button-standard" target="_blank">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.16797 14.0001H9.83464V12.3334H8.16797V14.0001ZM9.0013 0.666748C4.4013 0.666748 0.667969 4.40008 0.667969 9.00008C0.667969 13.6001 4.4013 17.3334 9.0013 17.3334C13.6013 17.3334 17.3346 13.6001 17.3346 9.00008C17.3346 4.40008 13.6013 0.666748 9.0013 0.666748ZM9.0013 15.6667C5.3263 15.6667 2.33464 12.6751 2.33464 9.00008C2.33464 5.32508 5.3263 2.33341 9.0013 2.33341C12.6763 2.33341 15.668 5.32508 15.668 9.00008C15.668 12.6751 12.6763 15.6667 9.0013 15.6667ZM9.0013 4.00008C7.15964 4.00008 5.66797 5.49175 5.66797 7.33342H7.33464C7.33464 6.41675 8.08464 5.66675 9.0013 5.66675C9.91797 5.66675 10.668 6.41675 10.668 7.33342C10.668 9.00008 8.16797 8.79175 8.16797 11.5001H9.83464C9.83464 9.62508 12.3346 9.41675 12.3346 7.33342C12.3346 5.49175 10.843 4.00008 9.0013 4.00008Z" fill="#141B38"/>
                    </svg>
				<span>{{genericText.help}}</span>
			</a>
			<button class="sbsw-btn sbsw-csz-btn-embd sbsw-btn-dark sb-button-standard" @click.prevent.default="activateView('embedPopup', null, true)">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 6L12.2287 9.77133L11.286 8.82867L14.1147 6L11.286 3.17133L12.2287 2.22867L16 6ZM1.88533 6L4.714 8.82867L3.77133 9.77133L0 6L3.77133 2.22867L4.71333 3.17133L1.88533 6ZM6.52533 12H5.10667L9.47467 0H10.8933L6.52533 12Z" fill="white"/>
                </svg>
				<span>Embed</span>
			</button>
			<button class="sbsw-btn sbsw-csz-btn-save sbsw-btn-orange sb-button-standard" @click.prevent.default="saveFeedSettings()">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.08058 8.36133L14.0355 0.406383L15.8033 2.17415L6.08058 11.8969L0.777281 6.59357L2.54505 4.8258L6.08058 8.36133Z" fill="white"/>
                </svg>
				<span>Save</span>
			</button>
		</div>
	</div>
	<div class="sb-loadingbar-ctn" v-if="loadingBar"></div>
</div>
