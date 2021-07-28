import React from 'react'; // eslint-disable-line no-unused-vars
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import { withSelect } from '@wordpress/data';

export const postTypes = {
	post: 'post',
	page: 'page',
};

export const PostSelect = withSelect((select, props) => {

	const {
		type,
		taxonomy = '',
		taxonomySlug = '',
	} = props;
	const query = {
		per_page: -1, // eslint-disable-line camelcase
		orderby: 'date',
		order: 'asc',
		status: 'publish',
	};

	// Set custom taxonomy if required.
	if (taxonomy) {
		const taxonomies = select('core').getEntityRecords('taxonomy', taxonomy);

		if (Array.isArray(taxonomies)) {
			taxonomies.forEach((singleTax) => {
				if (singleTax && singleTax.slug && singleTax.slug === taxonomySlug) {
					query[taxonomy] = singleTax.value;
				}
			});
		}
	}

	// In order to allow selection of multiple post types, we need to concatenate all results.
	let posts = [];
	if (Array.isArray(type)) {
		type.forEach((singleType) => {
			const newPosts = select('core').getEntityRecords('postType', singleType, query);
			if (Array.isArray(newPosts)) {
				posts = [...posts, ...newPosts];
			}
		});
	} else {
		posts = select('core').getEntityRecords('postType', type, query);
	}

	return {
		props,
		posts,
	};
})(({ props, posts }) => {

	const {
		selectedPostId,
		onChange,
	} = props;

	// Because of the lifecycle this might be called before posts is set. In that case we need to make sure posts is set before we map it.
	// there's probably a better solution to do this /shrug.
	const postValues = posts ? [
		{
			value: 0,
			label: __('Select form', 'eightshift-forms'),
		},
		...posts.map((post) => {
			return {
				value: post.id, // This is required for SelectControl
				label: post.title.raw,
			};
		})] : [];

	return (
		<SelectControl
			value={selectedPostId}
			onChange={onChange}
			options={postValues}
		/>
	);
});
