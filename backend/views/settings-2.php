		<div id="tabs-2" class="wrap">
			<?php
			$cmb = new_cmb2_box(
				array(
					'id'         => Y_TEXTDOMAIN . '_options-second',
					'hookup'     => false,
					'show_on'    => array( 'key' => 'options-page', 'value' => array( Y_TEXTDOMAIN ) ),
					'show_names' => true,
					)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'Text', Y_TEXTDOMAIN ),
					'desc'    => __( 'field description (optional)', Y_TEXTDOMAIN ),
					'id'      => '_text-second',
					'type'    => 'text',
					'default' => 'Default Text',
			)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'Color Picker', Y_TEXTDOMAIN ),
					'desc'    => __( 'field description (optional)', Y_TEXTDOMAIN ),
					'id'      => '_colorpicker-second',
					'type'    => 'colorpicker',
					'default' => '#bada55',
			)
			);

			cmb2_metabox_form( Y_TEXTDOMAIN . '_options-second', Y_TEXTDOMAIN . '-settings-second' );
			?>

			<!-- @TODO: Provide other markup for your options page here. -->
		</div>
