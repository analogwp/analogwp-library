import styled from 'styled-components';
import AnalogContext from '../AnalogContext';
const { __ } = wp.i18n;
const { TabPanel, TextControl } = wp.components;

const blockIdentifier = 'all';

const defaultTabs = [
	'favorites',
	blockIdentifier,
];


const sidebarOrientation = ! AGWP_LIBRARY.libraryCategoriesLocation ? 'horizontal' : AGWP_LIBRARY.libraryCategoriesLocation;

const SidebarWrapper = styled.div`
	.components-tab-panel__tabs > .components-button {
		text-transform: capitalize;
	}

	.components-toggle-control
	.components-base-control__field {
		flex-direction: row-reverse;
		justify-content: space-between;
	}

	.components-toggle-control
	.components-base-control__field
	.components-form-toggle {
		margin-right: 0;
	}

	.components-toggle-control
	.components-base-control__field
	.components-toggle-control__label {
		padding: 8px 0 10px;
	}

	.components-base-control.components-toggle-control {
		border-bottom: 1px solid var(--analog-custom-library-btn-border);
	}

	.block-categories-tabs .components-button {
		border-radius: 0;
		padding: 10px 0;
		font-size: 16px;
		color: var(--analog-custom-library-categories-text);
		justify-content: space-between;
	}

	.block-categories-tabs .components-button > span {
		color: rgba(0, 0, 0, 0.44);
		font-size: 14.22px;
		font-weight: normal;
	}

	.block-categories-tabs {
		.components-button.active-tab {
			box-shadow: none;
			font-weight: bold;
			color: var(--analog-custom-library-categories-active-text) !important;
		}
		.components-button {
			&:hover,
			&:focus,
			&:active {
				color: var(--analog-custom-library-categories-active-text) !important;
			}
		}

	}

	.block-categories-tabs
	.components-button:not(:disabled):not([aria-disabled="true"]):not(.is-secondary):not(.is-primary):not(.is-tertiary):not(.is-link):hover,
	.components-button:focus:not(:disabled) {
		background-color: transparent;
		outline: none;
		box-shadow: none;
	}

	.block-categories-tabs .components-button:not([aria-disabled=true]):active {
		color: var(--analog-custom-library-primary) !important;
	}

	.block-categories-tabs label,
	.components-toggle-control
	.components-base-control__field
	.components-toggle-control__label {
		font-size: 16px;
		color: #060606;
	}

	.block-categories-tabs {
		padding-right: 10px;
	}
`;

const Sidebar = ( { state } ) => {
	const context = React.useContext( AnalogContext );

	// Get source filter and filter blockArchive accordingly.
	const sourceFilter = context.state.sourceFilter;
	let sourceFilteredArchive = context.state.blockArchive;

	if ( sourceFilter && sourceFilter !== 'all' ) {
		sourceFilteredArchive = context.state.blockArchive.filter( block => {
			if ( sourceFilter === 'local' ) {
				return ! block.is_remote;
			}
			if ( sourceFilter === 'remote' ) {
				return block.is_remote === true;
			}
			return true;
		} );
	}

	const categories = [ ...new Set( sourceFilteredArchive.map( block => block.tags[ 0 ] ) ) ];
	let filteredBlocks = sourceFilteredArchive;
	let favoriteBlocks = filteredBlocks.filter( t => t.id in context.state.blockFavorites );

	const onSelect = ( tab ) => {
		context.dispatch( { blocksTab: tab } );

		let selectFilteredBlocks = filteredBlocks;

		if ( tab === 'favorites' ) {
			selectFilteredBlocks = favoriteBlocks;
		}
		if ( tab !== 'favorites' && tab !== blockIdentifier ) {
			selectFilteredBlocks = sourceFilteredArchive.filter( block => block.tags.indexOf( tab ) > -1 );
		}

		const { blocksSearchInput } = context.state;

		if ( blocksSearchInput ) {
			selectFilteredBlocks = context.state.itemFilteredWithSearchTerm( selectFilteredBlocks, blocksSearchInput );
		}

		context.dispatch( { blocks: selectFilteredBlocks } );
	}

	const getItemCount = ( tab ) => {
		const blocks = sourceFilteredArchive;
		const { blocksSearchInput } = context.state;
		let foundItems = [];

		if ( tab === blockIdentifier ) {
			foundItems = sourceFilteredArchive;
		}
		if ( tab === 'favorites' ) {
			foundItems = favoriteBlocks;
		}

		if ( tab !== blockIdentifier && tab !== 'favorites' ) {
			foundItems = blocks.filter( block => block.tags.indexOf( tab ) > -1 );
		}

		if ( AGWP_LIBRARY.license.status !== 'valid' && context.state.showFree ) {
			foundItems = foundItems.filter( block => !block.is_pro );
		}

		if ( blocksSearchInput ) {
			foundItems = context.state.itemFilteredWithSearchTerm( foundItems, blocksSearchInput );
		}

		if ( foundItems ) {
			return foundItems.length;
		}

		return false;
	}

	const categoriesData = () => {
		return defaultTabs.concat( categories.sort() );
	}

	const titleGenerator = (title) => {
		let count = getItemCount(title);
		let countTemplate = count > 0 ? count : 0;
		let label = title.replace(/-/g, ' ');

		return [`${label} `, AGWP_LIBRARY.showLibraryCategoriesTemplateCount ? <span key={title}>{countTemplate}</span> : ''];
	}

	const tabGenerator = (tabsArray) => {
		const tabs = tabsArray.filter( tab => tab && getItemCount(tab) > 0 );

		return tabs.map( (item) => ({
			name: item,
			title:  titleGenerator(item),
			className: `tab-${ item }`
		})
		);
	}

	const tabContent = () => {
		return null;
	}

	const getInitialTab = (defaultTab) => {
		let initialTab = defaultTab ? defaultTab : context.state.blocksTab;
		if ( typeof elementor !== 'undefined' && elementor && elementor.config ) {
			const type = elementor.config.document.type;
            const categories = categoriesData();

			if ( context.state.showFree && AGWP_LIBRARY.license.status !== 'valid' ) {
				return initialTab;
			}

			switch ( type ) {
				case 'header':
					initialTab = categories.includes( 'Headers' ) ? 'Headers' : defaultTab;
					break;
				case 'footer':
					initialTab = categories.includes( 'Footers' ) ? 'Footers' : defaultTab;
					break;
				case 'single-page':
				case 'single-post':
				case 'page':
					initialTab = categories.includes( 'Post Templates' ) ? 'Post Templates' : defaultTab;
					break;
				default:
					break;
			}

		}

		return initialTab;
	}

	return (
		<SidebarWrapper className={`sidebar ${!context.state.blockArchive.length ? 'no-templates' : ''}`}>
			{ context.state.blockArchive.length >= 10 && <TextControl
				placeholder={ __( 'Search Templates', 'analogwp-library' ) }
				value={ context.state.blocksSearchInput }
				onChange={ ( value ) => {
					context.handleSearch( value, 'patterns' );
					context.dispatch( { blocksSearchInput: value } );
				} }
			/> }

			{ tabGenerator( categoriesData() ).length >= 1 ?
				<TabPanel
				className="block-categories-tabs"
				orientation={ sidebarOrientation }
				activeClass="active-tab"
				initialTabName={getInitialTab( context.state.blocksTab ) }
				onSelect={onSelect}
				tabs={tabGenerator( categoriesData() )}
				key={context.state.blocksTab}
			>
				{
					( tab ) => tabContent()
				}
			</TabPanel> : <div className="block-categories-tabs"></div> }
		</SidebarWrapper>
	);
}

export default Sidebar;
