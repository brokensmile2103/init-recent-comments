/**
 * Init Recent Comments — Block Editor integration.
 *
 * Viết bằng vanilla JS (không JSX, không build step) để deploy trực tiếp lên
 * SVN của WordPress.org mà không cần Node/webpack. Mỗi block dùng
 * ServerSideRender để xem trước, và PHP render.php tương ứng (đăng ký qua
 * "render" trong block.json) để xuất HTML — dùng lại 100% logic shortcode
 * đã có, không lặp lại code.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ServerSideRender = wp.serverSideRender;

	function toInt( value, fallback ) {
		var parsed = parseInt( value, 10 );
		return isNaN( parsed ) ? fallback : parsed;
	}

	// Best-effort helper: while editing, default the user-scoped block
	// preview to the currently logged-in editor's own user ID, so the
	// preview isn't empty before the user picks someone in the sidebar.
	// Never overwrites the saved attribute.
	function getCurrentUserIdForPreview() {
		if ( ! wp.data || ! wp.data.select || ! wp.data.select( 'core' ) ) {
			return 0;
		}
		var user = wp.data.select( 'core' ).getCurrentUser();
		return user && user.id ? user.id : 0;
	}

	function numberControl( label, value, onChange ) {
		return el( TextControl, {
			label: label,
			type: 'number',
			value: value,
			onChange: function ( v ) {
				onChange( toInt( v, 0 ) );
			},
		} );
	}

	function themeControl( attributes, setAttributes ) {
		return el( SelectControl, {
			label: __( 'Theme', 'init-recent-comments' ),
			value: attributes.theme,
			options: [
				{ label: __( 'Default', 'init-recent-comments' ), value: '' },
				{ label: __( 'Dark', 'init-recent-comments' ), value: 'dark' },
			],
			onChange: function ( value ) {
				setAttributes( { theme: value } );
			},
		} );
	}

	function maxHeightControl( attributes, setAttributes ) {
		return el( TextControl, {
			label: __( 'Max Height (e.g. 300px)', 'init-recent-comments' ),
			value: attributes.maxHeight,
			onChange: function ( value ) {
				setAttributes( { maxHeight: value } );
			},
		} );
	}

	function pagedControl( attributes, setAttributes ) {
		return numberControl(
			__( 'Page Number (0 = first page)', 'init-recent-comments' ),
			attributes.paged,
			function ( value ) {
				setAttributes( { paged: value } );
			}
		);
	}

	// ---------------------------------------------------------------------
	// init-recent-comments/recent-comments
	// ---------------------------------------------------------------------
	registerBlockType( 'init-recent-comments/recent-comments', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Recent Comments Settings', 'init-recent-comments' ), initialOpen: true },
						numberControl(
							__( 'Number of Comments', 'init-recent-comments' ),
							attributes.number,
							function ( value ) {
								setAttributes( { number: value } );
							}
						),
						pagedControl( attributes, setAttributes ),
						maxHeightControl( attributes, setAttributes ),
						themeControl( attributes, setAttributes )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'init-recent-comments/recent-comments',
						attributes: attributes,
					} )
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	// ---------------------------------------------------------------------
	// init-recent-comments/recent-reviews
	// ---------------------------------------------------------------------
	registerBlockType( 'init-recent-comments/recent-reviews', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Recent Reviews Settings', 'init-recent-comments' ), initialOpen: true },
						numberControl(
							__( 'Number of Reviews', 'init-recent-comments' ),
							attributes.number,
							function ( value ) {
								setAttributes( { number: value } );
							}
						),
						pagedControl( attributes, setAttributes ),
						maxHeightControl( attributes, setAttributes ),
						themeControl( attributes, setAttributes )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'init-recent-comments/recent-reviews',
						attributes: attributes,
					} )
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	// ---------------------------------------------------------------------
	// init-recent-comments/user-recent-comments
	// ---------------------------------------------------------------------
	registerBlockType( 'init-recent-comments/user-recent-comments', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();
			var previewAttributes = Object.assign( {}, attributes );

			if ( ! previewAttributes.userId && ! previewAttributes.userLogin && ! previewAttributes.userEmail ) {
				previewAttributes.userId = getCurrentUserIdForPreview();
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'User Recent Comments Settings', 'init-recent-comments' ), initialOpen: true },
						numberControl(
							__( 'User ID', 'init-recent-comments' ),
							attributes.userId,
							function ( value ) {
								setAttributes( { userId: value } );
							}
						),
						el( TextControl, {
							label: __( 'Username (used if User ID is 0)', 'init-recent-comments' ),
							value: attributes.userLogin,
							onChange: function ( value ) {
								setAttributes( { userLogin: value } );
							},
						} ),
						el( TextControl, {
							label: __( 'Email (used if User ID and Username are empty)', 'init-recent-comments' ),
							value: attributes.userEmail,
							onChange: function ( value ) {
								setAttributes( { userEmail: value } );
							},
						} ),
						numberControl(
							__( 'Number of Comments', 'init-recent-comments' ),
							attributes.number,
							function ( value ) {
								setAttributes( { number: value } );
							}
						),
						pagedControl( attributes, setAttributes ),
						maxHeightControl( attributes, setAttributes ),
						themeControl( attributes, setAttributes )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'init-recent-comments/user-recent-comments',
						attributes: previewAttributes,
					} )
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	// ---------------------------------------------------------------------
	// init-recent-comments/user-recent-reviews
	// ---------------------------------------------------------------------
	registerBlockType( 'init-recent-comments/user-recent-reviews', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();
			var previewAttributes = Object.assign( {}, attributes );

			if ( ! previewAttributes.userId ) {
				previewAttributes.userId = getCurrentUserIdForPreview();
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'User Recent Reviews Settings', 'init-recent-comments' ), initialOpen: true },
						numberControl(
							__( 'User ID', 'init-recent-comments' ),
							attributes.userId,
							function ( value ) {
								setAttributes( { userId: value } );
							}
						),
						numberControl(
							__( 'Number of Reviews', 'init-recent-comments' ),
							attributes.number,
							function ( value ) {
								setAttributes( { number: value } );
							}
						),
						el( TextControl, {
							label: __( 'Status', 'init-recent-comments' ),
							value: attributes.status,
							help: __( 'e.g. approved, pending', 'init-recent-comments' ),
							onChange: function ( value ) {
								setAttributes( { status: value } );
							},
						} ),
						pagedControl( attributes, setAttributes ),
						maxHeightControl( attributes, setAttributes ),
						themeControl( attributes, setAttributes )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'init-recent-comments/user-recent-reviews',
						attributes: previewAttributes,
					} )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
