import { useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import Header from '../components/FeedEditor/Header';
import FeedEditorContext from '../context/FeedEditorContext';
import GetTabItem from '../components/FeedEditor/GetTabItem';
import { ReactComponent as ChevronLeft } from '../../images/chevron-left.svg';

const FeedEditor = () => {
	let { feedId } = useParams();
	console.log(feedId);
    const sbCustomizer = window.sbsw_admin;

	const [activeTab, setActiveTab] = useState(
		window.sbsw_admin.feed_editor.default_active_tab
	);
	const feActiveTab = useMemo(
		() => ({ activeTab, setActiveTab }),
		[activeTab, setActiveTab]
	);

	const [history, setHistory] = useState([activeTab]);
	const feHistory = useMemo(
		() => ({ history, setHistory }),
		[history, setHistory]
	);

	const [activeSection, setActiveSection] = useState(null);
	const feActiveSection = useMemo(
		() => ({ activeSection, setActiveSection }),
		[activeSection, setActiveSection]
	);

	const [ feedData, setFeedData ] = useState( sbCustomizer.feed_editor.feed_preview );
    const editorFeedData = useMemo(
        () => ({ feedData, setFeedData }),
		[feedData, setFeedData]
    );

	const tabs = [
		{
			id: 'tab-customize',
			label: 'Customize',
			items: [
				{
					type: 'section',
					id: 'feed-layout',
					label: 'Feed Layout',
					items: [
						{
							type: 'sub-section',
							id: 'layout-subsection',
							label: 'Layout',
							items: [
								{
									type: 'radio-button',
									id: 'layout-radio-button',
									items: [
										{
											id: 'masonry',
											label: 'Masonry',
										},
										{
											id: 'list',
											label: 'List',
										},
										{
											id: 'carousel',
											label: 'Carousel',
										},
									],
								},
							],
						},
						{
							type: 'sub-section',
							id: 'columns-subsection',
							label: 'Columns',
							items: [
								{
									type: 'select',
									id: 'desktop-columns',
									icon: 'desktop',
									label: 'Desktop',
									items: [
										{
											id: '3',
											value: '3',
										},
										{
											id: '4',
											value: '4',
										},
										{
											id: '5',
											value: '5',
										},
									],
								},
								{
									type: 'select',
									id: 'tablet-columns',
									icon: 'tablet',
									label: 'Tablet',
									items: [
										{
											id: '3',
											value: '3',
										},
										{
											id: '4',
											value: '4',
										},
										{
											id: '5',
											value: '5',
										},
									],
								},
							],
						},
					],
				},
				{
					type: 'section',
					id: 'color-scheme',
					label: 'Color Scheme',
				},
				{
					type: 'section-title',
					id: 'sections',
					label: 'Sections',
				},
				{
					type: 'section',
					id: 'posts',
					label: 'Posts',
				},
				{
					type: 'section',
					id: 'load-more-button',
					label: 'Load More Button',
					disabled: true,
				},
			],
		},
		{
			id: 'tab-settings',
			label: 'Settings',
			items: [],
		},
	];

	const handleTabClick = (tabId) => {
		setActiveTab(tabId);

		const newHistory = [];
		newHistory.push(tabId);
		setHistory(newHistory);
	};

	const findObject = (obj, id) => {
		return obj.id === id
			? obj
			: 'items' in obj &&
					obj.items.reduce(
						// eslint-disable-next-line no-shadow
						(acc, obj) => acc ?? findObject(obj, id),
						undefined
					);
	};

	const findItemById = (id) => {
		let item = {};
		for (const tab of tabs) {
			item = findObject(tab, id);
			if (typeof item !== 'undefined' && item) {
				break;
			}
		}
		return item;
	};

	const handleHistoryClick = (itemId) => {
		const item = findItemById(itemId);
		if (itemId.includes('tab')) {
			setActiveSection(null);
			setActiveTab(item.id);
			setHistory([item.id]);
		} else {
			setActiveTab(null);
			setActiveSection(item);
			setHistory([...history.splice(0, history.indexOf(itemId)), itemId]);
		}
	};

	const getHistory = () => {
		let h = history;

		if (history.length > 1) {
			h = history.slice(0, history.length - 1);
		}

		return (
			<div className={'sw-flex sw-items-center'}>
				{h.map((itemId) => {
					return (
						<div
							role={'presentation'}
							onClick={() => handleHistoryClick(itemId)}
							key={itemId}
							className={
								'sw-flex sw-items-center hover:sw-text-gray-900 first:sw-ml-0 sw-ml-2'
							}
						>
							<ChevronLeft />
							<span
								className={
									'sw-text-xs sw-font-semibold sw-uppercase sw-ml-1 sw-cursor-pointer'
								}
							>
								{findItemById(itemId).label}
							</span>
						</div>
					);
				})}
			</div>
		);
	};

	return (
		<>
			<FeedEditorContext.Provider
				value={{ 
					feActiveTab, 
					feActiveSection, 
					feHistory,
					editorFeedData
				}}
			>
				<Header />
				<div className={'sw-flex sw-w-full sw-min-h-screen'}>
					<div className={'sw-bg-white sw-w-1/4 sw-shadow-md'}>
						{/* Tab */}
						{activeTab ? (
							<div
								style={{
									gridTemplateColumns: `repeat(${tabs.length}, minmax(0, 1fr))`,
								}}
								className={
									'sw-bg-gray-100 sw-grid sw-gap-0 sw-mb-6'
								}
							>
								{tabs.map((tab) => {
									return (
										<span
											role={'presentation'}
											onClick={() =>
												handleTabClick(tab.id, tab)
											}
											key={tab.id}
											className={`sw-text-center sw-py-4 sw-text-lg sw-font-semibold sw-border-b-2 active:sw-border-sb-blue-light hover:sw-bg-white hover:sw-cursor-pointer ${
												tab.id === activeTab
													? 'sw-border-sb-blue-light'
													: ''
											}`}
										>
											{tab.label}
										</span>
									);
								})}
							</div>
						) : (
							activeSection && (
								<div
									className={
										'sw-py-5 sw-px-6 sw-border-b sw-border-gray-200'
									}
								>
									{getHistory()}
									<h2
										className={
											'sw-font-semibold sw-text-2xl sw-mt-4'
										}
									>
										{activeSection.label}
									</h2>
								</div>
							)
						)}

						{/* Tab Content */}
						<div className={'grid sw-grid-cols-1 sw-gap-2'}>
							{tabs.map((tab) => {
								if (!('items' in tab)) {
									return undefined;
								}

								if (activeTab !== tab.id) {
									return undefined;
								}

								return tab.items.map((item) => {
									return (
										<div
											key={`wrapper-${item.id}`}
											className={'sw-relative'}
										>
											{GetTabItem(item)}
										</div>
									);
								});
							})}

							{activeSection &&
								'items' in activeSection &&
								activeSection.items.map((item) => {
									return (
										<div
											key={`wrapper-${item.id}`}
											className={'sw-relative'}
										>
											{GetTabItem(item)}
										</div>
									);
								})}
						</div>
					</div>

					{/* Feed Preview */}
					<div
						className={
							'sw-w-3/4 sw-flex sw-p-2 sw-items-center sw-justify-center'
						}
					>
						<div className='feed-preview' dangerouslySetInnerHTML={{__html: editorFeedData.feedData}}></div>
					</div>
				</div>
			</FeedEditorContext.Provider>
		</>
	);
};

export default FeedEditor;
