<?php

// Used in "convertInnerBlocksToArray returns a properly sorted array of options for select" test.
dataset('inner block markup and expected options', [
	[
		[
			[
				'label' => ' First',
				'value' => '1',
				'original' => '<option value="1" > First</option>'
			],
			[
				'label' => ' Second',
				'value' => '2',
				'original' => '<option value="2" > Second</option>'
			]
		],
		'<select><option value="1" > First</option><option value="2" > Second</option></select>'
	],
	[
		[], ''
	],
	[
		[
			[
				'label' => 'First',
				'value' => '1',
				'original' => '<option value="1">First</option>'
			],
			[
				'label' => 'Second',
				'value' => '2',
				'original' => '<option value="2">Second</option>'
			],
			[
				'label' => ' Third option ',
				'value' => '3',
				'original' => '<option id="third-option" value="3" aria-hidden="true">  Third  option  </ option>'
			]
		],
		'
			<select>
				<option value="1">First</option>
				<option value="2">Second</option>
				<option id="third-option" value="3" aria-hidden="true">  Third  option  </ option>
			</select>
		',
	],
]);
