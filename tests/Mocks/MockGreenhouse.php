<?php

declare(strict_types=1);

namespace EightshiftFormsTests\Mocks;

use EightshiftForms\Integrations\Greenhouse\Greenhouse;
use EightshiftFormsTests\Integrations\Greenhouse\DataProvider;

class MockGreenhouse extends Greenhouse
{
	public function getConfirmationName(): string
	{
		return 'Eightshift';
	}

	public function getConfirmationEmail(): string
	{
		return 'info@eightshift.com';
	}

	public function getConfirmationSubject(): string
	{
		return 'Eightshift Subject';
	}

	public function getFallbackEmail(): string
	{
		return 'fallback@eightshift.com';
	}

	public function getFallbackSubject(): string
	{
		return 'Eightshift Fallback Subject';
	}

	public function getBoardToken(): string
	{
		return DataProvider::MOCKED_GH_BOARD_TOKEN;
	}

	public function getJobBoardUrl(): string
	{
		return DataProvider::MOCKED_GH_JOB_BOARD_URL;
	}

	public function getApiKey(): string
	{
		return base64_encode('testApiKey');
	}
}
