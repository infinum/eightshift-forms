<?php

/**
 * Data provider for Greenhouse integration.
 */

namespace EightshiftFormsTests\Integrations\Greenhouse;

class DataProvider
{

	const MOCKED_GH_BOARD_TOKEN = "token";
	const MOCKED_GH_JOB_BOARD_URL = "https://www.eightshift.com/";

	const MOCKED_GH_APPLICATION_SUCCESS_URL = "success";
	const MOCKED_GH_APPLICATION_ERROR_MISSING_FIRST_NAME_URL = "error_first_name";
	const MOCKED_GH_APPLICATION_ERROR_MISSING_LAST_NAME_URL = "error_last_name";
	const MOCKED_GH_APPLICATION_ERROR_MISSING_EMAIL_URL = "error_email";
	const MOCKED_GH_APPLICATION_ERROR_MISSING_MULTIPLE_URL = "error_multiple";
	const MOCKED_GH_APPLICATION_ERROR_MISSING_JOB_ID_URL = "error_missing_job_id";
	const MOCKED_GH_APPLICATION_ERROR_URL = "error";

	const MOCKED_GH_JOB_SUCCESS_ID = "1072422";

	/**
	 * Example of successful transaction Buckaroo response.
	 *
	 * @return array
	 */
	public static function greenhouseApplicationParams(): array
	{
		return [
			'job_id' => self::MOCKED_GH_JOB_SUCCESS_ID,
			'first_name' => 'Ivan Novi15',
			'last_name' => 'Ružević TEST 15',
			'email' => 'ivan.ruzevic.test15@infinum.com',
			'phone' => '33377788',
			'location' => '110 5th Ave New York, NY, 10',
			'latitude' => '40.7376671',
			'longitude' => '-73.9929196',
			'resume_text' => 'I have many years of experience as an expert asdasfbasket weaver...',
			'cover_letter_text' => 'I have a very particular set of fffskills, skills I have acquired over a very long career. Skills that make me...',
			'gender' => '3',
			'race' => '1',
			'question_19459869' => 'ivan1322',
			'question_19459870' => 'novi131',
			'question_19459871' => 'zadnji2132',
			'question_26417193' => '1',
			'question_26417615' => '75573977',
		];
	}

	/**
	 * Example of sucesful files.
	 *
	 * @return array
	 */
	public static function greenhouseApplicationFiles(): array
	{
		return [
			'resume' => [
				'name' => '04 copy.pdf',
				'type' => 'application/pdf',
				'tmp_name' => '/private/var/tmp/php5188hN',
				'error' => 0,
				'size' => 302598,
			],
			'cover_letter' => [
				'name' => 'File (1).pdf',
				'type' => 'application/pdf',
				'tmp_name' => '/private/var/tmp/php95l375',
				'error' => 0,
				'size' => 930663,
			],
		];
	}

	public static function greenhouseApplicationUrl($postID) {
		$token = self::MOCKED_GH_BOARD_TOKEN;
		$url = self::MOCKED_GH_JOB_BOARD_URL;

		return "{$url}boards/{$token}/jobs/{$postID}";
	}

	public static function greenhouseJobsUrl() {
		$token = self::MOCKED_GH_BOARD_TOKEN;
		$url = self::MOCKED_GH_JOB_BOARD_URL;

		return "{$url}boards/{$token}/jobs";
	}

	public static function greenhouseJobUrl($postID) {
		$token = self::MOCKED_GH_BOARD_TOKEN;
		$url = self::MOCKED_GH_JOB_BOARD_URL;

		return "{$url}boards/{$token}/jobs/{$postID}?questions=true";
	}

	public static function successGreenhouseApplicationResponse() {
		return [
			'body' => '{"success":"Candidate saved successfully"}'
		];
	}

	public static function errorMissingFirstNameGreenhouseApplicationResponse() {
		return [
			'body' => '{"status":422,"error":"Invalid attributes: first_name"}'
		];
	}

	public static function errorMissingLastNameGreenhouseApplicationResponse() {
		return [
			'body' => '{"status":422,"error":"Invalid attributes: last_name"}'
		];
	}

	public static function errorMissingEmailGreenhouseApplicationResponse() {
		return [
			'body' => '{"status":422,"error":"Invalid attributes: email"}'
		];
	}

	public static function errorMissingMultipleRequiredPropsGreenhouseApplicationResponse() {
		return [
			'body' => '{"status":422,"error":"Invalid attributes: first_name,last_name,email"}'
		];
	}

	public static function errorGreenhouseApplicationResponse() {
		return [
			'body' => '{"status":401,"error":"Failed to save person"}'
		];
	}

	public static function errorMissingJobIdGreenhouseApplicationResponse() {
		return [
			'body' => []
		];
	}

	public static function getJobsMock() {
		return [
			"jobs" => [
				[
					"absolute_url" => "https://infinum.co/careers/1072422?gh_jid=1072422",
					"data_compliance" => [
						[
							"type" => "gdpr",
							"requires_consent" => false,
							"retention_period" => null
						]
					],
					"internal_job_id" => 710395,
					"location" =>  [
						"name" => "Zagreb,Varaždin,Ljubljana,Podgorica,Remote"
					],
					"metadata" => [],
					"id" => 1072422,
					"updated_at" => "2021-06-29T09:47:34-04:00",
					"requisition_id" => null,
					"title" => "Android Engineer"
				]
			]
		];
	}

	public static function getJobMock() {
		return [
			"absolute_url" => "https://infinum.co/careers/1072422?gh_jid=1072422",
			"data_compliance" => [
				[
					"type" => "gdpr",
					"requires_consent" => false,
					"retention_period" => null
				],
			],
			"internal_job_id" => 710395,
			"location" => [
				"name" => "Zagreb, Varaždin, Ljubljana, Podgorica, Remote"
			],
			"metadata" => [],
			"id" => 1072422,
			"updated_at" => "2021-06-29T09:47:34-04:00",
			"requisition_id" => null,
			"title" => "Android Engineer",
			"content" => "\u0026lt;p\u0026gt;At Infinum, we develop and design great software for both mobile and web. Our clients are large brands, banks, insurance companies, media publishers, mobile carriers, etc. Many awards prove the quality of \u0026lt;a href=\u0026quot;https://infinum.co/client-work\u0026quot; target=\u0026quot;_blank\u0026quot;\u0026gt;our work\u0026lt;/a\u0026gt;, the experts working here share their knowledge on our \u0026lt;a href=\u0026quot;https://infinum.co/the-capsized-eight\u0026quot; target=\u0026quot;_blank\u0026quot;\u0026gt;blog\u0026lt;/a\u0026gt;, whereas our \u0026lt;a href=\u0026quot;https://www.facebook.com/infinum.co\u0026quot; target=\u0026quot;_blank\u0026quot;\u0026gt;Facebook\u0026lt;/a\u0026gt;\u0026amp;nbsp;and \u0026lt;a href=\u0026quot;https://www.instagram.com/infinumco/\u0026quot;\u0026gt;Instagram \u0026lt;/a\u0026gt;show how much fun we have while doing it.\u0026lt;/p\u0026gt;\n\u0026lt;p\u0026gt;We\u0026#39;re a \u0026lt;a href=\u0026quot;https://infinum.co/people\u0026quot; target=\u0026quot;_blank\u0026quot;\u0026gt;bunch of young people\u0026lt;/a\u0026gt;, we appreciate humour, music, quality code, beautiful design and a friendly work atmosphere.\u0026amp;nbsp;\u0026amp;nbsp;\u0026lt;/p\u0026gt;\n\u0026lt;h2\u0026gt;Who are we looking for?\u0026amp;nbsp; \u0026amp;nbsp;\u0026lt;/h2\u0026gt;\n\u0026lt;p\u0026gt;Experienced Android developers who have been working on complex projects in a team, with international clients.\u0026amp;nbsp;\u0026amp;nbsp;\u0026lt;/p\u0026gt;\n\u0026lt;p\u0026gt;If you recognise yourself in some of the following things, we will be happy to receive your application. If you have:\u0026lt;/p\u0026gt;\n\u0026lt;ul\u0026gt;\n\u0026lt;li\u0026gt;at least 3 years of professional experience with Android SDK and Java\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;good knowledge of Kotlin or published apps written in Kotlin would be considered as an advantage\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;familiarity with Continuous Integration and Deployment\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;familiarity with writing clean and testable code as well as unit tests\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;experience or familiarity with some of these terms: Android Studio, Git, RxJava, Dagger 2, Retrofit\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;experience working with both local and international clients\u0026amp;nbsp;\u0026amp;nbsp;\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;a desire for research and improvement of current development processes and code architecture\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;experience working in a team environment\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;excellent English knowledge\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;a good debugging and code review routine\u0026lt;/li\u0026gt;\n\u0026lt;/ul\u0026gt;\n\u0026lt;h2\u0026gt;What can we offer?\u0026lt;/h2\u0026gt;\n\u0026lt;p\u0026gt;If you join our team, you would have a chance to work on interesting projects like \u0026lt;a href=\u0026quot;https://infinum.co/client-work/vip-mobile\u0026quot;\u0026gt;MojVIP\u0026lt;/a\u0026gt;, \u0026lt;a href=\u0026quot;https://play.google.com/store/apps/details?id=co.infinum.hpb\u0026amp;amp;hl=hr\u0026quot;\u0026gt;mHPB\u0026lt;/a\u0026gt;, \u0026lt;a href=\u0026quot;https://play.google.com/store/apps/details?id=net.ultimatedrives.app\u0026amp;amp;hl=hr\u0026quot;\u0026gt;Ultimate Drives\u0026lt;/a\u0026gt; and many more.\u0026lt;/p\u0026gt;\n\u0026lt;p\u0026gt;Also, you will be contributing to our open source libraries such as \u0026lt;a href=\u0026quot;https://github.com/infinum/Android-Prince-of-Versions\u0026quot;\u0026gt;Android Prince of Versions\u0026lt;/a\u0026gt;, \u0026lt;a href=\u0026quot;https://github.com/infinum/android-complexify\u0026quot;\u0026gt;Android Complexify\u0026lt;/a\u0026gt;,\u0026amp;nbsp;\u0026lt;a href=\u0026quot;https://github.com/infinum/thrifty-retrofit-converter\u0026quot;\u0026gt;Thrifty Retrofit Converter\u0026lt;/a\u0026gt;,\u0026amp;nbsp;\u0026lt;a href=\u0026quot;https://github.com/infinum/MjolnirRecyclerView\u0026quot;\u0026gt;Mjölnir\u0026lt;/a\u0026gt;.\u0026lt;/p\u0026gt;\n\u0026lt;p\u0026gt;To get a better picture of what we can offer you, check our \u0026lt;a href=\u0026quot;https://infinum.co/careers\u0026quot; target=\u0026quot;_blank\u0026quot;\u0026gt;Careers page\u0026lt;/a\u0026gt;.\u0026lt;/p\u0026gt;\n\u0026lt;p\u0026gt;TL;DR:\u0026lt;/p\u0026gt;\n\u0026lt;ul\u0026gt;\n\u0026lt;li\u0026gt;work with clients from all over the world, and in an awesome team of the best people in the industry\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;a competitive compensation which will depend on your own experience\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;a chance to profit from our experience\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;a chance to share your knowledge with the rest of the team as well as young colleagues\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;opportunity to travel\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;sick leave paid in full (none of that 70% nonsense)\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;sponsored English courses (if your English is not perfect, make it perfect!)\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;baby cash - a cash bonus when becoming a parent (if applicable)\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;both specialization and team switching\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;flexible working hours\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;health checks\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;\u0026lt;a href=\u0026quot;https://instagram.com/p/4ZvgTLG6Po/\u0026quot; target=\u0026quot;_blank\u0026quot;\u0026gt;dog-friendly\u0026lt;/a\u0026gt; offices\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;a chance to communicate your own ideas and bring them to life\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;working on developing \u0026lt;a href=\u0026quot;https://infinum.co/our-stuff\u0026quot;\u0026gt;internal projects\u0026lt;/a\u0026gt;\u0026lt;/li\u0026gt;\n\u0026lt;/ul\u0026gt;\n\u0026lt;h2\u0026gt;How to apply?\u0026lt;/h2\u0026gt;\n\u0026lt;p\u0026gt;If you think we can live up to your expectations and you\u0026#39;re willing to share your experience and knowledge, apply using the form below. Please send us:\u0026lt;/p\u0026gt;\n\u0026lt;ul\u0026gt;\n\u0026lt;li\u0026gt;a code sample of some of your previous work or your Github/Bitbucket profile link\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;links to published apps you have been working on\u0026lt;/li\u0026gt;\n\u0026lt;li\u0026gt;your CV\u0026lt;/li\u0026gt;\n\u0026lt;/ul\u0026gt;\n\u0026lt;p\u0026gt;Make sure to upload all of the documents (CV, cover letter, ...) in .pdf.\u0026lt;/p\u0026gt;",
			"departments" => [
				[
					"id" => 19426,
					"name" => "Android team",
					"child_ids" => [],
					"parent_id" => null
				],
			],
			"offices" => [],
			"compliance" => [],
			"demographic_questions" => null,
			"questions" => [
				[
					"description" => null,
					"label" => "First Name",
					"required" => true,
					"fields" => [
						[
							"name" => "first_name",
							"type" => "input_text",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Last Name",
					"required" => true,
					"fields" => [
						[
							"name" => "last_name",
							"type" => "input_text",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Email",
					"required" => true,
					"fields" => [
						[
							"name" => "email",
							"type" => "input_text",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Phone",
					"required" => false,
					"fields" => [
						[
							"name" => "phone",
							"type" => "input_text",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Resume/CV",
					"required" => true,
					"fields" => [
						[
							"name" => "resume",
							"type" => "input_file",
							"values" => []
						],
					[
						"name" => "resume_text",
						"type" => "textarea",
						"values" => []
					]
				]
				],
				[
					"description" => null,
					"label" => "Cover Letter",
					"required" => false,
					"fields" => [
						[
							"name" => "cover_letter",
							"type" => "input_file",
							"values" => []
						],
						[
							"name" => "cover_letter_text",
							"type" => "textarea",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Education",
					"required" => false,
					"fields" => [
						[
							"name" => "question_8814744",
							"type" => "input_text",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Something about yourself",
					"required" => false,
					"fields" => [
						[
							"name" => "question_8814745",
							"type" => "textarea",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Links to your Github, Bitbucket, Twitter, Dribbble, Behance, ...",
					"required" => false,
					"fields" => [
						[
							"name" => "question_8814746",
							"type" => "textarea",
							"values" => []
						]
					]
				],
				[
					"description" => "\u003cp\u003econfig_location_field\u003c/p\u003e",
					"label" => "Preferred location of employment",
					"required" => true,
					"fields" => [
						[
							"name" => "question_8814786",
							"type" => "multi_value_single_select",
							"values" => [
								[
									"label" => "Zagreb",
									"value" => 17769183
								],
								[
									"label" => "Varaždin",
									"value" => 17769184
								],
								[
									"label" => "Ljubljana",
									"value" => 17769185
								],
								[
									"label" => "Podgorica",
									"value" => 73254404
								],
								[
									"label" => "Remote",
									"value" => 17769186
								],
							]
						]
					]
				],
				[
					"description" => "\u003cp\u003econfig_origin_field\u003c/p\u003e",
					"label" => "How did you hear about this job?",
					"required" => true,
					"fields" => [
						[
							"name" => "question_8814747",
							"type" => "multi_value_single_select",
							"values" => [
								[
										"label" => "Facebook-7fik0cey1",
									"value" => 17769607
								],
								[
									"label" => "LinkedIn-l1s7ifva1",
									"value" => 17769608
								],
								[
									"label" => "Twitter-6jtrccfo1",
									"value" => 17769609
								],
								[
									"label" => "Instagram-gyrctxas1",
									"value" => 17769610
								],
								[
									"label" => "Stack Overflow-wobdmcuk1",
									"value" => 17769611
								],
								[
									"label" => "GitHub-2f41dpro1",
									"value" => 17769612
								],
								[
									"label" => "Reddit-300ba3dd1",
									"value" => 24613918
								],
								[
									"label" => "Infinum website",
									"value" => 17769484
								],
								[
									"label" => "From a friend",
									"value" => 17769485
								],
								[
									"label" => "Other",
									"value" => 17769486
								]
							]
						]
					]
				],
				[
					"description" => null,
					"label" => "I want you to keep my information for all future positions I might be fit for. If something interesting pops up, send me an e-mail. The data will be kept for five years. ",
					"required" => false,
					"fields" => [
						[
							"name" => "question_9981025",
							"type" => "multi_value_single_select",
							"values" => [
								[
									"label" => "No",
									"value" => 0
								],
								[
									"label" => "Yes",
									"value" => 1
								]
							]
						]
					]
				],
				[
					"description" => "\u003cp\u003eincluded\u003c/p\u003e",
					"label" => "included",
					"required" => false,
					"fields" => [
						[
							"name" => "question_19555577",
							"type" => "multi_value_single_select",
							"values" => [
								[
									"label" => "No",
									"value" => 0
								],
								[
									"label" => "Yes",
									"value" => 1
								]
							]
						]
					]
				]
			],
			"location_questions" => [
				[
					"description" => null,
					"label" => "Location",
					"required" => true,
					"fields" => [
						[
							"name" => "location",
							"type" => "input_text",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Latitude",
					"required" => true,
					"fields" => [
						[
							"name" => "latitude",
							"type" => "input_hidden",
							"values" => []
						]
					]
				],
				[
					"description" => null,
					"label" => "Longitude",
					"required" => true,
					"fields" => [
						[
							"name" => "longitude",
							"type" => "input_hidden",
							"values" => [],
						]
					]
				]
			]
		];
	}

	public static function getJobsFullMock() {
		return json_encode([
			[
				"id" => "1072422",
				"title" => "Android Engineer",
				"locations" => [
					"Zagreb",
					"Vara\u017edin",
					"Ljubljana",
					"Podgorica",
					"Remote"
				],
				"questions" => [
					[
						"label" => "First Name",
						"required" => true,
						"name" => "first_name",
						"id" => "first_name",
						"description" => "",
						"type" => "input",
						"options" => [],
						"width" => "6"
					],
					[
						"label" => "Last Name",
						"required" => true,
						"name" => "last_name",
						"id" => "last_name",
						"description" => "",
						"type" => "input",
						"options" => [],
						"width" => "6"
					],
					[
						"label" => "Email",
						"required" => true,
						"name" => "email",
						"id" => "email",
						"description" => "",
						"type" => "input",
						"options" => [],
						"width" => "6"
					],
					[
						"label" => "Phone",
						"required" => false,
						"name" => "phone",
						"id" => "phone",
						"description" => "",
						"type" => "input",
						"options" => [],
						"width" => "6"
					],
					[
						"label" => "Resume\/CV",
						"required" => true,
						"name" => "resume",
						"id" => "resume",
						"description" => "",
						"type" => "file",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Resume\/CV",
						"required" => true,
						"name" => "resume_text",
						"id" => "resume_text",
						"description" => "",
						"type" => "textarea",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Cover Letter",
						"required" => false,
						"name" => "cover_letter",
						"id" => "cover_letter",
						"description" => "",
						"type" => "file",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Cover Letter",
						"required" => false,
						"name" => "cover_letter_text",
						"id" => "cover_letter_text",
						"description" => "",
						"type" => "textarea",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Education",
						"required" => false,
						"name" => "question_8814744",
						"id" => "question_8814744",
						"description" => "",
						"type" => "input",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Something about yourself",
						"required" => false,
						"name" => "question_8814745",
						"id" => "question_8814745",
						"description" => "",
						"type" => "textarea",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Links to your Github,
						Bitbucket,
						Twitter,
						Dribbble,
						Behance,
						...",
						"required" => false,
						"name" => "question_8814746",
						"id" => "question_8814746",
						"description" => "",
						"type" => "textarea",
						"options" => [],
						"width" => "12"
					],
					[
						"label" => "Preferred location of employment",
						"required" => true,
						"name" => "question_8814786",
						"id" => "question_8814786",
						"description" => "config_location_field",
						"type" => "select",
						"options" => [
							[
								"label" => "Zagreb",
								"value" => 17769183
							],
							[
								"label" => "Vara\u017edin",
								"value" => 17769184
							],
							[
								"label" => "Ljubljana",
								"value" => 17769185
							],
							[
								"label" => "Podgorica",
								"value" => 73254404
							],
							[
								"label" => "Remote",
								"value" => 17769186
							]
						],
						"width" => "12"
					],
					[
						"label" => "How did you hear about this job?",
						"required" => true,
						"name" => "question_8814747",
						"id" => "question_8814747",
						"description" => "config_origin_field",
						"type" => "select",
						"options" => [
							[
								"label" => "Facebook-7fik0cey1",
								"value" => 17769607
							],
							[
								"label" => "LinkedIn-l1s7ifva1",
								"value" => 17769608
							],
							[
								"label" => "Twitter-6jtrccfo1",
								"value" => 17769609
							],
							[
								"label" => "Instagram-gyrctxas1",
								"value" => 17769610
							],
							[
								"label" => "Stack Overflow-wobdmcuk1",
								"value" => 17769611
							],
							[
								"label" => "GitHub-2f41dpro1",
								"value" => 17769612
							],
							[
								"label" => "Reddit-300ba3dd1",
								"value" => 24613918
							],
							[
								"label" => "Infinum website",
								"value" => 17769484
							],
							[
								"label" => "From a friend",
								"value" => 17769485
							],
							[
								"label" => "Other",
								"value" => 17769486
							]
						],
						"width" => "12"
					],
					[
						"label" => "I want you to keep my information for all future positions I might be fit for. If something interesting pops up,
						send me an e-mail. The data will be kept for five years. ",
						"required" => false,
						"name" => "question_9981025",
						"id" => "question_9981025",
						"description" => "",
						"type" => "checkbox",
						"options" => [
							[
								"label" => "No",
								"value" => 0
							],
							[
								"label" => "Yes",
								"value" => 1
							]
						],
						"width" => "12"
					],
					[
						"label" => "included",
						"required" => false,
						"name" => "question_19555577",
						"id" => "question_19555577",
						"description" => "included",
						"type" => "checkbox",
						"options" => [
							[
								"label" => "No",
								"value" => 0
							],
							[
								"label" => "Yes",
								"value" => 1
							]
						],
						"width" => "12"
					],
				],
			],
		]);
	}

	public static function getJobsResponseMock() {
		return [
			'body' => json_encode(self::getJobsMock()),
		];
	}

	public static function getJobResponseMock() {
		return [
			'body' => json_encode(self::getJobMock()),
		];
	}
}
