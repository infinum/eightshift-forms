import { CONDITIONAL_TAGS_OPERATORS, CONDITIONAL_TAGS_ACTIONS, CONDITIONAL_TAGS_LOGIC } from '../../form/assets/utilities';

/**
 * Data set used for testing.
 */
const data = {
	"first_name": [
		CONDITIONAL_TAGS_ACTIONS.HIDE,
		CONDITIONAL_TAGS_LOGIC.ALL,
		[
			[
				"email",
				CONDITIONAL_TAGS_OPERATORS.IS,
				"ivan@gmail.com"
			],
			[
				"last_name",
				CONDITIONAL_TAGS_OPERATORS.IS,
				"ivan"
			],
		]
	],

	// Checkbox test.
	"question_29244283": [ // education.
		CONDITIONAL_TAGS_ACTIONS.HIDE,
		CONDITIONAL_TAGS_LOGIC.ANY,
		[
			[
				"question_29244288", // gdpr - checkbox.
				CONDITIONAL_TAGS_OPERATORS.IS,
				"true"
			],
		]
	],

	// Select test.
	"question_29244285": [ // links.
		CONDITIONAL_TAGS_ACTIONS.HIDE,
		CONDITIONAL_TAGS_LOGIC.ANY,
		[
			[
				"question_29244287", // how did you hear.
				CONDITIONAL_TAGS_OPERATORS.IS,
				"101721610"
			],
		]
	],
};

export const CONDITIONAL_TAGS_TEST_DATA = JSON.stringify(data);
