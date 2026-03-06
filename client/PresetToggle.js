import ThemeToggle from './icons/theme-toggle';

const { __ } = wp.i18n;
const { Button } = wp.components;
const { useState, useEffect, useRef, useCallback } = wp.element;

const CLASS_PREFIX = 'analog-custom-library-';
const PRESET_CLASSES = [ CLASS_PREFIX + 'light-preset', CLASS_PREFIX + 'dark-preset', CLASS_PREFIX + 'color-preset' ];

const PRESETS = [
	{ value: 'default', label: __( 'Default', 'analogwp-library' ) },
	{ value: 'light', label: __( 'Light', 'analogwp-library' ) },
	{ value: 'dark', label: __( 'Dark', 'analogwp-library' ) },
	{ value: 'color', label: __( 'Color', 'analogwp-library' ) },
];

/**
 * Get the initial preset value from the library_style_preset setting.
 * Always resets to this on mount (never persists user toggle).
 */
export const getDefaultPreset = () => {
	return ( window.AGWP_LIBRARY && window.AGWP_LIBRARY.libraryStylePreset ) || 'default';
};

/**
 * Apply the preset class on the body element.
 */
export const applyPresetClass = ( preset ) => {
	// Remove all preset classes first.
	PRESET_CLASSES.forEach( ( cls ) => document.body.classList.remove( cls ) );
	// Add the new one (default = no class).
	if ( preset !== 'default' ) {
		document.body.classList.add( CLASS_PREFIX + preset + '-preset' );
	}
};

const PresetToggle = () => {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ activePreset, setActivePreset ] = useState( getDefaultPreset );
	const wrapperRef = useRef( null );

	// Apply the default preset class on mount.
	useEffect( () => {
		applyPresetClass( activePreset );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Close popup on outside click.
	const handleOutsideClick = useCallback( ( e ) => {
		if ( wrapperRef.current && ! wrapperRef.current.contains( e.target ) ) {
			setIsOpen( false );
		}
	}, [] );

	useEffect( () => {
		if ( isOpen ) {
			document.addEventListener( 'mousedown', handleOutsideClick );
		}
		return () => {
			document.removeEventListener( 'mousedown', handleOutsideClick );
		};
	}, [ isOpen, handleOutsideClick ] );

	const handleSelect = ( preset ) => {
		setActivePreset( preset );
		applyPresetClass( preset );
		setIsOpen( false );
	};

	// Only show when style mode is 'preset'.
	const styleMode = ( window.AGWP_LIBRARY && window.AGWP_LIBRARY.libraryStyleMode ) || 'preset';
	if ( styleMode !== 'preset' ) {
		return null;
	}

	return (
		<div className="preset-toggle-wrapper" ref={ wrapperRef }>
			<Button
				className="preset-toggle-btn"
				onClick={ () => setIsOpen( ! isOpen ) }
				aria-label={ __( 'Switch style preset', 'analogwp-library' ) }
			>
				<ThemeToggle />
			</Button>
			{ isOpen && (
				<div className="preset-toggle-popup">
					{ PRESETS.map( ( preset ) => (
						<button
							key={ preset.value }
							className={ 'preset-toggle-option' + ( activePreset === preset.value ? ' is-active' : '' ) }
							onClick={ () => handleSelect( preset.value ) }
						>
							<span className="filter-icon"><span className="circle"></span></span>
							{ preset.label }
						</button>
					) ) }
				</div>
			) }
		</div>
	);
};

export default PresetToggle;
