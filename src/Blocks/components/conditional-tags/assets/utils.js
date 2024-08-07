/**
 * Conditional tags operators constants.
 *
 * is  - is                  - if value is exact match.
 * isn - is not              - if value is not exact match.
 * gt  - greater than        - if value is greater than.
 * gte - greater/equal than  - if value is greater/equal than.
 * lt  - less than           - if value is less than.
 * lte - less/equal than     - if value is less/equal than.
 * c   - contains            - if value contains value.
 * sw  - starts with         - if value starts with value.
 * ew  - ends with           - if value starts with value.
 */
export const CONDITIONAL_TAGS_OPERATORS = {
	IS: 'is',
	ISN: 'isn',
	GT: 'gt',
	GTE: 'gte',
	LT: 'lt',
	LTE: 'lte',
	C: 'c',
	SW: 'sw',
	EW: 'ew',
};

/**
 * Conditional tags operators constants - extended.
 *
 * b   - between range                    - if value is between two values.
 * bs  - between range strict             - if value is between two values strict.
 * bn  - not between range                - if value is not between two values.
 * bns - not between between range strict - if value is not between two values strict.
 */
export const CONDITIONAL_TAGS_OPERATORS_EXTENDED = {
	B: 'b',
	BS: 'bs',
	BN: 'bn',
	BNS: 'bns',
};

/**
 * Conditional tags actions constants.
 *
 * show - show item if conditions is set, hidden by default.
 * hide - hide item if conditions is set, visible by default.
 */
export const CONDITIONAL_TAGS_ACTIONS = {
	SHOW: 'show',
	HIDE: 'hide',
};

/**
 * Conditional tags logic constants.
 *
 * or - activate condition if at least one condition in rules array is met.
 * and - activate condition if all conditions in rules array are met.
 */
export const CONDITIONAL_TAGS_LOGIC = {
	OR: 'or',
	AND: 'and',
};
