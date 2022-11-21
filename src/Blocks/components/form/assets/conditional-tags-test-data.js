import { CONDITIONAL_TAGS_CONSTANTS } from './utilities';

/**
 * Data set used for testing.
 */
const data = {
	"first_name": [
		CONDITIONAL_TAGS_CONSTANTS.HIDE,
		CONDITIONAL_TAGS_CONSTANTS.ALL,
		[
			[
				"email",
				CONDITIONAL_TAGS_CONSTANTS.IS,
				"ivan@gmail.com"
			],
			[
				"last_name",
				CONDITIONAL_TAGS_CONSTANTS.IS,
				"ivan"
			],
		]
	],

	// Checkbox test.
	"question_29244283": [ // education.
		CONDITIONAL_TAGS_CONSTANTS.HIDE,
		CONDITIONAL_TAGS_CONSTANTS.ANY,
		[
			[
				"question_29244288", // gdpr - checkbox.
				CONDITIONAL_TAGS_CONSTANTS.IS,
				"true"
			],
		]
	],

	// Select test.
	"question_29244285": [ // links.
		CONDITIONAL_TAGS_CONSTANTS.HIDE,
		CONDITIONAL_TAGS_CONSTANTS.ANY,
		[
			[
				"question_29244287", // how did you hear.
				CONDITIONAL_TAGS_CONSTANTS.IS,
				"101721610"
			],
		]
	],
};

export const CONDITIONAL_TAGS_TEST_DATA = JSON.stringify(data);
