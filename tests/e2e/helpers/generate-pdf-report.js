#!/usr/bin/env node

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

async function generatePDFReport() {
	const projectRoot = path.join(__dirname, '..', '..', '..');
	const reportDir = path.join(projectRoot, 'playwright-report');
	const reportHtml = path.join(reportDir, 'index.html');
	const outputPdf = path.join(reportDir, 'playwright-report.pdf');

	// Check if HTML report exists
	if (!fs.existsSync(reportHtml)) {
		console.error('Error: HTML report not found. Please run tests first to generate the report.');
		console.error(`Expected location: ${reportHtml}`);
		process.exit(1);
	}

	// Check if existing PDF report exists and delete it
	if (fs.existsSync(outputPdf)) {
		console.log('Existing PDF report found. Deleting it...');

		try {
			fs.unlinkSync(outputPdf);
			console.log('✅ Existing PDF report deleted.');
		} catch (error) {
			console.error('Error deleting existing PDF report:', error);
			process.exit(1);
		}
	}

	console.log('Generating PDF report...');
	console.log(`Reading from: ${reportHtml}`);
	console.log(`Output will be saved to: ${outputPdf}`);

	const browser = await chromium.launch();
	const page = await browser.newPage();

	try {
		// Load the HTML report
		// Use file:// protocol for local files
		const fileUrl = `file://${reportHtml}`;
		await page.goto(fileUrl, { waitUntil: 'networkidle' });

		// Wait a bit for any dynamic content to load
		await page.waitForTimeout(2000);

		// Generate PDF
		await page.pdf({
			path: outputPdf,
			format: 'A4',
			printBackground: true,
			margin: {
				top: '20mm',
				right: '15mm',
				bottom: '20mm',
				left: '15mm',
			},
		});

		console.log(`✅ PDF report generated successfully: ${outputPdf}`);
	} catch (error) {
		console.error('Error generating PDF:', error);
		process.exit(1);
	} finally {
		await browser.close();
	}
}

generatePDFReport();

