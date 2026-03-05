import styled from 'styled-components';
import AnalogContext from '../AnalogContext';
import ChevronDown from '../icons/chevron-down';
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

	/* Subcategory tabs are indented under their parent */
	.block-categories-tabs .components-button.is-subcategory {
		padding-left: 14px;
		font-size: 14.22px;
	}

	/* Chevron icon for parent categories with subcategories */
	.block-categories-tabs .components-button .parent-chevron {
		display: inline-flex;
		margin-left: 4px;
		transition: transform 0.2s ease;
	}
	.block-categories-tabs .components-button .parent-chevron.is-collapsed {
		transform: rotate(-90deg);
	}

	/* Subcategory popup for horizontal mode */
	.subcategory-popup-overlay {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 99;
	}
	.subcategory-popup {
		z-index: 100;
		background: var(--analog-custom-library-categories-bg);
		border: none;
		border-radius: 0;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
		padding: 10px;
		min-width: 160px;
	}
	.subcategory-popup .subcategory-popup-item {
		display: block;
		width: 100%;
		padding: 14px 10px;
		background: none;
		border: none;
		text-align: left;
		font-size: 14px;
		color: var(--analog-custom-library-categories-text, #1e1e1e);
		cursor: pointer;
		white-space: nowrap;
	}
	.subcategory-popup .subcategory-popup-item:hover {
		color: var(--analog-custom-library-categories-active-text, #000);
	}
	.subcategory-popup .subcategory-popup-item.is-active {
		font-weight: bold;
		color: var(--analog-custom-library-categories-active-text, #000);
	}
`;

const isHorizontal = sidebarOrientation === 'horizontal';

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

	// ---- Hierarchical category helpers ----
	const categoryTree = context.state.categoryTree || [];
	const isHierarchical = categoryTree.some( cat => cat.parent > 0 );

	/**
	 * Get all descendant category names for a given category name.
	 * Flattened — only 1 level of nesting is displayed, but deep descendants
	 * are still collected for filtering purposes.
	 */
	const getDescendantNames = ( catName ) => {
		const cat = categoryTree.find( c => c.name === catName );
		if ( ! cat ) return [];
		const children = categoryTree.filter( c => c.parent === cat.id );
		return children.flatMap( child => [ child.name, ...getDescendantNames( child.name ) ] );
	};

	/**
	 * Category name + all descendant names.
	 * Used when a parent is clicked to show all templates in the family.
	 */
	const getMatchingNames = ( catName ) => [ catName, ...getDescendantNames( catName ) ];

	/**
	 * Get direct children names for a root-level category.
	 * Deep grandchildren are flattened to appear as direct children (max 1 level nesting).
	 */
	const getChildNames = ( catName ) => {
		const cat = categoryTree.find( c => c.name === catName );
		if ( ! cat ) return [];
		// Collect ALL descendants and flatten them under the parent.
		return getDescendantNames( catName );
	};

	/**
	 * Check if a tab name is a subcategory (i.e. it has a parent in the tree).
	 */
	const isSubcategory = ( tabName ) => {
		if ( ! isHierarchical ) return false;
		const cat = categoryTree.find( c => c.name === tabName );
		return cat && cat.parent > 0;
	};

	/**
	 * Check if a tab name is a root-level parent that has children.
	 */
	const isParentCategory = ( tabName ) => {
		if ( ! isHierarchical ) return false;
		const cat = categoryTree.find( c => c.name === tabName );
		if ( ! cat || cat.parent > 0 ) return false;
		return categoryTree.some( c => c.parent === cat.id );
	};

	// Collapse state: tracks which parent categories are collapsed.
	// All parents start collapsed by default (sidebar/vertical mode).
	const [ collapsedParents, setCollapsedParents ] = React.useState( {} );

	// Popup state for horizontal mode: { name, top, left } of the open popup, or null.
	const [ openPopup, setOpenPopup ] = React.useState( null );

	// categoryTree is loaded async — once it arrives, collapse all parents.
	React.useEffect( () => {
		if ( categoryTree.length === 0 ) return;
		setCollapsedParents( prev => {
			const next = { ...prev };
			let changed = false;
			categoryTree
				.filter( c => c.parent === 0 && categoryTree.some( ch => ch.parent === c.id ) )
				.forEach( c => {
					if ( ! ( c.name in next ) ) {
						next[ c.name ] = true;
						changed = true;
					}
				} );
			return changed ? next : prev;
		} );
	}, [ categoryTree ] );

	const toggleCollapse = ( parentName, e ) => {
		if ( e ) {
			e.stopPropagation();
			e.preventDefault();
		}
		if ( isHorizontal ) {
			// In horizontal mode, toggle a popup instead of inline expand.
			// Capture the chevron's screen position so the popup renders below it.
			if ( openPopup && openPopup.name === parentName ) {
				setOpenPopup( null );
			} else {
				// Left: align to the parent tab button (the title), not the chevron icon.
				const btnEl = e.currentTarget.closest( '.components-button' );
				const btnRect = btnEl ? btnEl.getBoundingClientRect() : e.currentTarget.getBoundingClientRect();
				// Top: position just below the full tab bar so there's no overlap.
				const tabBarEl = e.currentTarget.closest( '.components-tab-panel__tabs' );
				const tabBarRect = tabBarEl ? tabBarEl.getBoundingClientRect() : btnRect;
				setOpenPopup( { name: parentName, top: tabBarRect.bottom + 4, left: btnRect.left } );
			}
		} else {
			setCollapsedParents( prev => ( {
				...prev,
				[ parentName ]: ! prev[ parentName ],
			} ) );
		}
	};

	const onSelect = ( tab ) => {
		context.dispatch( { blocksTab: tab } );

		let selectFilteredBlocks = filteredBlocks;

		if ( tab === 'favorites' ) {
			selectFilteredBlocks = favoriteBlocks;
		}
		if ( tab !== 'favorites' && tab !== blockIdentifier ) {
			if ( isHierarchical && isParentCategory( tab ) ) {
				// Parent category: show parent + all descendant templates.
				const matchNames = getMatchingNames( tab );
				selectFilteredBlocks = sourceFilteredArchive.filter( block =>
					block.tags && block.tags.some( t => matchNames.indexOf( t ) > -1 )
				);
			} else {
				// Subcategory or flat category: show only its own templates.
				selectFilteredBlocks = sourceFilteredArchive.filter( block =>
					block.tags && block.tags.indexOf( tab ) > -1
				);
			}
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
			if ( isHierarchical && isParentCategory( tab ) ) {
				const matchNames = getMatchingNames( tab );
				foundItems = blocks.filter( block =>
					block.tags && block.tags.some( t => matchNames.indexOf( t ) > -1 )
				);
			} else {
				foundItems = blocks.filter( block =>
					block.tags && block.tags.indexOf( tab ) > -1
				);
			}
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

	/**
	 * Build category list for TabPanel.
	 * When hierarchical, root categories are listed and their children are
	 * inserted directly below them (flattened to 1 level of nesting max).
	 * Non-hierarchical mode remains identical to the original behavior.
	 */
	const categoriesData = () => {
		if ( ! isHierarchical ) {
			return defaultTabs.concat( categories.sort() );
		}

		// Build ordered list: for each root category, insert it then its
		// flattened children.  Categories not in the tree are appended at end.
		const treeNames = new Set( categoryTree.map( c => c.name ) );
		const rootCats = categoryTree.filter( c => c.parent === 0 );

		const ordered = [];

		// Sort root categories alphabetically.
		rootCats.sort( ( a, b ) => a.name.localeCompare( b.name ) );

		for ( const root of rootCats ) {
			// Only include if templates exist.
			if ( categories.indexOf( root.name ) === -1 ) {
				// Check if any child has templates.
				const childNames = getChildNames( root.name );
				const hasChildTemplates = childNames.some( n => categories.indexOf( n ) > -1 );
				if ( ! hasChildTemplates ) continue;
			}
			ordered.push( root.name );

			// In horizontal mode, subcategories appear in a popup — skip them here.
			// In sidebar mode, flatten all descendants under one level (skip if collapsed).
			if ( ! isHorizontal && ! collapsedParents[ root.name ] ) {
				const childNames = getChildNames( root.name );
				childNames.sort( ( a, b ) => a.localeCompare( b ) );
				for ( const cn of childNames ) {
					if ( categories.indexOf( cn ) > -1 ) {
						ordered.push( cn );
					}
				}
			}
		}

		// Append standalone categories not in the tree.
		const standalone = categories
			.filter( cat => cat && ! treeNames.has( cat ) )
			.sort();
		ordered.push( ...standalone );

		return defaultTabs.concat( ordered );
	}

	const titleGenerator = (title) => {
		let count = getItemCount(title);
		let countTemplate = count > 0 ? count : 0;
		let label = title.replace(/-/g, ' ');
		const hasChildren = isParentCategory( title );

		return (
			<React.Fragment>
				{`${label} `}
				{ AGWP_LIBRARY.showLibraryCategoriesTemplateCount ? <span key={title}>{countTemplate}</span> : '' }
				{ hasChildren && (
					<span
						role="button"
						tabIndex={ 0 }
									className={ `parent-chevron${ isHorizontal
							? ( openPopup && openPopup.name === title ? '' : ' is-collapsed' )
							: ( collapsedParents[ title ] ? ' is-collapsed' : '' ) }` }
						onClick={ ( e ) => toggleCollapse( title, e ) }
						onKeyDown={ ( e ) => {
							if ( 'Enter' === e.key || ' ' === e.key ) toggleCollapse( title, e );
						} }
									aria-label={ ( isHorizontal ? ! ( openPopup && openPopup.name === title ) : collapsedParents[ title ] )
							? __( 'Expand subcategories', 'analogwp-library' )
							: __( 'Collapse subcategories', 'analogwp-library' ) }
					>
						<ChevronDown />
					</span>
				) }
			</React.Fragment>
		);
	}

	const tabGenerator = (tabsArray) => {
		const tabs = tabsArray.filter( tab => tab && getItemCount(tab) > 0 );

		return tabs.map( (item) => ({
			name: item,
			title:  titleGenerator(item),
			className: `tab-${ item }${ isSubcategory( item ) ? ' is-subcategory' : '' }`
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
            const cats = categoriesData();

			if ( context.state.showFree && AGWP_LIBRARY.license.status !== 'valid' ) {
				return initialTab;
			}

			switch ( type ) {
				case 'header':
					initialTab = cats.includes( 'Headers' ) ? 'Headers' : defaultTab;
					break;
				case 'footer':
					initialTab = cats.includes( 'Footers' ) ? 'Footers' : defaultTab;
					break;
				case 'single-page':
				case 'single-post':
				case 'page':
					initialTab = cats.includes( 'Post Templates' ) ? 'Post Templates' : defaultTab;
					break;
				default:
					break;
			}

		}

		return initialTab;
	}

	return (
		<SidebarWrapper className={`sidebar ${!context.state.blockArchive.length ? 'no-templates' : ''}`}>
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

			{ /* Subcategory popup for horizontal mode — rendered with position:fixed to avoid layout shift */ }
			{ isHorizontal && isHierarchical && openPopup && ( () => {
				const childNames = getChildNames( openPopup.name ).sort();
				const visibleChildren = childNames.filter( n => categories.indexOf( n ) > -1 );
				if ( ! visibleChildren.length ) return null;
				return (
					<React.Fragment>
						<div
							className="subcategory-popup-overlay"
							onClick={ () => setOpenPopup( null ) }
						/>
						<div
							className="subcategory-popup"
							style={ { position: 'fixed', top: openPopup.top, left: openPopup.left } }
						>
							{ visibleChildren.map( name => (
								<button
									key={ name }
									className={ `subcategory-popup-item${ context.state.blocksTab === name ? ' is-active' : '' }` }
									onClick={ () => {
										onSelect( name );
										setOpenPopup( null );
									} }
								>
									{ name }
									{ AGWP_LIBRARY.showLibraryCategoriesTemplateCount && (
										<span style={ { opacity: 0.5, marginLeft: '6px' } }>{ getItemCount( name ) }</span>
									) }
								</button>
							) ) }
						</div>
					</React.Fragment>
				);
			} )() }
		</SidebarWrapper>
	);
}

export default Sidebar;
