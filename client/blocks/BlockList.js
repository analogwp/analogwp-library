import classnames from 'classnames';
import Masonry from 'react-masonry-css';
import styled, { keyframes } from 'styled-components';
import AnalogContext from '../AnalogContext';
import { NotificationConsumer } from '../Notifications';
import Star from '../icons/star';
import Popup from '../popup';
import Loader from '../icons/loader';
import Download from '../icons/download';
import Empty from '../helpers/Empty';
import Eye from "../icons/eye";
import Pencil from "../icons/pencil";
import Globe from '../icons/globe';


const { decodeEntities } = wp.htmlEntities;
const { __, sprintf } = wp.i18n;
const { Dashicon, Button, Card, CardBody } = wp.components;
const { addQueryArgs } = wp.url;

const rotateOpacity = keyframes`
  0% {
    opacity: 0.7;
  }

  50% {
    opacity: 0.1;
  }

  100% {
    opacity: 0.7;
  }
`;

const LoadingThumbs = styled.div`
	display: flex;
	margin-left: -25px;
	width: auto;

	img[src$="svg"].thumb {
		width: 33.3333%;
		padding-left: 25px;
		background-clip: padding-box;
		max-height: 300px;
		object-fit: cover;
		object-position: top;
		opacity: 0.7;
		transition: all 200ms ease-in-out;
		animation: ${ rotateOpacity } 2s linear infinite;
	}
`;

const Container = styled.div`
	flex: 1;

	.grid {
		display: flex;
		margin-left: -25px; /* gutter size offset */
		width: auto;
	}

	.grid-item {
		padding-left: 25px;
		background-clip: padding-box;

		&:empty {
			display: none;
		}

		> div > div.components-card {
			background: #fff;
			box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.05);
			position: relative;
			margin-bottom: 15px;
		}
	}

	figure {
		position: relative;
		overflow: hidden;
		margin: 0;
		min-height: 150px;
		display: flex;

		&:hover {
			.actions {
				opacity: 1;
				button {
					transform: none;
					opacity: 1;
				}
			}
			.favorite {
				opacity: 1;
			}
		}

		.pattern-title {
			position: absolute;
			bottom: 10px;
			text-align: center;
			width: 100%;
			font-size: 14px !important;
			text-transform: capitalize;
		}

		.actions {
			button {
				transform: translateY(20px);
				opacity: 0;
			}
			.analog-custom-library-promo {
				text-decoration: none;
			}
		}
	}

	.favorite {
		position: absolute;
		top: 0;
		left: 0;
		z-index: 200;
		display: inline-flex;
		justify-content: center;
		align-items: center;
		width: 40px;
		height: 40px;
		box-shadow: none !important;
		outline: none !important;

		&:not(.is-active) {
			opacity: 0;
		}

		&:before {
			content: "";
			width: 0;
			height: 0;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 190;
		}

		svg {
			fill: #fff;
			position: relative;
			z-index: 195;
			width: 17px;
			height: 17px;
		}
		&.is-active svg {
			fill: var(--analog-custom-library-favorites-icon) !important;
			stroke: var(--analog-custom-library-favorites-icon) !important;
		}
	}

	img {
		max-width: 100%;
		height: auto;
		align-self: center;
	}

	img[src$="svg"] {
		width: 100%;
		height: 100%;
		object-fit: cover;
		max-height: 400px;
	}

	h3 {
		margin: 0;
		font-weight: normal;
		font-size: 16px;
		line-height: 21px;
	}

	 .content {
		display: flex;
		justify-content: space-between;
		align-items: center;
	 	margin-bottom: 36px;
	}

	 .components-base-control {
		margin-bottom: 30px;
	}

	.components-text-control__input, .components-text-control__input[type="text"] {
		background-color: #fff;
		color: #060606;
		font-size: 16px;
	}

	.button-plain {
		padding: 0;
		margin: 0;
		border: none;
		border-radius: 0;
		box-shadow: none;
		cursor: pointer;
		appearance: none;
		outline: 0;
		background: transparent;
		font-weight: bold;
		color: var(--analog-custom-library-btn-text);
		font-size: 14.22px;
	}
	.inner-popup-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		position: sticky;
		background: #fff;
	}
	.inner-popup-header h1 {
		font-size: 16px;
		font-weight: bold;
		color: #000000;
		margin: 0;
	}
	.inner-popup-content p {
		font-size: 13px;
		line-height: 18px;
		color: #565d65;
	}
`;

const BlockList = ( { state, importBlock, favorites, makeFavorite } ) => {
	const context = React.useContext( AnalogContext );

	const filteredBlocks = context.state.blocks.filter( block => ! ( AGWP_LIBRARY.license.status !== 'valid' && context.state.showFree && Boolean( block.is_pro ) ) );

	const fallbackImg = AGWP_LIBRARY.pluginURL + 'assets/img/placeholder.svg';

	const isValid = ( isPro ) => ! ( isPro && AGWP_LIBRARY.license.status !== 'valid' );

	// Masonry breakpoints.
	let breakpointColumnsObj = {
		default: 3,
		2000: 3,
		1600: 3,
		1300: 2,
		700: 1,
	};

	if ( '2c' === AGWP_LIBRARY.libraryTemplateCols ) {
		breakpointColumnsObj = {
			default: 2,
			2000: 2,
			1600: 2,
			1300: 2,
			700: 1,
		};
	} else if ( 'auto' === AGWP_LIBRARY.libraryTemplateCols ) {
		breakpointColumnsObj = {
			default: 5,
			2000: 4,
			1600: 3,
			1300: 2,
			700: 1,
		};
	}

	const getScreenshot = ( block ) => {
		const defaultPlaceHolderThumb = AGWP_LIBRARY.libraryPlaceholderImgURL || AGWP_LIBRARY.pluginURL + 'assets/img/placeholder.svg';
		return block.thumbnail || defaultPlaceHolderThumb;
	};

	const loadingThumbs = () => {
		const thumbs = [];
		for ( let i = 1; i <= 3; i++ ) {
			thumbs.push(
				<img
					key={ i }
					className="thumb"
					src={ `${ AGWP_LIBRARY.pluginURL }assets/img/placeholder.svg` }
					alt="Loading icon"
				/>
			);
		}
		return thumbs;
	};

	return (
		<React.Fragment>
			{ state.state.modalActive && (
				<Popup
					title={ decodeEntities( state.state.activeBlock.title ) }
					style={ {
						textAlign: 'center',
					} }
					onRequestClose={ () => {
						state.dispatch( {
							activeBlock: false,
							modalActive: false,
							blockImported: false,
						} );
					} }
				>
					{ ! state.state.blockImported && <Loader /> }
					{ state.state.blockImported && (
						<React.Fragment>
							<p>
								{ sprintf( __( 'The %s has been imported and is now available in the', 'analogwp-library' ), AGWP_LIBRARY.isContainer ? 'container' : 'section' ) }
								{ ' ' }
								<a
									target="_blank"
									rel="noopener noreferrer"
									href={ addQueryArgs( 'edit.php', {
										post_type: 'elementor_library',
										tabs_group: true,
										elementor_library_type: AGWP_LIBRARY.isContainer ? 'container' : 'section',
									} ) }
								>
									{ sprintf( __( 'Elementor %s library', 'analogwp-library' ), AGWP_LIBRARY.isContainer ? 'container' : 'section' ) }
								</a>.
							</p>
							<p>
								<Button
									isPrimary
									onClick={ () => {
										state.dispatch( {
											activeBlock: false,
											modalActive: false,
											blockImported: false,
										} );
									} }
								>
									{ __( 'Ok, thanks', 'analogwp-library' ) } <Dashicon icon="yes" />
								</Button>
							</p>
						</React.Fragment>

					) }
				</Popup>
			) }

			<Container className="blocks-area">

				{ ! context.state.syncing && context.state.blocks.length < 1 && (
					<Empty text={ __( 'No Templates found', 'analogwp-library' ) } />
				) }

				{ context.state.syncing && context.state.blocks.length < 1 && (
					<Empty text={ __( 'Loading Templates...', 'analogwp-library' ) } />
				) }
				<Masonry
					breakpointCols={ breakpointColumnsObj ? breakpointColumnsObj : 3 }
					className="grid"
					columnClassName="grid-item block-list"
				>
					{ filteredBlocks.length >= 1 && filteredBlocks.map( ( block ) => {
						let requiresElementorPro = false;
						if ( block.requiredPluginsrequiredPlugins && block.requiredPlugins.length > 0 ) {
							const unresolvedPlugins = block.requiredPlugins.filter( ( plugin ) => plugin !== '' && ! AGWP_LIBRARY.activePlugins.includes( plugin )
							 );

							// We are intentionally hiding patterns for now.
							if ( unresolvedPlugins.length > 0 && ! unresolvedPlugins.includes( 'elementor-pro' ) ) {
								return null;
							}
							requiresElementorPro = unresolvedPlugins && unresolvedPlugins.includes( 'elementor-pro' );
						}
						return (
							<div key={block.id} className={classnames({ 'is-remote-template': block.is_remote })}>
								<Card>
									<CardBody>
										{block.is_pro && (
											<span className="pro">{__('Pro', 'analogwp-library')}</span>
										)}
										{block.is_remote && (
											<span className="remote-badge" title={block.server_name}><Globe /></span>
										)}

										<figure>
											<img
												src={getScreenshot(block)}
												loading="lazy"
												width="900"
												height="600"
												alt={block.title}
											/>

											<div className="actions">
												{! block.is_remote && <><a href={AGWP_LIBRARY.siteURL + `?post_type=elementor_library&p=${block.id}&preview=true`} target="_blank" className="template-preview-button">
													<Button isPrimary>
														<Eye />
													</Button>
												</a>
												<a href={AGWP_LIBRARY.adminURL + `post.php?post=${block.id}&action=elementor`}
												   target="_blank" className="template-edit-button">
													<Button isPrimary>
														<Pencil/>
													</Button>
												</a></>}
												<NotificationConsumer>
													{({add}) => (
														!requiresElementorPro && isValid(block.is_pro) && (
															<Button isPrimary onClick={() => importBlock(block, add)} className="is-large">
																<Download/>&nbsp;{__('Insert', 'analogwp-library')}
															</Button>
														)
													)}
												</NotificationConsumer>
											</div>
											<button
												className={classnames('button-plain favorite', {
													'is-active': block.id in favorites,
												})}
												onClick={() => makeFavorite(block.id)}
											>
												<Star/>
											</button>
										</figure>
									</CardBody>
								</Card>
								<div className="content">
									<h3>{decodeEntities(block.title)}</h3>
									{block.is_pro && <span className="pro">{__('Pro', 'analogwp-library')}</span>}
								</div>
							</div>
						);
					})}
				</Masonry>
			</Container>
		</React.Fragment>
	);
};

export default BlockList;
