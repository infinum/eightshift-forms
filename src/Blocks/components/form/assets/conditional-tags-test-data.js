import { CONDITIONAL_TAGS } from './utilities';

/**
 * Data set used for testing.
 */
const data = {
	"first_name": [
		CONDITIONAL_TAGS.HIDE,
		CONDITIONAL_TAGS.ALL,
		[
			[
				"email",
				CONDITIONAL_TAGS.IS,
				"ivan@gmail.com"
			],
			[
				"last_name",
				CONDITIONAL_TAGS.IS,
				"ivan"
			],
		]
	],

	// Checkbox test.
	"question_29244283": [ // education.
		CONDITIONAL_TAGS.HIDE,
		CONDITIONAL_TAGS.ANY,
		[
			[
				"question_29244288", // gdpr - checkbox.
				CONDITIONAL_TAGS.IS,
				"true"
			],
		]
	],

	// Select test.
	"question_29244285": [ // links.
		CONDITIONAL_TAGS.HIDE,
		CONDITIONAL_TAGS.ANY,
		[
			[
				"question_29244287", // how did you hear.
				CONDITIONAL_TAGS.IS,
				"101721610"
			],
		]
	],
};

export const CONDITIONAL_TAGS_TEST_DATA = JSON.stringify(data);
