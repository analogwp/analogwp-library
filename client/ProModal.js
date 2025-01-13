const { ExternalLink, Button } = wp.components;
const { __ } = wp.i18n;

const ProModal = () => (
	<div className="pro-modal-container">
		<p>{ __( 'Get unlimited access to the Custom Library for Elementor library and features with the PRO version.', 'analogwp-library' ) }</p>
		<a href="https://analogwp.com/pricing/?utm_medium=plugin&utm_source=library&utm_campaign=style+kits+pro" target="_blank">{ __( 'View Plans', 'analogwp-library' ) }</a>
	</div>
);

export default ProModal;
