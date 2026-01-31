import { default as styled, keyframes } from 'styled-components';
import ThemeContext from './contexts/ThemeContext';
import Synchronization from './Synchronization';
import SourceFilter from './SourceFilter';
import HeaderSearch from './HeaderSearch';

const rotate = keyframes`
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
`;

const Container = styled.div.attrs({
	className: 'analogwp-header',
})`
	padding: 8px 24px;
	background: var(--analog-custom-library-top-header-bg);
	border-bottom: 1px solid var(--analog-custom-library-top-header-border);
	color: var(--analog-custom-library-top-header-text);

	.analog-custom-library-container {
		display: flex;
	    justify-content: space-between;
	    align-items: center;
	}

	.logo img {
		max-width: 42px;
		max-height: 42px;
	}

	.logo h2 {
		font-size: 16px;
		line-height: 24px;
		font-weight: 700;
		text-transform: uppercase;
		color: var(--analog-custom-library-top-header-text);
	}

	a {
		color: #fff;
	}

	svg {
		vertical-align: bottom;
	}

	.button-plain {
		color: var(--analog-custom-library-btn-text) !important;
		font-weight: bold;
		text-decoration: none;
		display: inline-flex;
		align-items: center;

		&.is-active {
			pointer-events: none;
			svg {
				animation: ${ rotate } 2s linear infinite;
			}
		}

		svg {
			margin-left: 10px;
		}

		&:first-of-type {
			margin-left: auto;
		}
		+ .button-plain {
			position: relative;
			margin-left: 30px;
		}
	}

	.sync {
		text-transform: uppercase;
		font-size: 12.64px !important;
		letter-spacing: 1px;
	}

	.header-actions {
		display: flex;
		align-items: center;
	}
`;

const Header = () => {
	const { theme } = React.useContext( ThemeContext );

	return (
		<Container theme={ theme }>
			<div className="analog-custom-library-container">
				<div className="logo">
					<h2>{ AGWP_LIBRARY?.library_title_text }</h2>
				</div>
				<div className="header-actions">
					<HeaderSearch />
					<SourceFilter />
					<Synchronization />
				</div>
			</div>
		</Container>
	);
};

export default Header;
