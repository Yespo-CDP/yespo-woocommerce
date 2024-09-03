		<div id="tabs-2" class="wrap">
			<?php
			$cmb = new_cmb2_box(
				array(
					'id'         => 'yespo-cdp-plugin' . '_options-second',
					'hookup'     => false,
					'show_on'    => array( 'key' => 'options-page', 'value' => array( 'yespo-cdp-plugin' ) ),
					'show_names' => true,
					)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'Text', 'yespo-cdp-plugin' ),
					'desc'    => __( 'field description (optional)', 'yespo-cdp-plugin' ),
					'id'      => '_text-second',
					'type'    => 'text',
					'default' => 'Default Text',
			)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'Color Picker', 'yespo-cdp-plugin' ),
					'desc'    => __( 'field description (optional)', 'yespo-cdp-plugin' ),
					'id'      => '_colorpicker-second',
					'type'    => 'colorpicker',
					'default' => '#bada55',
			)
			);

			cmb2_metabox_form( 'yespo-cdp-plugin' . '_options-second', 'yespo-cdp-plugin' . '-settings-second' );
			?>

			<!-- @TODO: Provide other markup for your options page here. -->
		</div>
