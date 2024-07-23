import { __ } from '@wordpress/i18n';
import globalManifest from './../../../manifest.json';

export const CONDITIONAL_TAGS_OPERATORS_LABELS = {
	[globalManifest.comparator.IS]: __('is', 'eightshift-forms'),
	[globalManifest.comparator.ISN]: __('is not', 'eightshift-forms'),
	[globalManifest.comparator.GT]: __('greater than', 'eightshift-forms'),
	[globalManifest.comparator.GTE]: __('greater than or equal', 'eightshift-forms'),
	[globalManifest.comparator.LT]: __('less than', 'eightshift-forms'),
	[globalManifest.comparator.LTE]: __('less than or equal', 'eightshift-forms'),
	[globalManifest.comparator.C]: __('contains', 'eightshift-forms'),
	[globalManifest.comparator.SW]: __('starts with', 'eightshift-forms'),
	[globalManifest.comparator.EW]: __('ends with', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_OPERATORS_EXTENDED_LABELS = {
	[globalManifest.comparatorExtended.B]: __('in range', 'eightshift-forms'),
	[globalManifest.comparatorExtended.BS]: __('in range (strict)', 'eightshift-forms'),
	[globalManifest.comparatorExtended.BN]: __('not in range', 'eightshift-forms'),
	[globalManifest.comparatorExtended.BNS]: __('not in range (strict)', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_LABELS = {
	[globalManifest.comparatorActions.SHOW]: __('visible', 'eightshift-forms'),
	[globalManifest.comparatorActions.HIDE]: __('hidden', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_INVERSE_LABELS = {
	[globalManifest.comparatorActions.SHOW]: __('Hide', 'eightshift-forms'),
	[globalManifest.comparatorActions.HIDE]: __('Show', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_ACTIONS_SHORT_LABELS = {
	[globalManifest.comparatorActions.SHOW]: __('show', 'eightshift-forms'),
	[globalManifest.comparatorActions.HIDE]: __('hide', 'eightshift-forms'),
};

export const CONDITIONAL_TAGS_LOGIC_LABELS = {
	[globalManifest.comparatorLogic.OR]: __('or', 'eightshift-forms'),
	[globalManifest.comparatorLogic.AND]: __('and', 'eightshift-forms'),
};
