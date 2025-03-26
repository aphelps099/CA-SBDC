import {
	HashRouter as Router,
	Routes,
	Route,
	Navigate,
} from 'react-router-dom';

import Settings from './pages/Settings';
import AllFeeds from './pages/AllFeeds';
import AboutUs from './pages/AboutUs';
import Support from './pages/Support';
import CreateFeed from './pages/CreateFeed';

import FeedEditor from './FeedEditor';

import General from './components/Settings/tabs/General';
import Advanced from './components/Settings/tabs/Advanced';
import Feeds from './components/Settings/tabs/Feeds';

import './styles/main.scss';

const sbCustomizer = window.sbsw_admin;

const SocialWall = (
	<>

		{!sbCustomizer.isFeedEditor && (
			<Router>
				<div className={'sw-relative sb-h-screen'}>
					<Routes>
						<Route path="/" element={<AllFeeds />} />
						<Route path="/settings" element={<Settings />}>
							<Route
								index
								element={
									<Navigate to={'/settings/general'} General />
								}
							/>
							<Route path="general" element={<General />} />
							<Route path="feeds" element={<Feeds />} />
							<Route path="advanced" element={<Advanced />} />
						</Route>
						<Route path="/create-feed" element={<CreateFeed />} />
						<Route path="/about-us" element={<AboutUs />} />
						<Route path="/support" element={<Support />} />
					</Routes>
				</div>
			</Router>
		)}

		{/* The Feed Customizer */}
		{sbCustomizer.isFeedEditor && (
			<FeedEditor/>
		)}
	</>
);

export default SocialWall;
