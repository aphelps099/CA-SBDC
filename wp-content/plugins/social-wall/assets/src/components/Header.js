import { __ } from '@wordpress/i18n';
import { ReactComponent as Logo } from '../../images/header-logo.svg';
import { NavLink } from 'react-router-dom';
import SbHelpButton from './lib/SbHelpButton';

const Header = ({ title, classes }) => {
	return (
		<div className={'sw-bg-white sw-hidden sm:sw-block ' + classes}>
			<div
				className={
					'sw-py-4 sw-px-4 sw-flex sw-items-center sw-justify-between lg:sw-px-14 sw-desktop-header'
				}
			>
				<div className={'sw-flex sw-items-center'}>
					<Logo />
					<span className={'sw-ml-2 sw-text-sm'}>
						/ {title}
					</span>
				</div>

				<div className={'sw-mr-4 md:sw-mr-14 lg:sw-mr-24'}>
					<ul
						className={
							'sw-flex sw-items-center sw-gap-4 sw-text-sm md:sw-gap-8 md:sw-text-base'
						}
					>
						<li className={'sw-header-tab'}>
							<NavLink
								className={(navData) =>
									navData.isActive
										? 'sw-header-tab-active'
										: ''
								}
								to={'/'}
							>
								{__('All Feeds', 'social-wall')}
							</NavLink>
						</li>
						<li className={'sw-header-tab'}>
							<NavLink
								className={(navData) =>
									navData.isActive
										? 'sw-header-tab-active'
										: ''
								}
								to={'/settings'}
							>
								{__('Settings', 'social-wall')}
							</NavLink>
						</li>
						<li className={'sw-header-tab'}>
							<NavLink
								className={(navData) =>
									navData.isActive
										? 'sw-header-tab-active'
										: ''
								}
								to={'/about-us'}
							>
								{__('About Us', 'social-wall')}
							</NavLink>
						</li>
						<li className={'sw-header-tab'}>
							<NavLink
								className={(navData) =>
									navData.isActive
										? 'sw-header-tab-active'
										: ''
								}
								to={'/support'}
							>
								{__('Support', 'social-wall')}
							</NavLink>
						</li>
					</ul>
				</div>

				<SbHelpButton />
			</div>
		</div>
	);
};

export default Header;
