import { useContext } from 'react'
import { useLocation } from 'react-router-dom';
import FeedsContext from '../../context/FeedsContext.js'

const AddPluginFeed = () => {
    const location = useLocation();
    const selectedFeeds = location.state;

    const { sbPluginsFeeds } = useContext(FeedsContext);

	const gridCols = () => {
		if ( location.state.length <= 2 ) {
			return 2;
		}
		return location.state.length;
	}

    // get the feed plugin icon
    const getPluginIcon = (plugin) => {
        let icon = '';
        switch( plugin ) {
            case 'facebook': 
                icon = <FacebookIcon/>
                break;
            case 'instagram': 
                icon = <InstagramIcon/>
                break;
            case 'twitter': 
                icon = <TwitterIcon/>
                break;
            case 'youtube': 
                icon = <YouTubeIcon/>
                break;
        }
        return icon;
    }

    // all feed plugins type
    const pluginsType = [
		{
			id: 'facebook',
			title: 'Facebook',
			icon: <FacebookIcon />,
		},
		{
			id: 'instagram',
			title: 'Instagram',
			icon: <InstagramIcon />,
		},
		{
			id: 'twitter',
			title: 'Twitter',
			icon: <TwitterIcon />,
		},
		{
			id: 'youtube',
			title: 'YouTube',
			icon: <YouTubeIcon />,
		},
    ]

    return (
        <div className={`sw-grid sw-grid-cols-${gridCols()} sw-mt-4 sw-gap-2.5`}>
            {
                selectedFeeds.map((feed, index) => {
                    const plugin = pluginsType.find(plugin => plugin.id == feed)
                    return (
                        <div className={'sw-border sw-shadow-md'}>
                            <div className={'sw-border-b sw-p-3 sw-font-semibold text-sm'}>
                                <p className={'sw-flex sw-items-center sw-gap-2'}>
                                    {getPluginIcon(plugin.id)}
                                    { plugin.title }
                                </p>
                            </div>
                            <div className='sw-px-3 sw-py-5'>
                                <p>Select Feed</p>
                                <select name="" id="" className='sw-select sw-border sw-border-slate-300 sw-w-full sw-mt-1 sw-text-xs'>
                                    <option value="">Select</option>
                                    {
                                        sbPluginsFeeds.pluginsFeeds[plugin.id].map(feed => {
                                            return (
                                                <option id={feed.feed_id}>{feed.feed_name}</option>
                                            )
                                        })
                                    }
                                </select>
                            </div>
                        </div>
                    )
                })
            }
            
        </div>
    )
}

export default AddPluginFeed