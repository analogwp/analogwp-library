import classNames from 'classnames';
import AnalogContext from './AnalogContext';
import { NotificationConsumer } from './Notifications';
import XMark from './icons/x-mark';
import Refresh from "./icons/refresh";

const { __ } = wp.i18n;
const { Button } = wp.components;

const Synchronization = () => {
	return (
		<div className="actions">
			<AnalogContext.Consumer>
				{ context => (
					<NotificationConsumer>
						{ ( { add } ) => (
							<Button
								className={ classNames( 'analog-custom-library-sync', {
									'is-active': context.state.syncing,
								} ) }
								onClick={ e => {
									e.preventDefault();
									context.forceRefresh()
										.then( () => add( __( 'Library is now synced', 'custom-library-for-elementor' ) ) )
										.catch( () => add( __( 'Something is not right, please try again.', 'custom-library-for-elementor' ), 'error' ) );
								} }
							>
								{ context.state.syncing ?
									<Refresh  /> :
									<Refresh /> }
								{ /*<Refresh />*/ }
							</Button>
						) }
					</NotificationConsumer>
				) }
			</AnalogContext.Consumer>
			{ ! AGWP_LIBRARY.is_settings_page && (
				<Button className="close-modal">
					<XMark
						className="icons"
					/>
				</Button>
			) }
		</div>
	);
};

export default Synchronization;
