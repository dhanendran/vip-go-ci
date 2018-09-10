<?php

/*
 * Process all files in the PRs
 * involved with the commit specified.
 *
 * This function will add to an array
 * of auto-approvable files any files that
 * fit the criteria of having certain file-endings.
 * The allowable file-endings are specifiable
 * via the command-line.
 *
 * Note that the --skip-folders argument is ignored
 * in this function.
 */

function vipgoci_ap_file_types(
		$options,
		&$auto_approved_files_arr
	) {

	vipgoci_runtime_measure( 'start', 'ap_file_types' );

	vipgoci_log(
		'Doing auto-approval scanning based on file-types',
		array(
			'repo_owner'	=> $options['repo-owner'],
			'repo_name'	=> $options['repo-name'],
			'commit_id'	=> $options['commit'],
			'autoapprove'	=> $options['autoapprove'],

			'autoapprove-filetypes' =>
				$options['autoapprove-filetypes'],
		)
	);


	$prs_implicated = vipgoci_github_prs_implicated(
		$options['repo-owner'],
		$options['repo-name'],
		$options['commit'],
		$options['token'],
		$options['branches-ignore']
	);


	foreach ( $prs_implicated as $pr_item ) {
		$pr_diff = vipgoci_github_diffs_fetch(
			$options['repo-owner'],
			$options['repo-name'],
			$options['token'],
			$pr_item->base->sha,
			$options['commit']
		);


		foreach ( $pr_diff as
			$pr_diff_file_name => $pr_diff_contents
		) {
			$pr_diff_file_extension = pathinfo(
				$pr_diff_file_name,
				PATHINFO_EXTENSION
			);

			/*
			 * If the file is already in the array
			 * of approved file, do not do anything.
			 */
			if ( isset(
				$auto_approved_files_arr[
					$pr_diff_file_name
				]
			) ) {
				continue;
			}

			/*
			 * Check if the extension of the file
			 * is in a list of auto-approvable
			 * file extensions.
			 */
			if ( in_array(
				strtolower(
					$pr_diff_file_extension
				),
				$options['autoapprove-filetypes'],
				true
			) ) {
				$auto_approved_files_arr[
					$pr_diff_file_name
				] = 'autoapprove-filetypes';
			}
		}
	}

	/*
	 * Reduce memory-usage as possible
	 */
	unset( $prs_implicated );
	unset( $pr_diff );
	unset( $pr_item );
	unset( $pr_diff_file_extension );
	unset( $pr_diff_file_name );

	gc_collect_cycles();

	vipgoci_runtime_measure( 'stop', 'ap_file_types' );
}

