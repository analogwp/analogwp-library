import { default as styled, keyframes } from 'styled-components';
import ThemeContext from './contexts/ThemeContext';
import Synchronization from './Synchronization';

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
	border-bottom: 1px solid #DFDFDF;
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
		color: #fff;
	}

	a {
		color: #fff;
	}

	svg {
		vertical-align: bottom;
	}

	.button-plain {
		color: #fff !important;
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
`;

const Header = () => {
	const { theme } = React.useContext( ThemeContext );

	return (
		<Container theme={ theme }>
			<div className="analog-custom-library-container">
				<div className="logo">
					<h2>Library</h2>
				</div>
				<Synchronization />
			</div>
		</Container>
	);
};

export default Header;
