import { __ } from '@wordpress/i18n';
import { ReactComponent as LogoMobile } from '../../images/header-logo-mobile.svg';
import { ReactComponent as DownAngle } from '../../images/support-page/downAngle.svg';
import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import SbHelpButton from './lib/SbHelpButton';

const HeaderMobile = ({ title, classes }) => {
    const [ mobileMenuExpaned, setMobileMenuExpaned ] = useState(false);

	return (
		<div className={'sw-bg-white sw-justify-between sw-p-3 sw-mobile-header sw-flex sm:sw-hidden ' + classes}>
            <div className={'sw-flex sw-gap-2'}>
                <LogoMobile />
                    <div 
                        className={mobileMenuExpaned ? 'sw-mobile-menu-expanded sw-mobile-header-menu' : 'sw-mobile-header-menu'}
                        onClick={() => {
                            setMobileMenuExpaned(!mobileMenuExpaned);
                        }}
                    >
                        <NavLink
                            className={(navData) =>
                                navData.isActive
                                    ? 'sw-mobile-menu-active'
                                    : ''
                            }
                            to={'/'}
                        >
                            {__('All Feeds', 'social-wall')}
                        </NavLink>

                        <NavLink
                            className={(navData) =>
                                navData.isActive
                                    ? 'sw-mobile-menu-active'
                                    : ''
                            }
                            to={'/settings'}
                        >
                            {__('Settings', 'social-wall')}
                        </NavLink>
                        <NavLink
                            className={(navData) =>
                                navData.isActive
                                    ? 'sw-mobile-menu-active'
                                    : ''
                            }
                            to={'/about-us'}
                        >
                            {__('About Us', 'social-wall')}
                        </NavLink>
                        <NavLink
                            className={(navData) =>
                                navData.isActive
                                    ? 'sw-mobile-menu-active'
                                    : ''
                            }
                            to={'/support'}
                        >
                            {__('Support', 'social-wall')}
                        </NavLink>

                        <DownAngle className="sw-menu-arrow" />
                    </div>
                <div className={''}>
                </div>
            </div>
            <SbHelpButton />
		</div>
	);
};

export default HeaderMobile;
