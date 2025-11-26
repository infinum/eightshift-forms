![image](https://repository-images.githubusercontent.com/204961525/1788eeba-8046-47bf-b315-32b060db1c8e)

[![GitHub tag](https://img.shields.io/github/tag/infinum/eightshift-forms.svg?style=for-the-badge)](https://github.com/infinum/eightshift-forms)
[![GitHub stars](https://img.shields.io/github/stars/infinum/eightshift-forms.svg?style=for-the-badge&label=Stars)](https://github.com/infinum/eightshift-forms)

# Eightshift Forms Plugin

Eightshift forms plugins is a complete form builder tool that utilizes modern Block editor features with multiple third-party integrations to boost your project to another level.

## ⚠️ Requirements
To get started, you'll need:

* [Node LTS](https://nodejs.org/)
* [Composer](https://getcomposer.org/)
* [Bun](https://bun.sh/)
* [git](https://git-scm.com/)
* [WP-CLI](https://wp-cli.org/)

## 🏁 Quick start

Clone the project + build to get started:

1. `git clone git@github.com:infinum/eightshift-forms.git`
2. `composer install`
3. `bun install`
4. `bun start`

## 📚 Documentation

Eightshift forms documentation is located [here](docs/README.md).

Eightshift forms plugin is created on the [Eightshift development kit](https://eightshift.com).

## 🧪 Testing

All e2d tests are run using Playwright and are located in the `tests/e2e` folder and are run in isolated environment using WP Playground with predefined dataset.

URL structure is:
* `http://127.0.0.1:9400/tests/basic`
* `http://127.0.0.1:9400/tests/validation`
* etc.

To run the tests locally using WP Playground:
1. `bun run test:e2e:playground` - creates a new WP Playground instance and imports the dataset.
2. `bun run test:e2e:ui` or `bun run test:e2e` - runs the tests in the browser UI or headless mode.
3. `bun run test:e2e:report:show` - shows the test report.
4. `bun run test:e2e:report:pdf` - generates a PDF report of the tests.

Setting up custom test environment:
1. Create a new WordPress installation.
2. Install the Eightshift Forms plugin.
3. Check the `options` for the forms set in the `tests/e2e/playground/playground.json` file.
4. Import the dataset from the `tests/e2e/playground/dataset.xml` file, make sure you have a clean installation before importing the dataset as the page/forms ID increments remains the same as in the dataset.
6. Run `ES_URL=https://<test-environment>/ bun run test:e2e:ui` to run the tests.

Available environment variables:
* `ES_URL`: URL of the test environment. Required.
* `ES_CLASS`: Class name to be added to the body tag for additional styling if needed. Default is `es-forms-tests`.

## 🛟 Getting help

If you have any questions or problems, please [open an issue](https://github.com/infinum/eightshift-forms/issues) on GitHub. 

When submitting issues or otherwise participating in development, please follow our [code of conduct](https://github.com/infinum/eightshift-forms/blob/develop/CODE_OF_CONDUCT.md), and fill out the issue template properly. We'll do our best to answer your issues as quickly as humanly possible: following these steps helps us out a lot in doing that.

## 👩‍💻 Maintainers 🧑‍💻 
Eightshift Forms is maintained and sponsored by [Infinum](https://infinum.com).

## ⚖️ License
Eightshift Forms by [Infinum](https://infinum.com). It is free software, and may be redistributed under the terms specified in the LICENSE file.
