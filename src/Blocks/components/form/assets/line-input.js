import autosize from 'autosize';
import Choices from 'choices.js';
import Dropzone from "dropzone";

Dropzone.autoDiscover = false;

export class LineInput {
	constructor(options) {
		this.formSelector = options.formSelector;

		this.fieldSelector = `${this.formSelector}-field`;
		this.inputSelector = `${this.fieldSelector} input`;
		this.textareaSelector = `${this.fieldSelector} textarea`;
		this.selectSelector = `${this.fieldSelector} select`;
		this.fileSelector = `${this.fieldSelector} input[type="file"]`;

		this.CLASS_ACTIVE = 'is-active';
		this.CLASS_FILLED = 'is-filled';
		this.CLASS_UPLOAD_LABEL = 'is-upload-label';
		this.CLASS_UPLOAD_FIELD = 'is-upload-field';

		this.textareaCustom = options.textareaCustom ?? true;
		this.selectCustom = options.selectCustom ?? true;
		this.fileCustom = options.fileCustom ?? true;
	}

	// Run everything on init.
	init() {
		const elements = document.querySelectorAll(this.formSelector);

		[...elements].forEach((element) => {
			const inputs = element.querySelectorAll(this.inputSelector);
			const textareas = element.querySelectorAll(this.textareaSelector);
			const selects = element.querySelectorAll(this.selectSelector);
			const files = element.querySelectorAll(this.fileSelector);

			// Setup regular inputs.
			[...inputs].forEach((input) => {
				this.setupInputField(input);
			});
	
			// Setup select inputs.
			[...selects].forEach((select) => {
				this.setupSelectField(select);
			});
	
			// Setup textarea inputs.
			[...textareas].forEach((textarea) => {
				this.setupTextareaField(textarea);
			});

			// Setup file single inputs.
			[...files].forEach((file) => {
				// this.setupFileField(file);
			});
		});
	}
	// Setup Regular field.
	setupInputField = (input) => {
		this.preFillOnInit(input);

		input.addEventListener('focus', this.onFocusEvent);
		input.addEventListener('blur', this.onBlurEvent);
	}

	// Setup Select field.
	setupSelectField = (select) => {
		const option = select.querySelector('option');

		this.preFillOnInit(option);
		if (this.selectCustom === '1') {
			new Choices(select, {
				searchEnabled: false,
				shouldSort: false,
				placeholderValue: 'Choose'
			});
	
			select.closest('.choices').addEventListener('focus', this.onFocusEvent);
			select.closest('.choices').addEventListener('blur', this.onBlurEvent);
		} else {
			select.addEventListener('focus', this.onFocusEvent);
			select.addEventListener('blur', this.onBlurEvent);
		}

	}

	// Setup Textarea field.
	setupTextareaField = (textarea) => {
		this.preFillOnInit(textarea);

		textarea.addEventListener('focus', this.onFocusEvent);
		textarea.addEventListener('blur', this.onBlurEvent);

		if (this.textareaCustom === '1') {
			textarea.setAttribute('rows', '');
			textarea.setAttribute('cols', '');

			autosize(textarea);
		}
	}

	// Setup file single field.
	setupFileField = (file) => {
		if (this.fileCustom === '1') {
			const button = file.nextElementSibling;
			const list = file.nextElementSibling.nextElementSibling;

			button.addEventListener('click', (event) => {
				event.preventDefault();
				file.click();
			});

			file.addEventListener('change', (event) => {

				list.innerHTML = '';

				[...event.target.files].forEach((item) => {
					const image = URL.createObjectURL(item);
					list.innerHTML += `<div><img src="${image}" /> <span>${item.name}</span> <span>${item.size}</span></div>`;
				});
			});
		}
	}

	// // Prefill inputs active/filled on init.
	preFillOnInit = (input) => {
		if (input.type === 'checkbox' || input.type === 'radio') {
			if (input.checked) {
				input.closest(this.fieldSelector).classList.add(this.CLASS_FILLED);
			}
		} else {
			if (input.value && input.value.length) {
				input.closest(this.fieldSelector).classList.add(this.CLASS_FILLED);
			}
		}
	}

	// On Focus event for regular fields.
	onFocusEvent = (event) => {
		event.target.closest(this.fieldSelector).classList.add(this.CLASS_ACTIVE);
	}

	// On Blur generic method. Check for length of value.
	onBlurEvent = (event) => {
		const element = event.target;
		const field = element.closest(this.fieldSelector);

		let toCheck = element;
		let condition = false;

		switch (element.type) {
			case 'radio':
				condition = element.checked;
				break;
			case 'checkbox':
				condition = field.querySelectorAll('input:checked').length;
				break;
			case 'select':
				toCheck = element.options[element.options.selectedIndex];

				condition = toCheck.value && toCheck.value.length;
				break;
			default:
				condition = element.value && element.value.length;
				break;
		}

		if (condition) {
			field.classList.remove(this.CLASS_ACTIVE);
			field.classList.add(this.CLASS_FILLED);
		} else {
			field.classList.remove(this.CLASS_ACTIVE, this.CLASS_FILLED);
		}
	}

	// // On single upload change event.
	// onFileChangeEvent = (event) => {
	// 	const content = document.createElement('div');
	// 	content.className = "ginput_preview";
	// 	content.innerHTML = `
	// 		<button class="gform_delete_file js-upload-single-delete"></button>
	// 		<strong>${event.target?.files[0]?.name}</strong>
	// 	`;

	// 	const oldContainer = event.target.parentElement.querySelector("[id^='gform_preview_']");
	// 	const newContainer = event.target.parentElement.querySelector(".ginput_preview");

	// 	if (oldContainer !== null) {
	// 		oldContainer.remove();
	// 	}

	// 	if (newContainer !== null) {
	// 		newContainer.remove();
	// 	}

	// 	event.target.parentElement.appendChild(content);

	// 	const deleteSelector = event.target.parentElement.querySelector(".js-upload-single-delete");

	// 	if (deleteSelector !== null) {
	// 		deleteSelector.addEventListener('click', this.onFileDeleteEvent);
	// 	}
	// }

	// // On single upload delete button.
	// onFileDeleteEvent = (event) => {
	// 	event.preventDefault();
	// 	event.target.parentElement.parentElement.querySelector("input[type='file']").value = "";
	// 	event.target.parentElement.remove();
	// }
}
