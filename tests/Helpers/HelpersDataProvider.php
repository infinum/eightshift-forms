<?php

namespace EightshiftFormsTests\Helpers;

class HelpersDataProvider
{
	/**
	 * Example of "Parsed blocks content" response from Buckaroo.
	 *
	 * @return array
	 */
  public function parsedBlocksMock() {
    return [
      [
          'blockName' => 'eightshift-forms/form',
          'attrs' => [
              'classes' => 'some-class',
              'type' => 'buckaroo',
              'buckarooEmandateDescription' => 'Asdasd',
              'buckarooRedirectUrl' => 'http://dev.d66.test/buckaroo-testing/',
              'buckarooRedirectUrlCancel' => 'http://dev.d66.test/buckaroo-testing/cancel',
              'buckarooRedirectUrlError' => 'http://dev.d66.test/buckaroo-testing/',
              'buckarooRedirectUrlReject' => 'http://dev.d66.test/buckaroo-testing/',
              'eventNames' => [
                  'aaa',
                  'bbb',
              ],
              'theme' => 'dark',
          ],
          'innerBlocks' => [
              [
                'blockName' => 'd66/fields-group',
                'attrs' => [
                    'label' => 'Some details',
                    'theme' => 'dark',
                ],
                'innerBlocks' => [
                      [
                        'blockName' => 'd66/input',
                        'attrs' => [
                            'name' => 'email',
                            'label' => 'Email',
                            'tooltip' => 'gfhfhfghfgh',
                            'theme' => 'dark',
                        ],
                        'innerBlocks' => [],
                        'innerHTML' => '',
                        'innerContent' => [],
                    ],
                    [
                        'blockName' => 'd66/select-fields-group',
                        'attrs' => [
                            'name' => 'issuer',
                            'prefillData' => true,
                            'prefillDataSource' => 'buckarooBanksFromCrm',
                            'theme' => 'dark',
                        ],
                        'innerBlocks' => [],
                        'innerHTML' => '',
                        'innerContent' => [],
                    ],
                ],
                  'innerHTML' => '',
                  'innerContent' => [
                      '',
                  ],
              ],
              [
                  'blockName' => 'd66/donation-amount',
                  'attrs' => [
                      'label' => 'Donation amount',
                      'theme' => 'dark',
                  ],
                  'innerBlocks' => [
                      [
                          'blockName' => 'd66/donation-amount-item',
                          'attrs' => [
                              'name' => 'donation-amount',
                              'value' => '5',
                              'label' => '5',
                              'isChecked' => true,
                              'theme' => 'dark',
                          ],
                          'innerBlocks' => [
                          ],
                          'innerHTML' => '',
                          'innerContent' => [
                          ],
                      ],
                      [
                          'blockName' => 'd66/donation-amount-item',
                          'attrs' => [
                              'name' => 'donation-amount',
                              'value' => '10',
                              'label' => '10',
                              'theme' => 'dark',
                          ],
                          'innerBlocks' => [
                          ],
                          'innerHTML' => '',
                          'innerContent' => [
                          ],
                      ],
                      [
                          'blockName' => 'd66/donation-amount-item',
                          'attrs' => [
                              'name' => 'donation-amount',
                              'value' => '20',
                              'label' => '20',
                              'theme' => 'dark',
                          ],
                          'innerBlocks' => [
                          ],
                          'innerHTML' => '',
                          'innerContent' => [
                          ],
                      ],
                      [
                          'blockName' => 'd66/donation-amount-item',
                          'attrs' => [
                              'name' => 'donation-amount',
                              'isCustomValue' => true,
                              'theme' => 'dark',
                          ],
                          'innerBlocks' => [
                          ],
                          'innerHTML' => '',
                          'innerContent' => [
                          ],
                      ],
                  ],
                  'innerHTML' => '',
                  'innerContent' => [
                      '',
                      '',
                      '',
                      '',
                      '',
                  ],
              ],
              [
                  'blockName' => 'd66/input',
                  'attrs' => [
                      'name' => 'test',
                      'value' => '1',
                      'type' => 'hidden',
                      'theme' => 'dark',
                  ],
                  'innerBlocks' => [],
                  'innerHTML' => '',
                  'innerContent' => [],
              ],
              [
                  'blockName' => 'd66/input',
                  'attrs' => [
                      'name' => 'list-id',
                      'value' => 'eb7fd0b84a',
                      'type' => 'hidden',
                      'label' => 'ONLY DURING TESTING, IT NEEDS TO WORKING WITHOUT THIS',
                      'theme' => 'dark',
                  ],
                  'innerBlocks' => [],
                  'innerHTML' => '',
                  'innerContent' => [],
              ],
              [
                  'blockName' => 'eightshift-forms/submit',
                  'attrs' => [
                      'name' => 'submit',
                      'theme' => 'dark',
                  ],
                  'innerBlocks' => [],
                  'innerHTML' => '',
                  'innerContent' => [],
              ],
          ],
          'innerHTML' => '',
          'innerContent' => [
              '',
          ],
      ],
    ];
  }
}