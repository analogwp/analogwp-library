import Blocks from './blocks/blocks';

export const getPageComponents = () => {
	return <Blocks />;
};

export const debugMode = () => Boolean( AGWP.debugMode );

export const Log = ( what ) => {
	if ( debugMode ) {
		console.log( what ); // eslint-disable-line
	}
};
export const LogStart = ( title ) => {
	if ( debugMode ) {
		console.group( title ); // eslint-disable-line
	}
};
export const LogEnd = ( title ) => {
	if ( debugMode ) {
		console.groupEnd( title ); // eslint-disable-line
	}
};

export function generateUEID() {
	let first = ( Math.random() * 46656 ) || 0;
	let second = ( Math.random() * 46656 ) || 0;
	first = ( '000' + first.toString( 36 ) ).slice( -3 );
	second = ( '000' + second.toString( 36 ) ).slice( -3 );
	return first + second;
}

export function hasProTemplates( templates ) {
	return templates.some( ( template ) => template.is_pro === true );
}


export function isNewTheme( date ) {
	const start = moment.unix( date );
	const end = moment.now();
	return Math.ceil( moment.duration( start.diff( end ) ).asDays() );
}

export function getTime( date ) {
	const start = moment.unix( date );
	const end = moment.now();
	return Math.ceil( moment.duration( start.diff( end ) ).asMinutes() );
}
