<?php
/**
 * EventON Lite Uninstall
 *
 * Uninstalling EventON deletes everything.
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON lite/Uninstaller
 * @version     2.5
 */
if(defined( 'WP_UNINSTALL_PLUGIN' )){
	$evo_opt = get_option('evcal_options_evcal_1');


	// If options have been stored, and 'evo_delete_settings' is equal to 'yes' delete
	if(!empty($evo_opt['evo_delete_settings']) && $evo_opt['evo_delete_settings']=='yes'){

		global $wpdb, $wp_roles;

		// Delete pages
		wp_trash_post( get_option( 'evo_event_archive_page_id' ) );

		// Delete options
		$wpdb->query("DELETE FROM $wpdb->options WHERE 
			option_name LIKE '%evcal_%' 
			OR option_name LIKE '%_evo_%'
			OR option_name LIKE '%eventon_%';");

		// Delete posts + data.
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'ajde_events', 'evo-reviews', 'evo-rsvp', 'evo-tix', 'evo-subscriber' );" );
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

		// Delete term taxonomies
		foreach ( array( 'event_type', 'event_type_2', 'event_type_3', 'event_location','event_organizer' ) as $taxonomy ) {
			$wpdb->delete(
				$wpdb->term_taxonomy,
				array(
					'taxonomy' => $taxonomy,
				)
			);
		}

		// Delete orphan term meta
		if ( ! empty( $wpdb->termmeta ) ) {
			$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;" );
		}

		// delete capabilities
		// Get the list of capabilities that were added
		$capabilities = eventon_get_core_capabilities(); 
		if ( ! function_exists( 'eventon_get_core_capabilities' ) ) {
		    $capabilities = array(
		        'core' => array( 'manage_eventon' ),
		        'eventon' => array(
		            'publish_eventon',
		            'publish_eventons',
		            'edit_eventon',
		            'edit_eventons',
		            'edit_others_eventons',
		            'edit_private_eventons',
		            'edit_published_eventons',
		            'read_eventons',
		            'read_private_eventons',
		            'delete_eventon',
		            'delete_eventons',
		            'delete_private_eventons',
		            'delete_published_eventons',
		            'delete_others_eventons',
		            'assign_eventon_terms',
		            'manage_eventon_terms',
		            'edit_eventon_terms',
		            'delete_eventon_terms',
		            'upload_files'
		        )
		    );
		}

		// Get the administrator role object
		$role = get_role( 'administrator' );

		if ( $role ) {
		    foreach ( $capabilities as $cap_group ) {
		        foreach ( $cap_group as $cap ) {
		            $role->remove_cap( $cap );
		        }
		    }
		}

		wp_cache_flush();
	}

	
}