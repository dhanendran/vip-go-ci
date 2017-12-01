<?php


/*
 * Determine if repository specified is in
 * sync with the commit-ID specified.
 *
 * If it is not in sync, exit with error.
 */

function vipgoci_gitrepo_ok(
	$commit_id,
	$local_git_repo
) {

	/*
	 * Check at what revision the local git repository is.
	 *
	 * We do this to make sure the local repository
	 * is actually checked out at the same commit
	 * as the one we are working with.
	 */
	$lgit_head = @file_get_contents(
		$local_git_repo . '/.git/HEAD'
	);

	$lgit_branch_ref = false;


	/*
	 * Check if we successfully got any information
	 */

	if ( false !== $lgit_head ) {
		// We might have gotten a reference, work with that
		if ( strpos( $lgit_head, 'ref: ') === 0 ) {
			$lgit_branch_ref = substr(
				$lgit_head,
				5
			);

			$lgit_branch_ref = rtrim(
				$lgit_branch_ref
			);

			$lgit_head = false;
		}
	}


	/*
	 * If we have not established a head,
	 * but we have a reference, try to get the
	 * head
	 */
	if (
		( false === $lgit_head ) &&
		( false !== $lgit_branch_ref )
	) {
		$lgit_head = @file_get_contents(
			$local_git_repo . '/.git/' . $lgit_branch_ref
		);

		/*
		 * Still no success? Use the name of the branch,
		 * and see if we have a ref in '.git/refs/remotes/origin/'
		 */
		if ( false === $lgit_head ) {
			$tmp_ref_name_pos = strrpos(
				$lgit_branch_ref,
				'/'
			);

			$lgit_branch_ref = substr(
				$lgit_branch_ref,
				$tmp_ref_name_pos
			);

			unset( $tmp_ref_name_pos );

			$lgit_head = @file_get_contents(
				$local_git_repo .
					'/.git/refs/remotes/origin/' .
					$lgit_branch_ref
			);
		}

		$lgit_branch_ref = false;
	}


	/*
	 * Trim any whitespace characters away
	 */
	if ( false !== $lgit_head ) {
		$lgit_head = trim(
			$lgit_head
		);
	}


	/*
	 * Check if commit-ID and head are the same, and
	 * return with a status accordingly.
	 */

	if (
		( false !== $commit_id ) &&
		( $commit_id !== $lgit_head )
	) {
		vipgoci_log(
			'Can not use local Git repository, seems not to be in ' .
			'sync with current commit or does not exist',
			array(
				'commit_id'		=> $commit_id,
				'local_git_repo'	=> $local_git_repo,
				'local_git_repo_head'	=> $lgit_head,
			)
		);

		exit ( 253 );

	}

	return true;
}


/*
 * Fetch "tree" of the repository; a tree
 * of files that are part of the commit
 * specified.
 *
 * Allows filtering out files that the
 * caller does only want to see.
 */

function vipgoci_gitrepo_fetch_tree(
	$options,
	$commit_id,
	$filter = null
) {

	/* Check for cached version */
	$cached_id = array(
		__FUNCTION__, $options['repo-owner'], $options['repo-name'],
		$commit_id, $options['token'], $filter
	);

	$cached_data = vipgoci_cache( $cached_id );

	vipgoci_log(
		'Fetching tree info' .
			( $cached_data ? ' (cached)' : '' ),

		array(
			'repo_owner' => $options['repo-owner'],
			'repo_name' => $options['repo-name'],
			'commit_id' => $commit_id,
			'filter' => $filter,
		)
	);

	if ( false !== $cached_data ) {
		return $cached_data;
	}


	/*
	 * Use local git repository
	 */

	vipgoci_gitrepo_ok(
		$commit_id,
		$options['local-git-repo']
	);

	// Actually get files
	$files_arr = vipgoci_scandir_git_repo(
		$options['local-git-repo'],
		$filter
	);


	/*
	 * Cache the results and return
	 */
	vipgoci_cache(
		$cached_id,
		$files_arr
	);

	return $files_arr;
}


/*
 * Fetch from the local git-repository a particular file
 * which is a part of a commit. Will return the file (raw),
 * or false on error.
 */

function vipgoci_gitrepo_fetch_committed_file(
	$repo_owner,
	$repo_name,
	$github_token,
	$commit_id,
	$file_name,
	$local_git_repo
) {

	vipgoci_gitrepo_ok(
		$commit_id, $local_git_repo
	);

	vipgoci_log(
		'Fetching file-contents from local Git repository',
		array(
			'repo_owner'		=> $repo_owner,
			'repo_name'		=> $repo_name,
			'commit_id'		=> $commit_id,
			'filename'		=> $file_name,
			'local_git_repo'	=> $local_git_repo,
		)
	);


	/*
	 * If everything seems fine, return the file.
	 */

	$file_contents_tmp = @file_get_contents(
		$local_git_repo . '/' . $file_name
	);

	return $file_contents_tmp;
}


