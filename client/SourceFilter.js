/**
 * Source Filter Component
 *
 * Displays a dropdown to filter templates by source (All, Local, Remote).
 * Only shows when:
 * - PRO plugin is active
 * - Remote mode is set to 'client'
 * - Template visibility setting is 'show_all'
 *
 * These conditions are checked via AGWP_LIBRARY.sourceFilter global.
 */
import styled from 'styled-components';
import AnalogContext from './AnalogContext';
import Adjustments from './icons/adjustments';

const { __ } = wp.i18n;
const { Button, Dropdown, MenuGroup, MenuItem } = wp.components;

const FilterButton = styled( Button )`
	color: var(--analog-custom-library-btn-text) !important;
	font-weight: 500 !important;
	font-size: 12px !important;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	display: inline-flex !important;
	align-items: center !important;
	gap: 6px;
	padding: 6px 12px !important;
	border-radius: 4px !important;
	background: transparent !important;
	margin-right: 12px;
	height: auto !important;
	min-height: 32px;

	svg {
		width: 14px;
		height: 14px;
	}
`;

const FilterMenu = styled.div`
	min-width: 180px;

	.components-menu-group {
		padding: 8px 0;
		border: 1px solid var(--analog-custom-library-top-header-border);
		border-radius: 2px;
	}

	.components-menu-item__button {
		width: 100%;
		padding: 8px 14px;
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 14px;
		font-weight: 400;
		color: var(--analog-custom-library-top-header-text) !important;

		&.is-active {
			.filter-icon .circle {
				width: 8px;
				height: 8px;
				border-radius: 100%;
				background: var(--analog-custom-library-top-header-text);
			}
		}

		&:hover,
		&:focus {
			box-shadow: none !important;
			outline: none !important;
		}
	}

	.filter-icon {
		width: 18px;
		height: 18px;
		padding: 4px;
		display: flex;
		align-items: center;
		justify-content: center;
		border: 1px solid var(--analog-custom-library-top-header-text) !important;
		border-radius: 100%;
		margin-right: 8px;
	}
`;

const SOURCE_OPTIONS = [
	{
		value: 'all',
		label: __( 'All Templates', 'analogwp-library' ),
	},
	{
		value: 'local',
		label: __( 'Local Only', 'analogwp-library' ),
	},
	{
		value: 'remote',
		label: __( 'Remote Only', 'analogwp-library' ),
	},
];

const SourceFilter = () => {
	const context = React.useContext( AnalogContext );

	// Check if source filter should be shown.
	// This global is set by PRO plugin when conditions are met:
	// - PRO is active
	// - Mode is 'client'
	// - Template visibility is 'show_all'
	const sourceFilterConfig = AGWP_LIBRARY?.sourceFilter;

	if ( ! sourceFilterConfig?.enabled ) {
		return null;
	}

	const currentSource = context.state.sourceFilter || 'all';
	const currentOption = SOURCE_OPTIONS.find( opt => opt.value === currentSource ) || SOURCE_OPTIONS[ 0 ];

	return (
		<Dropdown
			className="analog-source-filter-dropdown"
			contentClassName="analog-custom-library-themed"
			popoverProps={ { placement: 'bottom-end', offset: 8 } }

			renderToggle={ ( { isOpen, onToggle } ) => (
				<FilterButton
					onClick={ onToggle }
					aria-expanded={ isOpen }
				>
					<Adjustments />
				</FilterButton>
			) }
			renderContent={ ( { onClose } ) => (
				<FilterMenu>
					<MenuGroup>
						{ SOURCE_OPTIONS.map( ( option ) => (
							<MenuItem
								key={ option.value }
								className={ currentSource === option.value ? 'is-active' : '' }
								onClick={ () => {
									context.setSourceFilter( option.value );
									onClose();
								} }
							>
								<span className="filter-icon"><span className='circle'></span></span>
								{ option.label }
							</MenuItem>
						) ) }
					</MenuGroup>
				</FilterMenu>
			) }
		/>
	);
};

export default SourceFilter;
