/**
 * Header Search Component
 *
 * Displays a search icon that transforms into a search bar when clicked.
 * Includes a close button to collapse back to the icon.
 */
import styled, { css, keyframes } from 'styled-components';
import { useEffect, useState, useRef, useContext } from 'react';
import AnalogContext from './AnalogContext';
import Search from './icons/search';
import XMark from './icons/x-mark';

const { __ } = wp.i18n;

const expandWidth = keyframes`
	from {
		width: 32px;
		opacity: 0.8;
	}
	to {
		width: 250px;
		opacity: 1;
	}
`;

const collapseWidth = keyframes`
	from {
		width: 250px;
		opacity: 1;
	}
	to {
		width: 32px;
		opacity: 0.8;
	}
`;

const SearchContainer = styled.div`
	display: flex;
	align-items: center;
	margin-right: 4px;
	position: relative;
`;

const SearchIconButton = styled.button`
	background: transparent;
	padding: 6px;
	border: none;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--analog-custom-library-btn-text);
	transition: background 0.2s ease;

	&:hover,
	&:focus {
		outline: none;
	}

	svg {
		width: 16px;
		height: 16px;
	}
`;

const SearchBarWrapper = styled.div`
	display: flex;
	align-items: center;
	border-radius: 4px;
	padding: 0;
	height: 32px;
	width: 250px;
	animation: ${expandWidth} 0.2s ease-out forwards;

	${props => props.$isClosing && css`
		animation: ${collapseWidth} 0.2s ease-out forwards;
	`}
`;

const SearchInput = styled.input`
	flex: 1;
	background-color: rgba(0, 0, 0, 0.15) !important;
	border: none;
	outline: none;
	color: var(--analog-custom-library-top-header-text) !important;
	font-size: 14px;
	font-weight: 400;
	line-height: 1;
	padding: 10px 12px;
	width: 100%;

	&::placeholder {
		color: rgba(255, 255, 255, 0.6);
	}
`;

const SearchIcon = styled.span`
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--analog-custom-library-btn-text);
	opacity: 0.7;

	svg {
		width: 14px;
		height: 14px;
	}
`;

const CloseButton = styled.button`
	margin-left: 14px;
	background: transparent;
	border: none;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--analog-custom-library-btn-text);
	padding: 0;
	border-radius: 2px;
	transition: background 0.2s ease;

	&:hover,
	&:focus {
		outline: none;
	}

	svg {
		width: 10px;
		height: 10px;
		fill: currentColor;
	}
`;

const HeaderSearch = () => {
	const context = useContext( AnalogContext );
	const [ isExpanded, setIsExpanded ] = useState( false );
	const [ isClosing, setIsClosing ] = useState( false );
	const [ searchValue, setSearchValue ] = useState( '' );
	const inputRef = useRef( null );

	// Sync with context search state
	useEffect( () => {
		setSearchValue( context.state.blocksSearchInput || '' );
	}, [ context.state.blocksSearchInput ] );

	const handleExpand = () => {
		setIsExpanded( true );
		setIsClosing( false );
		// Focus input after animation
		setTimeout( () => {
			if ( inputRef.current ) {
				inputRef.current.focus();
			}
		}, 100 );
	};

	const handleClose = () => {
		setIsClosing( true );
		// Clear search when closing
		setSearchValue( '' );
		context.handleSearch( '' );
		// Collapse after animation
		setTimeout( () => {
			setIsExpanded( false );
			setIsClosing( false );
		}, 200 );
	};

	const handleInputChange = ( e ) => {
		const value = e.target.value;
		setSearchValue( value );
		context.handleSearch( value );
	};

	const handleKeyDown = ( e ) => {
		if ( e.key === 'Escape' ) {
			handleClose();
		}
	};

	if ( ! isExpanded ) {
		return (
			<SearchContainer>
				<SearchIconButton
					onClick={ handleExpand }
					aria-label={ __( 'Search templates', 'analogwp-library' ) }
					title={ __( 'Search templates', 'analogwp-library' ) }
				>
					<Search />
				</SearchIconButton>
			</SearchContainer>
		);
	}

	return (
		<SearchContainer>
			<SearchBarWrapper $isClosing={ isClosing }>
				{! isExpanded && (
					<SearchIcon>
						<Search />
					</SearchIcon>
				)}
				<SearchInput
					ref={ inputRef }
					type="text"
					value={ searchValue }
					onChange={ handleInputChange }
					onKeyDown={ handleKeyDown }
					placeholder={ __( 'Search templates...', 'analogwp-library' ) }
					aria-label={ __( 'Search templates', 'analogwp-library' ) }
				/>
				<CloseButton
					onClick={ handleClose }
					aria-label={ __( 'Close search', 'analogwp-library' ) }
					title={ __( 'Close search', 'analogwp-library' ) }
				>
					<XMark
						className="icons"
					/>
				</CloseButton>
			</SearchBarWrapper>
		</SearchContainer>
	);
};

export default HeaderSearch;
