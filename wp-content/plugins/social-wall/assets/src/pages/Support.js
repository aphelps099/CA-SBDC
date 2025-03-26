import { __ } from '@wordpress/i18n';
import { useDispatch, useState, useMemo } from 'react'
import HTMLReactParser from "html-react-parser";
import Header from '../components/Header';
import HeaderMobile from '../components/HeaderMobile';
import SbUtils from '../utils/SbUtils';
import Notification from '../components/Global/Notification';
import { ReactComponent as Search } from "../../images/support-page/search.svg";
import { ReactComponent as RightAngle } from "../../images/support-page/rightAngle.svg";
import { ReactComponent as DownAngle } from "../../images/support-page/downAngle.svg";
import { ReactComponent as Rocket } from "../../images/support-page/rocket-icon.svg";
import { ReactComponent as Book } from "../../images/support-page/book-icon.svg";
import { ReactComponent as Plus } from "../../images/support-page/save-plus-icon.svg";
import { ReactComponent as Forum } from "../../images/support-page/forum.svg";
import { ReactComponent as Copy } from "../../images/support-page/copy.svg";
import { ReactComponent as ExportIcon } from "../../images/support-page/export.svg";
import SupportTeam from "../../images/support-page/support-team.jpeg";

const SupportBlock = ({
	icon,
	title,
	description,
	links,
	buttonTitle,
	buttonLink,
  }) => {
	return (
		<div className="sw-support-block">
			<div className="sb-block-header">{icon}</div>
			<div className="sb-support-item-info">
				<h3>{title}</h3>
				<p>{description}</p>
			</div>
			<div className="sb-support-item-links">
				<ul>
					{links.map((link) => {
					return (
						<li>
						<a href={link.link}>
							{link.title}
						</a>
						</li>
					);
					})}
				</ul>
			</div>
			<a href={buttonLink} target="_blank" className='sb-btn sb-btn-medium sb-btn-secondary'>
				{buttonTitle}
				<span className="sb-btn-icon">
				<RightAngle />
				</span>
			</a>
		</div>
	);
};


const ContactSection = () => {
	return (
		<div className="sw-support-contact-block clearfix">
			<div className="sw-contact-block-left">
				<div className="sw-cb-icon">
					<span>
						<Forum />
					</span>
				</div>
				<div className="sw-cb-content">
					<h3> {__("Need more support? We’re here to help.", "social-wall")} </h3>
					<a href="https://smashballoon.com/support/?license_type=pro&version=2.0&utm_campaign=social-wall&utm_source=support&utm_medium=submit-ticket" target="_blank" className="sb-btn sb-btn-medium"> {__("Submit a Support Ticket", "social-wall")} <span>
							<RightAngle />
						</span>
					</a>
				</div>
			</div>
			<div className="sw-contact-block-right">
				<div>
					<img src={SupportTeam} />
					{/* <SupportTeam/> */}
				</div>
				<p> {__( "Our fast and friendly support team is always happy to help!", "social-wall" )} </p>
			</div>
		</div>
	);
};

const SystemInfo = ({editorNotification}) => {
	const [expanded, setExpanded] = useState(false);
	const [selectedFeed, setSelectedFeed] = useState(null);
	const feedList = useState(window.sbsw_admin.swFeeds);

	const onExport = () => {
		if (selectedFeed !== "-1" && selectedFeed !== null) {
			SbUtils.exportStringToFile(
				feedList[0][selectedFeed].settings,
				`sby-feed-${feedList[0][selectedFeed].id}.json`
			);
		}
	};

	let notificationsContent = {
		type : 'success',
		icon : 'success',
		text : __('System info copied succesfully', 'sbc-customizer')
	}

	return (
		<div className="sw-system-info-section">
			<div className="sw-system-header">
				<h4>{__("System Info", "social-wall")}</h4>
				<button
				className="sb-btn sb-btn-medium"
				onClick={() => {
					SbUtils.copyToClipBoard(window.sbsw_admin.system_info);

					SbUtils.applyNotification(notificationsContent, editorNotification);
				}}
				>
				<span>
					<Copy />
				</span>
				<span>{__("Copy", "social-wall")}</span>
				</button>
			</div>
			<div className="sw-system-info">
				<div
					id="system_info"
					className={`sb-system-info-content ${expanded ? "expanded" : "collapsed"}`}
				>
				{HTMLReactParser(window.sbsw_admin.system_info)}
				</div>
				<button
					className="sw-si-expand-btn"
					onClick={() => {
						setExpanded(!expanded);
					}}
				>
				<span>
					<DownAngle />
				</span>
				<span>
					{!expanded
					? __("Expand", "social-wall")
					: __("Collapse", "social-wall")}
				</span>
				</button>
			</div>
			<div className="sw-export-settings-section">
				<div className="sw-export-left">
				<h4>{__("Export Settings", "social-wall")}</h4>
				<p>
					{__(
					"Share your plugin settings easily with Support",
					"social-wall"
					)}
				</p>
				</div>
				<div className="sw-export-right">
				<select
					onChange={(evt) => {
						setSelectedFeed(evt.target.value);
					}}
					id="sw-feeds-list"
				>
					<option value="-1">{__("Select Feed", "social-wall")}</option>
					{feedList[0].map((feed, index) => {
						return <option value={index}>{feed.feed_name}</option>;
					})}
				</select>
				<button
					disabled={selectedFeed === "-1" || selectedFeed === null}
					type="button"
					className="sb-btn sb-btn-secondary sb-btn-medium"
					onClick={onExport}
				>
					<span className="icon">
					<ExportIcon />
					</span>
					{__("Export", "social-wall")}
				</button>
				</div>
			</div>
		</div>
	);
};

const Support = () => {
	const sby_utm_source = 'social-wall'

	const StartedArticles = [
		{
			title: "How to Create a Social Wall Feed",
			link:
			"https://smashballoon.com/doc/how-to-create-a-feed-using-the-social-wall-wordpress-plugin/?utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=getting-started-create&utm_content=CreateAFeed",
		},
		{
			title: "Filtering Posts in your Social Wall Feed",
			link:
			"https://smashballoon.com/doc/filtering-posts-in-your-social-wall-feed/?utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=getting-started-filter&utm_content=FilteringPosts",
		},
		{
			title: "License Management FAQ",
			link:
			"https://smashballoon.com/doc/site-management/?utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=getting-started-license&utm_content=LicenseManagement",
		},
	];

	const DocsArticles = [
		{
			title: "Update Failed: Unauthorized Error",
			link:
			"https://smashballoon.com/doc/update-failed-unauthorized-error/?utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=troubleshooting-unauthorized&utm_content=UpdateFailed",
		},
		{
			title: "Instagram API Error Message Reference",
			link:
			"https://smashballoon.com/doc/instagram-api-error-message-reference/?instagram&utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=troubleshooting-instagram-api&utm_content=InstagramErrorReference",
		},
		{
			title: "Facebook API Error Message Reference",
			link:
			"https://smashballoon.com/doc/facebook-api-errors/?facebook&utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=troubleshooting-facebook-api&utm_content=FacebookErrorReference",
		},
	];

	const AdditionalArticles = [
		{
			title: "Guide to Creating and Using an API Key",
			link:
			"https://smashballoon.com/doc/youtube-api-key/?youtube&utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=additional-youtube-api-key&utm_content=GuideToAPIKey",
		},
		{
			title: "Creating Your Own Twitter App",
			link:
			"https://smashballoon.com/doc/create-your-own-twitter-app/?twitter&utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=additional-twitter-app&utm_content=CreatingTwitterApp",
		},
		{
			title: "Social Wall Custom Templates",
			link:
			"https://smashballoon.com/doc/social-wall-custom-templates/?social-wall&utm_source="+ sby_utm_source +"&utm_medium=support&utm_campaign=additional-custom-templates&utm_content=SWCustomTemplates",
		},
	];

	const [ notification, setNotification ] = useState({
        active : false
    });
    const editorNotification = useMemo(
        () => ({ notification, setNotification }),
		[ notification, setNotification ]
    );

	return (
		<>
			<Header title={__('Social Wall', 'social-wall')} />
			<HeaderMobile title={__('Social Wall', 'social-wall')} />
			<div className='sby-sb-container sby-no-margin sby-support-page'>
				<section class="sb-dashboard-heading sb-fs"><h2 class="sb-h2">Support</h2></section>
				<div className="sw-support-sections clearfix">
					<SupportBlock
						icon={<Rocket />}
						links={StartedArticles}
						title={__("Getting Started", "social-wall")}
						description={__(
						"Some helpful resources to get you started",
						"social-wall"
						)}
						buttonTitle={__("More help getting started", "social-wall")}
						buttonLink={
						"https://smashballoon.com/docs/getting-started/?youtube&utm_campaign=youtube-free&utm_source=support&utm_medium=docs"
						}
					/>
					<SupportBlock
						icon={<Book />}
						links={DocsArticles}
						title={__("Docs & Troubleshooting", "social-wall")}
						description={__(
						"Need help? Check out our help docs.",
						"social-wall"
						)}
						buttonTitle={__("View Documentation", "social-wall")}
						buttonLink={
						"https://smashballoon.com/docs/documentation/?youtube&utm_campaign=youtube-free&utm_source=support&utm_medium=docs"
						}
					/>
					<SupportBlock
						icon={<Plus />}
						links={AdditionalArticles}
						title={__("Additional Resources", "social-wall")}
						description={__(
						"To help you get the most out of the plugin",
						"social-wall"
						)}
						buttonTitle={__("View Blog", "social-wall")}
						buttonLink={
						"https://smashballoon.com/docs/faqs/?youtube&utm_campaign=youtube-free&utm_source=support&utm_medium=docs"
						}
					/>
				</div>
				<ContactSection />
				<SystemInfo editorNotification={editorNotification} />
			</div>
			<Notification
                active={ editorNotification.notification?.active }
                icon={ editorNotification.notification?.icon }
                text={ editorNotification.notification?.text }
                type={ editorNotification.notification?.type }
            />
		</>
	);
};

export default Support;
