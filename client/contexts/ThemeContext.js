const ThemeContext = React.createContext();

export const ThemeProvider = ThemeContext.Provider;
export const ThemeConsumer = ThemeContext.Consumer;

export const Theme = {
	accent: 'var(--analog-custom-library-primary)',
	textLight: 'var(--analog-custom-library-sec-text)',
	textDark: 'var(--analog-custom-library-main-text)',
	lightGray: '#F2F2F2',
};

export default ThemeContext;
