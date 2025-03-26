import { __ } from '@wordpress/i18n';
import { useState } from "react";
import ajaxRequest from '../utils/AjaxRequest';
import Header from '../components/Header';
import HeaderMobile from '../components/HeaderMobile';
import { ReactComponent as Facebook } from "../../images/about-page/fb-icon.svg";
import { ReactComponent as Instagram } from "../../images/about-page/insta-icon.svg";
import { ReactComponent as YouTube } from "../../images/about-page/youtube-icon.svg";
import { ReactComponent as Twitter } from "../../images/about-page/twitter-icon.svg";
import { ReactComponent as Checkmark } from "../../images/about-page/checkmark-dark.svg";
import { ReactComponent as Loader } from "../../images/loader.svg";
import WPForms from "../../images/about-page/plugin-wpforms.png";
import MonsterInsights from "../../images/about-page/plugin-mi.png";
import OptIn from "../../images/about-page/plugin-om.png";
import WPSMTP from "../../images/about-page/plugin-smtp.png";
import RafflePress from "../../images/about-page/plugin-rp.png";
import AIOSEO from "../../images/about-page/plugin-seo.png";
import SocialWallBanner from "../../images/about-page/social-wall-graphic.png";

const GetIcon = (slug) => {
	switch (slug) {
	  case "facebook":
		return <Facebook />;
	  case "instagram":
		return <Instagram />;
	  case "youtube":
		return <YouTube />;
	  case "twitter":
		return <Twitter />;
	  case "social_wall":
		return <SocialWallBanner />;
	  case "wpforms":
		return WPForms;
	  case "monsterinsights":
		return MonsterInsights;
	  case "optinmonster":
		return OptIn;
	  case "wp_mail_smtp":
		return WPSMTP;
	  case "rafflepress":
		return RafflePress;
	  case "aioseo":
		return AIOSEO;
	}
};

const PluginBox = ({ plugin, slug, noContainer, image }) => {
	const [isInstalling, setIsInstalling] = useState(false);
	const [isActivating, setIsActivating] = useState(false);
  
	const [isInstalled, setIsInstalled] = useState(plugin.installed);
	const [isActive, setIsActive] = useState(plugin.activated);
	const Container = ({ children }) => {
	  if (noContainer) {
		return children;
	  }
	  return (
		<div className="sb-plugins-box">
		  <div className="icon">
			{!image ? GetIcon(slug) : <img src={image} alt={slug} />}
		  </div>
		  {children}
		</div>
	  );
	};
  
	return (
	  <Container>
		<div className="plugin-box-content">
		  <h4 className="sb-box-title">{plugin.title}</h4>
		  <p className="sb-box-description">{plugin.description}</p>
		  <div className="sb-action-buttons">
			{!isInstalled && (
			  <button
				disabled={!plugin.download_plugin}
				className={`sby-btn sb-btn-add ${
				  !plugin.download_plugin ? "sb-btn-installed" : ""
				}`}
				onClick={() => {
					setIsInstalling(true);
					let data = {
						plugin: plugin.plugin,
						downloadPlugin: plugin.download_plugin,
						installed: plugin.installed
					}
					ajaxRequest('sw_install_plugin', data).then(
						(response) => {
							setIsInstalling(false);
							if ( response.data.success ) {
								setIsInstalled(true);
								setIsActive(true);
							}
						}
					);
				}}
			  >
				{isInstalling && (
				  <span className="loading">
					<Loader />
				  </span>
				)}
				{__("Install", "feeds-for-reviews")}
			  </button>
			)}
			{isInstalled && !isActive && (
			  <button className="sby-btn sb-btn-installed">
				<span>
				  <Checkmark />
				</span>
				{__("Installed", "feeds-for-reviews")}
			  </button>
			)}
			{isInstalled && isActive && (
			  <button className="sby-btn sb-btn-installed">
				<span>
				  <Checkmark />
				</span>
				{__("Activated", "feeds-for-reviews")}
			  </button>
			)}
			{isInstalled && !isActive && (
			  <button
				onClick={() => {
					setIsInstalling(true);
					let data = {
						plugin: plugin.plugin,
						downloadPlugin: plugin.download_plugin,
						installed: plugin.installed
					}
					ajaxRequest('sw_install_plugin', data).then(
						(response) => {
							setIsInstalling(false);
							if ( response.data.success ) {
								setIsInstalled(true);
								setIsActive(true);
							}
						}
					);
				}}
				className="sby-btn sb-btn-activate"
			  >
				{isActivating && (
				  <span className="loading">
					<Loader />
				  </span>
				)}
				{__("Activate", "feeds-for-reviews")}
			  </button>
			)}
		  </div>
		</div>
	  </Container>
	);
};

const AboutUs = () => {
	const { plugins, social_wall, recommendedPlugins } = window.sbsw_admin.pluginInfo;
  
	return (
		<div>
			<Header title="About Us" />
			<HeaderMobile title={__('Social Wall', 'social-wall')} />
			<div className="sby-sb-container sby-no-margin sby-about-us-page">
			<section class="sb-dashboard-heading sb-fs"><h2 class="sb-h2">About Us</h2></section>
				<div className="sby-about-box">
					<div className="sb-team-avatar">
						<img
							className={'sw-h-16'}
							src={require('../../images/team-avatar.png')}
							alt="team-avatar"
						/>
					</div>
					<div className="sb-team-info">
					<div className="sb-team-left">
						<h2>
						{__(
							"At Smash Balloon, we build software that helps you create beautiful responsive social media feeds for your website in minutes.",
							"feeds-for-reviews"
						)}
						</h2>
					</div>
					<div className="sb-team-right">
						<p className='sb-light-text2 sb-text-small'>
						{__(
							"We're on a mission to make it super simple to add social media feeds in WordPress. No more complicated setup steps, ugly iframe widgets, or negative page speed scores.",
							"feeds-for-reviews"
						)}
						</p>
						<p className='sb-light-text2 sb-text-small'>
						{__(
							"Our plugins aren't just easy to use, but completely customizable, reliable, and fast! Which is why over 1.6 million awesome users, just like you, choose to use them on their site.",
							"feeds-for-reviews"
						)}
						</p>
					</div>
					</div>
				</div>

				<div className="sby-section-second-header">
					<h3>
					{__("Our Other Social Media Feed Plugins", "feeds-for-reviews")}
					</h3>
					<p>
					{__(
						"We’re more than just an Social Wall plugin! Check out our other plugins and add more content to your site.",
						"feeds-for-reviews"
					)}
					</p>
				</div>

				<div className="sby-plugins-boxes-container">
					{Object.keys(plugins).map((key) => {
						const plugin = plugins[key];
						return <PluginBox slug={key} plugin={plugin} />;
					})}

					<div className="sb-plugins-box sby-social-wall-plugin-box">
						<span className="sb-box-bg-image">
							<img src={SocialWallBanner} />
						</span>
						<PluginBox
							noContainer={true}
							className="plugin-box-content"
							plugin={social_wall}
						/>
					</div>
				</div>

				<div className="sby-section-second-header">
					<h3>{__("Plugins we recommend", "feeds-for-reviews")}</h3>
				</div>

				<div className="sby-plugins-boxes-container sb-recommended-plugins">
					{Object.keys(recommendedPlugins).map((key) => {
						const plugin = recommendedPlugins[key];
						return (
							<PluginBox
								image={GetIcon(key)}
								slug={key}
								plugin={plugin}
							/>
						);
					})}
				</div>
			</div>
			{/* <StickyWidget /> */}
		</div>
	);
};

export default AboutUs;
